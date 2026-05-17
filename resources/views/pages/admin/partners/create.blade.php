<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Partner</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#faf9f7] min-h-screen text-gray-900">

<div class="max-w-3xl mx-auto px-6 py-10">

    <h1 class="text-3xl font-black mb-8">
        Tambah Partner
    </h1>

    <form method="POST"
          action="{{ route('admin.partners.store') }}"
          class="space-y-5">

        @csrf

        <div>
            <label class="text-sm font-semibold">Nama Partner</label>

            <input type="text"
                   name="partner_name"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-semibold">Kategori</label>

            <input type="text"
                   name="partner_type"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-semibold">Kota</label>

            <input type="text"
                   name="city"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-semibold">No HP</label>

            <input type="text"
                   name="phone"
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <hr>

        <div>
            <label class="text-sm font-semibold">Nama Akun</label>

            <input type="text"
                   name="name"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-semibold">Email Login</label>

            <input type="email"
                   name="email"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-semibold">Password</label>

            <input type="password"
                   name="password"
                   required
                   class="w-full mt-2 border border-gray-200 rounded-xl px-4 py-3">
        </div>

        <button
            class="bg-gray-900 hover:bg-black text-white px-6 py-3 rounded-xl font-bold transition">

            Simpan Partner

        </button>

    </form>

</div>

</body>
</html>
