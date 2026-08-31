<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Application Rejected - Pazarz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-8 py-12 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold tracking-tight mb-2">Aplikasi Ditolak</h1>
        <p class="text-gray-500 mb-6">
            Maaf, aplikasi seller Anda tidak disetujui oleh admin.
            Silakan hubungi support untuk informasi lebih lanjut.
        </p>

        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <p class="text-sm text-gray-600">
                <strong>Status:</strong>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-1">
                    Rejected
                </span>
            </p>
        </div>

        <a href="/" class="inline-block bg-black text-white px-6 py-3 rounded-full font-medium hover:bg-gray-800 transition">
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
