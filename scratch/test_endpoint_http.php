<?php
$url = 'http://localhost/importwala/api/visual-search';
$ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
$res = file_get_contents($url, false, $ctx);
echo "Status: " . ($http_response_header[0] ?? 'Unknown') . "\n";
echo "Response body:\n" . substr($res, 0, 1000) . "\n";
