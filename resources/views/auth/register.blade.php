<x-guest-layout>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Buat Akun</h1>
    <p class="text-gray-400 text-sm mb-6">Gratis. Trusted contact, histori laporan & lebih.</p>
    @if ($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Password</label>
            <input type="password" name="password" required
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-500 text-xs" />
        </div>
        <div>
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required
                class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
        </div>
        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition mt-2">Daftar</button>
        <p class="text-center text-gray-400 text-sm pt-1">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-gray-900 font-semibold hover:text-red-700 transition">Masuk</a>
        </p>
    </form>
</x-guest-layout>
