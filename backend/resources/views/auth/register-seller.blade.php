<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Seller - Pazarz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-8 py-12">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Pazarz</h1>
            <p class="text-gray-500 mt-2">Create your seller account</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('seller.register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <hr class="my-4">

            <div>
                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="business_type" class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                <input type="text" id="business_type" name="business_type" value="{{ old('business_type') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-1">Tax ID (optional)</label>
                <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <button type="submit" class="w-full bg-black text-white py-3 rounded-full font-medium hover:bg-gray-800 transition mt-4">
                Register as Seller
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-black font-medium hover:underline">Sign in</a>
        </p>
    </div>
</body>
</html>
