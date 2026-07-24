# Setup Login Google & GitHub (Laravel Socialite)

Panduan ini menjelaskan cara membuat kredensial OAuth asli di Google Cloud
Console dan GitHub, lalu mengisinya ke `.env`, sampai tombol "Masuk dengan
Google" / "Masuk dengan GitHub" di halaman Login & Register benar-benar
berfungsi.

Kode aplikasi (Socialite, controller, service, route, exception handling)
sudah lengkap dan tidak perlu diubah - satu-satunya yang kurang adalah
kredensial asli di bawah ini, yang hanya bisa dibuat oleh pemilik akun
Google/GitHub sendiri (tidak bisa dibuat otomatis).

## Cara kerjanya di kode (referensi singkat)

- `config/services.php` membaca `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`,
  `GOOGLE_REDIRECT_URI` (dan padanan `GITHUB_*`) dari `.env`.
- `App\Http\Controllers\Auth\SocialiteController` (route `social.redirect` /
  `social.callback`, lihat `routes/auth.php`) menangani redirect ke provider
  dan callback-nya - dipakai SAMA PERSIS oleh tombol Google/GitHub di
  halaman Login maupun Register.
- `App\Services\Auth\SocialAuthService` mencocokkan akun berdasarkan
  `google_id`/`github_id`, lalu berdasarkan email (supaya satu email = satu
  akun, tidak ada duplikat), baru membuat akun baru kalau benar-benar belum
  ada.
- Selama `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` (atau padanan GitHub)
  masih kosong, klik tombol akan diam-diam mengembalikan pengguna ke halaman
  asal TANPA banner/error di UI - detail lengkap tetap ditulis ke
  `storage/logs/laravel.log` untuk developer (lihat
  `App\Exceptions\OAuthProviderNotConfiguredException`).

---

## 1. Tentukan Redirect URI dulu

Redirect URI HARUS sama persis (termasuk `http`/`https`, domain, port, dan
path) antara tiga tempat: `.env`, Google Cloud Console, dan GitHub OAuth App
Settings. Cek dulu `APP_URL` di `.env`:

```env
APP_URL=http://localhost
```

Maka Redirect URI yang dipakai adalah:

| Provider | Redirect URI |
|---|---|
| Google | `http://localhost/auth/google/callback` |
| GitHub | `http://localhost/auth/github/callback` |

Kalau kamu menjalankan Laravel lewat `php artisan serve` (biasanya di
`http://127.0.0.1:8000`), ganti `APP_URL` dan kedua Redirect URI di atas
menjadi `http://127.0.0.1:8000/...` - **ketiganya harus konsisten**, bukan
campur `localhost` di satu tempat dan `127.0.0.1` di tempat lain (browser
menganggap keduanya origin yang berbeda).

---

## 2. Membuat Google OAuth Client

