<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950 antialiased">
@php
    $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
    $backLabel = 'Kembali';
    $showBrand = false;
@endphp
@include('partials.nav-auth')

<main class="mx-auto max-w-5xl px-4 pb-10 pt-5 sm:px-6">
    <section class="rounded-lg border border-red-100 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[1fr_280px] lg:items-start">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">Laporan #<span data-field="short_id">{{ $livePayload['report']['short_id'] }}</span></p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl" data-field="current_status">{{ $livePayload['current_status'] }}</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-gray-700" data-field="human_message">{{ $livePayload['human_message'] }}</p>
        <p class="mt-1 text-sm leading-6" data-field="next_instruction">{{ $livePayload['next_instruction'] }}</p>
        <p class="mt-2 text-xs leading-5" data-field="escalation_message">{{ $livePayload['escalation_message'] }}</p>
            </div>
            <div class="rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Lokasi</p>
                <a data-field="maps_url" href="{{ $livePayload['report']['location']['maps_url'] ?? '#' }}" target="_blank" class="mt-1 inline-block font-black text-red-700 underline mb-2">
                    {{ $livePayload['report']['location']['verified'] ? 'GPS diterima' : 'GPS belum tersedia' }}
                </a>
                <div id="tracking-map" class="h-36 w-full rounded-lg z-0 border border-gray-200"></div>
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Kategori</p>
                <p class="mt-1 font-black" data-field="category">{{ $livePayload['report']['category'] }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4" id="incident-date-container">
                <p class="text-xs font-bold uppercase text-gray-500">Waktu Kejadian</p>
                <p class="mt-1 font-black" data-field="incident_date">{{ $livePayload['report']['incident_date'] ?? '-' }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Estimasi Respons</p>
                <p class="mt-1 font-black" data-field="eta">{{ $livePayload['eta'] }}</p>
            </div>
        </div>
        @php
            $currUser = auth()->user();
            $isPartnerUser = $currUser && $currUser->role === 'partner';
            $pId = $isPartnerUser ? $currUser->partner_id : null;
            
            $pRouting = $isPartnerUser ? $report->partnerRoutings()->where('partner_id', $pId)->first() : null;
            $isChronoPending = ($pRouting && $pRouting->status === 'pending' && (is_null($pRouting->expires_at) || $pRouting->expires_at > now()));
            
            $canPartnerAccept = ($report->handler_partner_id === null && $isChronoPending);
            $isPartnerHandling = ($isPartnerUser && $report->handler_partner_id === $pId);
        @endphp

        @if(!$isPartnerUser && ((auth()->check() && auth()->id() === $report->user_id) || in_array($report->id, session('my_reports', []))))
            @if(in_array($report->status, ['In Progress', 'Assigned']))
                <div class="mt-4">
                    <form action="/tracking/{{ $report->id }}/resolve" method="POST" data-confirm="Apakah Anda yakin laporan ini telah tertangani dan Anda sudah aman?">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-black text-white hover:bg-green-700 transition">Laporan Selesai</button>
                    </form>
                </div>
            @endif
        @endif

        @if($isPartnerUser)
            @if($canPartnerAccept)
                <div class="mt-4">
                    <form method="POST" action="{{ route('partner.report.accept', $report->id) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg bg-red-700 hover:bg-red-800 text-white px-5 py-3.5 text-sm font-black transition hover:scale-[1.01] duration-200 shadow-md">
                            TERIMA KASUS
                        </button>
                    </form>
                </div>
            @elseif($isPartnerHandling && in_array($report->status, ['In Progress', 'Assigned', 'Viewed', 'Routed', 'Submitted']))
                <div class="mt-4">
                    <form method="POST" action="{{ route('partner.status', $report->id) }}" data-confirm="Apakah Anda yakin kasus ini telah selesai ditangani?">
                        @csrf
                        <input type="hidden" name="status" value="Resolved">
                        <button type="submit" class="w-full rounded-lg bg-green-600 hover:bg-green-700 text-white px-5 py-3.5 text-sm font-black transition hover:scale-[1.01] duration-200 shadow-md">
                            Laporan Selesai
                        </button>
                    </form>
                </div>
            @endif
        @endif
    </section>

            @php
                $user = auth()->user();
                $isPartner = $user && $user->role === 'partner';
                $cookieReports = [];
                if (request()->hasCookie('safora_my_reports')) {
                    $cookieReports = json_decode(request()->cookie('safora_my_reports'), true) ?: [];
                }
                $isReporter = !$isPartner && (
                    (auth()->check() && auth()->id() === $report->user_id)
                    || in_array($report->id, session('my_reports', []))
                    || in_array($report->id, $cookieReports)
                );
                
                $isOtherPartnerHandling = $isPartner && $report->handler_partner_id !== null && $report->handler_partner_id !== $user->partner_id;
                
                $isRoutingExist = $isPartner && $report->partnerRoutings()
                    ->where('partner_id', $user->partner_id)
                    ->exists();
                $isAcceptedPartner = $isPartner && $report->partnerRoutings()
                    ->where('partner_id', $user->partner_id)
                    ->where('status', 'accepted')
                    ->exists();
                $hasDirectChatAccess = $isReporter || $isAcceptedPartner || $isRoutingExist;
            @endphp

            <section class="mt-4 rounded-lg border border-gray-200 bg-white p-5">
                <!-- Heading & Toolbar -->
                <div class="flex items-center justify-between gap-3 flex-wrap border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Aktivitas Laporan</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kronologi, saksi, berkas bukti, dan chat koordinasi.</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if(!$isOtherPartnerHandling)
                            <!-- Tombol Tambah Bukti -->
                            <button type="button" onclick="openEvidenceModal()" class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition border border-gray-200">
                                + Bukti
                            </button>
                            
                            <!-- Tombol Tambah Kronologi -->
                            <button id="btn-add-chronology" onclick="openChronologyModal()" 
                                    class="{{ ($isReporter || $isTrustedContact) ? '' : 'hidden' }} text-xs font-semibold text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition border border-red-100">
                                + Kronologi
                            </button>
                        @endif

                        <!-- Tombol Chat -->
                        @if($report->status !== 'Resolved')
                            @if($hasDirectChatAccess)
                                <a href="/chat/report/{{ $report->id }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-700 hover:bg-red-800 text-white transition shadow-sm"
                                   title="Buka Chat Laporan">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                </a>
                            @else
                                <div id="chat-action" class="inline-flex items-center gap-1.5">
                                    <span id="chat-checking" class="text-[10px] text-gray-400 italic">Memeriksa lokasi...</span>
                                    <a id="chat-link" href="#" class="hidden items-center justify-center w-8 h-8 rounded-full bg-gray-900 hover:bg-gray-700 text-white transition shadow-sm" title="Buka Chat Laporan">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- GPS Alert Messages for Saksi -->
                <p id="chat-no-gps" class="hidden text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg mb-3">📍 Tidak bisa mengambil lokasi. Chat hanya tersedia untuk pelapor dan warga dalam 5 km.</p>

                <!-- ID Laporan (for saksi/warga around) -->
                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <p class="text-xs font-bold text-gray-500 uppercase">ID Laporan</p>
                    <code class="mt-1 block break-all text-xs font-mono text-gray-700 select-all cursor-pointer hover:text-black">{{ $report->id }}</code>
                </div>

                <!-- Isi Section: List Kronologi -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                        <span>Kronologi Kejadian</span>
                    </h3>
                    <div id="chronology-list" class="space-y-3">
                        @forelse($report->chronologies as $chrono)
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
                                <div class="flex justify-between items-start gap-3 flex-wrap">
                                    <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                                        @if($chrono->role === 'Korban')
                                            Korban ({{ $chrono->writer_name }})
                                        @elseif($chrono->role === 'Partner' || $chrono->role === 'Mitra')
                                            Mitra ({{ $chrono->writer_name }})
                                        @elseif($chrono->role === 'Saksi')
                                            Saksi ({{ $chrono->writer_name }})
                                        @else
                                            {{ $chrono->role }} ({{ $chrono->writer_name }})
                                        @endif
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        {{ $chrono->created_at->format('d M Y, H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line">{{ $chrono->description }}</p>
                            </div>
                        @empty
                            <div class="text-center py-6 text-gray-400 text-sm italic" id="chronology-empty-state">
                                Belum ada kronologi tambahan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Isi Section: List Bukti -->
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Bukti Kejadian</h3>
                    
                    <div id="evidence-list-container" class="space-y-6">
                        @if($report->evidences->count() > 0)
                            @php
                                $groupedEvidences = $report->evidences->groupBy('uploader_role');
                                $groupOrder = ['Korban', 'Saksi', 'Mitra'];
                            @endphp
                            
                            @foreach($groupOrder as $role)
                                @if($groupedEvidences->has($role) && $groupedEvidences->get($role)->count() > 0)
                                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4" id="evidence-group-{{ $role }}">
                                        <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                                            Bukti dari {{ $role }}
                                        </h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-{{ $role }}">
                                            @foreach($groupedEvidences->get($role) as $evidence)
                                                @php
                                                    $isImage = str_starts_with($evidence->file_type, 'image/');
                                                    $isVideo = str_starts_with($evidence->file_type, 'video/');
                                                    $isAudio = str_starts_with($evidence->file_type, 'audio/');
                                                    
                                                    $canView = $report->show_evidence 
                                                        || (auth()->check() && (auth()->id() === $report->user_id || auth()->user()->role === 'partner'))
                                                        || in_array($report->id, session('my_reports', []));
                                                        
                                                    $evidenceUrl = str_starts_with($evidence->file_url, 'data:') ? $evidence->file_url : url('/evidences/view/' . basename($evidence->file_url));
                                                    
                                                    $cardBg = 'bg-blue-50/50 border-blue-150';
                                                    $badgeBg = 'bg-blue-100 text-blue-800';
                                                    $badgeLabel = 'BERKAS';
                                                    
                                                    if ($isImage) {
                                                        $cardBg = 'bg-rose-50/50 border-rose-150';
                                                        $badgeBg = 'bg-rose-100 text-rose-800';
                                                        $badgeLabel = 'FOTO';
                                                    } elseif ($isVideo) {
                                                        $cardBg = 'bg-amber-50/50 border-amber-150';
                                                        $badgeBg = 'bg-amber-100 text-amber-800';
                                                        $badgeLabel = 'VIDEO';
                                                    } elseif ($isAudio) {
                                                        $cardBg = 'bg-emerald-50/50 border-emerald-150';
                                                        $badgeBg = 'bg-emerald-100 text-emerald-800';
                                                        $badgeLabel = 'REKAMAN';
                                                    }
                                                @endphp
                                                <a href="{{ $canView ? $evidenceUrl : '#' }}" target="{{ $canView ? '_blank' : '' }}" onclick="{{ !$canView ? "alert('Bukti hanya bisa dibuka oleh korban / mitra'); return false;" : '' }}" class="aspect-square rounded-2xl border {{ $cardBg }} p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md">
                                                    @if($isImage)
                                                        <svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 {{ !$canView ? 'blur-[3px]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    @elseif($isVideo)
                                                        <svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 {{ !$canView ? 'blur-[3px]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    @elseif($isAudio)
                                                        <svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 {{ !$canView ? 'blur-[3px]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 {{ !$canView ? 'blur-[3px]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    @endif
                                                    
                                                    <span class="mt-3 text-[10px] font-bold tracking-widest uppercase {{ $badgeBg }} px-2 py-0.5 rounded-full">{{ $badgeLabel }}</span>
                                                    
                                                    @if(!$canView)
                                                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                                                            <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <div class="text-center py-6 text-gray-400 text-sm italic" id="evidence-empty-state">
                                Belum ada berkas bukti diunggah.
                            </div>
                        @endif
                    </div>
                </div>
            </section>

    <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
        <div class="space-y-5">
            <section id="assigned-card" class="hidden rounded-lg border border-green-200 bg-green-50 p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-green-700">Sedang Menangani</p>
                <h2 class="mt-2 text-xl font-black text-green-950" data-field="assigned_name"></h2>
                <p class="mt-1 text-sm text-green-800" data-field="assigned_detail"></p>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black">Diteruskan ke Mitra</h2>
                        <p class="text-sm text-gray-500">Status ini diperbarui otomatis setiap beberapa detik.</p>
                    </div>
                </div>
                <div id="routed-partners" class="space-y-3"></div>

                @if((auth()->check() && auth()->id() === $report->user_id) || in_array($report->id, session('my_reports', [])))
                    <div id="re-alert-container" class="mt-4 hidden animate-pulse">
                        <button id="btn-re-alert" onclick="triggerReAlert()" class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white font-black py-3.5 px-4 text-sm flex items-center justify-center gap-2 transition duration-300 shadow-md transform active:scale-95">
                            <span>🚨 Kirim Ulang Alert ke Mitra</span>
                            <span id="re-alert-countdown" class="hidden font-mono bg-red-800 px-2 py-0.5 rounded text-xs"></span>
                        </button>
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-black">Update Terbaru</h2>
                <div id="timeline" class="mt-4 space-y-4"></div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-lg border border-red-200 bg-white p-5">
                <h2 class="text-lg font-black text-red-800">Nomor Darurat</h2>
                <p class="mt-1 text-sm text-gray-600">Jika kondisi memburuk, hubungi bantuan resmi sekarang. Safora berjalan paralel.</p>
                <div id="hotlines" class="mt-4 grid gap-2"></div>
            </section>
        </aside>
    </div>
</main>

<script>
    const initialPayload = @json($livePayload);
    const reportId = @json($report->id);
    const isCreator = @json($isReporter);
    const hasDirectChatAccess = @json($hasDirectChatAccess);
    const isLoggedIn = @json(auth()->check());
    const isPartner = @json($isPartner);
    let lastPayload = null;

    // Sync session my_reports dengan localStorage agar ketahanan 100% terjamin (misal habis login/logout)
    let storedReports = JSON.parse(localStorage.getItem('safora_guest_reports') || '[]');
    if (storedReports.includes(reportId) && !isPartner) {
        if (!isCreator && !sessionStorage.getItem('synced_' + reportId)) {
            sessionStorage.setItem('synced_' + reportId, 'true');
            // Pelapor terdeteksi di browser ini tapi session kosong (misal habis logout)
            fetch('/tracking/active-check?ids=' + encodeURIComponent(storedReports.join(',')), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    sessionStorage.removeItem('synced_' + reportId);
                }
            })
            .catch(() => {
                sessionStorage.removeItem('synced_' + reportId);
            });
        }
    } else if (isCreator && !isLoggedIn && !isPartner) {
        // Tambahkan ke localStorage jika kita adalah pelapor guest/anonim
        storedReports.push(reportId);
        localStorage.setItem('safora_guest_reports', JSON.stringify(Array.from(new Set(storedReports))));
    }

    let map = null;
    let marker = null;

    function initMap(lat, lng) {
        if (!lat || !lng) return;
        if (!map) {
            map = L.map('tracking-map', { zoomControl: false }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            marker = L.circleMarker([lat, lng], {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 8
            }).addTo(map);
        } else {
            map.setView([lat, lng]);
            if (marker) marker.setLatLng([lat, lng]);
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setText(selector, value) {
        const node = document.querySelector(selector);
        if (node) node.textContent = value || '-';
    }

    function statusLabel(status) {
        return {
            waiting: ['Menunggu', 'bg-gray-100 text-gray-700'],
            reviewing: ['Meninjau', 'bg-yellow-100 text-yellow-900'],
            unavailable: ['Tidak menerima', 'bg-orange-100 text-orange-800'],
            accepted: ['Menerima', 'bg-green-100 text-green-800'],
        }[status] || ['Menunggu', 'bg-gray-100 text-gray-700'];
    }

    function render(payload) {
        lastPayload = payload;
        setText('[data-field="current_status"]', payload.current_status);
        setText('[data-field="human_message"]', payload.human_message);
        setText('[data-field="eta"]', payload.eta);
        setText('[data-field="next_instruction"]', payload.next_instruction);
        setText('[data-field="escalation_message"]', payload.escalation_message);
        setText('[data-field="urgency"]', payload.report.urgency_level);
        setText('[data-field="category"]', payload.report.category);
        
        const incidentDateContainer = document.getElementById('incident-date-container');
        if (incidentDateContainer) {
            if (payload.report.incident_date) {
                incidentDateContainer.classList.remove('hidden');
                setText('[data-field="incident_date"]', payload.report.incident_date);
            } else {
                incidentDateContainer.classList.add('hidden');
            }
        }

        const maps = document.querySelector('[data-field="maps_url"]');
        if (maps && payload.report.location.maps_url) {
            maps.href = payload.report.location.maps_url;
            maps.textContent = payload.report.location.verified ? 'Titik Lokasi ' : 'Buka peta';
        }

        if (payload.report.location.latitude && payload.report.location.longitude) {
            initMap(payload.report.location.latitude, payload.report.location.longitude);
        }

        const assigned = document.getElementById('assigned-card');
        if (assigned) {
            if (payload.assigned_partner) {
                assigned.classList.remove('hidden');
                setText('[data-field="assigned_name"]', payload.assigned_partner.name);
                setText('[data-field="assigned_detail"]', payload.assigned_partner.specialization);
                
                const chatLink = document.getElementById('chat-link');
                if (chatLink) {
                    // Set chat URL with current user location
                    const qs = (window._userLat && window._userLng) ? `?lat=${window._userLat}&lng=${window._userLng}` : '';
                    chatLink.href = `/chat/report/${reportId}${qs}`;
                    // Show/hide based on distance check result
                    if (window._chatAllowed) {
                        chatLink.classList.remove('hidden');
                        chatLink.classList.add('inline-flex');
                    } else {
                        chatLink.classList.add('hidden');
                        chatLink.classList.remove('inline-flex');
                    }
                }
            } else {
                assigned.classList.add('hidden');
            }
        }

        const routedPartnersEl = document.getElementById('routed-partners');
        if (routedPartnersEl) {
            routedPartnersEl.innerHTML = (payload.routed_partners || []).map((partner) => {
                const [label, klass] = statusLabel(partner.status);
                return `
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black">${escapeHtml(partner.name)}</p>
                                <p class="mt-1 text-sm text-gray-500">${escapeHtml(partner.specialization)}${partner.city ? ' - ' + escapeHtml(partner.city) : ''}</p>
                                <p class="mt-1 text-xs text-gray-500">${escapeHtml(partner.distance || 'Jarak belum tersedia')}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ${klass}">${label}</span>
                                ${partner.status === 'accepted' ? `<span id="chat-access-badge" class="text-xs text-green-700 font-semibold hidden">✓ Chat tersedia</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Kami sedang mencari mitra yang relevan.</p>';
        }

        const timelineEl = document.getElementById('timeline');
        if (timelineEl) {
            timelineEl.innerHTML = (payload.timeline || []).map((event) => `
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-red-700"></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">${escapeHtml(event.message)}</p>
                        <p class="mt-1 text-xs text-gray-400">${escapeHtml(event.time)}</p>
                    </div>
                </div>
            `).join('');
        }

        const hotlinesEl = document.getElementById('hotlines');
        if (hotlinesEl) {
            hotlinesEl.innerHTML = (payload.hotlines || []).map((hotline) => `
                <a href="tel:${escapeHtml(hotline.phone)}" class="flex items-center justify-between rounded-lg border border-red-100 bg-red-50 px-4 py-3">
                    <span class="text-sm font-bold text-red-950">${escapeHtml(hotline.label)}</span>
                    <span class="text-lg font-black text-red-800">${escapeHtml(hotline.phone)}</span>
                </a>
            `).join('');
        }

        const latestMsgsEl = document.getElementById('latest-messages');
        if (latestMsgsEl) {
            latestMsgsEl.innerHTML = (payload.latest_messages || []).map((message) => `
                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm text-gray-800">${escapeHtml(message.message)}</p>
                    <p class="mt-1 text-xs text-gray-400">${escapeHtml(message.time)}</p>
                </div>
            `).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Belum ada pesan mitra. Tetap pantau halaman ini.</p>';
        }

        // Update Re-Alert Button UI secara dinamis
        const reAlertContainer = document.getElementById('re-alert-container');
        if (reAlertContainer && isCreator) {
            if (payload.retry_count >= 3 && payload.report.status !== 'Resolved' && !payload.assigned_partner) {
                reAlertContainer.classList.remove('hidden');
                
                const btnReAlert = document.getElementById('btn-re-alert');
                const btnCountdown = document.getElementById('re-alert-countdown');
                
                if (payload.cooldown_seconds > 0) {
                    btnReAlert.disabled = true;
                    btnReAlert.classList.remove('bg-red-600', 'hover:bg-red-700');
                    btnReAlert.classList.add('bg-gray-400', 'cursor-not-allowed');
                    btnCountdown.classList.remove('hidden');
                    
                    startCountdownTimer(payload.cooldown_seconds);
                } else {
                    // Check jika timer client-side tidak aktif baru aktifkan tombol
                    if (!window._countdownActive) {
                        btnReAlert.disabled = false;
                        btnReAlert.classList.add('bg-red-600', 'hover:bg-red-700');
                        btnReAlert.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    }
                }
            } else {
                reAlertContainer.classList.add('hidden');
            }
        }
        // Update Chronology List secara dinamis & real-time
        const chronologyList = document.getElementById('chronology-list');
        if (chronologyList && payload.chronologies) {
            if (payload.chronologies.length > 0) {
                chronologyList.innerHTML = payload.chronologies.map(chrono => {
                    let roleLabel = `${chrono.role} (${chrono.writer_name})`;
                    if (chrono.role === 'Korban') {
                        roleLabel = `Korban (${chrono.writer_name})`;
                    } else if (chrono.role === 'Saksi') {
                        roleLabel = `Saksi (${chrono.writer_name})`;
                    }
                    return `
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
                            <div class="flex justify-between items-start gap-3 flex-wrap">
                                <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                                    ${escapeHtml(roleLabel)}
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium">
                                    ${escapeHtml(chrono.created_at)}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line">${escapeHtml(chrono.description)}</p>
                        </div>
                    `;
                }).join('');
            } else {
                chronologyList.innerHTML = `
                    <div class="text-center py-6 text-gray-400 text-sm italic" id="chronology-empty-state">
                        Belum ada kronologi tambahan.
                    </div>
                `;
            }
        }

        // Update Evidence List secara dinamis & real-time
        const evidenceListContainer = document.getElementById('evidence-list-container');
        if (evidenceListContainer && payload.evidences) {
            if (payload.evidences.length > 0) {
                const roles = ['Korban', 'Saksi', 'Mitra'];
                const grouped = { Korban: [], Saksi: [], Mitra: [] };
                payload.evidences.forEach(ev => {
                    const role = ev.uploader_role || 'Saksi';
                    if (!grouped[role]) grouped[role] = [];
                    grouped[role].push(ev);
                });

                let html = '';
                roles.forEach(role => {
                    const list = grouped[role];
                    if (list && list.length > 0) {
                        html += `
                            <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4 mb-3" id="evidence-group-${role}">
                                <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                                    Bukti dari ${role}
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-${role}">
                        `;

                        list.forEach(ev => {
                            const isImage = (ev.file_type || '').startsWith('image/');
                            const isVideo = (ev.file_type || '').startsWith('video/');
                            const isAudio = (ev.file_type || '').startsWith('audio/');
                            const canView = payload.can_view_evidence;
                            const evidenceUrl = ev.file_url;

                            let cardBg = 'bg-blue-50/50 border-blue-150';
                            let badgeBg = 'bg-blue-100 text-blue-800';
                            let badgeLabel = 'BERKAS';
                            let svgIcon = '';

                            if (isImage) {
                                cardBg = 'bg-rose-50/50 border-rose-150';
                                badgeBg = 'bg-rose-100 text-rose-800';
                                badgeLabel = 'FOTO';
                                svgIcon = `<svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
                            } else if (isVideo) {
                                cardBg = 'bg-amber-50/50 border-amber-150';
                                badgeBg = 'bg-amber-100 text-amber-800';
                                badgeLabel = 'VIDEO';
                                svgIcon = `<svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`;
                            } else if (isAudio) {
                                cardBg = 'bg-emerald-50/50 border-emerald-150';
                                badgeBg = 'bg-emerald-100 text-emerald-800';
                                badgeLabel = 'REKAMAN';
                                svgIcon = `<svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>`;
                            } else {
                                svgIcon = `<svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                            }

                            html += `
                                <a href="${canView ? evidenceUrl : '#'}" target="${canView ? '_blank' : ''}" onclick="${!canView ? "alert('Bukti hanya bisa dibuka oleh korban / mitra'); return false;" : ''}" class="aspect-square rounded-2xl border ${cardBg} p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md">
                                    ${svgIcon}
                                    <span class="mt-3 text-[10px] font-bold tracking-widest uppercase ${badgeBg} px-2 py-0.5 rounded-full">${badgeLabel}</span>
                                    ${!canView ? `
                                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                                            <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    ` : ''}
                                </a>
                            `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    }
                });

                evidenceListContainer.innerHTML = html;
            } else {
                evidenceListContainer.innerHTML = `
                    <div class="text-center py-6 text-gray-400 text-sm italic" id="evidence-empty-state">
                        Belum ada berkas bukti diunggah.
                    </div>
                `;
            }
        }
    }

    let countdownInterval = null;
    function startCountdownTimer(seconds) {
        if (window._countdownActive) return;
        window._countdownActive = true;
        
        if (countdownInterval) clearInterval(countdownInterval);
        
        let remaining = seconds;
        const countdownSpan = document.getElementById('re-alert-countdown');
        const btnReAlert = document.getElementById('btn-re-alert');
        
        if (!countdownSpan) {
            window._countdownActive = false;
            return;
        }
        
        countdownSpan.classList.remove('hidden');
        if (btnReAlert) {
            btnReAlert.disabled = true;
            btnReAlert.classList.remove('bg-red-600', 'hover:bg-red-700');
            btnReAlert.classList.add('bg-gray-400', 'cursor-not-allowed');
        }
        
        function updateDisplay() {
            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            countdownSpan.textContent = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        
        updateDisplay();
        
        countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                window._countdownActive = false;
                countdownSpan.classList.add('hidden');
                if (btnReAlert) {
                    btnReAlert.disabled = false;
                    btnReAlert.classList.add('bg-red-600', 'hover:bg-red-700');
                    btnReAlert.classList.remove('bg-gray-400', 'cursor-not-allowed');
                }
            } else {
                updateDisplay();
            }
        }, 1000);
    }

    async function triggerReAlert() {
        const btnReAlert = document.getElementById('btn-re-alert');
        if (!btnReAlert || btnReAlert.disabled) return;
        
        if (!await window.showConfirm('Apakah Anda yakin ingin mengirim ulang alert WhatsApp ke mitra terdekat?')) return;
        
        btnReAlert.disabled = true;
        const originalContent = btnReAlert.innerHTML;
        btnReAlert.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengirimkan Alert...</span>
        `;
        
        try {
            const response = await fetch(`/tracking/${reportId}/re-alert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            if (response.ok && data.ok) {
                alert('Alert WhatsApp berhasil dikirim ulang ke mitra!');
                startCountdownTimer(600);
                pollLive();
            } else {
                alert(data.error || 'Gagal mengirim ulang alert.');
                btnReAlert.innerHTML = originalContent;
                btnReAlert.disabled = false;
            }
        } catch (error) {
            alert('Terjadi kesalahan sistem saat menghubungi mitra.');
            btnReAlert.innerHTML = originalContent;
            btnReAlert.disabled = false;
        }
    }

    async function pollLive() {
        try {
            const response = await fetch(`/tracking/${reportId}/live`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                render(await response.json());
                const liveDot = document.getElementById('live-dot');
                if (liveDot) liveDot.textContent = 'Live';
            }
        } catch (error) {
            const liveDot = document.getElementById('live-dot');
            if (liveDot) liveDot.textContent = 'Mencoba ulang';
        }
    }

    let watchId = null;

    function pushLocation() {
        if (!navigator.geolocation) return;

        const updateCoords = async (pos) => {
            try {
                await fetch(`/tracking/${reportId}/location`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    })
                });
            } catch (e) {}
        };

        // Dapatkan lokasi awal dengan cepat
        navigator.geolocation.getCurrentPosition(updateCoords, () => {}, {
            enableHighAccuracy: true,
            timeout: 5000
        });

        // Pantau perubahan lokasi secara real-time
        watchId = navigator.geolocation.watchPosition(updateCoords, (err) => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        });
    }

    render(initialPayload);
    setInterval(pollLive, 4000);

    const reportLat = initialPayload.report.location.latitude;
    const reportLng = initialPayload.report.location.longitude;

    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lon1) * Math.PI / 180; // Wait, actually:
        // const dLat = (lat2 - lat1) * Math.PI / 180;
        // let's write it standardly:
        const dLatRad = (lat2 - lat1) * Math.PI / 180;
        const dLonRad = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLatRad/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLonRad/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // Warga/guest: cek lokasi, tampilkan tombol hanya jika < 5km
    if (!hasDirectChatAccess && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                // Store on window so render can use it
                window._userLat = lat;
                window._userLng = lng;

                const checking  = document.getElementById('chat-checking');
                const chatLink  = document.getElementById('chat-link');
                const tooFar    = document.getElementById('chat-too-far');
                const distNote  = document.getElementById('chat-distance-note');
                const noGps     = document.getElementById('chat-no-gps');

                if (checking) checking.classList.add('hidden');

                // Set URL dengan koordinat agar server bisa verifikasi
                if (chatLink) chatLink.href = `/chat/report/${reportId}?lat=${lat}&lng=${lng}`;

                if (reportLat && reportLng) {
                    const dist = haversineKm(lat, lng, parseFloat(reportLat), parseFloat(reportLng));
                    if (dist <= 5.0) {
                        window._chatAllowed = true;
                        if (chatLink) {
                            chatLink.classList.remove('hidden');
                            chatLink.classList.add('inline-flex');
                        }
                        if (distNote) distNote.classList.remove('hidden');
                        
                        // Tampilkan tombol Tambah Kronologi jika saksi di bawah 5 km
                        const chronoBtn = document.getElementById('btn-add-chronology');
                        if (chronoBtn) chronoBtn.classList.remove('hidden');
                    } else {
                        window._chatAllowed = false;
                        // Terlalu jauh — tampilkan pesan, sembunyikan tombol
                        if (tooFar) tooFar.classList.remove('hidden');
                    }
                } else {
                    // Laporan tidak punya GPS — tidak bisa verifikasi jarak
                    window._chatAllowed = true;
                    if (chatLink) {
                        chatLink.classList.remove('hidden');
                        chatLink.classList.add('inline-flex');
                    }
                    if (distNote) {
                        distNote.textContent = '📍 Lokasi laporan tidak tersedia — tombol chat tetap bisa dicoba.';
                        distNote.classList.remove('hidden');
                    }
                }
            },
            () => {
                // Gagal ambil lokasi browser
                const checking = document.getElementById('chat-checking');
                const noGps    = document.getElementById('chat-no-gps');
                if (checking) checking.classList.add('hidden');
                if (noGps)    noGps.classList.remove('hidden');
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
        );
    } else if (!hasDirectChatAccess) {
        // Browser tidak support geolocation
        const checking = document.getElementById('chat-checking');
        const noGps    = document.getElementById('chat-no-gps');
        if (checking) checking.classList.add('hidden');
        if (noGps)    noGps.classList.remove('hidden');
    }


    // Pelapor: push GPS location secara real-time untuk tracking
    if (isCreator) {
        pushLocation();
    }

    const activeUploads = {};
    let uploadedEvidenceIdsInSession = [];
    let uploadedEvidencesInSession = [];
    const canViewEvidence = @json($report->show_evidence || (auth()->check() && (auth()->id() === $report->user_id || auth()->user()->role === 'partner')) || in_array($report->id, session('my_reports', [])));

    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('evf');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                Array.from(this.files).forEach(file => {
                    uploadFile(file);
                });
                this.value = '';
            });
        }
    });

    function uploadFile(file) {
        const uploadList = document.getElementById('upload-list');
        if (!uploadList) return;
        const uniqueId = Math.random().toString(36).substring(2, 15);
        
        const item = document.createElement('div');
        item.id = `upload-${uniqueId}`;
        item.className = "flex items-center justify-between bg-white border border-gray-100 p-3 rounded-xl shadow-sm mb-2";
        item.innerHTML = `
            <div class="flex-1 min-w-0 mr-3">
                <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5 overflow-hidden" id="progress-container-${uniqueId}">
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%" id="progress-${uniqueId}"></div>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <svg class="w-3 h-3 text-green-500 hidden" id="check-${uniqueId}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-[10px] text-gray-500" id="text-${uniqueId}">Menyiapkan...</p>
                </div>
            </div>
            <div class="flex items-center gap-2" id="action-${uniqueId}">
                <!-- Cancel button during upload -->
                <button type="button" onclick="cancelUpload('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Batalkan Upload">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;
        uploadList.appendChild(item);

        const formData = new FormData();
        formData.append('evidence[]', file);
        formData.append('_token', '{{ csrf_token() }}');

        const xhr = new XMLHttpRequest();
        activeUploads[uniqueId] = xhr;

        xhr.open('POST', '/tracking/{{ $report->id }}/evidence', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                const bar = document.getElementById(`progress-${uniqueId}`);
                if (bar) bar.style.width = percent + '%';
                const txt = document.getElementById(`text-${uniqueId}`);
                if (txt) txt.innerText = `Mengunggah ${percent}%`;
            }
        };

        xhr.onload = function() {
            delete activeUploads[uniqueId];
            if (xhr.status === 200) {
                let res = {};
                try {
                    res = JSON.parse(xhr.responseText);
                } catch(e) {}

                const txt = document.getElementById(`text-${uniqueId}`);
                if (txt) {
                    txt.innerText = 'Selesai';
                    txt.classList.replace('text-gray-500', 'text-green-600');
                }
                const pContainer = document.getElementById(`progress-container-${uniqueId}`);
                if (pContainer) pContainer.classList.add('hidden');
                const chk = document.getElementById(`check-${uniqueId}`);
                if (chk) chk.classList.remove('hidden');

                // Show delete button
                if (res.evidences && res.evidences.length > 0) {
                    const evidenceObj = res.evidences[0];
                    const evidenceId = evidenceObj.id;
                    uploadedEvidenceIdsInSession.push(evidenceId);
                    uploadedEvidencesInSession.push(evidenceObj);
                    
                    const actionContainer = document.getElementById(`action-${uniqueId}`);
                    if (actionContainer) {
                        actionContainer.innerHTML = `
                            <button type="button" onclick="deleteUploadedEvidence('${uniqueId}', '${evidenceId}')" class="text-red-600 hover:text-red-800 transition p-1" title="Hapus Bukti">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        `;
                    }
                } else {
                    const actionContainer = document.getElementById(`action-${uniqueId}`);
                    if (actionContainer) actionContainer.innerHTML = '';
                }
            } else {
                const txt = document.getElementById(`text-${uniqueId}`);
                let errMsg = 'Gagal upload';
                try {
                    const errData = JSON.parse(xhr.responseText);
                    if (errData && errData.error) errMsg = errData.error;
                } catch(e) {}
                if (txt) {
                    txt.innerText = errMsg;
                    txt.classList.add('text-red-500');
                }
                const bar = document.getElementById(`progress-${uniqueId}`);
                if (bar) bar.classList.replace('bg-green-500', 'bg-red-500');
                
                // Show remove button for failed item
                const actionContainer = document.getElementById(`action-${uniqueId}`);
                if (actionContainer) {
                    actionContainer.innerHTML = `
                        <button type="button" onclick="removeFailedItem('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Hapus dari daftar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    `;
                }
            }
        };

        xhr.onerror = function() {
            delete activeUploads[uniqueId];
            const txt = document.getElementById(`text-${uniqueId}`);
            if (txt) {
                txt.innerText = 'Gagal koneksi';
                txt.classList.add('text-red-500');
            }
            const bar = document.getElementById(`progress-${uniqueId}`);
            if (bar) bar.classList.replace('bg-green-500', 'bg-red-500');
            
            // Show remove button for failed connection
            const actionContainer = document.getElementById(`action-${uniqueId}`);
            if (actionContainer) {
                actionContainer.innerHTML = `
                    <button type="button" onclick="removeFailedItem('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Hapus dari daftar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
            }
        };

        xhr.send(formData);
    }

    function cancelUpload(uniqueId) {
        if (activeUploads[uniqueId]) {
            activeUploads[uniqueId].abort();
            delete activeUploads[uniqueId];
        }
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) {
            item.classList.add('opacity-50');
            item.querySelector(`#text-${uniqueId}`).innerText = 'Dibatalkan';
            item.querySelector(`#text-${uniqueId}`).classList.add('text-red-500');
            const pBar = item.querySelector(`#progress-${uniqueId}`);
            if (pBar) pBar.classList.replace('bg-green-500', 'bg-red-500');
            const action = item.querySelector(`#action-${uniqueId}`);
            if (action) action.innerHTML = '';
            setTimeout(() => item.remove(), 1500);
        }
    }

    function removeFailedItem(uniqueId) {
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) item.remove();
    }

    async function deleteUploadedEvidence(uniqueId, evidenceId) {
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) {
            item.classList.add('opacity-50');
            item.querySelector(`#text-${uniqueId}`).innerText = 'Menghapus...';
            item.querySelector(`#text-${uniqueId}`).classList.replace('text-green-600', 'text-gray-500');
        }

        try {
            const res = await fetch(`/evidence/${evidenceId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                uploadedEvidenceIdsInSession = uploadedEvidenceIdsInSession.filter(id => id !== evidenceId && id != evidenceId);
                uploadedEvidencesInSession = uploadedEvidencesInSession.filter(ev => ev.id !== evidenceId && ev.id != evidenceId);
                if (item) {
                    item.querySelector(`#text-${uniqueId}`).innerText = 'Terhapus';
                    item.querySelector(`#text-${uniqueId}`).classList.add('text-red-500');
                    setTimeout(() => item.remove(), 1000);
                }
            } else {
                alert('Gagal menghapus file.');
                if (item) {
                    item.classList.remove('opacity-50');
                    item.querySelector(`#text-${uniqueId}`).innerText = 'Selesai';
                    item.querySelector(`#text-${uniqueId}`).classList.replace('text-gray-500', 'text-green-600');
                }
            }
        } catch(e) {
            alert('Kesalahan koneksi saat menghapus.');
            if (item) {
                item.classList.remove('opacity-50');
                item.querySelector(`#text-${uniqueId}`).innerText = 'Selesai';
            }
        }
    }

    function getOrCreateEvidenceGroup(role) {
        let group = document.getElementById(`evidence-group-${role}`);
        if (group) return group;

        group = document.createElement('div');
        group.id = `evidence-group-${role}`;
        group.className = "bg-slate-50/50 border border-slate-100 rounded-xl p-4 fade-in";
        group.innerHTML = `
            <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                Bukti dari ${role}
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-${role}"></div>
        `;

        const container = document.getElementById('evidence-list-container');
        const emptyState = document.getElementById('evidence-empty-state');
        if (emptyState) emptyState.remove();

        if (role === 'Korban') {
            container.insertBefore(group, container.firstChild);
        } else if (role === 'Saksi') {
            const korbanGroup = document.getElementById('evidence-group-Korban');
            if (korbanGroup) {
                korbanGroup.parentNode.insertBefore(group, korbanGroup.nextSibling);
            } else {
                container.insertBefore(group, container.firstChild);
            }
        } else if (role === 'Mitra') {
            container.appendChild(group);
        }

        return group;
    }

    function insertEvidenceDOM(evidence) {
        const role = evidence.uploader_role || 'Saksi';
        getOrCreateEvidenceGroup(role);
        const grid = document.getElementById(`evidence-grid-${role}`);
        if (!grid) return;

        const isImage = evidence.file_type && evidence.file_type.startsWith('image/');
        const isVideo = evidence.file_type && evidence.file_type.startsWith('video/');
        const isAudio = evidence.file_type && evidence.file_type.startsWith('audio/');

        const href = canViewEvidence ? evidence.file_url : '#';
        const target = canViewEvidence ? '_blank' : '';

        const a = document.createElement('a');
        a.href = href;
        a.target = target;
        if (!canViewEvidence) {
            a.setAttribute('onclick', "alert('Bukti hanya bisa dibuka oleh korban / mitra'); return false;");
        }

        let cardBg = 'bg-blue-50/50 border-blue-150';
        let badgeBg = 'bg-blue-100 text-blue-800';
        let badgeLabel = 'BERKAS';
        let svgIcon = '';
        let blurClass = !canViewEvidence ? 'blur-[3px]' : '';

        if (isImage) {
            cardBg = 'bg-rose-50/50 border-rose-150';
            badgeBg = 'bg-rose-100 text-rose-800';
            badgeLabel = 'FOTO';
            svgIcon = `
                <svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            `;
        } else if (isVideo) {
            cardBg = 'bg-amber-50/50 border-amber-150';
            badgeBg = 'bg-amber-100 text-amber-800';
            badgeLabel = 'VIDEO';
            svgIcon = `
                <svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            `;
        } else if (isAudio) {
            cardBg = 'bg-emerald-50/50 border-emerald-150';
            badgeBg = 'bg-emerald-100 text-emerald-800';
            badgeLabel = 'REKAMAN';
            svgIcon = `
                <svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
            `;
        } else {
            svgIcon = `
                <svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            `;
        }

        a.className = `aspect-square rounded-2xl border ${cardBg} p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md fade-in`;

        let lockContent = '';
        if (!canViewEvidence) {
            lockContent = `
                <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                    <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            `;
        }

        a.innerHTML = `
            ${svgIcon}
            <span class="mt-3 text-[10px] font-bold tracking-widest uppercase ${badgeBg} px-2 py-0.5 rounded-full">${badgeLabel}</span>
            ${lockContent}
        `;
        grid.appendChild(a);
    }

    function saveEvidenceSession() {
        if (uploadedEvidencesInSession.length > 0) {
            uploadedEvidencesInSession.forEach(evidence => {
                insertEvidenceDOM(evidence);
            });
        }
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        closeEvidenceModal();
    }

    async function cancelEvidenceSession() {
        const idsToDiscard = [...uploadedEvidenceIdsInSession];
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        
        for (const uniqueId in activeUploads) {
            if (activeUploads[uniqueId]) {
                activeUploads[uniqueId].abort();
            }
        }
        
        if (idsToDiscard.length > 0) {
            const list = document.getElementById('upload-list');
            if (list) list.innerHTML = '<div class="text-xs text-red-500 font-semibold italic text-center py-2 animate-pulse">Membatalkan & menghapus seluruh berkas...</div>';
            
            try {
                await Promise.all(idsToDiscard.map(id => 
                    fetch(`/evidence/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                ));
            } catch(e) {
                console.error('Error discarding session uploads:', e);
            }
        }
        closeEvidenceModal();
    }

    function openEvidenceModal() {
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        const m = document.getElementById('evidence-modal');
        if (!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeEvidenceModal() {
        const m = document.getElementById('evidence-modal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('upload-list').innerHTML = '';
    }

    function openChronologyModal() {
        const m = document.getElementById('chronology-modal');
        if (!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeChronologyModal() {
        const m = document.getElementById('chronology-modal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('chrono-description').value = '';
    }

    async function submitChronology(event) {
        event.preventDefault();
        const desc = document.getElementById('chrono-description').value.trim();
        if (!desc) return;

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        // Set loading state manually
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Menyimpan...
        `;

        const lat = window._userLat || null;
        const lng = window._userLng || null;

        try {
            const res = await fetch(`/tracking/${reportId}/chronology`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    description: desc,
                    latitude: lat,
                    longitude: lng
                })
            });

            const data = await res.json();
            if (!res.ok) {
                alert(data.error || 'Gagal menambahkan kronologi.');
                return;
            }

            const emptyState = document.getElementById('chronology-empty-state');
            if (emptyState) emptyState.remove();

            const list = document.getElementById('chronology-list');
            const item = document.createElement('div');
            item.className = "p-4 bg-slate-50 border border-slate-100 rounded-xl fade-in";
            item.innerHTML = `
                <div class="flex justify-between items-start gap-3 flex-wrap">
                    <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                        ${data.chronology.writer_name}
                    </span>
                    <span class="text-[10px] text-gray-400 font-medium">
                        ${data.chronology.created_at}
                    </span>
                </div>
                <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line">${data.chronology.description}</p>
            `;
            list.insertBefore(item, list.firstChild);

            closeChronologyModal();
        } catch (e) {
            alert('Terjadi kesalahan sistem.');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    // Show "+ Tambah Kronologi" button if Korban or Trusted Contact, and not another partner handling
    const isTrustedContact = @json($isTrustedContact);
    const isOtherPartnerHandling = @json($isOtherPartnerHandling);
    if ((isCreator || isTrustedContact) && !isOtherPartnerHandling) {
        const btn = document.getElementById('btn-add-chronology');
        if (btn) btn.classList.remove('hidden');
    }
</script>

{{-- ===== EVIDENCE MODAL ===== --}}
<div id="evidence-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] items-center justify-center px-4 transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Tambah Bukti</h2>
                <p class="text-gray-500 text-xs">Unggah berkas foto, rekaman suara, atau video untuk melengkapi laporan.</p>
            </div>
            <button type="button" onclick="cancelEvidenceSession()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <input type="file" id="evf" multiple class="hidden" accept="image/*,video/*,audio/*">
        <div class="border-2 border-dashed border-gray-300 hover:border-gray-400 rounded-2xl p-8 text-center transition cursor-pointer bg-slate-50 hover:bg-slate-100/50" onclick="document.getElementById('evf').click()">
            <p class="text-4xl mb-3">📁</p>
            <p class="text-gray-700 font-bold text-base">Klik untuk pilih file</p>
            <p class="text-gray-400 text-xs mt-1">Bisa pilih lebih dari 1 (Foto, video, dll)</p>
        </div>
        
        <div id="upload-list" class="mt-4 space-y-2 max-h-60 overflow-y-auto"></div>
        
        <div class="flex flex-col gap-2 mt-5">
            <button type="button" onclick="saveEvidenceSession()" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold text-sm transition">Simpan Bukti</button>
        </div>
    </div>
</div>

{{-- ===== KRONOLOGI MODAL ===== --}}
<div id="chronology-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] items-center justify-center px-4 transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Tambah Kronologi</h2>
                <p class="text-gray-500 text-xs">Berikan detail kronologi atau pemutakhiran situasi terkini untuk membantu mitra krisis.</p>
            </div>
            <button type="button" onclick="closeChronologyModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="chronology-form" class="no-loading" onsubmit="submitChronology(event)">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-900 mb-1">Catatan Kronologi / Perkembangan</label>
                <textarea name="description" id="chrono-description" rows="4" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 focus:bg-white resize-none" placeholder="Tulis kronologi atau situasi terbaru di sini..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeChronologyModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-sm transition shadow-sm">Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
