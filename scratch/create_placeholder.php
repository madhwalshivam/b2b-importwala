<?php
// Minimal valid 1x1 neutral gray JPEG or SVG
$dir = __DIR__ . '/../public/assets/images/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Simple neutral SVG content saved as placeholder.jpg or placeholder.svg
$svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400" fill="none"><rect width="400" height="400" fill="#F3F4F6"/><rect x="1" y="1" width="398" height="398" rx="15" stroke="#E5E7EB" stroke-width="2"/><path d="M160 170L200 130L240 170M160 230L200 270L240 230" stroke="#9CA3AF" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><text x="200" y="200" text-anchor="middle" fill="#6B7280" font-family="sans-serif" font-size="16" font-weight="bold">MUDSOR EV ACCESSORIES</text><text x="200" y="225" text-anchor="middle" fill="#9CA3AF" font-family="sans-serif" font-size="13">No Image Available</text></svg>';

file_put_contents($dir . 'placeholder.svg', $svgContent);

// Base64 of a clean 200x200 neutral gray JPEG image
$base64Jpeg = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAHgAeABAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';
file_put_contents($dir . 'placeholder.jpg', base64_decode($base64Jpeg));

// Also ensure uploads/products directory exists
$uploadsProducts = __DIR__ . '/../public/uploads/products/';
if (!is_dir($uploadsProducts)) {
    mkdir($uploadsProducts, 0777, true);
}

echo "Created placeholder images and public/uploads/products directory.\n";
