<?php
$file = __DIR__ . '/../uploads/dfix/1785595009_6a6e0481eaa0f.png';
echo "File exists: " . (file_exists($file) ? "YES" : "NO") . "\n";
echo "Asset output: " . asset('/uploads/dfix/1785595009_6a6e0481eaa0f.png') . "\n";
