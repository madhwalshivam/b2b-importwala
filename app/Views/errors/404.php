<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found | ImportWale</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full text-center bg-white p-8 rounded-2xl shadow-xl">
        <h1 class="text-6xl font-black text-red-600 mb-2">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
        <p class="text-gray-600 mb-6">The electric scooter accessory or page you are looking for does not exist or has
            been moved.</p>
        <a href="<?= url('/') ?>"
            class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition">Back
            To Homepage</a>
    </div>
</body>

</html>