1. Buka [Google Cloud Console](https://console.cloud.google.com/) dan login
   dengan akun Google kamu.
2. Buat project baru (atau pilih project yang sudah ada) lewat dropdown
   project di pojok kiri atas.
3. Buka menu **APIs & Services > OAuth consent screen**.
   - Pilih **User Type: External** (kecuali kamu punya Google Workspace dan
     hanya ingin internal), klik **Create**.
   - Isi **App name** (mis. "Ular Tangga Statistik"), **User support email**,
     dan **Developer contact information**, lalu **Save and Continue**
     sampai selesai (scope & test user boleh dilewati untuk mode testing).
4. Buka menu **APIs & Services > Credentials**.
5. Klik **Create Credentials > OAuth client ID**.
   - **Application type**: pilih **Web application**.
   - **Name**: bebas, mis. "Ular Tangga Statistik - Local".
   - Pada **Authorized redirect URIs**, klik **Add URI** dan masukkan PERSIS:
     ```
     http://localhost/auth/google/callback
     ```
     (sesuaikan host/port kalau `APP_URL` kamu berbeda, lihat langkah 1).
   - Klik **Create**.
6. Sebuah dialog akan menampilkan **Client ID** dan **Client secret** -
   salin keduanya, akan dipakai di langkah 4 (`.env`).

> Catatan: selama OAuth consent screen masih berstatus "Testing", hanya
> email yang didaftarkan sebagai **Test user** (menu OAuth consent screen)
> yang bisa login. Untuk mengizinkan akun Google apa pun, publish app lewat
> tombol **Publish App** di halaman yang sama (Google mungkin meminta
> verifikasi tambahan untuk scope sensitif, tapi scope dasar login/email/
> profile tidak butuh verifikasi manual).

---

## 3. Membuat GitHub OAuth App

1. Buka [GitHub Developer Settings > OAuth Apps](https://github.com/settings/developers)
   (login dulu ke akun GitHub kamu).
2. Klik **New OAuth App** (atau **Register a new application**).
3. Isi form:
   - **Application name**: bebas, mis. "Ular Tangga Statistik".
   - **Homepage URL**: `http://localhost` (samakan dengan `APP_URL`).
   - **Authorization callback URL**: PERSIS:
     ```
     http://localhost/auth/github/callback
     ```
     (sesuaikan host/port kalau `APP_URL` kamu berbeda).
4. Klik **Register application**.
5. Di halaman detail app yang muncul:
   - **Client ID** langsung terlihat - salin.
   - Klik **Generate a new client secret**, lalu salin **Client secret**
     yang muncul (hanya ditampilkan sekali, simpan sekarang).

GitHub OAuth App hanya punya SATU callback URL aktif per app (tidak seperti
Google yang bisa daftar banyak sekaligus) - kalau butuh URL berbeda untuk
local vs production, buat dua OAuth App terpisah (satu untuk tiap environment).

---

## 4. Isi `.env`

Buka file `.env` di root project, cari bagian `GOOGLE OAUTH` dan
`GITHUB OAUTH`, lalu isi dengan nilai dari langkah 2 & 3:

```env
GOOGLE_CLIENT_ID=<Client ID dari langkah 2>
GOOGLE_CLIENT_SECRET=<Client secret dari langkah 2>
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

GITHUB_CLIENT_ID=<Client ID dari langkah 3>
GITHUB_CLIENT_SECRET=<Client secret dari langkah 3>
GITHUB_REDIRECT_URI="${APP_URL}/auth/github/callback"
```

`GOOGLE_REDIRECT_URI`/`GITHUB_REDIRECT_URI` di atas sudah otomatis mengikuti
`APP_URL` lewat interpolasi `${APP_URL}` - biasanya TIDAK perlu diubah
manual, asal `APP_URL` sudah benar (lihat langkah 1).

## 5. Bersihkan cache config

Setiap kali `.env` berubah, Laravel perlu di-refresh supaya nilai baru
kebaca (config bisa saja sudah ter-cache dari sebelumnya):

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

## 6. Uji coba

1. Jalankan server (`php artisan serve` atau XAMPP/Apache, sesuai `APP_URL`).
2. Buka halaman **Login** atau **Register**.
3. Klik tombol **Google** - browser harus langsung diarahkan ke halaman
   `accounts.google.com` yang menampilkan daftar akun Google yang sedang
   login di browser tsb (atau form login kalau belum ada sesi Google sama
   sekali). Pilih akun, izinkan akses saat diminta, lalu kamu akan diarahkan
   kembali ke aplikasi dan otomatis login/ke Dashboard.
4. Ulangi hal yang sama untuk tombol **GitHub** - browser diarahkan ke
   halaman login/otorisasi GitHub.
5. Coba lagi dengan email yang SAMA lewat provider yang beda (mis. daftar
   manual dengan email X, lalu login Google pakai email X yang sama) -
   harus login ke akun yang sama, BUKAN membuat akun baru.

---

## Troubleshooting

| Gejala | Penyebab paling umum | Solusi |
|---|---|---|
| Klik tombol Google/GitHub, tidak terjadi apa-apa / balik ke halaman yang sama tanpa pesan | `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` (atau GitHub) masih kosong | Cek `storage/logs/laravel.log` - akan ada baris `Konfigurasi OAuth "..." belum lengkap...`. Isi `.env` sesuai langkah 4, lalu `php artisan config:clear`. |
| Google/GitHub menampilkan error `redirect_uri_mismatch` / "The redirect_uri MUST match..." | Redirect URI di `.env` tidak identik dengan yang didaftarkan di Google Cloud Console / GitHub OAuth App | Samakan persis (skema http/https, host, port, path) di ketiga tempat - lihat langkah 1. Setelah ubah `.env`, jalankan `php artisan config:clear`. |
| Error `invalid_client` dari Google/GitHub | Client ID/Secret salah salin, atau app OAuth di provider terhapus/nonaktif | Buat ulang Client ID/Secret (langkah 2/3), pastikan tidak ada spasi tersalin, isi ulang `.env`. |
| Sudah isi `.env` tapi masih error konfigurasi belum lengkap | Config lama masih ter-cache | `php artisan config:clear` (atau `optimize:clear`) - kalau pakai `config:cache` di production, jalankan ulang setelah `.env` berubah. |
| Login Google berhasil tapi field nama/email/avatar kosong di akun baru | Scope default Socialite (`openid profile email`) seharusnya sudah cukup - kalau tetap kosong, cek OAuth consent screen di Google Cloud Console sudah mengizinkan scope `email` & `profile` | Tambahkan scope tsb di OAuth consent screen, lalu coba login ulang (mungkin perlu revoke akses lama di [myaccount.google.com/permissions](https://myaccount.google.com/permissions)). |
| Setelah OAuth berhasil, akun baru malah diminta verifikasi email lagi | Ini TIDAK seharusnya terjadi - akun OAuth otomatis diberi `email_verified_at` saat dibuat (lihat `SocialAuthService`) | Pastikan tidak ada perubahan lain di `App\Models\User::$fillable` yang menghapus `email_verified_at`, atau laporkan sebagai bug. |
