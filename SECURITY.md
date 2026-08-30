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

## Client-side security

- Query parameter dimasukkan ke DOM menggunakan `textContent`, bukan HTML injection.
- Nilai Elementor di-escape dengan API WordPress (`esc_html`, `esc_url`, `esc_attr`).
- QR berisi full current URL tanpa fragment/hash.
- QR dibuat setelah user membuka popup sehingga pekerjaan CPU tidak terjadi pada initial page load.
- PNG dibuat dengan native Canvas API; tidak memakai `html2canvas` atau library screenshot tambahan.
- Tidak ada `eval`, `new Function`, remote executable script, analytics, tracking, atau external QR API.

## QR engine

`assets/vendor/qrcode/qrcode.browser.js` berisi browser bundle lokal dari implementasi QRCode Kazuhiko Arase (MIT), yang juga divendor oleh paket `qrcode-terminal` 0.12.0. Bundle hanya berisi algoritma QR; bagian terminal/CLI tidak disertakan.

Pada tanggal build, Snyk menampilkan `qrcode-terminal` 0.12.0 dengan **no known direct vulnerabilities**. Kode QR runtime yang dibundle tidak memiliki dependency eksternal.

SHA-256 bundle QR dicatat pada `CHECKSUMS.txt`.

## Important limitation

Tidak ada proses audit yang dapat menjamin secara absolut bahwa software bebas dari semua bug atau future vulnerability. Build ini sengaja meminimalkan supply-chain surface: satu engine QR lokal, CSS custom, JavaScript custom, dan tanpa network dependency runtime.
