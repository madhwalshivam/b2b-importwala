<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class SupportController extends BaseController
{
    public function index(): void
    {
        $this->renderView('web/support', [
            'pageTitle' => 'Help Center & FAQs | ImportWale Wholesale Support'
        ]);
    }

    public function contact(): void
    {
        $this->renderView('web/contact', [
            'pageTitle' => 'Contact Support & B2B Inquiry | ImportWale Wholesale'
        ]);
    }

    public function shipping(): void
    {
        $this->renderView('web/shipping_policy', [
            'pageTitle' => 'Shipping & Air Freight Policy | ImportWale Wholesale'
        ]);
    }

    public function refund(): void
    {
        $this->renderView('web/refund_policy', [
            'pageTitle' => 'Refund & Replacement Policy | ImportWale Wholesale'
        ]);
    }

    public function cancellation(): void
    {
        $this->renderView('web/cancellation_policy', [
            'pageTitle' => 'Order Cancellation Policy | ImportWale Wholesale'
        ]);
    }

    public function terms(): void
    {
        $this->renderView('web/terms_conditions', [
            'pageTitle' => 'Terms & Conditions | ImportWale Wholesale'
        ]);
    }

    public function privacy(): void
    {
        $this->renderView('web/privacy_policy', [
            'pageTitle' => 'Privacy Policy | ImportWale Wholesale'
        ]);
    }

    public function submitContact(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? 'General Support Inquiry');
        $message = trim($_POST['message'] ?? '');

        $this->jsonResponse([
            'success' => true,
            'message' => 'Thank you! Your support inquiry has been received. Our trade team will contact you within 2-4 hours.'
        ]);
    }
}
