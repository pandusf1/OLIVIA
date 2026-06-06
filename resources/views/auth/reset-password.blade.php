<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Kata Sandi Baru</h1>
    <p class="text-gray-400 text-sm mb-6">Silakan buat kata sandi baru untuk mengamankan akun Anda kembali.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Password -->
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Kata Sandi Baru</label>
            <div class="relative">
                <input type="password" name="password" required id="new-pass" autofocus autocomplete="new-password"
                    class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white pr-12">
                <button type="button" onclick="const p = document.getElementById('new-pass'); const i = document.getElementById('pass-eye'); if(p.type === 'password') { p.type = 'text'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\' />'; } else { p.type = 'password'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\' /><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\' />'; }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="pass-eye">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <div class="mt-1.5 h-1 bg-gray-100 rounded-full overflow-hidden"><div id="str-bar" class="h-full rounded-full transition-all duration-300 w-0"></div></div>
            <p id="str-label" class="text-xs mt-1 text-gray-400"></p>
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-500 text-xs" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Konfirmasi Kata Sandi Baru</label>
            <div class="relative">
                <input type="password" name="password_confirmation" required id="new-pass-confirm" autocomplete="new-password"
                    class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white pr-12">
                <button type="button" onclick="const p = document.getElementById('new-pass-confirm'); const i = document.getElementById('pass-confirm-eye'); if(p.type === 'password') { p.type = 'text'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21\' />'; } else { p.type = 'password'; i.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\' /><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\' />'; }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="pass-confirm-eye">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-red-500 text-xs" />
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition mt-4">Simpan Kata Sandi Baru</button>
    </form>

    <script>
    document.getElementById('new-pass').addEventListener('input', function(){
        const v = this.value;
        let s = 0;
        if(v.length >= 8) s++;
        if(v.length >= 12) s++;
        if(/[A-Z]/.test(v)) s++;
        if(/[0-9]/.test(v)) s++;
        if(/[^A-Za-z0-9]/.test(v)) s++;
        const c = ['', 'bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
        const l = ['', 'Sangat lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'];
        document.getElementById('str-bar').className = 'h-full rounded-full transition-all duration-300 ' + (c[s] || '');
        document.getElementById('str-bar').style.width = v.length ? s * 20 + '%' : '0%';
        document.getElementById('str-label').textContent = v.length ? l[s] : '';
    });
    </script>
</x-guest-layout>
