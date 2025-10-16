<nav class="bg-white shadow-lg mb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <h1 class="text-sm sm:text-lg md:text-xl font-bold text-gray-800">Admin - Ujian Humanoid</h1>
                <div class="hidden md:flex space-x-4 ml-8">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-600' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-blue-100 text-blue-600' : '' }}">
                        Participants
                    </a>
                    <a href="{{ route('admin.periods.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.periods.*') ? 'bg-blue-100 text-blue-600' : '' }}">
                        Periods
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-2 sm:space-x-4">
                <span class="hidden sm:block text-gray-700 text-sm">{{ Auth::user()->nama }}</span>
                <a href="{{ route('change-password') }}" class="text-blue-600 hover:text-blue-800" title="Ganti Password">
                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-600 text-white px-2 py-1 sm:px-4 sm:py-2 rounded hover:bg-red-700 text-sm">
                        Logout
                    </button>
                </form>
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-blue-600 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <a href="{{ route('admin.dashboard') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-600' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-blue-100 text-blue-600' : '' }}">
                Participants
            </a>
            <a href="{{ route('admin.periods.index') }}" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md {{ request()->routeIs('admin.periods.*') ? 'bg-blue-100 text-blue-600' : '' }}">
                Periods
            </a>
            <div class="sm:hidden px-3 py-2 text-sm text-gray-700 border-t mt-2 pt-2">
                User: {{ Auth::user()->nama }}
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>
