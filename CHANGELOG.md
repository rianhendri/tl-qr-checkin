# Changelog

Semua perubahan penting TL QR Check-in dicatat di file ini. Versi mengikuti Semantic Versioning.

## 1.3.0 - 2026-08-30

### Added

- Menambahkan kontrol Style tombol QR untuk ukuran tombol/icon, border radius, serta warna icon, background, dan border pada state Normal dan Hover.

### Fixed

- Mengunci style hover/focus tombol QR agar CSS tombol global dari tema tidak mengubah icon tanpa menyesuaikan background.
- Memulihkan penguncian transform backdrop dan fallback transform foto untuk mencegah konflik CSS tema.

## 1.2.0 - 2026-08-30

### Added

- Menambahkan kontrol Display Size foto (`Cover`, `Contain`, dan `Auto`).
- Menambahkan kontrol warna dan Typography Elementor untuk teks hero, scan, detail tamu, dan Powered By.

### Changed

- Mengganti kontrol Zoom Foto dengan Position dan Display Size yang mengikuti pola background Elementor.
- Menyamakan pengaturan gambar dan style teks antara preview kartu dengan hasil PNG.
- Mempertahankan nilai Zoom Foto yang sudah tersimpan sebagai kompatibilitas layout widget lama tanpa menampilkannya pada UI baru.

## 1.1.2 - 2026-08-30

### Fixed

- Menyatukan hero, area QR/detail, dan footer pada hasil PNG agar tidak tampil sebagai tiga kartu terpisah.
- Menambahkan divider kolom dan footer pada Canvas agar layout download mengikuti preview widget.
- Mengunci warna backdrop popup agar style hover/focus tombol dari tema tidak mengubahnya menjadi warna lain.

## 1.1.1 - 2026-08-30

### Fixed

- Memperkuat mode cover foto agar tidak ditimpa CSS gambar global Elementor.
- Menambahkan zoom crop dan menyamakan posisi serta zoom foto pada preview dan PNG.

## 1.1.0 - 2026-08-30

### Added

- Pembaruan plugin melalui GitHub Releases dan panel WordPress.
- Deklarasi dependency Elementor pada header plugin.
- Kontrol posisi foto mempelai dengan sembilan titik fokus dan default `center center`.

### Changed

- Posisi foto pilihan Elementor sekarang diterapkan secara konsisten pada preview kartu dan ekspor PNG.

### Security

- Metadata update dibatasi ke repository publik yang dikonfigurasi, release stabil, tag Semantic Version, dan aset `tl-qr-checkin.zip`.
- Pemeriksaan update gagal tertutup saat respons, repository, versi, aset, atau koneksi tidak valid.

## 1.0.0 - 2026-08-30

- Rilis awal widget QR Check-in untuk Elementor.
