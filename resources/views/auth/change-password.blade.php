<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password</title>
    <script src="/js/tailwind.js"></script>
</head>
<body class="bg-gray-100">
    @if(Auth::user()->isAdmin())
        @include('admin.layouts.navbar')
    @endif

    <div class="max-w-md mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-6">Ganti Password</h2>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('change-password.post') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Password Saat Ini</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Password Baru</label>
                    <input type="password" name="new_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm sm:text-base">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required minlength="6">
                </div>

                <div class="flex flex-col sm:flex-row justify-between gap-3">
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('participant.exam.index') }}" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 text-center">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
