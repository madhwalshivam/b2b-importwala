<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Access Denied | ImportWale Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full text-center bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl">
        <div class="w-16 h-16 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h1 class="text-3xl font-black text-white mb-2">Access Restricted</h1>
        <p class="text-gray-400 mb-6">Your employee role does not have permission to view or manage this module (Required: <code class="bg-gray-900 px-2 py-1 rounded text-red-400"><?= htmlspecialchars($permission ?? 'Module Access') ?></code>).</p>
        <a href="<?= url('admin/dashboard') ?>" class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition">Return To Admin Dashboard</a>
    </div>
</body>
</html>
