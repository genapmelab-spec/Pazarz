<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pazarz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Pazarz</h1>
            <p class="text-gray-500 mt-2">Sign in to your account</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-black">Forgot password?</a>
            </div>

            <button type="submit" class="w-full bg-black text-white py-3 rounded-full font-medium hover:bg-gray-800 transition">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Want to sell on Pazarz?
            <a href="{{ route('seller.register') }}" class="text-black font-medium hover:underline">Register as Seller</a>
        </p>
    </div>
</body>
</html>
