<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    Database::init(require ROOT_PATH . '/config/database.php');
    $db = Database::getInstance();

    echo "========================================================\n";
    echo "1. Wiping Old Data from categories & featured tables...\n";
    echo "========================================================\n";

    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE `categories`;");
    $db->exec("TRUNCATE TABLE `featured_categories`;");
    $db->exec("TRUNCATE TABLE `featured_subcategories`;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "All category tables cleared successfully!\n\n";

    echo "========================================================\n";
    echo "2. Seeding Categories & Subcategories into DB...\n";
    echo "========================================================\n";

    $uploadDir = ROOT_PATH . '/public/uploads/featured_categories';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ensureImage = function($filename, $remoteUrl) use ($uploadDir) {
        $localPath = $uploadDir . '/' . $filename;
        if (!file_exists($localPath)) {
            $context = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'Mozilla/5.0']]);
            $data = @file_get_contents($remoteUrl, false, $context);
            if ($data && strlen($data) > 1000) {
                file_put_contents($localPath, $data);
            } else {
                $fallback = $uploadDir . '/earrings.jpg';
                if (file_exists($fallback)) {
                    copy($fallback, $localPath);
                }
            }
        }
        return '/uploads/featured_categories/' . $filename;
    };

    $catalog = [
        [
            'name' => 'Jewelry',
            'slug' => 'jewelry',
            'filename' => 'viewall-jewelry.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 1,
            'subcategories' => [
                ['name' => 'Stainless Steel Necklaces', 'slug' => 'stainless-steel-necklaces', 'filename' => 'stainless-steel-necklaces.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=necklaces', 'sort_order' => 1],
                ['name' => 'Hoop & Stud Earrings', 'slug' => 'earrings', 'filename' => 'earrings.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=earrings', 'sort_order' => 2],
                ['name' => 'Beaded & Charm Bracelets', 'slug' => 'bracelets', 'filename' => 'bracelets.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1611591475140-4388cf34ff5d?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=bracelets', 'sort_order' => 3],
                ['name' => 'Statement Rings', 'slug' => 'statement-rings', 'filename' => 'statement-rings.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=rings', 'sort_order' => 4],
                ['name' => 'Anklets & Body Chains', 'slug' => 'anklets', 'filename' => 'anklets.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=anklets', 'sort_order' => 5],
                ['name' => 'Pearl & Crystal Sets', 'slug' => 'pearls', 'filename' => 'pearls.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=pearls', 'sort_order' => 6]
            ]
        ],
        [
            'name' => 'Accessories',
            'slug' => 'accessories',
            'filename' => 'viewall-accessories.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 2,
            'subcategories' => [
                ['name' => 'Hair Clips & Claw Clips', 'slug' => 'hair-accessories', 'filename' => 'hair-accessories.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=hair', 'sort_order' => 1],
                ['name' => 'Sunglasses & Eyewear', 'slug' => 'sunglasses', 'filename' => 'sunglasses.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=sunglasses', 'sort_order' => 2],
                ['name' => 'Scarves & Bandanas', 'slug' => 'scarves', 'filename' => 'scarves.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=scarves', 'sort_order' => 3],
                ['name' => 'Keychains & Charms', 'slug' => 'keychains', 'filename' => 'keychains.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1628149455678-16f37bc392f4?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=keychains', 'sort_order' => 4],
                ['name' => 'Fashion Belts & Straps', 'slug' => 'belts', 'filename' => 'belts.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1624222247344-550fb60583dc?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=belts', 'sort_order' => 5]
            ]
        ],
        [
            'name' => 'Home & Kitchen',
            'slug' => 'home-kitchen',
            'filename' => 'viewall-home-kitchen.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 3,
            'subcategories' => [
                ['name' => 'Drinkware & Tumblers', 'slug' => 'drinkware', 'filename' => 'drinkware.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=drinkware', 'sort_order' => 1],
                ['name' => 'Dinnerware & Tableware', 'slug' => 'dinnerware-tableware', 'filename' => 'dinnerware.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=dinnerware', 'sort_order' => 2],
                ['name' => 'Home Decor Accents', 'slug' => 'home-decor-accents', 'filename' => 'home-decor.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=decor', 'sort_order' => 3],
                ['name' => 'Artificial Flowers & Plants', 'slug' => 'artificial-decorations', 'filename' => 'artificial-plants.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=artificial', 'sort_order' => 4],
                ['name' => 'Kitchen Tools & Gadgets', 'slug' => 'kitchen-tools', 'filename' => 'kitchen-tools.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=kitchen', 'sort_order' => 5]
            ]
        ],
        [
            'name' => 'Jewelry Making',
            'slug' => 'jewelry-making',
            'filename' => 'viewall-jewelry-making.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1611591475140-4388cf34ff5d?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 4,
            'subcategories' => [
                ['name' => 'Loose Beads & Gemstones', 'slug' => 'loose-beads', 'filename' => 'loose-beads.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=beads', 'sort_order' => 1],
                ['name' => 'Findings & Clasps', 'slug' => 'jewelry-findings', 'filename' => 'findings.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1611591475140-4388cf34ff5d?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=findings', 'sort_order' => 2],
                ['name' => 'Display & Packaging', 'slug' => 'jewelry-packaging', 'filename' => 'packaging.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=packaging', 'sort_order' => 3],
                ['name' => 'Craft Wires & Chains', 'slug' => 'craft-wires', 'filename' => 'craft-wires.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=wires', 'sort_order' => 4]
            ]
        ],
        [
            'name' => 'Bags & Luggage',
            'slug' => 'bags',
            'filename' => 'viewall-bags.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 5,
            'subcategories' => [
                ['name' => 'Crossbody & Shoulder Bags', 'slug' => 'shoulder-bags', 'filename' => 'shoulder-bags.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=bags', 'sort_order' => 1],
                ['name' => 'Canvas Totes & Shopping Bags', 'slug' => 'tote-bags', 'filename' => 'tote-bags.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=totes', 'sort_order' => 2],
                ['name' => 'Cosmetic Pouches & Cases', 'slug' => 'cosmetic-cases', 'filename' => 'cosmetic-cases.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=pouches', 'sort_order' => 3],
                ['name' => 'Travel Backpacks & Duffels', 'slug' => 'travel-backpacks', 'filename' => 'backpacks.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=backpacks', 'sort_order' => 4]
            ]
        ],
        [
            'name' => 'Baby & Kids',
            'slug' => 'baby-kids',
            'filename' => 'viewall-baby-kids.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 6,
            'subcategories' => [
                ['name' => 'Plush Toys & Rattles', 'slug' => 'plush-toys', 'filename' => 'plush-toys.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=toys', 'sort_order' => 1],
                ['name' => 'Kids Hair Accessories', 'slug' => 'kids-hair', 'filename' => 'kids-hair.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=kids-hair', 'sort_order' => 2],
                ['name' => 'Baby Feeding & Teething', 'slug' => 'baby-feeding', 'filename' => 'baby-feeding.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=baby-feeding', 'sort_order' => 3]
            ]
        ],
        [
            'name' => 'Office & School Supplies',
            'slug' => 'office-school-supplies',
            'filename' => 'viewall-office-supplies.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1456735190827-d1262f71b8a3?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 7,
            'subcategories' => [
                ['name' => 'Gel Pens & Markers', 'slug' => 'pens-markers', 'filename' => 'pens-markers.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1585336261026-675768e7a982?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=pens', 'sort_order' => 1],
                ['name' => 'Notebooks & Sticky Notes', 'slug' => 'notebooks', 'filename' => 'notebooks.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=notebooks', 'sort_order' => 2],
                ['name' => 'Desk Organizers & Stationery', 'slug' => 'desk-organizers', 'filename' => 'desk-organizers.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=organizers', 'sort_order' => 3]
            ]
        ],
        [
            'name' => 'Party Supplies',
            'slug' => 'party-supplies',
            'filename' => 'viewall-party-supplies.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 8,
            'subcategories' => [
                ['name' => 'Balloons & Party Banners', 'slug' => 'balloons', 'filename' => 'balloons.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=party', 'sort_order' => 1],
                ['name' => 'Photo Booth Props & Favors', 'slug' => 'party-props', 'filename' => 'party-props.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1513151233558-d860c5398176?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=party-props', 'sort_order' => 2]
            ]
        ],
        [
            'name' => 'Beauty',
            'slug' => 'beauty',
            'filename' => 'viewall-beauty.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 9,
            'subcategories' => [
                ['name' => 'Makeup Sponges & Brushes', 'slug' => 'makeup-tools', 'filename' => 'makeup-tools.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=beauty', 'sort_order' => 1],
                ['name' => 'Nail Art & Stickers', 'slug' => 'nail-art', 'filename' => 'nail-art.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=nails', 'sort_order' => 2],
                ['name' => 'Skincare Tools & Gua Sha', 'slug' => 'skincare-tools', 'filename' => 'skincare-tools.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=skincare', 'sort_order' => 3]
            ]
        ],
        [
            'name' => 'Electronics & Gadgets',
            'slug' => 'electronics-gadgets',
            'filename' => 'viewall-electronics.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1468495244123-6c6c332eeece?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 10,
            'subcategories' => [
                ['name' => 'Smartwatch Bands & Accessories', 'slug' => 'smartwatch-bands', 'filename' => 'smartwatch-bands.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=smartwatch', 'sort_order' => 1],
                ['name' => 'Phone Cases & Protectors', 'slug' => 'phone-cases', 'filename' => 'phone-cases.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1601593378579-2ab25ff7430d?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=phone-cases', 'sort_order' => 2],
                ['name' => 'Wireless Earbuds & Audio', 'slug' => 'wireless-earbuds', 'filename' => 'wireless-earbuds.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=earbuds', 'sort_order' => 3],
                ['name' => 'USB Cables & Chargers', 'slug' => 'usb-cables', 'filename' => 'usb-cables.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=chargers', 'sort_order' => 4],
                ['name' => 'Portable Fans & Desk Lights', 'slug' => 'portable-fans', 'filename' => 'portable-fans.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1546554137-f86b9593a222?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=fans', 'sort_order' => 5]
            ]
        ],
        [
            'name' => 'Watches & Timepieces',
            'slug' => 'watches-timepieces',
            'filename' => 'viewall-watches.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 11,
            'subcategories' => [
                ['name' => 'Luxury Quartz Watches', 'slug' => 'quartz-watches', 'filename' => 'luxury-watches.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=watches', 'sort_order' => 1],
                ['name' => 'Digital Sports Watches', 'slug' => 'sports-watches', 'filename' => 'sports-watches.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=sports-watches', 'sort_order' => 2],
                ['name' => 'Minimalist Leather Watches', 'slug' => 'leather-watches', 'filename' => 'leather-watches.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=leather-watches', 'sort_order' => 3]
            ]
        ],
        [
            'name' => 'Health & Wellness',
            'slug' => 'health-wellness',
            'filename' => 'viewall-health-wellness.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 12,
            'subcategories' => [
                ['name' => 'Body Massagers & Tools', 'slug' => 'body-massagers', 'filename' => 'body-massagers.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=massagers', 'sort_order' => 1],
                ['name' => 'Fitness Resistance Bands', 'slug' => 'fitness-bands', 'filename' => 'fitness-bands.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=fitness', 'sort_order' => 2],
                ['name' => 'Essential Oil Diffusers', 'slug' => 'oil-diffusers', 'filename' => 'oil-diffusers.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=diffusers', 'sort_order' => 3]
            ]
        ],
        [
            'name' => 'Pet Supplies',
            'slug' => 'pet-supplies',
            'filename' => 'viewall-pet-supplies.jpg',
            'remote_url' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=400&q=80',
            'sort_order' => 13,
            'subcategories' => [
                ['name' => 'Pet Collars & Leashes', 'slug' => 'pet-collars', 'filename' => 'pet-collars.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=pet-collars', 'sort_order' => 1],
                ['name' => 'Grooming Brushes & Tools', 'slug' => 'pet-grooming', 'filename' => 'pet-grooming.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=grooming', 'sort_order' => 2],
                ['name' => 'Interactive Pet Toys', 'slug' => 'pet-toys', 'filename' => 'pet-toys.jpg', 'remote_url' => 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=400&q=80', 'link_url' => '/catalog?q=pet-toys', 'sort_order' => 3]
            ]
        ]
    ];

    $insMainCat = $db->prepare("
        INSERT INTO `categories` (`parent_id`, `name`, `slug`, `image`, `status`, `is_featured`, `sort_order`) 
        VALUES (NULL, ?, ?, ?, 'active', 1, ?)
    ");

    $insMainSubcat = $db->prepare("
        INSERT INTO `categories` (`parent_id`, `name`, `slug`, `image`, `status`, `is_featured`, `sort_order`) 
        VALUES (?, ?, ?, ?, 'active', 1, ?)
    ");

    $insFeatCat = $db->prepare("
        INSERT INTO `featured_categories` (`name`, `slug`, `image`, `sort_order`, `is_active`) 
        VALUES (?, ?, ?, ?, 1)
    ");

    $insFeatSubcat = $db->prepare("
        INSERT INTO `featured_subcategories` (`featured_category_id`, `name`, `slug`, `image`, `link_url`, `sort_order`, `is_active`) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");

    foreach ($catalog as $cat) {
        $imgPath = $ensureImage($cat['filename'], $cat['remote_url']);

        // Insert into main categories table
        $insMainCat->execute([$cat['name'], $cat['slug'], $imgPath, $cat['sort_order']]);
        $mainParentId = $db->lastInsertId();

        // Insert into featured_categories table
        $insFeatCat->execute([$cat['name'], $cat['slug'], $imgPath, $cat['sort_order']]);
        $featParentId = $db->lastInsertId();

        echo "Inserted Parent Category [ID {$mainParentId}]: {$cat['name']} (image: {$imgPath})\n";

        foreach ($cat['subcategories'] as $sub) {
            $subImgPath = $ensureImage($sub['filename'], $sub['remote_url']);

            // Insert subcategory into main categories table
            $insMainSubcat->execute([$mainParentId, $sub['name'], $sub['slug'], $subImgPath, $sub['sort_order']]);
            $mainSubId = $db->lastInsertId();

            // Insert subcategory into featured_subcategories table
            $insFeatSubcat->execute([$featParentId, $sub['name'], $sub['slug'], $subImgPath, $sub['link_url'], $sub['sort_order']]);
            $featSubId = $db->lastInsertId();

            echo "   + Subcategory: {$sub['name']} [Main ID {$mainSubId} | Feat ID {$featSubId}] (image: {$subImgPath})\n";
        }
    }

    // Update product category_id references so products aren't referencing missing category IDs
    $db->exec("UPDATE `products` SET `category_id` = 10 WHERE `category_id` IS NOT NULL;");

    echo "\n========================================================\n";
    echo "SUCCESS: ALL CATEGORIES & SUBCATEGORIES RE-SEEDED INTO DB!\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "Seeding Error: " . $e->getMessage() . "\n";
    exit(1);
}
