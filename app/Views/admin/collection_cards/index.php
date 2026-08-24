<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

    <!-- ===================== TOP HEADER ===================== -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Homepage Collection Cards</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Collection Cards Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                "Trending Collections" jaisi cards manage karo. Har card mein title, image aur manually selected products hote hain.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="<?= url('') ?>" target="_blank"
               class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-2 shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Preview</span>
            </a>
            <button onclick="openAddModal()"
                    class="px-4 py-2.5 bg-red-600 text-white rounded-xl text-xs font-semibold hover:bg-red-700 transition flex items-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add Collection Card</span>
            </button>
        </div>
    </div>

    <!-- ===================== CARDS GRID ===================== -->
    <?php if (empty($cards)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-16 flex flex-col items-center justify-center text-center gap-4">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center">
                <i data-lucide="layout-grid" class="w-8 h-8 text-slate-400"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-700">Abhi koi collection card nahi hai</p>
                <p class="text-xs text-slate-400 mt-1">Upar "Add Collection Card" click karke pehla card banao.</p>
            </div>
            <button onclick="openAddModal()"
                    class="mt-2 px-5 py-2.5 bg-red-600 text-white rounded-xl text-xs font-semibold hover:bg-red-700 transition cursor-pointer">
                + Add Collection Card
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6" id="cardsContainer">
            <?php foreach ($cards as $card):
                $cid = $card['id'];
                $assignedProducts = $cardProducts[$cid] ?? [];
                $assignedIds = array_column($assignedProducts, 'id');
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden" data-card-id="<?= $cid ?>">

                <!-- Card Header -->
                <div class="flex items-start gap-4 p-5 border-b border-slate-100">
                    <!-- Thumbnail -->
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 shrink-0 border border-slate-200">
                        <?php if (!empty($card['image'])): ?>
                            <img src="<?= htmlspecialchars($card['image']) ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i data-lucide="image" class="w-6 h-6 text-slate-300"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-sm text-slate-900 truncate"><?= htmlspecialchars($card['title']) ?></span>
                            <?php if (!empty($card['badge_text'])): ?>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 rounded-full border border-amber-200">
                                    <?= htmlspecialchars($card['badge_text']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($card['is_active']): ?>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 text-slate-500 rounded-full">Hidden</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($card['subtitle'])): ?>
                            <p class="text-xs text-slate-500 mt-0.5 truncate"><?= htmlspecialchars($card['subtitle']) ?></p>
                        <?php endif; ?>
                        <p class="text-[11px] text-slate-400 font-mono mt-1">
                            Link: <?= htmlspecialchars($card['link_url']) ?>  •  Sort: <?= $card['sort_order'] ?>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1 shrink-0">
                        <button onclick='openEditModal(<?= json_encode($card) ?>)'
                                class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Edit Card">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <form action="<?= url('admin/collection-cards/delete/' . $cid) ?>" method="POST"
                              onsubmit="return confirm('\"<?= htmlspecialchars(addslashes($card['title'])) ?>\" aur uske sab products delete ho jayenge. Sure hai?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Card">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Products Panel -->
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="package" class="w-3.5 h-3.5 text-red-500"></i>
                            Products in this card
                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[10px] font-bold" id="count-<?= $cid ?>">
                                <?= count($assignedProducts) ?>
                            </span>
                        </span>
                        <button onclick="openProductModal(<?= $cid ?>, '<?= htmlspecialchars(addslashes($card['title'])) ?>')"
                                class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-[11px] font-semibold hover:bg-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="settings-2" class="w-3 h-3"></i>
                            Manage Products
                        </button>
                    </div>

                    <!-- Assigned Products Preview -->
                    <div id="preview-<?= $cid ?>" class="flex flex-wrap gap-2">
                        <?php if (empty($assignedProducts)): ?>
                            <p class="text-xs text-slate-400 italic" id="empty-<?= $cid ?>">Koi product assign nahi. "Manage Products" click karo.</p>
                        <?php else: ?>
                            <?php foreach (array_slice($assignedProducts, 0, 8) as $ap): ?>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-700 font-medium">
                                    <img src="<?= htmlspecialchars(asset($ap['main_image'] ?? 'assets/images/placeholder.jpg')) ?>"
                                         alt="" class="w-5 h-5 rounded object-cover">
                                    <span class="max-w-[120px] truncate"><?= htmlspecialchars($ap['name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($assignedProducts) > 8): ?>
                                <span class="px-2.5 py-1 bg-red-50 text-red-600 border border-red-100 rounded-lg text-[11px] font-semibold">
                                    +<?= count($assignedProducts) - 8 ?> more
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Hidden assigned IDs for JS -->
                <input type="hidden" id="assigned-<?= $cid ?>"
                       value="<?= htmlspecialchars(json_encode($assignedIds)) ?>">
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- ===================== ADD CARD MODAL ===================== -->
<div id="addModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-white"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">New Collection Card</h3>
            </div>
            <button onclick="closeAddModal()" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="<?= url('admin/collection-cards/store') ?>" method="POST" enctype="multipart/form-data"
              class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Card Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Bags & Accessories"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subtitle / Description</label>
                    <input type="text" name="subtitle" placeholder="e.g. Top 100 · 30 Days Running"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" name="badge_text" placeholder="e.g. HOT, NEW, TRENDING"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Link URL</label>
                    <input type="text" name="link_url" value="/catalog" placeholder="/catalog"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" min="0"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded">
                        <span class="text-xs font-semibold text-slate-700">Active (visible on site)</span>
                    </label>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700">Cover Image</label>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] text-slate-500 mb-1">Upload File</label>
                        <input type="file" name="image_file" accept="image/*"
                               class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 mb-1">Ya Image URL paste karo</label>
                        <input type="text" name="image_url" placeholder="https://..."
                               class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-500 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 transition cursor-pointer">
                    Create Card
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== EDIT CARD MODAL ===================== -->
<div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-slate-800 rounded-lg flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-4 h-4 text-white"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Edit Collection Card</h3>
            </div>
            <button onclick="closeEditModal()" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editForm" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Card Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="edit_title" required
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Subtitle</label>
                    <input type="text" name="subtitle" id="edit_subtitle"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" name="badge_text" id="edit_badge_text"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Link URL</label>
                    <input type="text" name="link_url" id="edit_link_url"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" id="edit_sort_order" min="0"
                           class="w-full h-10 px-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:border-red-500 transition">
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded">
                        <span class="text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-slate-700">Cover Image</label>
                <div id="edit_current_image" class="mb-2 hidden">
                    <img id="edit_image_preview" src="" alt="" class="h-16 w-24 object-cover rounded-xl border border-slate-200">
                    <p class="text-[10px] text-slate-400 mt-1">Current image</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] text-slate-500 mb-1">Nayi image upload karo</label>
                        <input type="file" name="image_file" accept="image/*"
                               class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[10px] text-slate-500 mb-1">Ya URL paste karo</label>
                        <input type="text" name="image_url" id="edit_image_url" placeholder="https://..."
                               class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-500 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-700 transition cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== PRODUCT SELECTOR MODAL ===================== -->
<div id="productModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col" style="max-height: 90vh;">

        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Manage Products</h3>
                    <p class="text-[11px] text-slate-500" id="productModalSubtitle">Card ke liye products select karo</p>
                </div>
            </div>
            <button onclick="closeProductModal()" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="flex flex-1 overflow-hidden">

            <!-- Left: Search & Select Panel -->
            <div class="w-1/2 border-r border-slate-100 flex flex-col">
                <!-- Search -->
                <div class="p-4 border-b border-slate-100 shrink-0">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                        <input type="text" id="productSearch"
                               placeholder="Product naam ya SKU se search karo..."
                               oninput="searchProducts(this.value)"
                               class="w-full h-9 pl-9 pr-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-500 transition">
                    </div>
                </div>

                <!-- Product List -->
                <div class="flex-1 overflow-y-auto p-3 space-y-1" id="productSearchResults">
                    <p class="text-xs text-slate-400 text-center py-8">Search karo ya sab products dekhne ke liye kuch type karo...</p>
                </div>
            </div>

            <!-- Right: Selected Products Panel -->
            <div class="w-1/2 flex flex-col">
                <div class="px-4 py-3 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500"></i>
                        Selected Products
                        <span id="selectedCount" class="px-1.5 py-0.5 bg-red-100 text-red-600 rounded text-[10px] font-bold">0</span>
                    </span>
                    <button onclick="clearAllSelected()" class="text-[10px] text-slate-400 hover:text-red-600 transition cursor-pointer font-medium">
                        Clear All
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-1" id="selectedProductsList">
                    <p class="text-xs text-slate-400 text-center py-8 italic" id="noSelectedMsg">
                        Koi product select nahi hua...
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex justify-between items-center">
            <p class="text-xs text-slate-500">
                <span id="selectedCount2" class="font-bold text-slate-900">0</span> products selected
            </p>
            <div class="flex gap-3">
                <button onclick="closeProductModal()"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                    Cancel
                </button>
                <button onclick="saveProducts()"
                        class="px-5 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 transition cursor-pointer flex items-center gap-2">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i>
                    Save Products
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
// ============================================================
// Collection Cards Admin JS
// ============================================================

let currentCardId  = null;
let selectedProducts = {}; // { id: { id, name, main_image, sku } }
let searchTimeout  = null;
let allProductsCache = <?= json_encode(array_map(function($p) {
    return [
        'id'         => $p['id'],
        'name'       => $p['name'],
        'sku'        => $p['sku'] ?? '',
        'main_image' => asset($p['main_image'] ?? 'assets/images/placeholder.jpg'),
        'price'      => $p['price'] ?? 0,
        'category_name' => $p['category_name'] ?? '',
    ];
}, $allProducts)) ?>;

// ----------- ADD MODAL -----------
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addModal').classList.add('flex');
}
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addModal').classList.remove('flex');
}

// ----------- EDIT MODAL -----------
function openEditModal(card) {
    document.getElementById('edit_title').value       = card.title || '';
    document.getElementById('edit_subtitle').value    = card.subtitle || '';
    document.getElementById('edit_badge_text').value  = card.badge_text || '';
    document.getElementById('edit_link_url').value    = card.link_url || '/catalog';
    document.getElementById('edit_sort_order').value  = card.sort_order || 0;
    document.getElementById('edit_is_active').checked = card.is_active == 1;
    document.getElementById('edit_image_url').value   = '';

    const preview = document.getElementById('edit_current_image');
    const img     = document.getElementById('edit_image_preview');
    if (card.image) {
        img.src = card.image;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }

    document.getElementById('editForm').action = `<?= url('admin/collection-cards/update') ?>/${card.id}`;

    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}

// ----------- PRODUCT MODAL -----------
function openProductModal(cardId, cardTitle) {
    currentCardId    = cardId;
    selectedProducts = {};

    // Load currently assigned products
    const assigned = JSON.parse(document.getElementById('assigned-' + cardId).value || '[]');
    allProductsCache.forEach(p => {
        if (assigned.includes(p.id)) {
            selectedProducts[p.id] = p;
        }
    });

    document.getElementById('productModalSubtitle').textContent = `"${cardTitle}" ke liye products select karo`;
    document.getElementById('productSearch').value = '';

    renderSelectedProducts();
    loadInitialProducts();

    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}
function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
    document.getElementById('productModal').classList.remove('flex');
    currentCardId    = null;
    selectedProducts = {};
}

