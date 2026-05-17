# TODO

- [ ] Get repo context: locate all page heading styles used for user pages (dashboard tiles + pages: evidence, emergency (arsip), witness (mode saksi), trusted-contacts (kontak darurat/terpercaya), etc.).
- [ ] Identify common mismatch: some pages use `font-black` / different font family (`Inter`, `Space Grotesk`, `Unbounded`) or different font-weight.
- [ ] Create consistent heading style classes (font family = same as dashboard, weight not too bold).
- [x] Update headings for targeted pages:

  - [ ] Kontak Terpercaya / Kontak Darurat page headings (h1/h2).
  - [ ] Bukti tersimpan aman (evidence) heading.
  - [ ] Arsip Laporan / Status & Riwayat (emergency) headings.
  - [ ] Mode Saksi (witness) heading.
  - [ ] Riwayat Laporan / Partner Terdekat tiles in dashboard (h2).
- [ ] Replace overly bold classes on headings (`font-black`, `font-bold`, etc.) with lighter ones (`font-semibold` or `font-medium`) while preserving sizes.
- [ ] Run quick grep/search after edits to confirm no remaining mismatched classes for the specified headings.
- [ ] Run `npm run build` or `php artisan` lint/build if available to ensure no syntax errors.
