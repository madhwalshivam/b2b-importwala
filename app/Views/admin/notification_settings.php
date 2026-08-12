<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6 font-sans">

    <!-- Title Card -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-gray-900 shadow-xs">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 leading-snug">Notification & Communication Settings</h2>
            <p class="text-xs text-gray-500 mt-0.5 font-medium">Manage Admin Email & WhatsApp Cloud API alert recipients
                for Orders, Wishlist & Cart events</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?= url('admin/notification-settings/test?channel=email') ?>"
                class="h-9 px-3 bg-gray-100 text-gray-700 hover:bg-gray-900 text-xs font-semibold rounded-xl transition flex items-center space-x-1">
                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                <span>Test Email</span>
            </a>
            <a href="<?= url('admin/notification-settings/test?channel=whatsapp') ?>"
                class="h-9 px-3 bg-green-50 text-green-700 hover:bg-green-100 text-xs font-semibold rounded-xl transition flex items-center space-x-1 border border-green-200">
                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                <span>Test WhatsApp</span>
            </a>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form action="<?= url('admin/notification-settings/update') ?>" method="POST"
        class="bg-white p-8 rounded-2xl border border-gray-900 space-y-6 shadow-xs">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">

            <!-- Email Recipient -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-900 space-y-3">
                <label
                    class="block font-semibold text-gray-900 uppercase text-xs tracking-wider flex items-center space-x-1.5">
                    <i data-lucide="mail" class="w-4 h-4 text-red-600"></i>
                    <span>Admin Notification Email *</span>
                </label>
                <input type="email" name="notification_email" required
                    value="<?= htmlspecialchars($notification_email) ?>"
                    class="w-full h-11 px-4 bg-white border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                <p class="text-[11px] text-gray-500 font-medium">All new orders, wishlist adds & cart activity alerts
                    will be emailed here.</p>
            </div>

            <!-- WhatsApp Number -->
            <div class="bg-gray-50 p-5 rounded-xl border border-gray-900 space-y-3">
                <label
                    class="block font-semibold text-gray-900 uppercase text-xs tracking-wider flex items-center space-x-1.5">
                    <i data-lucide="phone" class="w-4 h-4 text-green-600"></i>
                    <span>Admin WhatsApp Number *</span>
                </label>
                <input type="text" name="notification_whatsapp" required
                    value="<?= htmlspecialchars($notification_whatsapp) ?>"
                    class="w-full h-11 px-4 bg-white border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                <p class="text-[11px] text-gray-500 font-medium">10-digit mobile number for instant WhatsApp alerts
                    (default: 9217714452).</p>
            </div>

            <!-- Meta WhatsApp Cloud API Token (Optional) -->
            <div class="sm:col-span-2 bg-gray-50 p-5 rounded-xl border border-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-900 pb-2">
                    <label
                        class="block font-semibold text-gray-900 uppercase text-xs tracking-wider flex items-center space-x-1.5">
                        <i data-lucide="key" class="w-4 h-4 text-blue-600"></i>
                        <span>Meta / WhatsApp Cloud API Credentials (Optional)</span>
                    </label>
                    <span class="text-[10px] bg-blue-100 text-blue-800 font-semibold px-2 py-0.5 rounded-md">API
                        CONFIG</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1 text-[11px]">Phone Number
                            ID</label>
                        <input type="text" name="whatsapp_phone_number_id"
                            value="<?= htmlspecialchars($whatsapp_phone_number_id) ?>" placeholder="e.g. 104829104928"
                            class="w-full h-11 px-4 bg-white border border-gray-900 rounded-xl font-mono text-xs font-semibold text-gray-900">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1 text-[11px]">Bearer Access
                            Token</label>
                        <input type="password" name="whatsapp_api_token"
                            value="<?= htmlspecialchars($whatsapp_api_token) ?>" placeholder="EAAG..."
                            class="w-full h-11 px-4 bg-white border border-gray-900 rounded-xl font-mono text-xs font-semibold text-gray-900">
                    </div>
                </div>
            </div>

        </div>

        <div class="flex justify-end pt-4 border-t border-gray-100">
            <button type="submit"
                class="h-11 px-8 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs">Save
                Settings</button>
        </div>
    </form>

    <!-- Recent Notification Audit Logs Table -->
    <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-4 shadow-xs">
        <h3 class="font-semibold text-gray-900 text-sm flex items-center space-x-2">
            <i data-lucide="history" class="w-4 h-4 text-gray-500"></i>
            <span>Recent Notification Audit Log (`notification_log`)</span>
        </h3>

        <div class="overflow-x-auto rounded-xl border border-gray-900 text-xs">
            <table class="w-full text-left">
                <thead class="bg-gray-50 font-semibold text-gray-700 border-b border-gray-900 uppercase tracking-wider">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Event</th>
                        <th class="p-3">Channel</th>
                        <th class="p-3">Recipient</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400 font-medium">No notification events
                                recorded yet. Place an order or click Test Email to verify.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3 font-mono text-gray-500">#<?= $l['id'] ?></td>
                                <td class="p-3 font-semibold text-gray-900"><?= htmlspecialchars($l['event_type']) ?></td>
                                <td class="p-3">
                                    <span
                                        class="px-2 py-0.5 rounded-md font-semibold text-[10px] uppercase <?= $l['channel'] === 'whatsapp' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= htmlspecialchars($l['channel']) ?>
                                    </span>
                                </td>
                                <td class="p-3 font-mono text-gray-700"><?= htmlspecialchars($l['recipient']) ?></td>
                                <td class="p-3">
                                    <span
                                        class="px-2 py-0.5 rounded-md font-semibold text-[10px] uppercase <?= $l['status'] === 'sent' ? 'bg-green-500/10 text-green-700 border border-green-500/20' : ($l['status'] === 'simulated' ? 'bg-amber-500/10 text-amber-700 border border-amber-500/20' : 'bg-red-500/10 text-red-700 border border-red-500/20') ?>">
                                        <?= htmlspecialchars($l['status']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500 text-[11px]"><?= $l['created_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>