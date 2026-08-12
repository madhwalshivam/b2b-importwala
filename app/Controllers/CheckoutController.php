<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Core\Database;
use App\Services\NotificationService;
use App\Services\CouponService;
use App\Services\Payment\RazorpayGateway;
use App\Services\Shipping\ShiprocketProvider;

class CheckoutController extends Controller {

    public function index(): string {
        // 1. Check if Cart is empty
        if (empty($_SESSION['cart'])) {
            $this->setFlash('error', 'Your cart is empty.');
            $this->redirect(url('shop'));
            return '';
        }

        // 2. MANDATORY LOGIN CHECK BEFORE CHECKOUT
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Please login to continue with checkout.');
            $this->redirect(url('login?return=checkout'));
            return '';
        }

        $userId = (int)$_SESSION['user_id'];
        $cartData = $this->getCartData();

        if (!empty($cartData['has_out_of_stock'])) {
            $this->setFlash('error', 'Some items in your cart are out of stock or exceed available quantity. Please update your cart before checkout.');
            $this->redirect(url('cart'));
            return '';
        }

        // 3. Fetch Saved Address for logged-in user
        $userAddressModel = new UserAddress();
        $savedAddress = $userAddressModel->getDefaultAddress($userId);

        // Fetch User Info fallback from session
        $userData = [
            'name'  => $_SESSION['user_name']  ?? ($savedAddress['full_name'] ?? ''),
            'email' => $_SESSION['user_email'] ?? ($savedAddress['email'] ?? ''),
            'phone' => $_SESSION['user_phone'] ?? ($savedAddress['phone'] ?? '')
        ];

