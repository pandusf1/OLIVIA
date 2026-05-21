<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Masuk ke Safora</h1>
    <p class="text-gray-400 text-sm mb-6">Akses dashboard dan laporanmu.</p>

    <x-auth-session-status class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div>
            <div class="flex justify-between items-center mb-1.5">
                <label class="text-gray-600 text-xs font-semibold uppercase tracking-wider">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-gray-400 hover:text-gray-600 transition">Lupa?</a>
                @endif
            </div>
            <div class="relative">
                <input type="password" name="password" required id="login-password"
                    class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white pr-12">
                <button type="button" onclick="const p = document.getElementById('login-password'); const i = document.getElementById('login-eye'); if(p.type === 'password') { p.type = 'text'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\' />'; } else { p.type = 'password'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\' /><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\' />'; }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="login-eye">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
            <label for="remember" class="text-gray-500 text-sm">Ingat saya</label>
        </div>
        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition mt-2">Masuk</button>
        
        <div class="my-4 flex items-center gap-3">
            <hr class="w-full border-gray-200">
            <span class="text-xs text-gray-400 font-semibold uppercase">ATAU</span>
            <hr class="w-full border-gray-200">
        </div>
        <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-900 py-3 rounded-xl font-bold text-sm transition">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Lanjutkan dengan Google
        </a>
        <p class="text-center text-gray-400 text-sm pt-1">
            Belum punya akun? <a href="{{ route('register') }}" class="text-gray-900 font-semibold hover:text-red-700 transition">Daftar</a>
        </p>
    </form>
</x-guest-layout>
