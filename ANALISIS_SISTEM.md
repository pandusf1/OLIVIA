# Analisis Alur Aplikasi & Struktur File

Dokumen ini berisi analisis menyeluruh mengenai alur sistem dari berbagai perspektif pengguna, fitur-fitur unggulan, serta daftar file yang tidak diperlukan yang telah dihapus untuk menjaga kebersihan *codebase*.

## 1. Alur Pengguna (User POV)

### A. User Tanpa Login (Guest)
Sistem dirancang agar responsif terhadap krisis, sehingga pengguna yang belum terdaftar (atau tidak sempat login) tetap dapat menggunakan fitur darurat:
- **Panic Button / Emergency:** Guest dapat menekan tombol darurat dari halaman utama. Sistem akan merekam kategori darurat dan titik lokasi GPS.
- **Live Tracking:** Setelah darurat dibuat, guest diarahkan ke halaman pelacakan (`/tracking/{id}`) melalui data yang disimpan di *session*. Guest dapat memantau status laporannya secara *real-time*.
- **Upload Bukti (Evidence):** Guest diizinkan untuk mengunggah bukti langsung ke dalam laporan pelacakan tanpa perlu autentikasi tambahan.
- **Laporan Saksi (Witness):** Pengguna awam di sekitar kejadian dapat mengirimkan laporan saksi secara anonim.

### B. User Dengan Login
Pengguna yang sudah masuk akan difilter menggunakan *middleware* wajib mengisi nomor telepon (`phone.required`) untuk memastikan keandalan data.
- **Dashboard & Pemetaan:** Memiliki akses ke dashboard lengkap dengan penanda (marker) darurat di sekitarnya.
- **Manajemen Kontak Darurat (Trusted Contacts):** Pengguna dapat menambah dan memverifikasi orang terdekat. Saat tombol darurat ditekan, sistem akan otomatis mengirim notifikasi WhatsApp kepada mereka via Fonnte API.
- **Laporan Terstruktur (Past Reports):** Pengguna bisa membuat laporan rinci setelah kejadian berlalu, yang mencakup penyimpanan sementara untuk bukti (*evidence locker*).
- **Pencarian Partner (Mitra):** Pengguna bisa mencari mitra terdekat seperti polisi, rumah sakit, psikolog, atau pengacara berbasis lokasi GPS.
- **Simulasi Pembayaran & Konsultasi:** Tersedia alur pembayaran layanan (*mock payment*) dan chat interaktif dengan mitra yang bersangkutan.

---

## 2. Alur Mitra (Partner POV)
Mitra (Partner) memiliki peran krusial dalam merespons panggilan darurat. Akses mitra dibatasi oleh *middleware* khusus.
- **Dashboard Mitra:** Mitra melihat laporan yang dialokasikan (routed) khusus untuk institusinya berdasarkan jarak dan kategori darurat.
- **Penerimaan & Status:** Mitra dapat menyetujui (`accept`) laporan darurat dan secara aktif memperbarui status penanganannya (misal: "Menuju Lokasi", "Selesai").
- **Komunikasi:** Mitra dapat melihat lokasi langsung pelapor dan saling berkirim pesan melalui fitur chat untuk koordinasi evakuasi dan tindakan lanjutan.

---

## 3. Fitur Utama & Keunggulan
- **Routing Darurat Geospasial:** Pencarian dan pengalokasian mitra dilakukan menggunakan algoritma jarak (`DistanceKm`) memastikan mitra terdekat dihubungi lebih dulu untuk penanganan paling cepat.
- **Integrasi WhatsApp Otomatis:** Pemberitahuan keadaan darurat secara instan via bot WhatsApp (Fonnte) ke keluarga dan instansi keamanan tanpa intervensi manual.
- **Proteksi Idempotensi:** Mencegah terjadinya duplikasi pengiriman laporan (*double submit*) terutama ketika pelapor panik atau berada di area fakir sinyal (menggunakan header `Idempotency-Key`).
- **Akses Darurat Inklusif (Guest Mode):** Memprioritaskan keselamatan di atas proses registrasi; pengguna dapat berteriak meminta tolong (panic button) dan sistem tetap bekerja memanggil bantuan.
- **Evidence Locker:** Bukti fisik (foto/video) diamankan dan terikat dengan ID Laporan demi menjaga integritas data secara hukum di masa depan.

---

## 4. Daftar File yang Tidak Diperlukan (Telah Dihapus)
Setelah dilakukan analisis *codebase*, beberapa file yang usang, kosong, atau di luar standar produksi telah dihapus secara permanen untuk efisiensi sistem:

1. `app/Http/Controllers/DashboardLokasiController.php`
   - **Alasan:** Controller sepenuhnya kosong (tidak memiliki *method*) dan sama sekali tidak direferensikan dalam `routes/web.php` maupun struktur API lainnya.

2. `app/Http/Controllers/PastReportEvidenceController.php`
   - **Alasan:** Controller kosong. Proses unggah *evidence* sementara telah disatukan dan ditangani oleh fungsi di dalam `PastReportController`.

3. `app/Http/Controllers/EmergencyController.php.patch_note`
   - **Alasan:** File *backup/patch note* sisa dari proses pengembangan masa lalu yang tidak lagi relevan berada di direktori aplikasi produksi.

4. `database/migrations/2026_05_14_144001_add_user_locations_fake_fix.php`
   - **Alasan:** File migrasi (*migration*) yang 100% kosong (tidak memiliki baris kode pada *method* `up` maupun `down`). Hanya menjadi "sampah" di dalam riwayat migrasi.

5. `check_migrations.php`
   - **Alasan:** *Script* pengecekan database mandiri di dalam direktori *root*. Pengecekan seperti ini seharusnya menggunakan *command artisan* bawaan (`php artisan migrate:status`), sehingga keberadaan file ini berisiko dan mengotori root folder.