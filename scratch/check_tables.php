<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Existing Tables:\n" . implode("\n", $tables) . "\n\n";

if (in_array('reviews', $tables)) {
    echo "--- reviews columns ---\n";
    $cols = $db->query("DESCRIBE reviews")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
}

if (in_array('google_reviews', $tables)) {
    echo "--- google_reviews columns ---\n";
    $cols = $db->query("DESCRIBE google_reviews")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
}

if (in_array('testimonials', $tables)) {
    echo "--- testimonials columns ---\n";
    $cols = $db->query("DESCRIBE testimonials")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
}
