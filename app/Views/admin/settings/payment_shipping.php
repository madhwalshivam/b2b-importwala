<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans max-w-6xl mx-auto">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Integration Settings
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Payment Gateway & Shipping Aggregator</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Razorpay & Shiprocket Settings</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Configure live API credentials, encrypt sensitive secrets, test connections, and manage automated order
                fulfillment.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="openRevealModal()"
                class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Reveal Secrets</span>
            </button>
        </div>
    </div>



    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-900 space-x-6 text-xs font-semibold">
        <button id="tab-btn-razorpay" onclick="switchTab('razorpay')"
            class="pb-3 text-red-600 border-b-2 border-red-600 flex items-center space-x-2 cursor-pointer transition-colors">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>Razorpay Payment Gateway</span>
        </button>
        <button id="tab-btn-shiprocket" onclick="switchTab('shiprocket')"
            class="pb-3 text-gray-500 hover:text-gray-900 border-b-2 border-transparent flex items-center space-x-2 cursor-pointer transition-colors">
            <i data-lucide="truck" class="w-4 h-4"></i>
            <span>Shiprocket Shipping Provider</span>
        </button>
        <button id="tab-btn-audit" onclick="switchTab('audit')"
            class="pb-3 text-gray-500 hover:text-gray-900 border-b-2 border-transparent flex items-center space-x-2 cursor-pointer transition-colors">
            <i data-lucide="history" class="w-4 h-4"></i>
            <span>Settings Audit Logs</span>
        </button>
    </div>

    <!-- TAB 1: Razorpay Payment Gateway -->
    <div id="tab-content-razorpay" class="space-y-6">
        <form action="<?= url('admin/settings/payment-shipping/razorpay') ?>" method="POST"
            class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-6">
            <?= csrf_field() ?>

            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Razorpay API Configuration</h3>
                    <p class="text-xs text-gray-500">API Key ID, Key Secret, and Webhook Signing Secret</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-gray-700">Status:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            <?= ($razorpay['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <div
                            class="w-9 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:bg-red-600 transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                </div>
            </div>

            <!-- AJAX Result Alert -->
            <div id="razorpay-test-result"
                class="hidden p-4 rounded-xl text-xs font-semibold flex items-center space-x-2"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Gateway Provider</label>
                    <input type="text" value="Razorpay (Pluggable)" disabled
                        class="w-full h-11 px-4 bg-gray-100 border border-gray-900 rounded-xl font-semibold text-gray-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Environment Mode</label>
                    <select name="mode"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <option value="test" <?= ($razorpay['mode'] ?? 'test') === 'test' ? 'selected' : '' ?>>Test /
                            Sandbox Mode</option>
                        <option value="live" <?= ($razorpay['mode'] ?? 'test') === 'live' ? 'selected' : '' ?>>Live /
                            Production Mode</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Razorpay Key ID <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="razorpay_key_id" name="key_id"
                        value="<?= htmlspecialchars($razorpay['key_id'] ?? '') ?>" placeholder="rzp_test_..." required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-mono text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Razorpay Key Secret (Encrypted AES-256) <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="razorpay_key_secret" name="key_secret"
                            value="<?= htmlspecialchars($maskedKeySecret) ?>" placeholder="••••••••••••" required
                            class="w-full h-11 pl-4 pr-10 bg-gray-50 border border-gray-900 rounded-xl font-mono text-gray-900 focus:outline-none focus:border-red-600">
                        <button type="button" onclick="openRevealModal()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700 mb-1">Razorpay Webhook Secret (Encrypted
                        AES-256)</label>
                    <div class="relative">
                        <input type="password" id="razorpay_webhook_secret" name="webhook_secret"
                            value="<?= htmlspecialchars($maskedWebhookSecret) ?>" placeholder="••••••••••••"
                            class="w-full h-11 pl-4 pr-10 bg-gray-50 border border-gray-900 rounded-xl font-mono text-gray-900 focus:outline-none focus:border-red-600">
                        <button type="button" onclick="openRevealModal()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Webhook URL: <code
                            class="bg-gray-100 px-2 py-0.5 rounded font-mono text-gray-700"><?= url('api/webhooks/razorpay') ?></code>
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button" onclick="testRazorpayConnection()" id="btn-test-razorpay"
                    class="h-11 px-5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Test API Connection</span>
                </button>

                <button type="submit"
                    class="h-11 px-6 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Razorpay Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 2: Shiprocket Shipping Aggregator -->
    <div id="tab-content-shiprocket" class="hidden space-y-6">
        <form action="<?= url('admin/settings/payment-shipping/shiprocket') ?>" method="POST"
            class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-6">
            <?= csrf_field() ?>

            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Shiprocket API Configuration</h3>
                    <p class="text-xs text-gray-500">Automated courier assignment, AWB generation & live tracking</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-gray-700">Status:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            <?= ($shiprocket['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <div
                            class="w-9 h-5 bg-gray-300 peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:bg-red-600 transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                </div>
            </div>

            <!-- AJAX Result Alert -->
            <div id="shiprocket-test-result"
                class="hidden p-4 rounded-xl text-xs font-semibold flex items-center space-x-2"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Shipping Provider</label>
                    <input type="text" value="Shiprocket Aggregator (Pluggable)" disabled
                        class="w-full h-11 px-4 bg-gray-100 border border-gray-900 rounded-xl font-semibold text-gray-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Courier Assignment Mode</label>
                    <select name="auto_assign_courier"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <option value="1" <?= ($shiprocket['auto_assign_courier'] ?? 1) ? 'selected' : '' ?>>Auto-Assign
                            (Fastest / Cheapest Partner)</option>
                        <option value="0" <?= !($shiprocket['auto_assign_courier'] ?? 1) ? 'selected' : '' ?>>Manual
                            Courier Selection in Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Shiprocket Email Address <span
                            class="text-red-500">*</span></label>
                    <input type="email" id="shiprocket_email" name="email"
                        value="<?= htmlspecialchars($shiprocket['email'] ?? '') ?>" placeholder="admin@importwale.com"
                        required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Shiprocket Password (Encrypted AES-256) <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="shiprocket_password" name="password"
                            value="<?= htmlspecialchars($maskedShiprocketPass) ?>" placeholder="••••••••••••" required
                            class="w-full h-11 pl-4 pr-10 bg-gray-50 border border-gray-900 rounded-xl font-mono text-gray-900 focus:outline-none focus:border-red-600">
                        <button type="button" onclick="openRevealModal()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-gray-700 mb-1">Pickup Location Name</label>
                    <select id="pickup_location_select" name="pickup_location"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <?php foreach ($pickupLocations as $loc): ?>
                            <?php $locName = $loc['pickup_location']; ?>
                            <option value="<?= htmlspecialchars($locName) ?>" <?= ($shiprocket['pickup_location'] ?? 'Primary') === $locName ? 'selected' : '' ?>>
                                <?= htmlspecialchars($locName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Locations are synchronized from your registered Shiprocket warehouse profile.</p>
                </div>

                <div class="md:col-span-2 pt-2 border-t border-gray-100">
                    <label class="block font-semibold text-gray-700 mb-1">Shiprocket Auto-Status Webhook URL</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" readonly value="<?= url('api/webhooks/shiprocket') ?>"
                            class="w-full h-10 px-3 bg-gray-100 border border-gray-300 rounded-xl font-mono text-xs text-gray-700 select-all">
                        <button type="button" onclick="navigator.clipboard.writeText('<?= url('api/webhooks/shiprocket') ?>'); alert('Webhook URL copied to clipboard!');"
                            class="h-10 px-3 bg-gray-800 text-white rounded-xl text-xs font-semibold hover:bg-gray-900 transition flex items-center space-x-1 cursor-pointer shrink-0">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span>Copy</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Add this Webhook URL in Shiprocket Panel (Settings → Webhooks) to trigger real-time status updates (In Transit, Out for Delivery, Delivered, Cancelled) on your website!</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button type="button" onclick="testShiprocketConnection()" id="btn-test-shiprocket"
                    class="h-11 px-5 bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Test API & Refresh Locations</span>
                </button>

                <button type="submit"
                    class="h-11 px-6 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Shiprocket Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- TAB 3: Audit Logs -->
    <div id="tab-content-audit" class="hidden space-y-6">
        <div class="bg-white rounded-2xl border border-gray-900 overflow-hidden shadow-xs">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">Settings Change Audit Trail</h3>
                <span class="text-xs text-gray-500">Security & compliance activity history</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr
                            class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-900 uppercase tracking-wider text-[11px]">
                            <th class="p-4 pl-6">ID</th>
                            <th class="p-4">Admin Email</th>
                            <th class="p-4">Action</th>
                            <th class="p-4">Details</th>
                            <th class="p-4">IP Address</th>
                            <th class="p-4 pr-6 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($auditLogs)): ?>
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-400 font-medium">No settings changes logged
                                    yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($auditLogs as $log): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 pl-6 font-mono font-semibold text-gray-900">#<?= $log['id'] ?></td>
                                    <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($log['admin_email']) ?>
                                    </td>
                                    <td class="p-4"><span
                                            class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded font-semibold text-[11px]"><?= htmlspecialchars($log['action']) ?></span>
                                    </td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($log['details'] ?? '') ?></td>
                                    <td class="p-4 font-mono text-gray-500"><?= htmlspecialchars($log['ip_address'] ?? '') ?>
                                    </td>
                                    <td class="p-4 pr-6 text-right text-gray-500 font-mono">
                                        <?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- REVEAL SECRETS MODAL -->
<div id="revealModal"
    class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                <i data-lucide="lock" class="w-4 h-4 text-red-600"></i>
                <span>Admin Password Verification</span>
            </h3>
            <button onclick="closeRevealModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <p class="text-xs text-gray-600">
            Re-enter your admin account password to decrypt and reveal API Key Secrets and Passwords.
        </p>
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Admin Password</label>
            <input type="password" id="modal_admin_password" placeholder="Enter your password..."
                class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-xs focus:outline-none focus:border-red-600">
            <div id="modal_error" class="hidden text-xs text-red-600 font-semibold mt-1"></div>
        </div>
        <div class="flex justify-end space-x-3 pt-2">
            <button type="button" onclick="closeRevealModal()"
                class="h-10 px-4 bg-gray-100 hover:bg-gray-900 text-gray-700 font-semibold text-xs rounded-xl">Cancel</button>
            <button type="button" onclick="submitRevealSecrets()" id="btn-submit-reveal"
                class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs">Verify
                & Unmask</button>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        ['razorpay', 'shiprocket', 'audit'].forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            const content = document.getElementById('tab-content-' + t);
            if (t === tab) {
                btn.className = 'pb-3 text-red-600 border-b-2 border-red-600 flex items-center space-x-2 cursor-pointer transition-colors font-semibold';
                content.classList.remove('hidden');
            } else {
                btn.className = 'pb-3 text-gray-500 hover:text-gray-900 border-b-2 border-transparent flex items-center space-x-2 cursor-pointer transition-colors font-semibold';
                content.classList.add('hidden');
            }
        });
    }

    function testRazorpayConnection() {
        const btn = document.getElementById('btn-test-razorpay');
        const resultBox = document.getElementById('razorpay-test-result');
        const keyId = document.getElementById('razorpay_key_id').value;
        const keySecret = document.getElementById('razorpay_key_secret').value;

        btn.disabled = true;
        btn.innerText = 'Pinging Razorpay API...';
        resultBox.className = 'hidden';

        fetch('<?= url("admin/settings/payment-shipping/test-razorpay") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf_token': '<?= csrf_token() ?>',
                'key_id': keyId,
                'key_secret': keySecret
            })
        })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="activity" class="w-4 h-4"></i><span>Test API Connection</span>';
                lucide.createIcons();

                resultBox.classList.remove('hidden');
                if (data.success) {
                    resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center space-x-2';
                    resultBox.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i><span>' + data.message + '</span>';
                } else {
                    resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 flex items-center space-x-2';
                    resultBox.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i><span>' + data.message + '</span>';
                }
                lucide.createIcons();
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Test API Connection';
                resultBox.classList.remove('hidden');
                resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200';
                resultBox.innerText = 'Network error testing Razorpay API connection.';
            });
    }

    function testShiprocketConnection() {
        const btn = document.getElementById('btn-test-shiprocket');
        const resultBox = document.getElementById('shiprocket-test-result');
        const email = document.getElementById('shiprocket_email').value;
        const password = document.getElementById('shiprocket_password').value;

        btn.disabled = true;
        btn.innerText = 'Authenticating with Shiprocket...';
        resultBox.className = 'hidden';

        fetch('<?= url("admin/settings/payment-shipping/test-shiprocket") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf_token': '<?= csrf_token() ?>',
                'email': email,
                'password': password
            })
        })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="activity" class="w-4 h-4"></i><span>Test API & Refresh Locations</span>';
                lucide.createIcons();

                resultBox.classList.remove('hidden');
                if (data.success) {
                    resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center space-x-2';
                    resultBox.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i><span>' + data.message + '</span>';

                    // Populate pickup locations dropdown if returned
                    if (data.locations && data.locations.length > 0) {
                        const select = document.getElementById('pickup_location_select');
                        select.innerHTML = '';
                        data.locations.forEach(loc => {
                            const opt = document.createElement('option');
                            opt.value = loc;
                            opt.textContent = loc;
                            select.appendChild(opt);
                        });
                    }
                } else {
                    resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 flex items-center space-x-2';
                    resultBox.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i><span>' + data.message + '</span>';
                }
                lucide.createIcons();
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Test API & Refresh Locations';
                resultBox.classList.remove('hidden');
                resultBox.className = 'p-4 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200';
                resultBox.innerText = 'Network error testing Shiprocket API connection.';
            });
    }

    function openRevealModal() {
        document.getElementById('revealModal').classList.remove('hidden');
        document.getElementById('modal_admin_password').focus();
    }

    function closeRevealModal() {
        document.getElementById('revealModal').classList.add('hidden');
        document.getElementById('modal_admin_password').value = '';
        document.getElementById('modal_error').classList.add('hidden');
    }

    function submitRevealSecrets() {
        const password = document.getElementById('modal_admin_password').value;
        const errorBox = document.getElementById('modal_error');
        const btn = document.getElementById('btn-submit-reveal');

        if (!password) {
            errorBox.innerText = 'Please enter your password.';
            errorBox.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerText = 'Verifying...';

        fetch('<?= url("admin/settings/payment-shipping/reveal-secrets") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf_token': '<?= csrf_token() ?>',
                'admin_password': password
            })
        })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Verify & Unmask';

                if (data.success) {
                    document.getElementById('razorpay_key_secret').value = data.key_secret || '';
                    document.getElementById('razorpay_key_secret').type = 'text';
                    document.getElementById('razorpay_webhook_secret').value = data.webhook_secret || '';
                    document.getElementById('razorpay_webhook_secret').type = 'text';
                    document.getElementById('shiprocket_password').value = data.shiprocket_password || '';
                    document.getElementById('shiprocket_password').type = 'text';

                    closeRevealModal();
                    alert('Secrets unmasked successfully!');
                } else {
                    errorBox.innerText = data.message || 'Verification failed.';
                    errorBox.classList.remove('hidden');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = 'Verify & Unmask';
                errorBox.innerText = 'Error verifying password.';
                errorBox.classList.remove('hidden');
            });
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>