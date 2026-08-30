# TL QR Check-in 1.3.0

Lightweight Elementor widget untuk menampilkan QR Check-in tamu dari URL yang sedang dibuka.

## Prinsip arsitektur

- QR dibuat **client-side** di browser pengunjung.
- PNG 9:16 dibuat **client-side** memakai native Canvas API.
- Plugin tidak membuat custom table, option, transient, post, attachment, log, atau file QR di server.
- Tidak ada AJAX/REST endpoint dan tidak ada request ke QR API/CDN eksternal.
- Asset widget dideklarasikan melalui `get_script_depends()` / `get_style_depends()` sehingga Elementor hanya memuatnya saat widget digunakan.
- QR engine disimpan lokal di plugin dan tidak memiliki runtime dependency.

> Catatan: saat Anda menambahkan/mengubah widget lalu menekan **Update** di Elementor, Elementor sendiri tetap menyimpan konfigurasi halaman/widget ke post meta seperti widget Elementor lainnya. Plugin TL QR Check-in tidak menjalankan query INSERT/UPDATE/DELETE sendiri.

## Instalasi

1. Upload `tl-qr-checkin.zip` dari **Plugins > Add New > Upload Plugin**.
2. Activate plugin.
3. Edit template undangan dengan Elementor.
4. Cari kategori **TL Invitation** lalu drag widget **TL QR Check-in**.
5. Atur dynamic tag pada field undangan sesuai data website Anda.

### Instalasi pertama versi dengan updater

Versi `1.0.0` belum memiliki kode updater. Karena itu, upgrade pertama ke `1.1.0` harus dilakukan satu kali secara manual melalui **Plugins > Add New > Upload Plugin** dan mengganti versi lama saat diminta WordPress.

Setelah `1.1.0` terpasang, pembaruan stabil berikutnya dapat muncul di halaman **Dashboard > Updates** dan **Plugins** ketika tersedia sebagai GitHub Release dengan aset bernama tepat `tl-qr-checkin.zip`.

Updater:

- hanya memeriksa repository publik `rianhendri/tl-qr-checkin`;
- hanya menerima release yang sudah dipublikasikan, bukan draft atau prerelease;
- tidak memakai token GitHub dan tidak mengaktifkan auto-update secara paksa;
- gagal dengan aman tanpa mengganggu widget jika GitHub tidak tersedia;
- tidak berjalan atau membuat request jaringan dari frontend pengunjung.

## Dynamic content Elementor

Field berikut mendukung Elementor Dynamic Tags:

- Foto Mempelai
- The Wedding Of
- Nama Groom
- Nama Bride
- Subtitle (opsional)
- Tanggal
- Waktu (opsional)
- Venue
- Notes
- Logo Cincin / Logo Brand
- Powered By
- Nama Tamu Fallback

## Posisi foto mempelai

Foto mempelai memakai elemen gambar dengan perilaku visual setara background Elementor. Kontrol **Posisi Foto** menyediakan sembilan titik fokus dari kiri atas sampai kanan bawah, dengan default **Tengah (`center center`)**.

Kontrol **Display Size** menyediakan:

- `Cover` (default): memenuhi seluruh area hero dan memotong sisi foto bila perlu.
- `Contain`: menampilkan seluruh foto tanpa crop, dengan kemungkinan ruang kosong.
- `Auto`: memakai ukuran asli foto.

Posisi dan display size yang dipilih digunakan secara konsisten pada kartu di halaman dan pada hasil download PNG 1080 × 1920.

Nilai Zoom Foto dari widget versi lama tetap dipakai untuk menjaga layout setelah update, tetapi kontrol tersebut tidak lagi ditampilkan pada widget baru.

## Style teks kartu

Tab **Style** Elementor menyediakan kontrol warna dan Typography (termasuk font family/Custom Fonts, ukuran, weight, transform, style, line-height, dan spacing) untuk:

- The Wedding Of, nama pasangan, dan subtitle hero;
- judul serta petunjuk scan;
- label dan isi detail tamu;
- teks Powered By.

Font family, ukuran, weight, style, transform, dan warna yang didukung Canvas ikut diterapkan pada PNG. Browser memakai fallback font jika Custom Font belum berhasil dimuat saat download.

## Style tombol QR

Bagian **Style > Tombol QR** menyediakan pengaturan responsif untuk ukuran tombol, ukuran icon, border radius, serta state **Normal** dan **Hover**. Masing-masing state memiliki warna icon, background, dan warna border sendiri.

Selector tombol mengunci warna icon dan background pada hover/focus agar style tombol global dari tema atau Elementor tidak membuat icon putih di atas background putih. Default hover memakai background accent dengan icon putih.

## Parameter URL tamu

Default:

- `to` = nama tamu
- `guest` = jumlah pax
- `tag` = badge tamu, misalnya VIP / VVIP

Contoh:

```text
https://domain.com/rian-yuli/?to=Budi%20Santoso&guest=2&tag=VVIP&checkin=A8F39K2
```

Hasil:

- Nama: Budi Santoso
- Pax: 2 Pax
- Badge: VVIP
- QR: berisi URL lengkap di atas, termasuk `checkin=A8F39K2`

Jika `tag` tidak ada atau kosong, badge otomatis disembunyikan.

Plugin tidak perlu mengenali parameter `checkin`; parameter apa pun yang ada di URL otomatis ikut masuk ke isi QR karena QR menggunakan current full URL.

## Download PNG

Tombol **Download QR** membuat PNG **1080 × 1920 (9:16)** langsung di browser.

Hasil PNG memakai satu kartu utuh: hero, area QR/detail, dan footer saling menyambung dengan garis pemisah seperti preview widget.

Tidak ada file yang di-upload ke Media Library atau disimpan ke server.

### Foto/logo dari CDN

Untuk memasukkan foto/logo dari domain lain ke PNG, CDN harus mengizinkan CORS untuk browser. Jika CORS tidak diizinkan, kartu di halaman tetap dapat menampilkan gambar seperti biasa, tetapi exporter akan melewati gambar yang tidak dapat dimasukkan ke Canvas dan tetap membuat PNG QR yang aman.

## Runtime files

Plugin memiliki 7 file runtime utama:

1. `tl-qr-checkin.php`
2. `includes/class-tl-qr-checkin-updater.php`
3. `includes/class-tl-qr-checkin-widget.php`
4. `templates/qr-checkin.php`
5. `assets/css/tl-qr-checkin.css`
6. `assets/js/tl-qr-checkin.js`
7. `assets/vendor/qrcode/qrcode.browser.js`

File lain hanya dokumentasi/lisensi.
