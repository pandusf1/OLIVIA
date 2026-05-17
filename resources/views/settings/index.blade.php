<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savora — Pengaturan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php $backUrl = route('dashboard'); $backLabel = 'Dashboard'; @endphp
    @include('partials.nav-auth')
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">AKUN</p>
            <h1 class="font-unbounded text-3xl font-black text-gray-900">Pengaturan</h1>
        </div>

        @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">✓ {{ session('success') }}</div>@endif

        {{-- Tab nav --}}
        <div class="flex gap-1 border-b border-gray-200 mb-8">
            @foreach(['profil'=>'Profil','keamanan'=>'Keamanan','notifikasi'=>'Notifikasi','akun'=>'Akun'] as $key=>$label)
            <button onclick="switchTab('{{ $key }}')" id="tab-{{ $key }}"
                class="px-4 py-2.5 text-sm font-semibold border-b-2 transition -mb-px
                {{ $key==='profil' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-700' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- PROFIL --}}
        <div id="panel-profil" class="tab-panel">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 bg-red-700 rounded-2xl flex items-center justify-center text-xl font-black text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-gray-400 text-sm">{{ auth()->user()->email }}</p>
                        <p class="text-gray-400 text-xs">Bergabung {{ auth()->user()->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" class="px-6 py-6 space-y-4">
                    @csrf @method('patch')
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. HP</label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                    <div class="flex justify-end"><button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Simpan</button></div>
                </form>
            </div>
        </div>

        {{-- KEAMANAN --}}
        <div id="panel-keamanan" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Ubah Password</h2></div>
                <form action="{{ route('password.update') }}" method="POST" class="px-6 py-6 space-y-4">
                    @csrf @method('put')
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                        @error('current_password','updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Password Baru</label>
                        <input type="password" name="password" id="new-pass" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                        <div class="mt-1.5 h-1 bg-gray-100 rounded-full overflow-hidden"><div id="str-bar" class="h-full rounded-full transition-all duration-300 w-0"></div></div>
                        <p id="str-label" class="text-xs mt-1 text-gray-400"></p>
                        @error('password','updatePassword')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Konfirmasi</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                    <div class="flex justify-end"><button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Perbarui</button></div>
                </form>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        <div id="panel-notifikasi" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Preferensi Notifikasi</h2></div>
                <div class="px-6 py-6 space-y-3">
                    @foreach([['WhatsApp Alert Panic','Kirim alert ke trusted contact saat panic',true],['Update Status','Notifikasi saat status laporan berubah',true],['Pengingat Keamanan','Tips keamanan berkala',false]] as [$l,$d,$c])
                    <div class="flex items-center justify-between border border-gray-100 rounded-xl px-4 py-3">
                        <div><p class="text-sm font-semibold text-gray-900">{{ $l }}</p><p class="text-gray-400 text-xs">{{ $d }}</p></div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" {{ $c?'checked':'' }} class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 peer-checked:bg-red-700 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>
                    @endforeach
                    <p class="text-gray-400 text-xs pt-2">💡 WhatsApp alert sudah aktif otomatis saat panic.</p>
                </div>
            </div>
        </div>

        {{-- AKUN --}}
        <div id="panel-akun" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-4">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Info Akun</h2></div>
                <div class="px-6 py-5 space-y-3 text-sm">
                    @foreach([['Role', auth()->user()->role??'user'],['Jumlah Laporan', $reportCount.' laporan'],['Bergabung', auth()->user()->created_at->format('d M Y')]] as [$k,$v])
                    <div class="flex justify-between"><span class="text-gray-400">{{ $k }}</span><span class="text-gray-900 font-medium">{{ $v }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white border border-red-100 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-red-100"><h2 class="font-bold text-red-600">Hapus Akun</h2></div>
                <div class="px-6 py-6">
                    <p class="text-gray-400 text-sm mb-4">Semua data akan terhapus permanen dan tidak bisa dipulihkan.</p>
                    <button onclick="document.getElementById('del-form').classList.toggle('hidden')" class="bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">Hapus Akun Saya</button>
                    <div id="del-form" class="hidden mt-4 border border-red-100 rounded-xl p-4">
                        <form action="{{ route('profile.destroy') }}" method="POST">
                            @csrf @method('delete')
                            <input type="password" name="password" placeholder="Konfirmasi password..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none mb-3">
                            @error('password','userDeletion')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-xl font-bold text-sm">Ya, Hapus</button>
                                <button type="button" onclick="document.getElementById('del-form').classList.add('hidden')" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl font-semibold text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    const tabs=['profil','keamanan','notifikasi','akun'];
    function switchTab(n){tabs.forEach(t=>{document.getElementById('panel-'+t).classList.add('hidden');const b=document.getElementById('tab-'+t);b.classList.remove('border-gray-900','text-gray-900');b.classList.add('border-transparent','text-gray-400');});document.getElementById('panel-'+n).classList.remove('hidden');const ab=document.getElementById('tab-'+n);ab.classList.add('border-gray-900','text-gray-900');ab.classList.remove('border-transparent','text-gray-400');}
    document.getElementById('new-pass').addEventListener('input',function(){const v=this.value;let s=0;if(v.length>=8)s++;if(v.length>=12)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;const c=['','bg-red-500','bg-orange-500','bg-yellow-500','bg-blue-500','bg-green-500'];const l=['','Sangat lemah','Lemah','Cukup','Kuat','Sangat kuat'];document.getElementById('str-bar').className='h-full rounded-full transition-all duration-300 '+(c[s]||'');document.getElementById('str-bar').style.width=v.length?s*20+'%':'0%';document.getElementById('str-label').textContent=v.length?l[s]:'';});
    @if($errors->updatePassword->any()) switchTab('keamanan'); @endif
    @if($errors->userDeletion->any()) switchTab('akun'); document.getElementById('del-form').classList.remove('hidden'); @endif
    </script>
</body>
</html>

