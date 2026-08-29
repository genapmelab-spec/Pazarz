<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Pazarz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Pazarz</h1>
            <p class="text-gray-500 mt-2">Reset your password</p>
        </div>

        @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>

            <button type="submit" class="w-full bg-black text-white py-3 rounded-full font-medium hover:bg-gray-800 transition">
                Send Reset Link
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="{{ route('login') }}" class="text-black font-medium hover:underline">Back to Login</a>
        </p>
    </div>
</body>
</html>
