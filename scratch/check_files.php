<?php
$f1 = __DIR__ . '/../public/uploads/dfix/1785595009_6a6e0481eaa0f.png';
$f2 = __DIR__ . '/../public/uploads/dfix/1785734493_6a70255d26001.png';

echo "File 1: {$f1}\n";
echo "  Exists: " . (file_exists($f1) ? 'YES' : 'NO') . "\n";
if (file_exists($f1)) {
    echo "  Size: " . filesize($f1) . " bytes\n";
    echo "  Is readable: " . (is_readable($f1) ? 'YES' : 'NO') . "\n";
    print_r(getimagesize($f1));
}

echo "\nFile 2: {$f2}\n";
echo "  Exists: " . (file_exists($f2) ? 'YES' : 'NO') . "\n";
if (file_exists($f2)) {
    echo "  Size: " . filesize($f2) . " bytes\n";
    echo "  Is readable: " . (is_readable($f2) ? 'YES' : 'NO') . "\n";
    print_r(getimagesize($f2));
}