// ----------- SEARCH PRODUCTS -----------
function loadInitialProducts() {
    renderProductList(allProductsCache.slice(0, 50));
}

function searchProducts(query) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const q = query.toLowerCase().trim();
        if (!q) {
            renderProductList(allProductsCache.slice(0, 50));
            return;
        }
        const filtered = allProductsCache.filter(p =>
            p.name.toLowerCase().includes(q) ||
            (p.sku || '').toLowerCase().includes(q) ||
            (p.category_name || '').toLowerCase().includes(q)
        );
        renderProductList(filtered.slice(0, 50));
    }, 250);
}

function renderProductList(products) {
    const container = document.getElementById('productSearchResults');
    if (!products.length) {
        container.innerHTML = '<p class="text-xs text-slate-400 text-center py-8">Koi product nahi mila...</p>';
        return;
    }

    container.innerHTML = products.map(p => {
        const isSelected = selectedProducts[p.id] !== undefined;
        return `
            <div class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer transition hover:border-red-200 hover:bg-red-50/50 product-item ${isSelected ? 'border-red-300 bg-red-50/60' : 'border-transparent'}"
                 id="pitem-${p.id}" onclick="toggleProduct(${p.id})">
                <div class="w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition ${isSelected ? 'bg-red-600 border-red-600' : 'border-slate-300'}">
                    ${isSelected ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>' : ''}
                </div>
                <img src="${p.main_image}" alt="" class="w-9 h-9 rounded-lg object-cover border border-slate-100 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">${p.name}</p>
                    <p class="text-[10px] text-slate-400">${p.sku || '—'} ${p.category_name ? '• ' + p.category_name : ''}</p>
                </div>
            </div>
        `;
    }).join('');
}

