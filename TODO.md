# TODO

## Seeder reset & rebuild
- [ ] Update `database/seeders/DatabaseSeeder.php` supaya hanya seed akun admin dan user (tanpa seed partner demo)
- [ ] Update `database/seeders/PartnerSeeder.php`:
  - [x] DELETE semua isi tabel `users`, `partners`, `price_lists` (sesuai permintaan)
  - [x] Seed ulang `partners` untuk tipe: legal, counselor, ambulance, pemadam dengan prefixes yang diminta
  - [x] Seed ulang `users`:
    - [x] role `admin`
    - [x] role `user`
    - [x] role `partner` untuk setiap partner (agar `partner_id` valid)
    - [x] `user_locations` untuk user biasa (korban/saksi)
  - [x] Seed ulang `price_lists` hanya untuk `partner_type` legal dan counselor
- [ ] Jalankan `php artisan db:seed` untuk verifikasi jumlah data


