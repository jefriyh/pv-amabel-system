# Buku Tamu Komplek

Sistem pencatatan tamu dan pengantar paket untuk komplek perumahan.

- **Tamu** mengisi form: nama, foto KTP, foto selfie, dan keperluan sebelum masuk.
- **Kurir** yang menitipkan paket di kotak paket mengisi form terpisah.
- Setiap pengisian langsung memunculkan notifikasi di **grup Telegram** pengurus/satpam.
- **Admin** melihat seluruh catatan di dashboard, lengkap dengan foto, filter tanggal, dan export CSV/Excel.

Dibangun dengan Laravel 13, MySQL 8.4, dan Filament 5. Semuanya berjalan di Docker.

---

## Isi

1. [Menjalankan pertama kali](#menjalankan-pertama-kali)
2. [Setup Telegram](#setup-telegram)
3. [Alur pemakaian sehari-hari](#alur-pemakaian-sehari-hari)
4. [Perintah yang tersedia](#perintah-yang-tersedia)
5. [Deploy ke server](#deploy-ke-server)
6. [Privasi data KTP](#privasi-data-ktp)
7. [Catatan teknis](#catatan-teknis)
8. [Struktur proyek](#struktur-proyek)

---

## Menjalankan pertama kali

Yang perlu terpasang di komputer/server hanya **Docker** dan **Docker Compose**.

```bash
cp .env.example .env
make setup
make admin
```

`make setup` akan build image, memasang dependensi PHP & JS, menjalankan migrasi, dan
membangun aset frontend. `make admin` membuat akun untuk masuk dashboard.

Di **Windows** perintah `make` tidak tersedia. Jalankan langkahnya satu per satu:

```bash
docker compose build
```

```bash
docker compose run --rm app sh -c "composer install"
```

```bash
docker compose up -d
```

```bash
docker compose exec app php artisan key:generate
```

```bash
docker compose exec app php artisan migrate --force
```

```bash
docker compose run --rm node npm install
```

```bash
docker compose run --rm node npm run build
```

```bash
docker compose exec app php artisan make:filament-user
```

Setelah itu buka:

| Alamat | Isi |
|---|---|
| http://localhost:8080 | Halaman pilihan untuk tamu/kurir |
| http://localhost:8080/tamu | Form tamu |
| http://localhost:8080/paket | Form antar paket |
| http://localhost:8080/admin | Dashboard admin |

> **Penting soal kamera.** Browser hanya mengizinkan halaman membuka kamera pada
> `localhost` atau alamat **https**. Selama masih diakses lewat IP lokal seperti
> `http://192.168.1.10:8080`, tombol foto di HP tamu tidak akan membuka kamera.
> Lihat [Deploy ke server](#deploy-ke-server).

---

## Setup Telegram

Bagian ini yang perlu **Anda siapkan sendiri** di aplikasi Telegram. Hasilnya berupa dua
nilai yang dimasukkan ke `.env`: token bot dan id grup.

### 1. Buat bot dan ambil token

1. Buka Telegram, cari **@BotFather**, lalu tekan **Start**.
2. Kirim perintah `/newbot`.
3. BotFather menanyakan **nama bot** (bebas, misalnya `Buku Tamu Amabel`).
4. Lalu menanyakan **username bot**, wajib diakhiri kata `bot` dan harus unik,
   misalnya `amabel_guestbook_bot`.
5. BotFather membalas dengan token seperti:

   ```
   8123456789:AAHk3lQ-contoh-token-jangan-dipakai
   ```

   Token inilah nilai `TELEGRAM_BOT_TOKEN`.

> Token ini setara password bot. Jangan dibagikan dan jangan di-commit ke git
> (`.env` sudah masuk `.gitignore`). Kalau terlanjur bocor, kirim `/revoke` ke BotFather
> untuk membuat token baru.

### 2. Masukkan bot ke grup

1. Buat grup Telegram berisi satpam dan pengurus, misalnya "Security Komplek".
2. Buka **info grup - Add members**, cari username bot Anda, lalu tambahkan.
3. Jadikan bot **admin grup** (info grup - Administrators - Add admin).
   Bot tidak perlu izin tambahan apa pun; yang penting statusnya admin, karena ini cara
   paling aman agar bot pasti bisa mengirim pesan di grup supergroup maupun grup yang
   memakai Topics.

### 3. Ambil chat_id grup

1. Kirim satu pesan apa pun di grup tersebut, misalnya `/start@amabel_guestbook_bot`.
2. Buka alamat berikut di browser (ganti `<TOKEN>` dengan token Anda):

   ```
   https://api.telegram.org/bot<TOKEN>/getUpdates
   ```

3. Cari bagian seperti ini pada hasilnya:

   ```json
   "chat": { "id": -1001234567890, "title": "Security Komplek", "type": "supergroup" }
   ```

4. Angka `-1001234567890` itulah `TELEGRAM_CHAT_ID`. **Tanda minus ikut disalin.**

**Kalau hasil `getUpdates` kosong** (`{"ok":true,"result":[]}`), coba dua hal mudah ini dulu:

- Pastikan pesan yang Anda kirim di grup **menyebut bot secara langsung**, misalnya
  `/start@amabel_guestbook_bot`. Pesan berawalan `/` yang menyebut username bot selalu
  diterima bot.
- Pastikan bot memang sudah **admin grup** (langkah 2). Bot berstatus admin menerima
  semua pesan grup.

Kalau masih kosong juga, matikan privacy mode bot:

1. Kirim `/setprivacy` ke **@BotFather**.
2. BotFather membalas *"Choose a bot to change group messages settings"* dan menampilkan
   **daftar bot Anda sebagai tombol**. Tap bot yang tadi dibuat, misalnya
   `@amabel_guestbook_bot`.
3. BotFather menampilkan penjelasan dan **dua tombol: `Enable` dan `Disable`**.
   Tap **`Disable`**.
4. BotFather membalas *"Success! The new status is: DISABLED"*.
5. **Keluarkan bot dari grup, lalu masukkan dan jadikan admin lagi.** Perubahan privacy
   mode tidak berlaku untuk grup yang sudah dimasuki bot sebelumnya.
6. Kirim pesan lagi di grup, lalu buka `getUpdates` sekali lagi.

> Nama tombolnya terasa terbalik: `Disable` berarti **mematikan privacy mode**, sehingga
> bot justru **bisa** membaca pesan grup. Itulah yang kita inginkan di sini.

### 4. Kalau grup memakai Topics (opsional)

Grup dengan fitur Topics punya beberapa "kanal" di dalamnya. Kirim pesan di topik yang
ingin dipakai, lalu di hasil `getUpdates` cari `"message_thread_id": 42`. Isi angkanya ke
`TELEGRAM_THREAD_ID`. Kalau grupnya biasa, biarkan kosong.

### 5. Masukkan ke `.env` dan uji

```dotenv
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=8123456789:AAHk3lQ-contoh-token-jangan-dipakai
TELEGRAM_CHAT_ID=-1001234567890
TELEGRAM_THREAD_ID=
```

Lalu:

```bash
docker compose restart app
```

```bash
docker compose exec app php artisan telegram:test
```

(dengan `make`: `make telegram-test`)

Kalau berhasil, sebuah pesan percobaan muncul di grup. Kalau gagal, perintah tersebut
menyebutkan penyebabnya: token ditolak, chat_id salah, atau bot belum ada di grup.

### Contoh notifikasi yang akan muncul

```
TAMU MASUK - Komplek Amabel

Nama: Budi Santoso
No. HP: 081234567890
Menemui: Pak Andi, Blok C2 No. 5
Keperluan: Silaturahmi keluarga
Waktu: 26 Agu 2026, 09:14 WIB

Foto KTP & selfie: buka di dashboard
```

```
PAKET MASUK KE KOTAK - Komplek Amabel

Kurir: Rizal Pratama (J&T Express)
Untuk: Ibu Sari, Blok B1 No. 12
Resi: JT1234567890
Waktu: 26 Agu 2026, 15:10 WIB

Detail & foto: buka di dashboard
```

**Foto KTP dan selfie sengaja tidak ikut dikirim ke grup.** Grup hanya menerima ringkasan
dan tautan; fotonya hanya bisa dibuka pengurus yang login ke dashboard. Ini menghindari
foto KTP warga tersebar di riwayat chat yang bisa di-forward siapa saja.

### Kalau server tidak bisa menghubungi Telegram

Sebagian jaringan memblokir `api.telegram.org`. Kalau `telegram:test` gagal dengan
error koneksi, tambahkan proxy keluar di `.env`:

```dotenv
HTTPS_PROXY=http://user:pass@proxy-anda:8080
```

---

## Alur pemakaian sehari-hari

**Untuk tamu / kurir**

1. Scan QR di gerbang, atau satpam membukakan halamannya di tablet.
2. Pilih "Saya Tamu" atau "Saya Antar Paket".
3. Isi data, ambil foto (kamera HP terbuka langsung), tekan kirim.
4. Notifikasi langsung masuk ke grup Telegram.
5. Layar tamu berganti menjadi **kartu bukti** berisi identitas dan kedua fotonya,
   dengan instruksi besar: *tunjukkan ini ke security, atau tunggu hingga dihampiri
   pemilik rumah*. Foto bisa diketuk untuk diperbesar agar satpam dapat mencocokkan
   wajah dengan KTP.

Kartu bukti itu hanya bisa dibuka dari HP yang mengisi formulir, dan hilang saat sesi
browsernya berakhir. Tamu berikutnya tidak bisa melihat kartu tamu sebelumnya.

Buat QR untuk dicetak:

```bash
make qr
```

File-nya tersimpan di `storage/app/private/qr/guestbook.svg`. Cetak dan tempel di gerbang
beserta tulisan "Scan untuk isi buku tamu".

**Untuk pengurus**

1. Login di `/admin`.
2. Menu **Tamu** dan **Paket** menampilkan seluruh catatan, terbaru di atas.
   Angka di sebelah menu adalah jumlah hari ini.
3. Gunakan filter **Periode** untuk memilih rentang tanggal, lalu tombol
   **Export CSV / Excel** untuk mengunduh rekapnya. Export mengikuti filter yang sedang aktif.
4. Klik **Detail** untuk melihat foto KTP dan selfie ukuran penuh.

Catatan buku tamu **tidak bisa dibuat atau disunting** dari dashboard, hanya bisa dibaca
dan dihapus. Ini disengaja: catatan hanya sah kalau berasal dari form di gerbang.

---

## Perintah yang tersedia

**Windows tidak punya `make`.** Kolom kanan di bawah bisa dipakai di mana saja
(PowerShell, CMD, maupun Linux/macOS), jadi pakai itu kalau `make` tidak dikenali.

| Kegunaan | Dengan `make` | Tanpa `make` |
|---|---|---|
| Jalankan semua container | `make up` | `docker compose up -d` |
| Hentikan semua container | `make down` | `docker compose down` |
| Ikuti log aplikasi & queue | `make logs` | `docker compose logs -f app queue` |
| Buat akun admin baru | `make admin` | `docker compose exec app php artisan make:filament-user` |
| Jalankan seluruh test | `make test` | `docker compose run --rm app php artisan test` |
| Build ulang CSS/JS | `make build` | `docker compose run --rm node npm run build` |
| Buat QR code gerbang | `make qr` | `docker compose exec app php artisan guestbook:qr` |
| Kirim pesan uji ke grup | `make telegram-test` | `docker compose exec app php artisan telegram:test` |
| Hapus foto lewat retensi | `make purge` | `docker compose exec app php artisan guestbook:purge-photos` |
| Masuk shell container | `make shell` | `docker compose exec app bash` |
| Jalankan migrasi | `make migrate` | `docker compose exec app php artisan migrate --force` |

Semua perintah dijalankan dari dalam folder proyek ini.

---

## Deploy ke server

Aplikasi ini mendengarkan HTTP di port `8080` dan dirancang berdiri di belakang reverse
proxy yang menangani SSL (Nginx Proxy Manager, Traefik, Caddy, atau Cloudflare Tunnel).

1. Arahkan subdomain, misalnya `guestbook.amabel.web.id`, ke server.
2. Terbitkan sertifikat SSL di reverse proxy, arahkan ke `http://127.0.0.1:8080`.
3. Pastikan proxy meneruskan header `X-Forwarded-For` dan `X-Forwarded-Proto`.
   Laravel sudah dikonfigurasi mempercayainya di `bootstrap/app.php`.
4. Sesuaikan `.env`:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://guestbook.amabel.web.id
   ```

5. Jalankan:

   ```bash
   docker compose exec app php artisan config:cache
   ```

   ```bash
   docker compose exec app php artisan route:cache
   ```

   ```bash
   make qr
   ```

`APP_URL` harus **https**. Selain menentukan tautan di notifikasi Telegram, alamat inilah
yang masuk ke QR code, dan kamera HP tamu hanya aktif pada halaman https.

Ganti juga password database di `.env` (`DB_PASSWORD` dan `DB_ROOT_PASSWORD`) sebelum
dipakai sungguhan.

---

## Privasi data KTP

Foto KTP adalah data pribadi. Yang sudah dibangun untuk menjaganya:

- Foto disimpan di `storage/app/private/`, **di luar** folder yang bisa diakses web.
  Jalan keluarnya hanya dua, keduanya berpagar: route `/admin/media/...` yang mewajibkan
  login, dan route `/selesai/foto/...` yang hanya melayani foto milik session yang
  sedang membukanya, sehingga tamu tidak bisa mengintip foto tamu lain.
- Metadata EXIF, termasuk koordinat GPS dari kamera HP, dibuang saat foto diproses ulang.
- Foto **tidak pernah dikirim ke grup Telegram**, hanya tautannya.
- Export CSV/Excel berisi tautan, bukan file foto.
- Foto dihapus otomatis setelah masa retensi, sementara baris rekapnya tetap tersimpan.
  Atur lamanya di `.env`:

  ```dotenv
  GUESTBOOK_PHOTO_RETENTION_DAYS=90
  ```

  Penghapusan dijalankan otomatis tiap hari pukul 02:15 oleh container `scheduler`.
  Untuk melihat apa yang akan dihapus tanpa menghapusnya:

  ```bash
  docker compose exec app php artisan guestbook:purge-photos --dry-run
  ```

---

## Catatan teknis

### Foto selalu dikompres di server

Tidak ada berkas unggahan yang disimpan apa adanya. Semuanya melewati
`App\Services\PhotoStorageService` dan selalu mengalami hal yang sama:

| Langkah | Hasil |
|---|---|
| Diperkecil | sisi terpanjang maksimal 1600 px |
| Di-encode ulang | JPEG progresif, kualitas 80 |
| Metadata dibuang | EXIF, termasuk koordinat GPS |

Foto kamera HP 3–8 MB biasanya menyusut menjadi sekitar 25–60 KB. Browser memang sudah
memperkecil foto sebelum mengunggah, tapi itu semata optimasi kecepatan dan bisa saja
tidak berjalan (JavaScript mati, browser lawas, atau kiriman dibuat manual) — jadi
server tidak menggantungkan ukuran penyimpanannya pada itikad baik klien.

Batasnya bisa diubah di `config/guestbook.php`, dan jaminannya dikunci oleh
`tests/Feature/PhotoCompressionTest.php`.

### Notifikasi Telegram dikirim langsung

Pesan dikirim saat itu juga ketika tamu menekan kirim, bukan lewat antrean, supaya
satpam menerimanya pada detik yang sama. Konsekuensinya tamu ikut menunggu jawaban
Telegram — sekitar 0,6 detik pada jaringan sehat, dari total submit ±1,5 detik.

Kegagalan Telegram **tidak pernah menggagalkan submit**: data tamu sudah tersimpan, tamu
tetap melihat kartu buktinya, dan errornya masuk ke `storage/logs/laravel.log`. Karena
tidak ada antrean, notifikasi yang gagal **tidak dicoba ulang belakangan** — hanya ada
satu percobaan ulang seketika. Kalau grup Telegram sepi padahal ada tamu masuk, periksa
log dan cocokkan dengan daftar di dashboard.

Container `queue` tetap dijalankan karena fitur export CSV/Excel di dashboard memakainya.

### vendor/ berada di named volume

`vendor/` berisi lebih dari 23 ribu berkas. Lewat bind mount Docker Desktop di Windows
dan macOS, sekadar memeriksa seluruhnya butuh berdetik-detik, dan opcache melakukannya
berkala sehingga hampir setiap request jadi lambat — terukur 5,5 detik per halaman.
Karena itu `vendor/` dipasang sebagai named volume (`vendor-data`), dibaca dari
filesystem container yang cepat. Halaman yang sama kini terbuka dalam 0,2 detik.

Konsekuensinya, dependensi PHP dipasang **lewat container**, bukan dari host:

```bash
docker compose run --rm app sh -c "composer require nama/paket"
```

Folder `vendor/` di host tidak ikut terisi. Kalau editor Anda membutuhkannya untuk
autocomplete, jalankan `composer install` sekali di host — salinan itu hanya dipakai
editor dan tidak memengaruhi container.

---

## Struktur proyek

```
app/
  Console/Commands/     telegram:test, guestbook:qr, guestbook:purge-photos
  Filament/             dashboard admin (resource Tamu & Paket, widget, exporter)
  Http/Controllers/     form publik + route foto ber-auth
  Models/               Visitor, PackageDelivery
  Services/             PhotoStorageService, TelegramNotifier
  Support/              penyusun teks pesan Telegram, pemetaan tipe entri
config/
  guestbook.php         retensi foto, ukuran foto, daftar ekspedisi, nama komplek
  telegram.php          token, chat_id, thread_id
docker/                 Dockerfile PHP, konfigurasi nginx & php.ini
resources/views/        halaman tamu, paket, dan komponennya
tests/Feature/          test yang mencakup seluruh alur di atas
```

Jalankan seluruh test dengan `make test`.
