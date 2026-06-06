<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Lupa Kata Sandi</h1>
    <p class="text-gray-400 text-sm mb-6">Kami akan mengirimkan kode OTP 5-digit ke nomor WhatsApp terdaftar Anda untuk memulihkan akun.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email or Phone -->
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Email atau Nomor WhatsApp</label>
            <input type="text" name="identity" value="{{ old('identity') }}" required autofocus
                placeholder="email@example.com atau 628xxxxxxxxx"
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white">
            <x-input-error :messages="$errors->get('identity')" class="mt-1 text-red-500 text-xs" />
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition mt-2">Kirim OTP ke WhatsApp</button>

        <p class="text-center text-gray-400 text-sm pt-2">
            Sudah ingat sandi? <a href="{{ route('login') }}" class="text-gray-900 font-semibold hover:text-red-700 transition">Masuk</a>
        </p>
    </form>
</x-guest-layout>
