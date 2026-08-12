<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$src = \App\Models\Banner::getImageSrc($banner);
$tabletSrc = \App\Models\Banner::getTabletImageSrc($banner);
$mobileSrc = \App\Models\Banner::getMobileImageSrc($banner);
?>

<div class="space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center space-x-3">
        <a href="<?= url('admin/banners') ?>"
            class="text-xs text-gray-500 hover:text-red-600 transition flex items-center space-x-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i><span>Back to Banners</span>
        </a>
        <span class="text-gray-300">/</span>
        <h1 class="text-xl font-semibold text-gray-900">Edit Banner</h1>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center space-x-2">
            <i data-lucide="pencil" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Edit:
                <?= htmlspecialchars($banner['title'] ?: 'Untitled Banner') ?>
            </h2>
        </div>

        <form action="<?= url('admin/banners/update/' . $banner['id']) ?>" method="POST" enctype="multipart/form-data"
            class="p-5 space-y-5">
            <?= csrf_field() ?>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Banner Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($banner['title']) ?>"
                    class="w-full h-10 px-3 border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:border-red-600">
            </div>

            <!-- ── Device-Specific Images ── -->
            <div class="bg-gray-50/70 dark:bg-slate-900/80 p-4 rounded-2xl border border-gray-200 dark:border-slate-700 space-y-3">
                <h3 class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider flex items-center space-x-1.5">
                    <i data-lucide="monitor-smartphone" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                    <span>Device-Specific Banners</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- ── DESKTOP ── -->
                    <div x-data="editBannerUploader('<?= addslashes($src) ?>', '<?= !empty($banner['image_path']) ? 'upload' : 'url' ?>')"
                        class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-gray-200 dark:border-slate-700 shadow-xs space-y-2">

                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="monitor" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <label class="text-xs font-semibold text-gray-800 dark:text-gray-200">Desktop <span
                                    class="text-gray-400 dark:text-gray-500 font-normal">(1025px+)</span></label>
                        </div>

                        <!-- Current Preview -->
                        <div x-show="preview && !removed" class="relative">
                            <img :src="preview" class="w-full h-20 object-cover rounded-lg border border-gray-900">
                            <button type="button" @click="removeImage()"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700 transition"
                                title="Remove desktop image">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div x-show="removed"
                            class="text-[10px] text-red-500 font-semibold flex items-center space-x-1">
                            <i data-lucide="alert-triangle" class="w-3 h-3"></i><span>Image will be removed on
                                save</span>
                        </div>
                        <input type="hidden" name="remove_desktop_image" :value="removed ? '1' : ''">

                        <!-- Toggle Buttons -->
                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Replace File</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>

                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif"
                                @change="onFile($event)"
                                class="block w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 1400 × 480 px · Max 5 MB</span>
                            </p>
                        </div>
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="image_url"
                                value="<?= htmlspecialchars($banner['image_url'] ?? '') ?>"
                                placeholder="https://example.com/desktop-banner.jpg" @input="onUrl($event)"
                                class="w-full h-8 px-2 border border-gray-900 rounded-lg text-xs focus:outline-none focus:border-red-600">
                        </div>
                    </div>

                    <!-- ── TABLET ── -->
                    <div x-data="editBannerUploader('<?= addslashes($tabletSrc ?? '') ?>', '<?= !empty($banner['tablet_image_path']) ? 'upload' : 'url' ?>')"
                        class="bg-white p-3 rounded-xl border border-gray-900 shadow-xs space-y-2">

                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="tablet" class="w-4 h-4 text-red-600"></i>
                            <label class="text-xs font-semibold text-gray-800">Tablet <span
                                    class="text-gray-400 font-normal">(768px–1024px)</span></label>
                        </div>

                        <div x-show="preview && !removed" class="relative">
                            <img :src="preview" class="w-full h-20 object-cover rounded-lg border border-gray-900">
                            <button type="button" @click="removeImage()"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div x-show="removed"
                            class="text-[10px] text-red-500 font-semibold flex items-center space-x-1">
                            <i data-lucide="alert-triangle" class="w-3 h-3"></i><span>Image will be removed on
                                save</span>
                        </div>
                        <input type="hidden" name="remove_tablet_image" :value="removed ? '1' : ''">

                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Replace File</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>

                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="tablet_image_file"
                                accept="image/jpeg,image/png,image/webp,image/gif" @change="onFile($event)"
                                class="block w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 900 × 450 px</span>
                            </p>
                        </div>
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="tablet_image_url"
                                value="<?= htmlspecialchars($banner['tablet_image_url'] ?? '') ?>"
                                placeholder="https://example.com/tablet-banner.jpg" @input="onUrl($event)"
                                class="w-full h-8 px-2 border border-gray-900 rounded-lg text-xs focus:outline-none focus:border-red-600">
                        </div>
                    </div>

                    <!-- ── MOBILE ── -->
                    <div x-data="editBannerUploader('<?= addslashes($mobileSrc ?? '') ?>', '<?= !empty($banner['mobile_image_path']) ? 'upload' : 'url' ?>')"
                        class="bg-white p-3 rounded-xl border border-gray-900 shadow-xs space-y-2">

                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="smartphone" class="w-4 h-4 text-red-600"></i>
                            <label class="text-xs font-semibold text-gray-800">Mobile <span
                                    class="text-gray-400 font-normal">(&lt;768px)</span></label>
                        </div>

                        <div x-show="preview && !removed" class="relative">
                            <img :src="preview" class="w-full h-20 object-cover rounded-lg border border-gray-900">
                            <button type="button" @click="removeImage()"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white rounded-full flex items-center justify-center hover:bg-red-700 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div x-show="removed"
                            class="text-[10px] text-red-500 font-semibold flex items-center space-x-1">
                            <i data-lucide="alert-triangle" class="w-3 h-3"></i><span>Image will be removed on
                                save</span>
                        </div>
                        <input type="hidden" name="remove_mobile_image" :value="removed ? '1' : ''">

                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Replace File</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>

                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="mobile_image_file"
                                accept="image/jpeg,image/png,image/webp,image/gif" @change="onFile($event)"
                                class="block w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 500 × 300 px</span>
                            </p>
                        </div>
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="mobile_image_url"
                                value="<?= htmlspecialchars($banner['mobile_image_url'] ?? '') ?>"
                                placeholder="https://example.com/mobile-banner.jpg" @input="onUrl($event)"
                                class="w-full h-8 px-2 border border-gray-900 rounded-lg text-xs focus:outline-none focus:border-red-600">
                        </div>
                    </div>

                </div><!-- /grid -->
            </div><!-- /device images -->

            <!-- Link & Sort -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Click Link (Optional)</label>
                    <input type="text" name="link_url" value="<?= htmlspecialchars($banner['link_url'] ?? '') ?>"
                        placeholder="e.g. /shop"
                        class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="<?= (int) $banner['sort_order'] ?>" min="0"
                        class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
            </div>

            <!-- Active -->
            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_active" id="is_active_edit" value="1" <?= $banner['is_active'] ? 'checked' : '' ?> class="w-4 h-4 accent-red-600 rounded">
                <label for="is_active_edit" class="text-xs font-semibold text-gray-700">Active (show on
                    homepage)</label>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?= url('admin/banners') ?>"
                    class="h-10 px-5 bg-gray-100 hover:bg-gray-900 text-gray-700 text-xs font-semibold rounded-xl transition flex items-center">Cancel</a>
                <button type="submit"
                    class="h-10 px-6 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-sm flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editBannerUploader(existingSrc, initialMethod) {
        return {
            method: initialMethod || 'upload',
            preview: existingSrc || null,
            removed: false,
            onFile(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.removed = false;
                const reader = new FileReader();
                reader.onload = (ev) => { this.preview = ev.target.result; };
                reader.readAsDataURL(file);
            },
            onUrl(e) {
                const val = e.target.value.trim();
                this.preview = val || null;
                this.removed = false;
            },
            removeImage() {
                this.preview = null;
                this.removed = true;
            }
        };
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>