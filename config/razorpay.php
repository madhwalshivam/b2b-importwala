<?php
return [
    'key_id'     => getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_your_key_id',
    'key_secret' => getenv('RAZORPAY_KEY_SECRET') ?: 'your_key_secret'
];
