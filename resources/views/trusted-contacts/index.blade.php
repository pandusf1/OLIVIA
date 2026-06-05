<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Terpercaya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php
        $backUrl = request()->headers->get('referer') ?: route('dashboard');
        $backLabel = 'Kembali';
        $showBrand = false;
        $contactResendAvailableAt = session('verify_contact_id') ? \Illuminate\Support\Facades\Cache::get('trusted_contact_resend_available_at:' . session('verify_contact_id')) : null;
        $contactResendSeconds = session('contact_resend_seconds', $contactResendAvailableAt ? max(0, $contactResendAvailableAt - time()) : 0);
    @endphp
    @include('partials.nav-auth')

    <div class="max-w-lg mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">TRUSTED CONTACT</p>
            <h1 class="font-unbounded text-3xl font-bold text-gray-900">Kontak Terpercaya</h1>
            <p class="text-gray-400 text-sm mt-1">Orang-orang ini otomatis dapat alert WhatsApp saat kamu menekan panic button, lengkap dengan lokasi dan link tracking.</p>
        </div>



<div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">+ Tambah Kontak</h2>
            <form action="/trusted-contact" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama</label>
                    <input type="text" name="contact_name" placeholder="Nama kontak..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. WhatsApp</label>
                    <input type="text" name="contact_phone" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                    <p class="text-gray-400 text-xs mt-1">Format internasional tanpa + (contoh: 6281234567890)</p>
                </div>
<button class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tambah Kontak</button>
            </form>
        </div>

        <!-- Skeleton Loading for Contacts -->
        <div id="contacts-skeleton" class="bg-white border border-gray-200 rounded-2xl overflow-hidden divide-y divide-gray-50 mb-6">
            <div class="px-5 py-3 border-b border-gray-100 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
            </div>
            <div class="p-5 flex items-center justify-between animate-pulse">
                <div class="space-y-2 flex-1">
                    <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                </div>
                <div class="h-4 bg-gray-200 rounded w-12"></div>
            </div>
            <div class="p-5 flex items-center justify-between animate-pulse">
                <div class="space-y-2 flex-1">
                    <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                </div>
                <div class="h-4 bg-gray-200 rounded w-12"></div>
            </div>
        </div>

        <!-- Real Contacts List (hidden by default) -->
        <div id="contacts-list" class="bg-white border border-gray-200 rounded-2xl overflow-hidden hidden mb-6">
            <div class="px-5 py-3 border-b border-gray-100">
                <p id="contacts-count-label" class="font-semibold text-gray-900 text-sm">Kontak Tersimpan (0)</p>
            </div>
            <div id="contacts-items" class="divide-y divide-gray-50"></div>
        </div>

        <!-- Empty State (hidden by default) -->
        <div id="contacts-empty" class="bg-white border border-gray-200 rounded-2xl p-8 text-center hidden mb-6">
            <p class="text-gray-400 text-sm">Belum ada kontak tersimpan.</p>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mt-6">
            <p class="text-amber-800 text-xs">💡 Tambahkan keluarga, pasangan, atau teman dekat. Mereka akan menerima lokasi GPS, status darurat, dan link tracking otomatis saat kamu panic.</p>
        </div>
    </div>

    @if(session('verify_contact_id'))
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Verifikasi WhatsApp</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan 5 digit kode yang telah dikirimkan ke nomor <strong>{{ session('verify_contact_phone') }}</strong>.</p>
            


            <form action="{{ route('trusted-contact.verify') }}" method="POST">
                @csrf
                <input type="hidden" name="contact_id" value="{{ session('verify_contact_id') }}">
                
                <div class="mb-4">
                    <input type="text" name="code" maxlength="5" placeholder="_____" class="w-full text-center text-2xl tracking-[0.5em] font-bold border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 focus:outline-none transition" required>
                </div>
                
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Verifikasi</button>
            </form>
            <form action="{{ route('trusted-contact.resend') }}" method="POST" class="mt-3 js-resend-form">
                @csrf
                <input type="hidden" name="contact_id" value="{{ session('verify_contact_id') }}">
                <button type="submit" data-resend-button data-initial-seconds="{{ (int) $contactResendSeconds }}" class="w-full border border-gray-200 hover:bg-gray-50 text-gray-700 py-3 rounded-xl font-semibold text-sm transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 flex items-center justify-center gap-2">
                    <span data-resend-spinner class="hidden h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-gray-800"></span>
                    <span data-resend-label>Kirim Ulang Kode</span>
                </button>
            </form>
            <form action="{{ route('trusted-contact.destroy', session('verify_contact_id')) }}" method="POST" class="mt-3">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full text-gray-500 hover:text-gray-700 py-2 rounded-xl text-sm transition">Batal</button>
            </form>
        </div>
    </div>
    @endif
    <script>
    document.querySelectorAll('.js-resend-form').forEach((form) => {
        const button = form.querySelector('[data-resend-button]');
        const label = form.querySelector('[data-resend-label]');
        const spinner = form.querySelector('[data-resend-spinner]');
        if (!button || !label) return;

        let remaining = Number(button.dataset.initialSeconds || 0);
        const defaultText = 'Kirim Ulang Kode';
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
            label.textContent = 'Mengirim kode...';
        });
    });

    const csrf = '{{ csrf_token() }}';

    async function loadContacts() {
        const skeleton = document.getElementById('contacts-skeleton');
        const list = document.getElementById('contacts-list');
        const itemsEl = document.getElementById('contacts-items');
        const countLabel = document.getElementById('contacts-count-label');
        const empty = document.getElementById('contacts-empty');

        try {
            const res = await fetch('/trusted-contacts', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            const contacts = data.contacts || [];

            if (contacts.length > 0) {
                if (countLabel) countLabel.textContent = `Kontak Tersimpan (${contacts.length})`;
                if (itemsEl) {
                    itemsEl.innerHTML = contacts.map(c => `
                        <div class="flex items-center justify-between px-5 py-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-gray-900">${escapeHtml(c.contact_name)}</p>
                                </div>
                                <p class="text-gray-400 text-sm">${escapeHtml(c.contact_phone)}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="/trusted-contact/${c.id}/edit" class="text-gray-600 hover:text-gray-900 text-sm font-semibold transition">Edit</a>
                                <form action="/trusted-contact/${c.id}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus kontak ini?" class="inline">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-semibold transition">Hapus</button>
                                </form>
                            </div>
                        </div>
                    `).join('');
                }
                if (skeleton) skeleton.classList.add('hidden');
                if (empty) empty.classList.add('hidden');
                if (list) list.classList.remove('hidden');
            } else {
                if (skeleton) skeleton.classList.add('hidden');
                if (list) list.classList.add('hidden');
                if (empty) empty.classList.remove('hidden');
            }
        } catch (e) {
            console.error('Failed to load contacts:', e);
            if (skeleton) skeleton.classList.add('hidden');
            if (empty) {
                empty.innerHTML = `<p class="text-red-500 text-sm">Gagal memuat kontak terpercaya: ${e.message}</p>`;
                empty.classList.remove('hidden');
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    loadContacts();
    </script>
</body>
</html>

