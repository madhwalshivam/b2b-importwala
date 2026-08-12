<?php
namespace App\Helpers;

class SEO {
    public static function renderMeta(array $options = []): string {
        $siteName = 'Mudsor | Electric Scooter Accessories';
        $title = !empty($options['title']) ? $options['title'] . ' | Mudsor' : $siteName;
        $description = $options['description'] ?? 'Buy premium electric scooter crash guards, mobile holders, waterproof body covers, seat cushions and screen guards for Ola, Ather, TVS, and Chetak.';
        $url = $options['url'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $image = $options['image'] ?? url('assets/images/mudsor-banner.jpg');
        $canonical = $options['canonical'] ?? $url;

        $ogType = !empty($options['type']) ? $options['type'] : (!empty($options['article']) ? 'article' : 'website');

        $html = "<!-- Primary Meta Tags -->\n";
        $html .= "<title>" . htmlspecialchars($title) . "</title>\n";
        $html .= '<meta name="description" content="' . htmlspecialchars($description) . "\">\n";
        $html .= '<link rel="canonical" href="' . htmlspecialchars($canonical) . "\">\n\n";

        $html .= "<!-- Open Graph / Facebook -->\n";
        $html .= '<meta property="og:type" content="' . htmlspecialchars($ogType) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($url) . "\">\n";
        $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . "\">\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . "\">\n";
        $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . "\">\n\n";

        $html .= "<!-- Twitter -->\n";
        $html .= '<meta property="twitter:card" content="summary_large_image">' . "\n";
        $html .= '<meta property="twitter:url" content="' . htmlspecialchars($url) . "\">\n";
        $html .= '<meta property="twitter:title" content="' . htmlspecialchars($title) . "\">\n";
        $html .= '<meta property="twitter:description" content="' . htmlspecialchars($description) . "\">\n";
        $html .= '<meta property="twitter:image" content="' . htmlspecialchars($image) . "\">\n\n";

        // Organization Schema
        $orgSchema = [
            "@context" => "https://schema.org",
            "@type" => "Organization",
            "name" => "Mudsor (Rughwani Enterprises)",
            "url" => url('/'),
            "logo" => url('assets/images/mudsor-logo.png'),
            "contactPoint" => [
                "@type" => "ContactPoint",
                "telephone" => "+91 9217714452",
                "contactType" => "customer service",
                "email" => "mudsorinfo@gmail.com"
            ]
        ];
        $html .= '<script type="application/ld+json">' . json_encode($orgSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";

        // Product Schema if provided
        if (!empty($options['product'])) {
            $prod = $options['product'];
            $prodSchema = [
                "@context" => "https://schema.org/",
                "@type" => "Product",
                "name" => $prod['name'],
                "image" => [url($prod['main_image'] ?? 'assets/images/placeholder.jpg')],
                "description" => strip_tags($prod['description'] ?? ''),
                "sku" => $prod['sku'],
                "brand" => [
                    "@type" => "Brand",
                    "name" => "Mudsor"
                ],
                "offers" => [
                    "@type" => "Offer",
                    "url" => $url,
                    "priceCurrency" => "INR",
                    "price" => $prod['sale_price'] ?? $prod['price'],
                    "availability" => ($prod['stock'] > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
                ]
            ];
            $html .= '<script type="application/ld+json">' . json_encode($prodSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
        }

        // Article / BlogPosting Schema if provided
        if (!empty($options['article'])) {
            $art = $options['article'];
            $articleSchema = [
                "@context" => "https://schema.org",
                "@type" => "BlogPosting",
                "headline" => $art['title'] ?? $title,
                "description" => strip_tags($art['meta_description'] ?? $art['excerpt'] ?? $description),
                "image" => !empty($art['featured_image']) ? [url($art['featured_image'])] : [$image],
                "datePublished" => date('c', strtotime($art['published_at'] ?? $art['created_at'] ?? 'now')),
                "dateModified" => date('c', strtotime($art['updated_at'] ?? 'now')),
                "author" => [
                    "@type" => "Person",
                    "name" => $art['author_name'] ?? 'Mudsor Team'
                ],
                "publisher" => [
                    "@type" => "Organization",
                    "name" => "Mudsor",
                    "logo" => [
                        "@type" => "ImageObject",
                        "url" => url('assets/images/mudsor-logo.png')
                    ]
                ],
                "mainEntityOfPage" => [
                    "@type" => "WebPage",
                    "@id" => $url
                ]
            ];
            $html .= '<script type="application/ld+json">' . json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
        }

        return $html;
    }
}
