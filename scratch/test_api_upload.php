<?php
$url = 'http://localhost/importwala/api/visual-search';

// Test 1: Upload watch image
$cfile1 = new CURLFile(__DIR__ . '/../public/uploads/products/luxury-watches.jpg', 'image/jpeg', 'luxury-watches.jpg');
$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $url);
curl_setopt($ch1, CURLOPT_POST, true);
curl_setopt($ch1, CURLOPT_POSTFIELDS, ['photo' => $cfile1]);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$res1 = curl_exec($ch1);
curl_close($ch1);

echo "=== WATCH UPLOAD API RESPONSE ===\n";
$data1 = json_decode($res1, true);
echo "Success: " . ($data1['success'] ? 'true' : 'false') . "\n";
echo "Total Returned (>=55% threshold): " . ($data1['total'] ?? 0) . "\n";
foreach ($data1['items'] ?? [] as $item) {
    echo "  - {$item['name']} | Score: {$item['similarity_score']}%\n";
}

// Test 2: Upload sunglasses image
$cfile2 = new CURLFile(__DIR__ . '/../public/uploads/products/sunglasses.jpg', 'image/jpeg', 'sunglasses.jpg');
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, ['photo' => $cfile2]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$res2 = curl_exec($ch2);
curl_close($ch2);

echo "\n=== SUNGLASSES UPLOAD API RESPONSE ===\n";
$data2 = json_decode($res2, true);
echo "Success: " . ($data2['success'] ? 'true' : 'false') . "\n";
echo "Total Returned (>=55% threshold): " . ($data2['total'] ?? 0) . "\n";
foreach ($data2['items'] ?? [] as $item) {
    echo "  - {$item['name']} | Score: {$item['similarity_score']}%\n";
}
