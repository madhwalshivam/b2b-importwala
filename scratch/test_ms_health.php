<?php
$ch = curl_init('http://127.0.0.1:5005/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo "Microservice Health Response:\n" . $response . "\n";
