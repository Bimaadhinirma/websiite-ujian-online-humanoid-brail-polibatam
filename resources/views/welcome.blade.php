<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ujian Online - Humanoid</title>
    <script src="/js/tailwind.js"></script>
    <style>
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); } 50% { box-shadow: 0 0 40px rgba(59, 130, 246, 0.8); } }
        .float { animation: float 3s ease-in-out infinite; }
        .rotate { animation: rotate 20s linear infinite; }
        .pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }
        .gradient-bg { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); }
        .robot-gradient { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); }
    </style>
</head>
<body class="min-h-screen gradient-bg overflow-x-hidden">
    <nav class="bg-white/10 backdrop-blur-md border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 robot-gradient rounded-lg flex items-center justify-center rotate">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h1 class="text-xl font-bold text-white">Ujian Online Humanoid</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        @if(Auth::user()->role == 1)
                            <a href="{{ route('admin.dashboard') }}" class="px-5 py-2 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Dashboard Admin</a>
                        @else
                            <a href="{{ route('participant.exam.index') }}" class="px-5 py-2 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Dashboard Peserta</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2 bg-white/20 text-white rounded-lg font-semibold hover:bg-white/30 transition duration-300 backdrop-blur-sm">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-white space-y-6">
                <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4">🤖 Platform Ujian Digital</div>
                <h1 class="text-5xl lg:text-6xl font-bold leading-tight">Website Ujian <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-blue-600">Humanoid</span></h1>
                <p class="text-xl text-white/90 leading-relaxed">Platform ujian digital yang dirancang khusus untuk Tim Humanoid.</p>
                <div class="flex flex-wrap gap-4 pt-4">
                    @guest
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-white text-blue-600 rounded-xl font-bold text-lg hover:bg-gray-100 transition duration-300 pulse-glow">Mulai Ujian</a>
                    @else
                        @if(Auth::user()->role == 1)
                            <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-white text-blue-600 rounded-xl font-bold text-lg hover:bg-gray-100 transition duration-300 pulse-glow">Dashboard Admin</a>
                        @else
                            <a href="{{ route('participant.exam.index') }}" class="px-8 py-4 bg-white text-blue-600 rounded-xl font-bold text-lg hover:bg-gray-100 transition duration-300 pulse-glow">Lihat Ujian</a>
                        @endif
                    @endguest
                </div>
            </div>
            <div class="relative hidden lg:block"><div class="float"><div class="relative mx-auto w-64 h-80"><div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-40 h-32 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl shadow-2xl"><div class="absolute -top-8 left-1/2 transform -translate-x-1/2 w-2 h-8 bg-gradient-to-t from-blue-600 to-blue-500 rounded-full"></div><div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-blue-500 rounded-full pulse-glow"></div><div class="flex justify-center space-x-8 pt-10"><div class="w-8 h-8 bg-white rounded-full pulse-glow"></div><div class="w-8 h-8 bg-white rounded-full pulse-glow"></div></div></div><div class="absolute top-32 left-1/2 transform -translate-x-1/2 w-48 h-40 bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl shadow-2xl"></div><div class="absolute top-40 -left-12 w-16 h-24 bg-gradient-to-b from-blue-600 to-blue-800 rounded-full shadow-xl"></div></div></div></div>
        </div>
    </div>
    <footer class="bg-white/10 backdrop-blur-md border-t border-white/20 mt-20"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"><div class="text-center text-white/70"><p>&copy; 2025 Ujian Online - Humanoid.</p></div></div></footer>
</body>
</html>
