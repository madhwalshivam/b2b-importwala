<?php
require dirname(__DIR__) . '/app/Services/PhoneNumber.php';
$cases = [
    '9876543210' => '9876543210',
    '+91 98765 43210' => '9876543210',
    '09876543210' => '9876543210',
    '919876543210' => '9876543210',
    '5876543210' => null,
    '12345' => null,
    '98765 43210' => '9876543210',
];
$failed = 0;
foreach ($cases as $input => $expected) {
    $got = App\Services\PhoneNumber::normalize($input);
    if ($got !== $expected) {
        echo "FAIL {$input} got " . var_export($got, true) . " expected " . var_export($expected, true) . PHP_EOL;
        $failed++;
    }
}
echo $failed === 0 ? "phone ok\n" : "phone failed {$failed}\n";
exit($failed === 0 ? 0 : 1);
