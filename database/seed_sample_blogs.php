<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/BlogPost.php';

use App\Models\BlogPost;

$blogModel = new BlogPost();

echo "Seeding sample blog post...\n";

$title = "ERW Pipe vs Seamless Pipe";
$slug = $blogModel->generateUniqueSlug($title);
$excerpt = "Discover the structural differences, manufacturing processes, strength comparisons, and application suitability between ERW and Seamless steel pipes for heavy automotive framing and guards.";

$content = '
<h2>Understanding ERW Pipe vs Seamless Pipe</h2>
<p>When selecting steel piping for automotive crash guards, structural chassis, and heavy-duty electric scooter accessories, understanding the differences between <strong>Electric Resistance Welded (ERW)</strong> pipes and <strong>Seamless</strong> pipes is critical for durability and safety.</p>

<h3>What is an ERW Pipe?</h3>
<p>ERW pipes are manufactured by cold-forming a flat strip of high-tensile steel into a cylindrical shape. An electric current is passed between the edges to heat the steel to a point where the edges melt and fuse together without using filler material.</p>

<h3>What is a Seamless Pipe?</h3>
<p>Seamless pipes are produced by extruding solid steel billets into a hollow tube without any longitudinal weld seam. This process provides uniform circumferential strength across the entire wall thickness.</p>

<h2>Specification & Mechanical Comparison Table</h2>
<table>
  <thead>
    <tr>
      <th>Parameter</th>
      <th>ERW Pipe</th>
      <th>Seamless Pipe</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Manufacturing Method</strong></td>
      <td>Electric Resistance Welding from rolled coil</td>
      <td>Hot extrusion or cold drawing of solid billet</td>
    </tr>
    <tr>
      <td><strong>Pressure Rating</strong></td>
      <td>Moderate to High</td>
      <td>Ultra High</td>
    </tr>
    <tr>
      <td><strong>Surface Finish</strong></td>
      <td>Smooth internal & external finish</td>
      <td>Slightly rougher extruded surface</td>
    </tr>
    <tr>
      <td><strong>Cost Efficiency</strong></td>
      <td>Highly cost-effective</td>
      <td>Premium pricing</td>
    </tr>
  </tbody>
</table>

<h2>Key Advantages for Automotive Crash Protection</h2>
<ul>
  <li><strong>Impact Absorption:</strong> Engineered steel alloys ensure high resistance against road debris and side impacts.</li>
  <li><strong>Corrosion Prevention:</strong> Premium powder coating protects against rain, moisture, and road salts.</li>
  <li><strong>Precision Fitment:</strong> Laser-bent tubing aligns perfectly with factory mounting points.</li>
</ul>

<p>For more electric scooter protection guides and fitting options, check out our <a href="/shop" target="_self">Mudsor Scooter Accessories Shop</a> or consult external material engineering standards at <a href="https://www.astm.org" target="_blank" rel="nofollow">ASTM International</a>.</p>
';

// Insert sample post if slug does not already exist
$existing = $blogModel->findBy('slug', $slug);
if (!$existing) {
    $blogModel->insert([
        'title' => $title,
        'slug' => $slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'featured_image' => 'assets/images/mudsor-banner.jpg',
        'featured_image_alt' => 'ERW Pipe vs Seamless Pipe comparison diagram for electric scooter crash guards',
        'meta_title' => 'ERW Pipe vs Seamless Pipe: Differences, Strength & Specs',
        'meta_description' => 'Comprehensive guide comparing ERW Pipe vs Seamless Pipe manufacturing, tensile strength, pressure ratings, and automotive structural applications.',
        'focus_keyword' => 'erw pipe vs seamless pipe',
        'author_name' => 'Mudsor Engineering Team',
        'status' => 'published',
        'published_at' => date('Y-m-d H:i:s')
    ]);
    echo "Sample blog post '{$title}' created with slug '/blog/{$slug}'.\n";
} else {
    echo "Sample post already exists.\n";
}
