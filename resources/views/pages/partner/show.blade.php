<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php $backUrl = route('partner.index'); $backLabel = 'Kembali'; @endphp
    @include('partials.nav-auth')

    <div class="max-w-3xl mx-auto px-6 py-10">



        @php
        $sc=['Submitted'=>'bg-gray-100 text-gray-600','Routed'=>'bg-blue-50 text-blue-700','Viewed'=>'bg-yellow-50 text-yellow-700','In Progress'=>'bg-orange-50 text-orange-700','Resolved'=>'bg-green-50 text-green-700'];
        $stages=[['Submitted','Diajukan'],['Routed','Diteruskan'],['Viewed','Ditinjau'],['In Progress','Diproses'],['Resolved','Selesai']];
        $ci=array_search($report->status,array_column($stages,0));
        $statusIndo = match($report->status) {
            'Submitted' => 'Diajukan',
            'Routed' => 'Diteruskan',
            'Viewed' => 'Ditinjau',
            'Assigned' => 'Diterima',
            'In Progress' => 'Diproses',
            'Resolved' => 'Selesai',
            'Rejected' => 'Ditolak',
            default => $report->status
        };
        @endphp

        {{-- Header --}}
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">LAPORAN #{{ strtoupper(substr($report->id,0,8)) }}</p>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-unbounded text-3xl font-black text-gray-900">{{ $report->category }}</h1>
                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-400 flex-wrap">
                        @if($report->anonymous)<span class="bg-purple-50 text-purple-700 text-xs px-2 py-0.5 rounded-full font-semibold">Anonim</span>@endif
                        <span>{{ $report->created_at->format('d M Y, H:i') }}</span>
                        @if($report->incident_date)<span class="bg-gray-150 text-gray-700 text-xs px-2 py-0.5 rounded-full font-semibold">🗓️ Kejadian: {{ \Carbon\Carbon::parse($report->incident_date)->format('d M Y, H:i') }}</span>@endif
                        @if($report->latitude)<a href="https://maps.google.com/?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank" class="text-red-700 hover:text-red-800 text-xs underline">📍 Lihat Peta</a>@endif
                    </div>
                    @if($canViewSensitive && $report->user && $report->user->phone)
                    <div class="mt-2 text-sm text-gray-700 font-semibold">
                        📞 <a href="tel:{{ $report->user->phone }}" class="text-blue-600 hover:underline">{{ $report->user->phone }}</a>
                    </div>
                    @endif
                </div>
                <span class="px-3 py-1.5 rounded-full text-sm font-semibold {{ $sc[$report->status]??'bg-gray-100 text-gray-600' }} whitespace-nowrap">{{ $statusIndo }}</span>
            </div>
            
            @if($isHandling)
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/chat/report/{{ $report->id }}" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition text-center">💬 Lanjut Chat</a>
                @if($report->status !== 'Resolved')
                <form method="POST" action="{{ route('partner.status', $report->id) }}" class="inline-block">
                    @csrf
                    <input type="hidden" name="status" value="Resolved">
                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">✅ Tandai Selesai</button>
                </form>
                @endif
            </div>
            @endif

            @if($report->handler_partner_id === null && isset($isPending) && $isPending)
            <div class="mt-6">
                <form method="POST" action="{{ route('partner.report.accept', $report->id) }}">
                    @csrf
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-6 py-3 rounded-xl font-black text-sm transition shadow-md">
                        TERIMA KASUS
                    </button>
                </form>
            </div>
            @endif
        </div>

        @if($report->description)
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Deskripsi</p>
            <p class="text-gray-700">{{ $report->description }}</p>
        </div>
        @endif

        {{-- Update Status --}}
        @if($isHandling)
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Update Status</h2>
            <form action="/partner/report/{{ $report->id }}/status" method="POST" class="flex gap-3">
                @csrf
                <select name="status" class="flex-1 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-2.5 text-sm focus:outline-none bg-white">
                    @foreach(['Submitted' => 'Diajukan', 'Routed' => 'Diteruskan', 'Viewed' => 'Ditinjau', 'In Progress' => 'Diproses', 'Resolved' => 'Selesai'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $report->status===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Update</button>
            </form>
        </div>
        @endif

        {{-- Status Timeline --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-5">Timeline Status</h2>

            {{-- Step indicator --}}
            <div class="flex items-center mb-6">
                @foreach($stages as $i=>[$key,$label])
                <div class="flex flex-col items-center flex-1">
                    <div class="flex items-center w-full">
                        @if($i>0)<div class="flex-1 h-0.5 {{ $i<=$ci?'bg-green-500':'bg-gray-200' }} transition-all"></div>@endif
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                            {{ $i<$ci?'bg-green-500 text-white':($i==$ci?'bg-green-500 text-white ring-4 ring-green-100':'bg-gray-100 text-gray-400') }}">
                            @if($i<$ci)<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @elseif($i==$ci)<div class="w-2 h-2 bg-white rounded-full"></div>
                            @else{{ $i+1 }}@endif
                        </div>
                        @if($i<count($stages)-1)<div class="flex-1 h-0.5 {{ $i<$ci?'bg-green-500':'bg-gray-200' }}"></div>@endif
                    </div>
                    <p class="text-xs mt-1 {{ $i<=$ci?'text-gray-700 font-semibold':'text-gray-400' }} text-center hidden sm:block">{{ $label }}</p>
                </div>
                @endforeach
            </div>

            <div class="space-y-3 border-t border-gray-100 pt-4">
                @foreach($report->statusLogs as $log)
                @php
                    $logStatusIndo = match($log->new_status) {
                        'Submitted' => 'Diajukan',
                        'Routed' => 'Diteruskan',
                        'Viewed' => 'Ditinjau',
                        'Assigned' => 'Diterima',
                        'In Progress' => 'Diproses',
                        'Resolved' => 'Selesai',
                        'Rejected' => 'Ditolak',
                        default => $log->new_status
                    };
                @endphp
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $logStatusIndo }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y, H:i:s') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Bukti Korban --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Bukti dari Korban ({{ $report->evidences->count() }})</h2>
            @if(!$canViewSensitive)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold">Bukti hanya dapat dilihat oleh mitra yang menangani kasus ini, atau jika permintaan belum kadaluarsa.</div>
            @elseif($report->evidences->count() > 0)
            <div class="space-y-2">
                @foreach($report->evidences as $ev)
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-sm text-gray-700">{{ $ev->file_type }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ substr($ev->file_hash,0,28) }}...</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($ev->uploaded_at)->format('d M Y, H:i') }}</p>
                    </div>
                    <a href="javascript:void(0)" 
                       data-url="{{ str_starts_with($ev->file_url, 'data:') ? $ev->file_url : url('/evidences/view/' . basename($ev->file_url)) }}" 
                       data-type="{{ $ev->file_type }}"
                       onclick="viewEvidence(this.dataset.url, this.dataset.type)" 
                       class="text-red-700 hover:text-red-800 text-sm font-semibold transition">Buka →</a>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-400 text-sm">Belum ada bukti diupload.</p>
            @endif
        </div>




    </div>
    
    <script>
    function viewEvidence(url, fileType) {
        if (!url || url === '#') return;
        
        fileType = fileType || '';
        
        if (url.startsWith('data:')) {
            const newWindow = window.open();
            if (!newWindow) {
                alert('Pop-up diblokir oleh browser. Silakan aktifkan pop-up untuk melihat bukti.');
                return;
            }
            newWindow.document.write(`
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Safora - Lihat Bukti</title>
                    <style>
                        body {
                            margin: 0;
                            background-color: #0b0f19;
                            color: #f3f4f6;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-height: 100vh;
                            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                        }
                        .container {
                            text-align: center;
                            padding: 24px;
                            max-width: 90%;
                            width: 100%;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            gap: 16px;
                        }
                        img, video {
                            max-width: 100%;
                            max-height: 80vh;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                            border-radius: 16px;
                            border: 1px solid rgba(255, 255, 255, 0.1);
                        }
                        audio {
                            width: 100%;
                            max-width: 400px;
                        }
                        iframe {
                            width: 100%;
                            height: 80vh;
                            border: none;
                            border-radius: 16px;
                            background: white;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
                        }
                        .meta {
                            font-size: 13px;
                            color: #9ca3af;
                            font-weight: 500;
                        }
                        .btn-download {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            background-color: #dc2626;
                            color: white;
                            padding: 10px 20px;
                            text-decoration: none;
                            border-radius: 12px;
                            font-weight: 700;
                            font-size: 14px;
                            transition: background-color 0.2s, transform 0.1s;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        }
                        .btn-download:hover {
                            background-color: #b91c1c;
                            transform: scale(1.02);
                        }
                        .btn-download:active {
                            transform: scale(0.98);
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
            `);

            if (fileType.startsWith('image/')) {
                newWindow.document.write(\`<img src="\${url}" alt="Bukti Foto">\`);
            } else if (fileType.startsWith('video/')) {
                newWindow.document.write(\`<video src="\${url}" controls autoplay></video>\`);
            } else if (fileType.startsWith('audio/')) {
                newWindow.document.write(\`<audio src="\${url}" controls autoplay></audio>\`);
            } else {
                newWindow.document.write(\`<iframe src="\${url}"></iframe>\`);
            }

            newWindow.document.write(\`
                        <div class="meta">Format: \${fileType}</div>
                        <a href="\${url}" download="bukti-\${Date.now()}" class="btn-download">Unduh Berkas</a>
                    </div>
                </body>
                </html>
            \`);
            newWindow.document.close();
        } else {
            window.open(url, '_blank');
        }
    }
    </script>
</body>
</html>

