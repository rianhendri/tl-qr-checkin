# Security Notes

Build date: 2026-08-30

## Read-only runtime policy

Kode plugin tidak menggunakan operasi database write WordPress seperti:

- `$wpdb->insert`, `$wpdb->update`, `$wpdb->delete`, `$wpdb->query`
- `add_option`, `update_option`, `delete_option`
- `set_transient`, `delete_transient`
- `wp_insert_post`, `wp_update_post`, `wp_delete_post`
- attachment/media creation
- custom database table creation

Plugin juga tidak membuat AJAX/REST write endpoint, cron job, server-side QR image, atau log file.

WordPress core dapat menyimpan cache hasil pemeriksaan update di site transient seperti plugin lain. TL QR Check-in tidak membuat option atau transient khusus miliknya sendiri.

## Client-side security

- Query parameter dimasukkan ke DOM menggunakan `textContent`, bukan HTML injection.
- Nilai Elementor di-escape dengan API WordPress (`esc_html`, `esc_url`, `esc_attr`).
- QR berisi full current URL tanpa fragment/hash.
- QR dibuat setelah user membuka popup sehingga pekerjaan CPU tidak terjadi pada initial page load.
- PNG dibuat dengan native Canvas API; tidak memakai `html2canvas` atau library screenshot tambahan.
- Tidak ada `eval`, `new Function`, remote executable script, analytics, tracking, atau external QR API.

## GitHub updater

- Pemeriksaan update hanya menggunakan WordPress HTTP API menuju endpoint HTTPS tetap `api.github.com/repos/rianhendri/tl-qr-checkin/releases/latest`.
- Request menggunakan timeout 5 detik, maksimal 2 redirect, verifikasi TLS, batas respons 1 MiB, dan User-Agent plugin yang tidak berisi domain website.
- Updater tidak mengirim URL undangan, nama tamu, parameter check-in, domain website, telemetry, atau token GitHub.
- Hanya release stabil yang sudah dipublikasikan dengan tag Semantic Version dan aset tepat `tl-qr-checkin.zip` dari repository yang dikonfigurasi yang dapat ditawarkan.
- Draft, prerelease, respons rusak, versi lama/sama, URL repository lain, aset hilang, HTTP error, dan timeout diabaikan secara fail-closed.
- GitHub hanya digunakan untuk pemeriksaan update administratif WordPress. Frontend, QR, dan PNG tetap bekerja tanpa GitHub dan tidak membuat request updater.

## QR engine

`assets/vendor/qrcode/qrcode.browser.js` berisi browser bundle lokal dari implementasi QRCode Kazuhiko Arase (MIT), yang juga divendor oleh paket `qrcode-terminal` 0.12.0. Bundle hanya berisi algoritma QR; bagian terminal/CLI tidak disertakan.

Pada tanggal build, Snyk menampilkan `qrcode-terminal` 0.12.0 dengan **no known direct vulnerabilities**. Kode QR runtime yang dibundle tidak memiliki dependency eksternal.

SHA-256 bundle QR dicatat pada `CHECKSUMS.txt`.

## Important limitation

Tidak ada proses audit yang dapat menjamin secara absolut bahwa software bebas dari semua bug atau future vulnerability. Build ini sengaja meminimalkan supply-chain surface: satu engine QR lokal, CSS custom, JavaScript custom, tanpa dependency jaringan frontend, serta satu pemeriksaan administratif terbatas ke GitHub untuk update.
