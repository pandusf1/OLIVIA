<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Partner Management</title>

    @vite('resources/css/app.css')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        *{font-family:'Inter',sans-serif;}
    </style>
</head>

<body class="bg-[#faf9f7] text-gray-900 min-h-screen">

@include('partials.nav-auth')

<div class="max-w-6xl mx-auto px-6 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">
                ADMIN PANEL
            </p>

            <h1 class="text-3xl font-black">
                Partner Management
            </h1>

            <p class="text-gray-400 text-sm mt-1">
                Kelola semua partner terverifikasi SuraRa.
            </p>
        </div>

        <a href="{{ route('admin.partners.create') }}"
           class="bg-gray-900 text-white px-5 py-3 rounded-xl text-sm font-bold hover:bg-black transition">
            + Tambah Partner
        </a>
    </div>

    <div class="space-y-3">

        @foreach($partners as $partner)

        <div class="bg-white border border-gray-200 rounded-2xl p-5 flex items-center justify-between">

            <div>
                <div class="flex items-center gap-2 flex-wrap">

                    <h2 class="font-bold text-lg">
                        {{ $partner->partner_name }}
                    </h2>

                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                        {{ $partner->partner_type }}
                    </span>

                    @if($partner->verified)
                        <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-700">
                            Verified
                        </span>
                    @else
                        <span class="text-xs px-2 py-1 rounded-full bg-red-50 text-red-700">
                            Unverified
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-400 mt-1">
                    {{ $partner->city }}
                </p>

                <p class="text-sm text-gray-500">
                    {{ $partner->email }}
                </p>
            </div>

            <form method="POST"
                  action="{{ route('admin.partners.verify', $partner->id) }}">
                @csrf
                @method('PATCH')

                <button
                    class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 hover:border-gray-400 transition">

                    {{ $partner->verified ? 'Cabut Verifikasi' : 'Verifikasi' }}

                </button>
            </form>

        </div>

        @endforeach

    </div>
</div>

</body>
</html>