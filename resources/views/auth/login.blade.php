<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ujian Humanoid</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Ujian Humanoid</h1>
            <p class="text-gray-600 mt-2 text-sm sm:text-base">Silakan masuk untuk melanjutkan ke platform ujian</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-semibold mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200" 
                    required autofocus>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" id="password" name="password" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-200" 
                    required>
            </div>

            <button type="submit" 
                class="w-full bg-orange-600 text-white py-2 rounded-lg hover:bg-orange-700 transition duration-200 font-semibold">
                Login
            </button>
        </form>
            <br>
            <button type="button" onclick="window.history.back();" 
                class="w-full bg-orange-600 text-white py-2 rounded-lg hover:bg-orange-700 transition duration-200 font-semibold">
                Back
            </button>

        <!-- <div class="mt-6 text-center text-sm text-gray-600">
            <p>Demo Account:</p>
            <p class="mt-1">Admin: <span class="font-semibold">admin / admin123</span></p>
            <p>Participant: <span class="font-semibold">participant1 / participant123</span></p>
        </div> -->
    </div>
</body>
</html>