        return $this->render('storefront/checkout', [
            'cart'         => $cartData,
            'savedAddress' => $savedAddress,
            'userData'     => $userData
        ]);
    }

    public function processCheckout(): void {
        if (empty($_SESSION['cart'])) {
            $this->setFlash('error', 'Your cart is empty.');
            $this->redirect(url('shop'));
            return;
        }

        // MANDATORY LOGIN CHECK BEFORE CHECKOUT
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Please login to continue with checkout.');
            $this->redirect(url('login?return=checkout'));
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $cartData = $this->getCartData();
        if (empty($cartData['items'])) {
            $this->setFlash('error', 'Your cart is empty.');
            $this->redirect(url('shop'));
            return;
        }

        $name         = trim((string)$this->request->input('name', $this->request->input('full_name', '')));
        $email        = trim((string)$this->request->input('email', ''));
        $phone        = trim((string)$this->request->input('phone', ''));
        $addressLine1 = trim((string)$this->request->input('address_line1', ''));
        $addressLine2 = trim((string)$this->request->input('address_line2', ''));
        $landmark     = trim((string)$this->request->input('landmark', ''));
        $city         = trim((string)$this->request->input('city', ''));
        $state        = trim((string)$this->request->input('state', ''));
        $pincode      = trim((string)$this->request->input('pincode', ''));
        $country      = trim((string)$this->request->input('country', 'India'));
        if (empty($country)) $country = 'India';

        $orderNotes    = trim((string)$this->request->input('order_notes', ''));
        $paymentMethod = strtolower((string)$this->request->input('payment_method', 'cod'));
        $companyName   = (string)$this->request->input('company_name', '');
        $gstin         = (string)$this->request->input('gstin', '');

        // Server-Side Validation
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Full Name is required.';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
        }
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone) || strlen($cleanPhone) < 10) {
            $errors[] = 'Please provide a valid 10-digit mobile number.';
        }
        if (empty($addressLine1)) {
            $errors[] = 'Address Line 1 is required.';
        }
        if (empty($city)) {
            $errors[] = 'City is required.';
        }
        if (empty($state)) {
            $errors[] = 'State is required.';
        }
        $cleanPincode = preg_replace('/[^0-9]/', '', $pincode);
        if (empty($pincode) || strlen($cleanPincode) !== 6) {
            $errors[] = 'Please provide a valid 6-digit Pincode.';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect(url('checkout'));
            return;
        }

        // Save / Update Address in Database for future checkouts
        $userAddressModel = new UserAddress();
        $userAddressModel->saveOrUpdate($userId, [
            'full_name'     => $name,
            'phone'         => $cleanPhone,
            'email'         => $email,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'landmark'      => $landmark,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $cleanPincode,
            'country'       => $country
        ]);

        // Address Snapshot for Order Record
        $addressSnapshot = [
            'full_name'     => $name,
            'phone'         => $cleanPhone,
            'email'         => $email,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'landmark'      => $landmark,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $cleanPincode,
            'country'       => $country
        ];

        $db = Database::getInstance();
        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber();

        try {
            // STEP 1: Begin Transaction
            $db->beginTransaction();

            // STEP 2: Stock Lock Verification
            $stmtLock = $db->prepare("SELECT id, name, stock FROM products WHERE id = ? FOR UPDATE");
            foreach ($cartData['items'] as $item) {
                $stmtLock->execute([$item['id']]);
                $prod = $stmtLock->fetch(\PDO::FETCH_ASSOC);
                if (!$prod || (int)$prod['stock'] < (int)$item['quantity']) {
                    $db->rollBack();
                    $this->setFlash('error', "Sorry, '{$item['name']}' has insufficient stock. Available: " . ($prod['stock'] ?? 0));
                    $this->redirect(url('cart'));
                    return;
                }
            }

            // STEP 3: Create Order Record with customer_id (Payment status ALWAYS starts as 'pending')
            $orderId = $orderModel->insert([
                'order_number'    => $orderNumber,
                'customer_id'     => $userId,
                'customer_name'   => $name,
                'customer_email'  => $email,
                'customer_phone'  => $cleanPhone,
                'shipping_address'=> json_encode($addressSnapshot),
                'company_name'    => $companyName,
                'gstin'           => $gstin,
                'subtotal'        => $cartData['subtotal'],
                'tax_total'       => $cartData['tax_total'],
                'shipping_charge' => $cartData['shipping_charge'],
                'discount_amount' => $cartData['discount'],
                'coupon_code'     => $cartData['coupon_code'],
                'total_amount'    => $cartData['grand_total'],
                'payment_method'  => $paymentMethod,
                'payment_status'  => 'pending',
                'order_status'    => ($paymentMethod === 'cod') ? 'confirmed' : 'pending',
                'notes'           => $orderNotes
            ]);

            // STEP 4: Insert Order Items & Decrement Stock
            $stmtItem = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, sku, hsn_code, price, tax_percent, tax_amount, quantity, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            $itemSummaryNames = [];
            foreach ($cartData['items'] as $item) {
                $unitPrice = (float)($item['price'] ?? 0);
                $taxPercent = (float)($item['tax_percent'] ?? 18);
                $qty = (int)($item['quantity'] ?? 1);
                $hsnCode = !empty($item['hsn_code']) ? $item['hsn_code'] : '8714.99.90';

                // GST INCLUSIVE Line Calculation
                $unitBase = $unitPrice / (1 + ($taxPercent / 100));
                $unitGst = $unitPrice - $unitBase;
                $lineTax = $unitGst * $qty;
                $lineTotal = $unitPrice * $qty; // Inclusive Total

                $stmtItem->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['sku'],
                    $hsnCode,
                    $unitPrice,
                    $taxPercent,
                    $lineTax,
                    $qty,
                    $lineTotal
                ]);
                $stmtStock->execute([$qty, $item['id']]);
                $itemSummaryNames[] = "{$item['name']} (x{$qty})";
            }

            // Commit Transaction
            $db->commit();

            // STEP 5: Record Coupon Usage
            if (!empty($cartData['discount']) && !empty($_SESSION['applied_coupon']['coupon_id'])) {
                CouponService::recordUsage(
                    (int)$_SESSION['applied_coupon']['coupon_id'],
                    $userId,
                    session_id(),
                    $orderId,
                    (float)$cartData['discount']
                );
            }

            // STEP 6: Shiprocket Integration (Auto-push order to Shiprocket)
            try {
                CheckoutApiController::pushOrderToShiprocket($orderId);
            } catch (\Throwable $ste) {
                error_log("Shiprocket push warning: " . $ste->getMessage());
            }

            // STEP 7: Trigger Notifications
            NotificationService::trigger('New Order Placed', [
                'user_name'    => $name,
                'user_email'   => $email,
                'user_phone'   => $cleanPhone,
                'product_name' => implode(', ', $itemSummaryNames),
                'quantity'     => $cartData['item_count'],
                'price'        => number_format((float)$cartData['grand_total'], 2)
            ]);

            // Clear Cart Session
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);

            $this->redirect(url("order-success/{$orderNumber}"));

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Checkout Failure: " . $e->getMessage());
            $this->setFlash('error', 'Checkout processing failed: ' . $e->getMessage());
            $this->redirect(url('checkout'));
        }
    }

    public function saveAddressAjax(): void {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to save address.']);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $name         = trim((string)$this->request->input('name', $this->request->input('full_name', '')));
        $email        = trim((string)$this->request->input('email', ''));
        $phone        = trim((string)$this->request->input('phone', ''));
        $addressLine1 = trim((string)$this->request->input('address_line1', ''));
        $addressLine2 = trim((string)$this->request->input('address_line2', ''));
        $landmark     = trim((string)$this->request->input('landmark', ''));
        $city         = trim((string)$this->request->input('city', ''));
        $state        = trim((string)$this->request->input('state', ''));
        $pincode      = trim((string)$this->request->input('pincode', ''));
        $country      = trim((string)$this->request->input('country', 'India'));

        $cleanPhone   = preg_replace('/[^0-9]/', '', $phone);
        $cleanPincode = preg_replace('/[^0-9]/', '', $pincode);

        if (empty($name) || empty($email) || strlen($cleanPhone) < 10 || empty($addressLine1) || empty($city) || empty($state) || strlen($cleanPincode) !== 6) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required address fields correctly.']);
            exit;
        }

        $userAddressModel = new UserAddress();
        $id = $userAddressModel->saveOrUpdate($userId, [
            'full_name'     => $name,
            'phone'         => $cleanPhone,
            'email'         => $email,
            'address_line1' => $addressLine1,
            'address_line2' => $addressLine2,
            'landmark'      => $landmark,
            'city'          => $city,
            'state'         => $state,
            'pincode'       => $cleanPincode,
            'country'       => $country
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Address saved successfully!',
            'address_id' => $id
        ]);
        exit;
    }

    public function success(string $orderNumber): string {
        $orderModel = new Order();
        $order = $orderModel->findBy('order_number', $orderNumber);

        if (!$order) {
            $this->redirect(url('/'));
        }

        $items = $orderModel->getOrderItems($order['id']);
        $shippingAddress = json_decode($order['shipping_address'] ?? '{}', true);

        return $this->render('storefront/order_success', [
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress
        ]);
    }

    private function getCartData(): array {
        $cartController = new CartController();
        $ref = new \ReflectionClass($cartController);
        $method = $ref->getMethod('getCart');
        $method->setAccessible(true);
        return $method->invoke($cartController);
    }
}