function toggleProduct(productId) {
    const p = allProductsCache.find(x => x.id == productId);
    if (!p) return;

    if (selectedProducts[productId]) {
        delete selectedProducts[productId];
    } else {
        selectedProducts[productId] = p;
    }

    // Update list item style
    const item = document.getElementById('pitem-' + productId);
    if (item) {
        const isNowSelected = selectedProducts[productId] !== undefined;
        item.className = `flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer transition hover:border-red-200 hover:bg-red-50/50 product-item ${isNowSelected ? 'border-red-300 bg-red-50/60' : 'border-transparent'}`;
        const checkbox = item.querySelector('div');
        checkbox.className = `w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition ${isNowSelected ? 'bg-red-600 border-red-600' : 'border-slate-300'}`;
        checkbox.innerHTML = isNowSelected
            ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
            : '';
    }

    renderSelectedProducts();
}

function renderSelectedProducts() {
    const list    = document.getElementById('selectedProductsList');
    const noMsg   = document.getElementById('noSelectedMsg');
    const count   = Object.keys(selectedProducts).length;
    const count1  = document.getElementById('selectedCount');
    const count2  = document.getElementById('selectedCount2');

    count1.textContent = count;
    count2.textContent = count;

    if (!count) {
        list.innerHTML = '<p class="text-xs text-slate-400 text-center py-8 italic" id="noSelectedMsg">Koi product select nahi hua...</p>';
        return;
    }

    list.innerHTML = Object.values(selectedProducts).map((p, i) => `
        <div class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 group">
            <span class="text-[10px] font-bold text-slate-400 w-4">${i + 1}</span>
            <img src="${p.main_image}" alt="" class="w-8 h-8 rounded-lg object-cover border border-slate-100 shrink-0">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate">${p.name}</p>
                <p class="text-[10px] text-slate-400">${p.sku || ''}</p>
            </div>
            <button onclick="toggleProduct(${p.id})"
                    class="p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `).join('');
}

