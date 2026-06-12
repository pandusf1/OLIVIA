<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Verifikasi OTP</h1>
    <p class="text-gray-400 text-sm mb-6">Kami telah mengirimkan 5-digit kode OTP ke nomor WhatsApp terdaftar Anda (<strong>{{ $maskedPhone }}</strong>).</p>

    <!-- Status Sesi -->
    <x-auth-session-status class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('password.otp-verify') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider text-center">Kode OTP 5-Digit</label>
            <input type="text" name="code" maxlength="5" placeholder="_____" autofocus required
                class="w-full text-center text-2xl tracking-[0.5em] font-bold border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 focus:outline-none transition bg-white">
            <x-input-error :messages="$errors->get('code')" class="mt-1 text-red-500 text-xs text-center" />
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition">Verifikasi OTP</button>
    </form>

    <form action="{{ route('password.otp-resend') }}" method="POST" class="mt-3 js-resend-form">
        @csrf
        <button type="submit" data-resend-button data-initial-seconds="{{ (int) session('phone_resend_seconds', 60) }}" 
            class="w-full border border-gray-200 hover:bg-gray-50 text-gray-700 py-3 rounded-xl font-semibold text-sm transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 flex items-center justify-center gap-2">
            <span data-resend-spinner class="hidden h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-gray-800"></span>
            <span data-resend-label>Kirim Ulang OTP</span>
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('password.request') }}" class="text-xs text-gray-400 hover:text-gray-600 transition">Kembali ke halaman sebelumnya</a>
    </div>

    <script>
    document.querySelectorAll('.js-resend-form').forEach((form) => {
        const button = form.querySelector('[data-resend-button]');
        const label = form.querySelector('[data-resend-label]');
        const spinner = form.querySelector('[data-resend-spinner]');
        if (!button || !label) return;

        let remaining = Number(button.dataset.initialSeconds || 0);
        const defaultText = 'Kirim Ulang OTP';
        let timer = null;

        const render = () => {
            if (remaining > 0) {
                button.disabled = true;
                label.textContent = `Kirim ulang dalam ${remaining} detik`;
            } else {
                button.disabled = false;
                label.textContent = defaultText;
                if (timer) window.clearInterval(timer);
            }
        };

        if (remaining > 0) {
            render();
            timer = window.setInterval(() => {
                remaining -= 1;
                render();
            }, 1000);
        }

        form.addEventListener('submit', () => {
            button.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            label.textContent = 'Mengirim OTP...';
        });
    });
    </script>
</x-guest-layout>
