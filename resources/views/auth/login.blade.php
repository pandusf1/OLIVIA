<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Masuk ke Savora</h1>
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
            <input type="password" name="password" required
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
            <label for="remember" class="text-gray-500 text-sm">Ingat saya</label>
        </div>
        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition mt-2">Masuk</button>
        <p class="text-center text-gray-400 text-sm pt-1">
            Belum punya akun? <a href="{{ route('register') }}" class="text-gray-900 font-semibold hover:text-red-700 transition">Daftar</a>
        </p>
    </form>
</x-guest-layout>
