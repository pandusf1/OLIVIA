# TODO - Fitur Maps Emergency (User Terdekat)

## Backend
- [x] Tambah tabel `report_user_routing` untuk menyimpan target penerima WA (nearest user) per report.


- [x] Update `EmergencyController@store()`:


  - [x] Tetap kirim ke trusted contacts milik user korban (existing).
  - [x] Cari `target_user_id` terdekat berdasarkan `user_locations` dari semua user dengan `role='user'` (kecuali user korban).
  - [x] Kirim WA ke `users.phone` milik target (menggunakan `FonnteService`).
  - [x] Simpan record ke `report_user_routing`.
- [x] Buat endpoint JSON untuk dashboard user terdekat:

  - [x] `GET /dashboard/emergency-markers` (mengembalikan report active yang dirouted ke user login).

- [x] Pastikan query hanya mengembalikan report dengan status aktif (Submitted/Routed/Viewed/In Progress) dan ada latitude/longitude.


## Frontend
- [x] Update `dashboard.blade.php`:
  - [x] Tambahkan loader marker untuk laporan emergency di canvas peta semu.
  - [x] Marker warna berbeda berdasarkan status report.
  - [x] Klik marker redirect ke `/tracking/{report_id}`.
- [x] Update JS agar marker refresh ketika user menekan reload lokasi (setelah `/user-location/reload`).


## Testing
- [ ] Test scenario:
  - [ ] User A kirim emergency.
  - [ ] User B (nearest) menerima WA.
  - [ ] Dashboard user B menampilkan marker emergency.
  - [ ] Klik marker membuka page tracking.

