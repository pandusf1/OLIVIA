<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Edit Trusted Contact</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>

</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @include('partials.nav-auth')

    <div class="max-w-lg mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">TRUSTED CONTACT</p>
            <h1 class=" text-3xl font-bold text-gray-900">Edit Kontak Terpercaya</h1>
            <p class="text-gray-400 text-sm mt-1">Perbarui data kontak trusted untuk menerima alert WhatsApp.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms>
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

<div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">Form Edit</h2>

            <form action="{{ route('trusted-contact.update', $contact->id) }}" method="POST" class="space-y-3">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $contact->contact_name) }}" placeholder="Nama kontak..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. WhatsApp</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $contact->contact_phone) }}" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                    <p class="text-gray-400 text-xs mt-1">Format internasional tanpa + (contoh: 6281234567890)</p>
                </div>

                <div class="flex gap-3">
<a href="{{ route('trusted-contact.index') }}" class="flex-1 text-center border border-gray-200 hover:bg-gray-50 text-gray-800 py-3 rounded-xl font-semibold text-sm transition">Batal</a>
                </div>
            </form>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
            <p class="text-amber-800 text-xs">Perubahan akan berlaku untuk alert otomatis berikutnya.</p>
        </div>
    </div>
</body>
</html>


