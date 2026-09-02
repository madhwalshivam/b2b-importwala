<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Testimonial.php';

$m = new App\Models\Testimonial();
$reviews = $m->getFeatured(6);

echo "Fetched " . count($reviews) . " featured reviews:\n";
foreach ($reviews as $r) {
    echo "- ID: {$r['id']} | {$r['reviewer_name']} ({$r['location']}) | Color: {$r['avatar_color']}\n";
    echo "  Text: " . substr($r['review_text'], 0, 60) . "...\n";
}
