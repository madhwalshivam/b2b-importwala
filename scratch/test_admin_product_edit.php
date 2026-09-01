<?php
$ch = curl_init('http://localhost/importwala/admin/products/edit/1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "ADMIN PRODUCT EDIT HTTP STATUS: {$code}\n";
