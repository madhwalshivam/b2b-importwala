<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

// 1. Update contact_phone in settings
$db->exec("UPDATE settings SET setting_value = '+91 9217714452' WHERE setting_key = 'contact_phone'");

// 2. Define pages data
$pages = [
    [
        'slug' => 'terms-and-conditions',
        'title' => 'Terms & Conditions',
        'meta_title' => 'Terms & Conditions - Mudsor EV Accessories',
        'meta_description' => 'Read Mudsor Terms and Conditions for website usage, orders, payments, shipping, returns, and governing laws.',
        'content' => '<p class="text-xs text-gray-500 font-semibold mb-6">Last Updated: August 2026</p>
<p class="leading-relaxed mb-6">Welcome to <strong>Mudsor</strong>. By accessing or using <a href="http://www.mudsor.com" class="text-red-600 font-semibold hover:underline">www.mudsor.com</a>, you agree to these Terms &amp; Conditions.</p>

<div class="space-y-6">
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">1. Orders</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>All orders are subject to acceptance and availability.</li>
            <li>We reserve the right to cancel or refuse any order if fraudulent or incorrect information is provided.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">2. Pricing</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>All prices are in INR (₹) and include applicable taxes unless stated otherwise.</li>
            <li>Prices and offers may change without prior notice.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">3. Payments</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>We accept secure online payments.</li>
            <li>Cash on Delivery (COD) is available on eligible orders.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">4. Shipping</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Orders are generally dispatched within 1–2 business days.</li>
            <li>Delivery timelines may vary depending on your location and courier services.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">5. Returns</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Eligible products can be returned within 7 days of delivery as per our Return &amp; Refund Policy.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">6. Product Information</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>We strive to provide accurate product descriptions and images. Minor variations in color or appearance may occur.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">7. Intellectual Property</h3>
        <p class="text-gray-700 text-xs sm:text-sm">All content on this website, including logos, images, text, and designs, is the property of Mudsor and may not be copied or used without permission.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">8. Limitation of Liability</h3>
        <p class="text-gray-700 text-xs sm:text-sm">Mudsor shall not be responsible for any indirect or consequential damages arising from the use of our products or website.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">9. Governing Law</h3>
        <p class="text-gray-700 text-xs sm:text-sm">These Terms &amp; Conditions are governed by the laws of India. Any disputes shall be subject to the jurisdiction of the courts in New Delhi, India.</p>
    </div>
</div>

<div class="mt-8 p-6 bg-red-50 rounded-2xl border border-red-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h4 class="font-semibold text-gray-900">Need help regarding Terms?</h4>
        <p class="text-xs text-gray-600 mt-1">Email: <a href="mailto:support@mudsor.com" class="text-red-600 font-semibold">support@mudsor.com</a> | Phone: <a href="tel:+919217714452" class="text-red-600 font-semibold">+91 9217714452</a></p>
    </div>
    <a href="http://www.mudsor.com" class="px-5 py-2.5 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 transition shrink-0">Visit Homepage</a>
</div>'
    ],
    [
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'meta_title' => 'Privacy Policy - Mudsor EV Accessories',
        'meta_description' => 'Mudsor Privacy Policy outlining data collection, payment security, usage, cookies, and user privacy rights.',
        'content' => '<p class="text-xs text-gray-500 font-semibold mb-6">Last Updated: August 2026</p>
<p class="leading-relaxed mb-6">At <strong>Mudsor</strong>, we value your privacy and are committed to protecting your personal information. By using <a href="http://www.mudsor.com" class="text-red-600 font-semibold hover:underline">www.mudsor.com</a>, you agree to this Privacy Policy.</p>

<div class="space-y-6">
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">1. Information We Collect</h3>
        <p class="text-xs text-gray-600 mb-2">We may collect the following information:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Name</li>
            <li>Mobile Number</li>
            <li>Email Address</li>
            <li>Shipping &amp; Billing Address</li>
            <li>Payment Details (processed securely through payment gateways)</li>
            <li>Order History</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">2. How We Use Your Information</h3>
        <p class="text-xs text-gray-600 mb-2">Your information is used to:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Process and deliver your orders</li>
            <li>Provide customer support</li>
            <li>Send order updates</li>
            <li>Improve our products and services</li>
            <li>Prevent fraud and unauthorized activities</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">3. Payment Security</h3>
        <p class="text-gray-700 text-xs sm:text-sm">All online payments are processed through trusted and secure payment gateways. Mudsor does not store your debit/credit card or UPI credentials.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">4. Information Sharing</h3>
        <p class="text-gray-700 text-xs sm:text-sm">We do not sell, rent, or trade your personal information. Information may only be shared with trusted courier partners, payment providers, or when required by law.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">5. Cookies</h3>
        <p class="text-gray-700 text-xs sm:text-sm">Our website may use cookies to improve your browsing experience, analyze website traffic, and enhance our services.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">6. Data Security</h3>
        <p class="text-gray-700 text-xs sm:text-sm">We implement reasonable security measures to protect your personal information. However, no method of online transmission is 100% secure.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">7. Your Rights</h3>
        <p class="text-gray-700 text-xs sm:text-sm">You may request to update or delete your personal information by contacting our support team, subject to applicable laws.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">8. Policy Updates</h3>
        <p class="text-gray-700 text-xs sm:text-sm">We may update this Privacy Policy from time to time. Any changes will be posted on this page.</p>
    </div>
</div>

<div class="mt-8 p-6 bg-red-50 rounded-2xl border border-red-100">
    <h4 class="font-semibold text-gray-900 text-sm">Privacy Support Contact</h4>
    <p class="text-xs text-gray-600 mt-1">Email: <a href="mailto:support@mudsor.com" class="text-red-600 font-semibold">support@mudsor.com</a> | Phone: <a href="tel:+919217714452" class="text-red-600 font-semibold">+91 9217714452</a></p>
</div>'
    ],
    [
        'slug' => 'refund-policy',
        'title' => 'Return & Refund Policy',
        'meta_title' => 'Return & Refund Policy - Mudsor EV Accessories',
        'meta_description' => '7-Day Easy Return & Refund Policy for Mudsor EV accessories. Learn return eligibility, non-returnable items, process, and refund timelines.',
        'content' => '<p class="text-xs text-gray-500 font-semibold mb-6">Last Updated: August 2026</p>
<p class="leading-relaxed mb-6">At <strong>Mudsor</strong>, customer satisfaction is our priority. If you are not completely satisfied with your purchase, please review our return and refund policy below.</p>

<div class="space-y-6">
    <div class="bg-emerald-50/50 p-5 rounded-2xl border border-emerald-200">
        <h3 class="text-base font-semibold text-emerald-900 mb-2">1. Return Eligibility</h3>
        <p class="text-xs text-emerald-800 mb-2">You may request a return within <strong>7 days</strong> of receiving your order if:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>You received a damaged product.</li>
            <li>You received a defective product.</li>
            <li>You received the wrong item.</li>
            <li>The product is unused, uninstalled, and in its original packaging with all accessories and invoices.</li>
        </ul>
    </div>

    <div class="bg-red-50/50 p-5 rounded-2xl border border-red-200">
        <h3 class="text-base font-semibold text-red-900 mb-2">2. Non-Returnable Items</h3>
        <p class="text-xs text-red-800 mb-2">Returns will not be accepted for:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Used or installed products.</li>
            <li>Products damaged due to improper use or installation.</li>
            <li>Products returned without original packaging.</li>
            <li>Items marked as Non-Returnable on the product page.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">3. Refund Process</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Once the returned product is received and inspected, your refund request will be reviewed.</li>
            <li>If approved, the refund will be processed to the original payment method.</li>
            <li>For Cash on Delivery (COD) orders, the refund will be made via bank transfer or another suitable payment method.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">4. Refund Timeline</h3>
        <p class="text-gray-700 text-xs sm:text-sm">Approved refunds are generally processed within <strong>5–7 business days</strong> after successful quality inspection.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">5. Return Shipping</h3>
        <p class="text-gray-700 text-xs sm:text-sm leading-relaxed">If the return is due to a damaged, defective, or incorrect product, Mudsor will arrange the return or bear the applicable return shipping cost.<br>For returns due to any other approved reason, return shipping charges may apply.</p>
    </div>
</div>

<div class="mt-8 p-6 bg-red-50 rounded-2xl border border-red-100">
    <h4 class="font-semibold text-gray-900 text-sm">6. Need Help with Returns?</h4>
    <p class="text-xs text-gray-600 mt-1">For any return or refund-related assistance, contact us:</p>
    <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold">
        <span class="text-gray-800">Website: <a href="http://www.mudsor.com" class="text-red-600">www.mudsor.com</a></span>
        <span class="text-gray-800">Email: <a href="mailto:support@mudsor.com" class="text-red-600">support@mudsor.com</a></span>
        <span class="text-gray-800">Phone: <a href="tel:+919217714452" class="text-red-600">+91 9217714452</a></span>
    </div>
</div>'
    ],
    [
        'slug' => 'shipping-policy',
        'title' => 'Shipping & Delivery Policy',
        'meta_title' => 'Shipping & Delivery Policy - Mudsor EV Accessories',
        'meta_description' => 'Fast dispatch in 1-2 business days. Estimated delivery 3-7 business days across India. Learn shipping charges, COD availability, and order tracking.',
        'content' => '<p class="text-xs text-gray-500 font-semibold mb-6">Last Updated: August 2026</p>
<p class="leading-relaxed mb-6">At <strong>Mudsor</strong>, we aim to deliver your orders safely and on time.</p>

<div class="space-y-6">
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">1. Order Processing</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Orders are processed within 24 hours of confirmation.</li>
            <li>Orders are generally dispatched within 1–2 business days.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">2. Delivery Time</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Estimated delivery time is <strong>3–7 business days</strong>, depending on your location.</li>
            <li>Delivery timelines may vary due to weather, courier delays, public holidays, or other unforeseen circumstances.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">3. Shipping Charges</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Shipping charges, if applicable, will be displayed at checkout before payment.</li>
            <li>Free shipping may be available on selected products or promotional offers.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">4. Cash on Delivery (COD)</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Cash on Delivery (COD) is available on eligible products and serviceable locations.</li>
            <li>Mudsor reserves the right to disable COD for certain PIN codes or high-value orders.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">5. Order Tracking</h3>
        <p class="text-gray-700 text-xs sm:text-sm">Once your order is shipped, tracking details will be shared via SMS, email, or WhatsApp (where applicable).</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">6. Failed Delivery</h3>
        <p class="text-gray-700 text-xs sm:text-sm">If delivery fails due to an incorrect address, unavailable recipient, or repeated delivery attempts, the order may be returned to us. Additional shipping charges may apply for re-dispatch.</p>
    </div>
</div>

<div class="mt-8 p-6 bg-red-50 rounded-2xl border border-red-100">
    <h4 class="font-semibold text-gray-900 text-sm">7. Shipping Queries Contact</h4>
    <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold">
        <span class="text-gray-800">Website: <a href="http://www.mudsor.com" class="text-red-600">www.mudsor.com</a></span>
        <span class="text-gray-800">Email: <a href="mailto:support@mudsor.com" class="text-red-600">support@mudsor.com</a></span>
        <span class="text-gray-800">Phone: <a href="tel:+919217714452" class="text-red-600">+91 9217714452</a></span>
    </div>
</div>'
    ],
    [
        'slug' => 'cancellation-policy',
        'title' => 'Cancellation Policy',
        'meta_title' => 'Order Cancellation Policy - Mudsor EV Accessories',
        'meta_description' => 'Learn how to cancel your order before dispatch, cancellation rules, refund options, and Mudsor cancellation terms.',
        'content' => '<p class="text-xs text-gray-500 font-semibold mb-6">Last Updated: August 2026</p>
<p class="leading-relaxed mb-6">At <strong>Mudsor</strong>, we understand that you may need to cancel an order. Please read our cancellation policy below.</p>

<div class="space-y-6">
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">1. Order Cancellation</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Orders can be cancelled before they are dispatched.</li>
            <li>Once an order has been shipped, cancellation requests cannot be accepted.</li>
        </ul>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">2. Cancellation by Mudsor</h3>
        <p class="text-xs text-gray-600 mb-2">Mudsor reserves the right to cancel any order due to:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>Product unavailability</li>
            <li>Pricing or technical errors</li>
            <li>Suspected fraudulent activity</li>
            <li>Incomplete or incorrect customer information</li>
        </ul>
        <p class="text-xs text-gray-700 mt-2 font-medium">In such cases, any prepaid amount will be refunded to the original payment method.</p>
    </div>

    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900">
        <h3 class="text-base font-semibold text-gray-900 mb-2">3. Refund for Cancelled Orders</h3>
        <ul class="list-disc pl-5 space-y-1 text-gray-700 text-xs sm:text-sm">
            <li>For prepaid orders, approved refunds are processed within 5–7 business days.</li>
            <li>For Cash on Delivery (COD) orders, no refund is applicable if no payment has been made.</li>
        </ul>
    </div>
</div>

<div class="mt-8 p-6 bg-red-50 rounded-2xl border border-red-100">
    <h4 class="font-semibold text-gray-900 text-sm">4. Need Cancellation Assistance?</h4>
    <div class="mt-3 flex flex-wrap gap-4 text-xs font-semibold">
        <span class="text-gray-800">Website: <a href="http://www.mudsor.com" class="text-red-600">www.mudsor.com</a></span>
        <span class="text-gray-800">Email: <a href="mailto:support@mudsor.com" class="text-red-600">support@mudsor.com</a></span>
        <span class="text-gray-800">Phone: <a href="tel:+919217714452" class="text-red-600">+91 9217714452</a></span>
    </div>
</div>'
    ],
    [
        'slug' => 'about-us',
        'title' => 'About Mudsor',
        'meta_title' => 'About Us - Mudsor Premium EV & Scooter Accessories',
        'meta_description' => 'Discover Mudsor - your trusted destination for heavy-duty EV crash guards, waterproof mobile holders, seat covers, and electric scooter accessories.',
        'content' => '<p class="text-base text-gray-800 font-semibold mb-6 leading-relaxed">Welcome to <strong>Mudsor</strong>, your trusted destination for premium bike and scooter accessories.</p>

<div class="space-y-6 text-sm text-gray-700 leading-relaxed">
    <p>At Mudsor, we are committed to providing high-quality products that enhance the style, comfort, safety, and performance of your ride. Every product is carefully selected to meet our quality standards and deliver the best value to our customers.</p>

    <p>Our goal is to offer a seamless shopping experience with genuine products, secure payments, fast dispatch, and reliable customer support.</p>

    <p>Whether you\'re upgrading your daily ride or adding a personal touch to your motorcycle or scooter, Mudsor is here to help you find the right accessories with confidence.</p>

    <p>We believe in quality, transparency, and customer satisfaction, and we continuously work to improve our products and services.</p>

    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-900 space-y-4 my-6">
        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-900 pb-2">Why Choose Mudsor?</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm font-semibold text-gray-800">
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>Premium Quality Products</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>Secure Online Payments</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>Cash on Delivery Available</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>Fast Dispatch (1–2 Days)</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>7-Day Easy Returns</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-semibold text-xs">✓</span>
                <span>Dedicated Customer Support</span>
            </div>
        </div>
    </div>

    <div class="p-6 bg-red-50 rounded-2xl border border-red-100 space-y-3">
        <h4 class="font-semibold text-gray-900 text-base">Contact Us</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-medium">
            <p><strong>Website:</strong> <a href="http://www.mudsor.com" class="text-red-600">www.mudsor.com</a></p>
            <p><strong>Email:</strong> <a href="mailto:support@mudsor.com" class="text-red-600">support@mudsor.com</a></p>
            <p><strong>Phone:</strong> <a href="tel:+919217714452" class="text-red-600">+91 9217714452</a></p>
            <p class="sm:col-span-2"><strong>Address:</strong> 3rd Floor, M-192, Block M, Pocket N, Sector 3, Bawana Industrial Area, New Delhi, Delhi – 110039</p>
        </div>
    </div>
</div>'
    ],
    [
        'slug' => 'contact-us',
        'title' => 'Contact Us',
        'meta_title' => 'Contact Us - Mudsor Customer Support',
        'meta_description' => 'Get in touch with Mudsor customer support for inquiries regarding orders, shipping, returns, wholesale, and EV accessories.',
        'content' => '<p class="text-base text-gray-800 font-semibold mb-6">We\'re here to help! If you have any questions about our products, orders, shipping, returns, or need assistance, feel free to get in touch with us.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-900 space-y-3">
        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-900 pb-2">Customer Support</h3>
        <p class="text-xs text-gray-600"><strong>Website:</strong> <a href="http://www.mudsor.com" class="text-red-600 font-semibold">www.mudsor.com</a></p>
        <p class="text-xs text-gray-600"><strong>Email:</strong> <a href="mailto:support@mudsor.com" class="text-red-600 font-semibold">support@mudsor.com</a></p>
        <p class="text-xs text-gray-600"><strong>Phone:</strong> <a href="tel:+919217714452" class="text-red-600 font-semibold">+91 9217714452</a></p>
        <div class="pt-2 border-t border-gray-900 text-xs">
            <p class="font-semibold text-gray-900 mb-1">Customer Support Hours</p>
            <p class="text-gray-600">Monday – Saturday: 10:00 AM – 7:00 PM (IST)</p>
            <p class="text-gray-600">Sunday: Closed</p>
        </div>
    </div>

    <div class="bg-gray-50 p-6 rounded-2xl border border-gray-900 space-y-3">
        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-900 pb-2">Business Address</h3>
        <p class="font-semibold text-gray-900 text-xs">Rughwani Enterprises</p>
        <p class="text-xs text-gray-600 leading-relaxed">3rd Floor, M-192, Block M, Pocket N, Sector 3,<br>Bawana Industrial Area,<br>New Delhi, Delhi - 110039, India</p>

        <div class="pt-2 border-t border-gray-900 text-xs space-y-1">
            <p class="font-semibold text-gray-900 mb-1">Response Time</p>
            <p class="text-gray-600"><strong>Email:</strong> Within 24 business hours</p>
            <p class="text-gray-600"><strong>Phone:</strong> During business hours</p>
            <p class="text-gray-600"><strong>Order &amp; Return Queries:</strong> Within 1–2 business days</p>
        </div>
    </div>
</div>

<div class="mt-8 p-6 bg-gray-900 text-white rounded-2xl text-center space-y-2">
    <h4 class="text-lg font-semibold">Thank You for Choosing Mudsor</h4>
    <p class="text-xs text-gray-300 max-w-xl mx-auto">We appreciate your trust and are committed to providing quality products and excellent customer service. If you need any assistance, our support team is always happy to help.</p>
</div>'
    ]
];

$stmtSelect = $db->prepare("SELECT id FROM cms_pages WHERE slug = ?");
$stmtInsert = $db->prepare("INSERT INTO cms_pages (title, slug, content, meta_title, meta_description) VALUES (?, ?, ?, ?, ?)");
$stmtUpdate = $db->prepare("UPDATE cms_pages SET title = ?, content = ?, meta_title = ?, meta_description = ? WHERE slug = ?");

foreach ($pages as $p) {
    $stmtSelect->execute([$p['slug']]);
    $existing = $stmtSelect->fetch();
    if ($existing) {
        $stmtUpdate->execute([$p['title'], $p['content'], $p['meta_title'], $p['meta_description'], $p['slug']]);
        echo "Updated page: {$p['slug']}\n";
    } else {
        $stmtInsert->execute([$p['title'], $p['slug'], $p['content'], $p['meta_title'], $p['meta_description']]);
        echo "Inserted page: {$p['slug']}\n";
    }
}

echo "All CMS pages updated successfully!\n";
