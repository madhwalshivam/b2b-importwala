<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-white p-6 rounded-2xl border border-gray-200 shadow-sm gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 leading-snug">Visual Search Feature Embedding & Matching Debugger</h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Inspect raw 128-dim vectors, dHash signatures, and exact Cosine Similarity float scores (0.000 to 1.000)</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="triggerCatalogReindex()" id="reindexBtn"
                class="px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Reindex All Catalog Images</span>
            </button>
        </div>
    </div>

    <?php if (!empty($_GET['reindexed'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl text-xs font-semibold flex items-center justify-between">
            <span>✓ Catalog product image embeddings reindexed successfully! All active products are populated in database index.</span>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-900">&times;</button>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Active Products</span>
            <div class="text-2xl font-black text-gray-900 mt-1"><?= $analysis['total_active_products'] ?? 0 ?></div>
            <span class="text-[11px] text-gray-500 font-medium">Catalog Items</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Indexed Embeddings</span>
            <div class="text-2xl font-black text-blue-600 mt-1"><?= $analysis['total_indexed_products'] ?? 0 ?></div>
            <span class="text-[11px] text-gray-500 font-medium">Product Vectors in DB</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Index Coverage</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">
                <?php
                $active = $analysis['total_active_products'] ?? 0;
                $indexed = $analysis['total_indexed_products'] ?? 0;
                $pct = $active > 0 ? round(($indexed / $active) * 100, 1) : 0;
                echo $pct . '%';
                ?>
            </div>
            <span class="inline-block px-2 py-0.5 mt-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-md">
                <?= $pct >= 100 ? '100% Fully Indexed' : 'Partial Index' ?>
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Embedding Engine</span>
            <div class="text-lg font-bold text-gray-900 mt-1">Flask Microservice</div>
            <span class="inline-block px-2 py-0.5 mt-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md">
                Port 5005 (Active)
            </span>
        </div>
    </div>

    <!-- Testing Input & Controls -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
            <span>🔍 Upload or Select Image to Test Visual Matching</span>
        </h3>

        <form action="<?= url('admin/visual-search/debug') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Option 1: File Upload -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                <label class="block text-xs font-bold text-gray-700">1. Upload Local Image File</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer">
                <p class="text-[10px] text-gray-400">Upload any exact or modified product photo</p>
            </div>

            <!-- Option 2: Image URL -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                <label class="block text-xs font-bold text-gray-700">2. Enter Image URL</label>
                <input type="url" name="image_url" placeholder="https://example.com/photo.jpg" class="w-full h-9 px-3 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500">
                <p class="text-[10px] text-gray-400">Direct HTTP or HTTPS image URL</p>
            </div>

            <!-- Option 3: Existing Product Dropdown -->
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                <label class="block text-xs font-bold text-gray-700">3. Select Catalog Product</label>
                <select name="product_id" class="w-full h-9 px-3 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-orange-500">
                    <option value="">-- Pick Existing Product --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $selectedProductId == $p['id'] ? 'selected' : '' ?>>
                            [ID: <?= $p['id'] ?>] <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-gray-400">Select product to test against catalog</p>
            </div>

            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    Run Visual Feature & Similarity Analysis &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- Analysis Output Section -->
    <?php if ($analysis): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Query Image & Vector Telemetry Card -->
            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span>Query Image Telemetry</span>
                    <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-full">
                        <?= $analysis['embedding_generated'] ? 'Vector Generated' : 'Parse Error' ?>
                    </span>
                </h3>

                <div class="text-center">
                    <div class="w-36 h-36 mx-auto rounded-xl overflow-hidden border-2 border-orange-500 bg-gray-100 shadow-xs mb-3">
                        <img src="<?= htmlspecialchars($analysis['resolved_path'] && preg_match('~^https?://~i', $analysis['resolved_path']) ? $analysis['resolved_path'] : asset(ltrim(parse_url($analysis['query_image_path'], PHP_URL_PATH), '/'))) ?>"
                             class="w-full h-full object-cover"
                             onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                    </div>
                </div>

                <div class="space-y-2 text-xs font-medium text-gray-600">
                    <div class="flex justify-between border-b border-gray-100 py-1.5">
                        <span class="text-gray-400">Vector Dimensions:</span>
                        <span class="font-bold text-gray-900"><?= $analysis['vector_dimensions'] ?> floats</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-1.5">
                        <span class="text-gray-400">dHash MD5 Signature:</span>
                        <span class="font-mono text-gray-800 font-bold"><?= $analysis['dhash'] ?? 'N/A' ?></span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 py-1.5">
                        <span class="text-gray-400">Extraction Time:</span>
                        <span class="font-bold text-blue-600"><?= $analysis['embedding_time_ms'] ?> ms</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-gray-400 block mb-1">Source Path / URL:</span>
                        <span class="font-mono text-[11px] text-gray-700 break-all bg-gray-50 p-2 rounded-lg block">
                            <?= htmlspecialchars($analysis['query_image_path']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top 5 Closest Matching Products Table -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">Top 5 Closest Products & Cosine Similarity Scores</h3>
                    <div class="text-[11px] text-gray-500 font-medium">
                        Exact: <strong class="text-emerald-600">&ge; 0.85</strong> | Similar: <strong class="text-orange-600">0.60 &ndash; 0.85</strong> | Fallback: <strong class="text-gray-500">&lt; 0.60</strong>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-400 font-bold uppercase text-[10px] tracking-wider">
                                <th class="py-2.5 px-3">Rank</th>
                                <th class="py-2.5 px-3">Product</th>
                                <th class="py-2.5 px-3 text-right">Raw Score</th>
                                <th class="py-2.5 px-3">Similarity Bar</th>
                                <th class="py-2.5 px-3">Classification</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($analysis['top_5_matches'] as $idx => $m): ?>
                                <?php
                                $score = (float)$m['raw_score'];
                                $pct = (float)$m['similarity_pct'];
                                
                                $badgeBg = 'bg-gray-100 text-gray-700 border-gray-300';
                                $barBg = 'bg-gray-400';
                                if ($score >= 0.85) {
                                    $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-300';
                                    $barBg = 'bg-emerald-500';
                                } elseif ($score >= 0.60) {
                                    $badgeBg = 'bg-orange-50 text-orange-700 border-orange-300';
                                    $barBg = 'bg-orange-500';
                                }
                                ?>
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="py-3 px-3 font-bold text-gray-400">#<?= $idx + 1 ?></td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-3">
                                            <img src="<?= htmlspecialchars($m['image_url']) ?>" class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0">
                                            <div>
                                                <a href="<?= url('product/' . $m['slug']) ?>" target="_blank" class="font-bold text-gray-900 hover:text-orange-600 line-clamp-1">
                                                    <?= htmlspecialchars($m['name']) ?>
                                                </a>
                                                <div class="text-[10px] text-gray-400">ID: <?= $m['product_id'] ?> | Category: <?= htmlspecialchars($m['category_name']) ?></div>
                                                <div class="text-[10px] text-orange-600 font-medium">Matched: <span class="capitalize font-bold"><?= htmlspecialchars($m['matched_type'] ?? 'main') ?></span> Image<?= !empty($m['variant_id']) ? " (Variant ID {$m['variant_id']})" : '' ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-gray-900 text-sm">
                                        <?= sprintf('%.4f', $score) ?>
                                    </td>
                                    <td class="py-3 px-3 w-36">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full <?= $barBg ?> rounded-full" style="width: <?= max(5, $pct) ?>%;"></div>
                                            </div>
                                            <span class="text-[10px] font-bold text-gray-700 w-10 text-right"><?= $pct ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="inline-block px-2.5 py-1 text-[10px] font-bold rounded-lg border <?= $badgeBg ?>">
                                            <?= htmlspecialchars($m['match_category']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<script>
function triggerCatalogReindex() {
    const btn = document.getElementById('reindexBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-spin">&circlearrowright;</span> Reindexing Catalog...';

    fetch('<?= url("admin/visual-search/reindex") ?>', {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        alert(data.message || 'Reindex complete!');
        window.location.reload();
    })
    .catch(err => {
        alert('Reindex completed.');
        window.location.reload();
    });
}
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
