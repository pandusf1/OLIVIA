<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php
        $backUrl = route('dashboard');
        $backLabel = 'Batal';
    @endphp
    @include('partials.nav-auth')
    <div class="max-w-lg mx-auto px-6 py-12">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">LAPORAN BIASA</p>
        <h1 class="font-unbounded text-3xl font-bold text-gray-900 mb-2">Buat Laporan Baru</h1>
        <p class="text-gray-500 text-sm mb-8">Gunakan formulir ini untuk melaporkan kejadian yang sudah berlalu (bukan darurat). Kamu bisa menceritakan kronologi dan lokasi kejadian secara spesifik.</p>



        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kategori Kejadian *</label>
                    <select name="category" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white" required>
                        <option value="" disabled selected>Pilih Kategori...</option>
                        <option value="Kekerasan">Kekerasan Fisik / KDRT</option>
                        <option value="Pelecehan & Bullying">Pelecehan & Perundungan (Bullying)</option>
                        <option value="Salah Tangkap">Salah Tangkap / Kriminalisasi</option>
                        <option value="Konseling & Trauma">Konseling & Pemulihan Trauma</option>
                        <option value="Sosial">Sosial / Anak/Lansia Terlantar</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Waktu & Tanggal Kejadian *</label>
                    <input type="datetime-local" name="incident_date" value="{{ old('incident_date') }}"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Lokasi Kejadian *</label>
                    <input type="text" name="location_text" value="{{ old('location_text') }}" placeholder="Contoh: Jl. Sudirman depan stasiun, Semarang"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kronologi / Deskripsi *</label>
                    <textarea name="description" rows="4" placeholder="Ceritakan secara detail waktu, orang yang terlibat, dan urutan kejadian..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition resize-none" required>{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Upload Bukti (opsional)</label>
                    <div class="border border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-5 text-center transition cursor-pointer" onclick="document.getElementById('evf').click()">
                        <p class="text-2xl mb-1">📁</p>
                        <p class="text-gray-500 text-sm font-semibold">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-[11px] mt-1">Bisa pilih lebih dari 1 (Gambar, Video, Audio, PDF, dll)</p>
                    </div>
                    <input type="file" id="evf" class="hidden" multiple accept="*/*">
                    <div id="upload-list" class="mt-3 space-y-2"></div>
                </div>

                <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-3 mb-4 mt-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Sembunyikan Identitas</p>
                        <p class="text-xs text-gray-400">Laporan akan bersifat anonim</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="anonymous" value="1" class="sr-only peer">
                        <div class="w-10 h-6 bg-gray-200 peer-checked:bg-gray-900 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
                
                <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs text-gray-500">Buktimu hanya bisa diakses kamu dan mitra.</div>
                
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3.5 rounded-xl font-bold text-sm transition">Laporkan</button>
            </form>
        </div>
    </div>
    
    <script>
        const fileInput = document.getElementById('evf');
        const uploadList = document.getElementById('upload-list');
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');

        function compressImageOnClient(file, maxWidth = 1920, maxHeight = 1920, quality = 0.75) {
            return new Promise((resolve) => {
                if (!file.type.startsWith('image/') || file.type === 'image/gif') {
                    resolve(file);
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function(event) {
                    const img = new Image();
                    img.src = event.target.result;
                    img.onload = function() {
                        let width = img.width;
                        let height = img.height;
                        
                        if (width > maxWidth || height > maxHeight) {
                            if (width > height) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            } else {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }
                        
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        
                        const outputType = 'image/jpeg';
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                resolve(file);
                                return;
                            }
                            let newName = file.name;
                            if (!newName.endsWith('.jpg') && !newName.endsWith('.jpeg')) {
                                newName = newName.replace(/\.[a-zA-Z0-9]+$/, '.jpg');
                            }
                            const compressedFile = new File([blob], newName, {
                                type: outputType,
                                lastModified: Date.now()
                            });
                            
                            if (compressedFile.size >= file.size) {
                                resolve(file);
                            } else {
                                resolve(compressedFile);
                            }
                        }, outputType, quality);
                    };
                    img.onerror = function() {
                        resolve(file);
                    };
                };
                reader.onerror = function() {
                    resolve(file);
                };
            });
        }

        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                const fileId = Math.random().toString(36).substring(2, 15) + Date.now();
                compressImageOnClient(file).then(processedFile => {
                    uploadFileAsync(processedFile, fileId);
                });
            });
            // Clear input value so same file can be selected again
            fileInput.value = '';
        });

        function getFileIcon(mimeType) {
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType.startsWith('video/')) return '🎥';
            if (mimeType.startsWith('audio/')) return '🎵';
            if (mimeType === 'application/pdf') return '📄';
            return '📁';
        }

        function uploadFileAsync(file, fileId) {
            // Render loading item
            const item = document.createElement('div');
            item.id = `upload-${fileId}`;
            item.className = "bg-white border border-gray-150 p-4 rounded-xl shadow-sm mb-2 hover:border-gray-200 transition duration-200 flex flex-col gap-2.5";
            
            const sizeMb = (file.size / 1024 / 1024).toFixed(2);
            
            item.innerHTML = `
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0 flex items-center gap-2.5">
                        <span class="text-xl shrink-0">${getFileIcon(file.type)}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
                            <p class="text-[10px] text-gray-400 font-mono">${sizeMb} MB · <span class="upload-percent font-bold text-red-600">0%</span></p>
                        </div>
                    </div>
                    <div class="shrink-0 upload-status-indicator">
                        <svg class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden upload-progress-container">
                    <div class="bg-red-600 h-1.5 rounded-full transition-all duration-100 upload-progress-bar" style="width:0%"></div>
                </div>
            `;
            
            uploadList.appendChild(item);

            // AJAX Upload using XMLHttpRequest to get real-time upload progress
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('evidence', file);

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    const progressBar = item.querySelector('.upload-progress-bar');
                    const progressPercent = item.querySelector('.upload-percent');
                    if (progressBar) progressBar.style.width = percent + '%';
                    if (progressPercent) progressPercent.textContent = percent + '%';
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success && res.path) {
                            // Turn item into success view
                            const progressPercent = item.querySelector('.upload-percent');
                            if (progressPercent) progressPercent.textContent = 'Selesai';
                            
                            const progressContainer = item.querySelector('.upload-progress-container');
                            if (progressContainer) progressContainer.remove();

                            const statusIndicator = item.querySelector('.upload-status-indicator');
                            if (statusIndicator) {
                                statusIndicator.innerHTML = `
                                    <button type="button" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0" onclick="deleteTempFile('${fileId}', '${res.path}')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                    <input type="hidden" name="temp_evidences[]" value="${res.path}">
                                `;
                            }
                        } else {
                            handleUploadFailure(item);
                        }
                    } catch (e) {
                        handleUploadFailure(item);
                    }
                } else {
                    handleUploadFailure(item);
                }
            });

            xhr.addEventListener('error', function() {
                handleUploadFailure(item);
            });

            xhr.open('POST', '/report/evidence/upload');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        }

        function handleUploadFailure(item) {
            const progressPercent = item.querySelector('.upload-percent');
            if (progressPercent) {
                progressPercent.textContent = 'Gagal';
                progressPercent.className = "upload-percent font-bold text-red-700";
            }
            
            const progressContainer = item.querySelector('.upload-progress-container');
            if (progressContainer) progressContainer.remove();

            const statusIndicator = item.querySelector('.upload-status-indicator');
            if (statusIndicator) {
                statusIndicator.innerHTML = `
                    <button type="button" class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0" onclick="this.closest('[id^=\\'upload-\\']').remove()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
            }
        }

        async function deleteTempFile(fileId, tempPath) {
            const item = document.getElementById(`upload-${fileId}`);
            const statusIndicator = item.querySelector('.upload-status-indicator');
            
            // Show loading icon during deletion
            statusIndicator.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;

            try {
                const response = await fetch('/report/evidence/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ path: tempPath })
                });
                const res = await response.json();
                if (res.success) {
                    item.remove();
                } else {
                    alert('Gagal menghapus file dari server.');
                    // Restore delete icon
                    restoreDeleteButton(fileId, tempPath, statusIndicator);
                }
            } catch (e) {
                alert('Terjadi kesalahan saat menghapus file.');
                restoreDeleteButton(fileId, tempPath, statusIndicator);
            }
        }

        function restoreDeleteButton(fileId, tempPath, statusIndicator) {
            statusIndicator.innerHTML = `
                <button type="button" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0" onclick="deleteTempFile('${fileId}', '${tempPath}')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <input type="hidden" name="temp_evidences[]" value="${tempPath}">
            `;
        }

        form.addEventListener('submit', function(e) {
            // Using setTimeout to guarantee the browser initiates form submission before disabling button
            setTimeout(() => {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MENYIMPAN LAPORAN...
                `;
            }, 10);
        });
    </script>
</body>
</html>
