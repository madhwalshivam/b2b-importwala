<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/BlogPost.php';

use App\Models\BlogPost;

$blogModel = new BlogPost();

$samplePosts = [
    [
        'title' => 'Top 5 Must-Have Crash Guards for Ola S1 Pro & Air',
        'excerpt' => 'Protect your Ola electric scooter from scratches, side drops, and traffic bumps with heavy-duty stainless steel crash guards.',
        'content' => '<h2>Why Your Ola S1 Needs High-Grade Crash Protection</h2><p>Electric scooters are lightweight and quick, making them vulnerable to minor tip-overs and parking scrapes. Installing a custom-fit stainless steel crash guard shields the painted body panels and battery casing from expensive repair costs.</p><h3>1. Stainless Steel Grade 304 Guard</h3><p>Offers maximum rust resistance during rainy seasons.</p><h3>2. Dual Slider Rubber Pads</h3><p>Absorbs shock during accidental side falls.</p>',
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'Ola S1 Pro crash guard side view',
        'meta_title' => 'Top 5 Must-Have Crash Guards for Ola S1 Pro & Air',
        'meta_description' => 'Discover top stainless steel crash guards for Ola S1 electric scooters. Prevent body scratches and drop damage.',
        'author_name' => 'Amit Sharma',
        'status' => 'published'
    ],
    [
        'title' => 'How to Care for Your EV Scooter Battery in Heavy Monsoon',
        'excerpt' => 'Essential waterproofing tips to keep your EV battery pack and electronic display screen safe during rainy seasons.',
        'content' => '<h2>Monsoon Maintenance Guide for Electric Scooters</h2><p>Riding in rain requires extra precautions to protect sensitive electronic controllers, lithium-ion battery compartments, and digital gauge displays.</p><ul><li>Use waterproof body covers when parked outdoors.</li><li>Install tempered screen guards to prevent rainwater leakage into digital dashboards.</li><li>Keep bottom frame bolts tight and rust-free.</li></ul>',
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'Electric scooter rain protection cover',
        'meta_title' => 'EV Scooter Battery Care Guide in Monsoon',
        'meta_description' => 'Protect your electric scooter battery and digital display screen during heavy rain with waterproofing accessories.',
        'author_name' => 'Priya Verma',
        'status' => 'published'
    ],
    [
        'title' => 'Ather 450X vs TVS iQube: Frame Protection & Fitting Comparison',
        'excerpt' => 'A detailed structural comparison between Ather 450X and TVS iQube frame designs for choosing the ideal body guard kit.',
        'content' => '<h2>Comparing Chassis Geometry for Custom Accessories</h2><p>Both Ather 450X and TVS iQube feature distinct frame geometry requiring custom-tailored mounting brackets for crash guards and luggage racks.</p>',
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'Ather 450X and TVS iQube comparison',
        'meta_title' => 'Ather 450X vs TVS iQube: Frame Protection Fitting Guide',
        'meta_description' => 'Compare Ather 450X and TVS iQube mounting points and frame protection accessories.',
        'author_name' => 'Rahul Rughwani',
        'status' => 'published'
    ],
    [
        'title' => '7 Tips to Extend Your Electric Scooter Range in City Traffic',
        'excerpt' => 'Simple riding habits, tire pressure adjustments, and weight optimizations to extract max range per charge.',
        'content' => '<h2>Maximize Kilometers Per Charge</h2><p>Regenerative braking, maintaining 32 PSI tire pressure, and avoiding sudden acceleration can boost your real-world riding range by up to 15-20%.</p>',
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'Electric scooter range optimization',
        'meta_title' => '7 Tips to Extend Electric Scooter Range Per Charge',
        'meta_description' => 'Learn how to maximize your electric scooter range per full charge with simple maintenance and riding tips.',
        'author_name' => 'Mudsor Tech Team',
        'status' => 'published'
    ],
    [
        'title' => 'Waterproof Body Covers vs Standard Tarps: Which is Better?',
        'excerpt' => 'Compare multi-layer UV reflective waterproof covers against cheap plastic tarps for long-term outdoor parking protection.',
        'content' => '<h2>Investing in All-Weather Protection</h2><p>Standard tarps trap condensation inside, rusting metal parts. Breathable waterproof body covers shield paintwork from UV fading while letting internal moisture escape.</p>',
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'Waterproof body cover comparison',
        'meta_title' => 'Waterproof Body Covers vs Standard Tarps Comparison',
        'meta_description' => 'Find out why custom-fit waterproof EV covers protect paint and electronics better than standard plastic tarps.',
        'author_name' => 'Neha Singh',
        'status' => 'published'
    ]
];

foreach ($samplePosts as $postData) {
    $slug = $blogModel->generateUniqueSlug($postData['title']);
    if (!$blogModel->findBy('slug', $slug)) {
        $blogModel->insert([
            'title' => $postData['title'],
            'slug' => $slug,
            'excerpt' => $postData['excerpt'],
            'content' => $postData['content'],
            'featured_image' => $postData['featured_image'],
            'featured_image_alt' => $postData['featured_image_alt'],
            'meta_title' => $postData['meta_title'],
            'meta_description' => $postData['meta_description'],
            'author_name' => $postData['author_name'],
            'status' => $postData['status'],
            'published_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days'))
        ]);
        echo "Seeded post: {$postData['title']}\n";
    }
}
echo "Done seeding sample posts!\n";
