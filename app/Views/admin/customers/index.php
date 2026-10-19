<?php
$title = 'Customers | Admin Panel';
include __DIR__ . '/../layouts/header.php';

$filter = $filter ?? '';
$q = $q ?? '';
$exportQuery = http_build_query(array_filter([
    'q' => $q,
    'filter' => $filter,
]));
?>

<div class="space-y-6 font-sans pb-10">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Customers</h1>
            <p class="text-xs text-slate-500 mt-1"><?= (int)($total ?? 0) ?> accounts. Search by phone or email for the calling team.</p>
        </div>
        <a href="<?= url('admin/customers/export' . ($exportQuery ? '?' . $exportQuery : '')) ?>"
            class="h-10 px-4 inline-flex items-center bg-slate-900 text-white text-xs font-semibold rounded-xl">
            Export CSV
        </a>
    </div>

    <form method="GET" action="<?= url('admin/customers') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Search</label>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Phone or email"
                class="h-10 px-3 border border-slate-200 rounded-xl text-xs w-64">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Filter</label>
            <select name="filter" class="h-10 px-3 border border-slate-200 rounded-xl text-xs">
                <option value="">All customers</option>
                <option value="incomplete" <?= $filter === 'incomplete' ? 'selected' : '' ?>>Profile incomplete</option>
                <option value="google" <?= $filter === 'google' ? 'selected' : '' ?>>Google users</option>
                <option value="phone_unverified" <?= $filter === 'phone_unverified' ? 'selected' : '' ?>>Phone not verified</option>
            </select>
        </div>
        <button class="h-10 px-4 bg-[#f05a29] text-white text-xs font-semibold rounded-xl">Apply</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">GST Number</th>
                        <th class="p-3">Phone verified</th>
                        <th class="p-3">Source</th>
                        <th class="p-3">Profile</th>
                        <th class="p-3">Signed up</th>
                        <th class="p-3">Last login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($users)): ?>
                        <tr><td colspan="9" class="p-6 text-center text-slate-400">No customers match this search.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $phone = preg_replace('/\D+/', '', (string)($user['phone'] ?? ''));
                            $dial = strlen($phone) === 10 ? '91' . $phone : $phone;
                            ?>
                            <tr>
                                <td class="p-3 font-semibold text-slate-900"><?= htmlspecialchars($user['name'] ?? '') ?></td>
                                <td class="p-3"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td class="p-3">
                                    <?php if ($dial !== ''): ?>
                                        <a class="text-[#f05a29] font-semibold" href="tel:+<?= htmlspecialchars($dial) ?>"><?= htmlspecialchars($user['phone']) ?></a>
                                    <?php else: ?>
                                        <span class="text-slate-400">Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if (!empty($user['gst_number'])): ?>
                                        <?= htmlspecialchars((string)$user['gst_number']) ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3"><?= !empty($user['phone_verified']) ? 'Yes' : 'No' ?></td>
                                <td class="p-3"><?= htmlspecialchars($user['signup_source'] ?: $user['auth_provider']) ?></td>
                                <td class="p-3"><?= !empty($user['profile_completed']) ? 'Complete' : 'Incomplete' ?></td>
                                <td class="p-3"><?= htmlspecialchars((string)($user['created_at'] ?? '')) ?></td>
                                <td class="p-3"><?= htmlspecialchars((string)($user['last_login_at'] ?? '—')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
        <div class="flex gap-2 text-xs">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a class="px-3 py-1 rounded-lg border <?= $i === (int)$page ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-white' ?>"
                    href="<?= url('admin/customers?' . http_build_query(['q' => $q, 'filter' => $filter, 'page' => $i])) ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
