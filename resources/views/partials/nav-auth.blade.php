<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if(isset($backUrl) && $backUrl)
            <a href="{{ $backUrl }}" class="text-gray-400 hover:text-gray-700 text-sm transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ $backLabel ?? 'Kembali' }}
            </a>
            <div class="w-px h-4 bg-gray-200"></div>
            @endif
            <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="flex items-center gap-2">
                <div class="w-7 h-7 bg-red-700 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                </div>
                <span class="font-bold text-gray-900 text-sm">SuraRa</span>
            </a>
        </div>
        <div class="flex items-center gap-4">
            @auth
            <a href="/settings" class="text-gray-900 text-sm font-medium hover:text-red-700 transition flex items-center gap-1.5">
                <div class="w-6 h-6 bg-red-700 rounded-full flex items-center justify-center text-white font-black" style="font-size:10px">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                {{ auth()->user()->name }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex">
                @csrf
                <button class="text-gray-400 hover:text-gray-700 text-sm transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm">Masuk</a>
            <a href="{{ route('register') }}" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
            @endauth
        </div>
    </div>
</nav>