function clearAllSelected() {
    selectedProducts = {};
    renderSelectedProducts();
    // Uncheck all in left panel
    document.querySelectorAll('.product-item').forEach(el => {
        el.className = el.className.replace('border-red-300 bg-red-50/60', 'border-transparent');
        const cb = el.querySelector('div');
        if (cb) {
            cb.className = 'w-5 h-5 rounded border-2 flex items-center justify-center shrink-0 transition border-slate-300';
            cb.innerHTML = '';
        }
    });
}

// ----------- SAVE PRODUCTS -----------
function saveProducts() {
    if (!currentCardId) return;

    const ids  = Object.keys(selectedProducts).map(Number);
    const btn  = document.querySelector('[onclick="saveProducts()"]');
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';
    btn.disabled = true;

    fetch(`<?= url('admin/collection-cards/update-products') ?>/${currentCardId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ product_ids: ids, _token: '<?= csrf_token() ?>' }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update preview on card
            updateCardPreview(currentCardId, ids);
            closeProductModal();
            showToast('Products save ho gaye! (' + ids.length + ' products)', 'success');
        } else {
            showToast('Error: ' + (data.message || 'Save failed'), 'error');
        }
    })
    .catch(() => showToast('Network error. Please retry.', 'error'))
    .finally(() => {
        btn.innerHTML = orig;
        btn.disabled  = false;
    });
}

function updateCardPreview(cardId, productIds) {
    // Count update
    const countEl = document.getElementById('count-' + cardId);
    if (countEl) countEl.textContent = productIds.length;

    // Update assigned IDs
    const hiddenInput = document.getElementById('assigned-' + cardId);
    if (hiddenInput) hiddenInput.value = JSON.stringify(productIds);

    // Rebuild preview chips
    const preview = document.getElementById('preview-' + cardId);
    if (!preview) return;

    const selected = Object.values(selectedProducts);
    if (!selected.length) {
        preview.innerHTML = '<p class="text-xs text-slate-400 italic">Koi product assign nahi.</p>';
        return;
    }

    const chips = selected.slice(0, 8).map(p => `
        <div class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] text-slate-700 font-medium">
            <img src="${p.main_image}" alt="" class="w-5 h-5 rounded object-cover">
            <span class="max-w-[120px] truncate">${p.name}</span>
        </div>
    `).join('');

    const extra = selected.length > 8
        ? `<span class="px-2.5 py-1 bg-red-50 text-red-600 border border-red-100 rounded-lg text-[11px] font-semibold">+${selected.length - 8} more</span>`
        : '';

    preview.innerHTML = chips + extra;
}

// ----------- TOAST -----------
function showToast(msg, type = 'success') {
    const div = document.createElement('div');
    div.className = `fixed top-6 right-6 z-[999] px-5 py-3 rounded-2xl text-white text-xs font-semibold shadow-2xl transition-all ${type === 'success' ? 'bg-emerald-600' : 'bg-red-600'}`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

// ----------- CSRF HELPER -----------
function csrf_token() {
    return document.querySelector('input[name="_token"]')?.value || '';
}

// Close modals on backdrop click
['addModal', 'editModal', 'productModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
            this.classList.remove('flex');
        }
    });
});

// Lucide icons reinit
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});
</script>
