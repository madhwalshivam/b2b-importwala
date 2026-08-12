<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-10 min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-3xl p-8 lg:p-12 shadow-sm border border-gray-900">
            <h1 class="text-3xl font-black text-gray-900 mb-6 border-b pb-4"><?= htmlspecialchars($page['title']) ?>
            </h1>
            <div class="prose max-w-none text-sm text-gray-700 leading-relaxed space-y-4">
                <?= $page['content'] ?>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>