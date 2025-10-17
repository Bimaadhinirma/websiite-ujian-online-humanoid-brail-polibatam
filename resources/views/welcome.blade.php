<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ujian - Humanoid</title>
    <script src="/js/tailwind.js"></script>
    <style>
        /* subtle helpers kept for minimal motion */
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="min-h-screen bg-white text-gray-800 flex flex-col">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h1 class="text-lg font-semibold text-gray-900">Website Ujian Digital — Humanoid</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        @if(Auth::user()->role == 1)
                            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-orange-50 text-orange-600 rounded-lg font-medium hover:bg-orange-100 transition duration-200">Dashboard Admin</a>
                        @else
                            <a href="{{ route('participant.exam.index') }}" class="px-4 py-2 bg-orange-50 text-orange-600 rounded-lg font-medium hover:bg-orange-100 transition duration-200">Dashboard Peserta</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition duration-200">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 flex-grow">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-block px-4 py-2 bg-orange-50 rounded-full text-sm font-semibold text-orange-600 mb-2">🤖 Digital Examination Platform</div>
                <h1 class="text-4xl lg:text-5xl font-bold leading-tight text-gray-900">Humanoid Examination <span class="text-orange-600">— Website Digital Tests</span></h1>
                <p class="text-lg text-gray-600 leading-relaxed">A clean, secure and easy-to-use platform to create and deliver timed exams with advanced grading options.</p>
                <div class="flex flex-wrap gap-4 pt-4">
                    @guest
                        <a href="{{ route('login') }}" class="px-6 py-3 bg-orange-600 text-white rounded-lg font-semibold text-base hover:bg-orange-700 transition">Get Started</a>
                    @else
                        @if(Auth::user()->role == 1)
                            <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 bg-white border border-gray-200 text-orange-600 rounded-lg font-semibold text-base hover:bg-gray-50 transition">Admin Dashboard</a>
                        @else
                            <a href="{{ route('participant.exam.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-orange-600 rounded-lg font-semibold text-base hover:bg-gray-50 transition">View Exams</a>
                        @endif
                    @endguest
                </div>
            </div>
            <div class="relative hidden lg:flex items-center justify-center">
                <div class="mx-auto w-72 h-84 bg-white rounded-2xl shadow-xl p-6 flex items-center justify-center">
                    <div class="w-48 h-48 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center float">
                        <svg class="w-24 h-24 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-gray-50 border-t mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; 2025 Humanoid BRAIL — Digital Examination.</p>
            </div>
        </div>
    </footer>
</body>
</html>
