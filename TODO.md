# TODO

- [ ] Update navbar partial `resources/views/partials/nav-auth.blade.php`:
  - [ ] Tampilkan tombol “Kembali” saat referer/page sebelumnya ada (untuk dashboard user/admin/partner)
  - [ ] Hilangkan logo & teks “SuraRa” saat berada di dashboard user/admin/partner (bukan dashboard awal)
  - [ ] Tetap tampilkan logo & teks “SuraRa” di dashboard awal `/dashboard`
- [ ] Update view dashboard-role yang relevan (user/admin/partner):
  - [ ] `resources/views/pages/user/data_partner.blade.php`
  - [ ] Cari dan update view dashboard user/admin/partner lain yang belum ter-cover.
- [ ] Test manual:
  - [ ] Login -> buka `/dashboard` -> pastikan logo+teks tetap ada & tidak ada tombol kembali
  - [ ] Dari dalam dashboard -> buka halaman dashboard user/admin/partner -> pastikan ada tombol kembali dan kembali ke page sebelumnya

