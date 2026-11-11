# Masuk lewat Super App Madrasah (SSO)

Siswa, guru, dan pegawai bisa masuk ke PTSP tanpa mengetik password lagi: cukup login di Super App, lalu
klik kartu **PTSP** di dashboard Super App. Cara kerjanya mengikuti dokumentasi Super App
(`KamaludinZ/super_app_madrasah`, folder `docs/sso`).

```
Pengguna ── klik kartu PTSP ──▶ Super App membuat TIKET (60 detik, sekali pakai)
         ◀─ diarahkan ke https://<domain-ptsp>/sso/masuk?tiket=…
PTSP (server) ── POST /api/sso/tukar + kunci rahasia ──▶ Super App
PTSP ◀── data pengguna (e-mail, jenis, …) ── akun PTSP dicocokkan lewat e-mail ── sesi login dibuat
```

Di halaman login PTSP muncul tombol **Masuk lewat Super App** (hanya bila sudah diatur). Tombol itu membuka
dashboard Super App; pengguna lalu memilih kartu PTSP.

## 1. Daftarkan PTSP di Super App (admin Super App)

Menu **Admin → Sistem & Utilitas → Aplikasi Terhubung (SSO)** → **Tambah aplikasi**:

| Isian | Nilai untuk PTSP |
| --- | --- |
| Kode aplikasi | `ptsp` |
| Nama | PTSP MTsN 2 Kota Malang |
| Alamat penerima tiket (redirect URL) | `https://<domain-ptsp>/sso/masuk`, mis. `https://ptsp.mtsn2malang.sch.id/sso/masuk` (wajib https) |
| Peran yang boleh membuka | **Semua Siswa**, **Semua Guru**, **Semua Pegawai** |

Setelah disimpan, Super App menampilkan **kunci rahasia** (diawali `sso_`) beserta isian `.env`. Kunci hanya
tampil **sekali**: salin langsung ke Coolify (langkah 2). Bila hilang, klik **Kunci baru** di Super App lalu
ganti nilainya di Coolify.

> Sistem ini tidak memakai *client id* / *client secret* seperti OAuth. Yang dibutuhkan hanya **kode
> aplikasi** (didaftarkan di Super App), **alamat penerima tiket**, dan **kunci rahasia**.

## 2. Isi Environment Variables PTSP (Coolify)

Coolify → aplikasi PTSP → tab **Environment Variables**:

| Variabel | Contoh | Keterangan |
| --- | --- | --- |
| `SUPERAPP_SSO_URL` | `https://superapp.mtsn2malang.sch.id/api/sso` | Alamat API SSO Super App |
| `SUPERAPP_SSO_KUNCI` | `sso_…` | Kunci rahasia dari langkah 1. **Rahasia**: jangan ditulis di kode, git, atau chat. |
| `SUPERAPP_SSO_PORTAL_URL` | `https://superapp.mtsn2malang.sch.id/` | Opsional. Tujuan tombol di halaman login. Kosong = `SUPERAPP_SSO_URL` tanpa `/api/sso`. |

Lalu klik **Redeploy**. Selama `SUPERAPP_SSO_URL` atau `SUPERAPP_SSO_KUNCI` kosong, tombol tidak tampil dan
login biasa tetap berjalan seperti sekarang.

Uji kunci dari server PTSP (terminal Coolify):

```bash
curl -H "Authorization: Bearer $SUPERAPP_SSO_KUNCI" "$SUPERAPP_SSO_URL/cek"
# {"aplikasi":"ptsp","nama":"PTSP MTsN 2 Kota Malang","umur_tiket_detik":60}
```

## 3. Pastikan akun cocok

PTSP mencari akun lewat **e-mail**: e-mail pengguna di Super App harus sama dengan e-mail akun PTSP
(huruf besar/kecil tidak berpengaruh). Bila belum ada akun PTSP dengan e-mail itu, pengguna melihat pesan
"Akun Anda belum terdaftar di PTSP" dan perlu didaftarkan dulu (daftar sendiri atau oleh petugas).

Aturan tambahan di PTSP (`config/superapp_sso.php`):

* Hanya jenis `siswa`, `guru`, `pegawai` dari Super App yang diterima (alumni ditolak).
* Akun PTSP yang dinonaktifkan tetap tidak bisa masuk.
* Staf diarahkan ke panel `/cp`, pemohon ke `/portal`, sama seperti login biasa. Setiap percobaan tercatat di
  log percobaan login dan log akses (Monitoring Sistem).

## Bila ada masalah

| Pesan di halaman login | Penyebab umum |
| --- | --- |
| Login lewat Super App belum diatur | `SUPERAPP_SSO_URL`/`SUPERAPP_SSO_KUNCI` kosong di Coolify |
| Login lewat Super App sedang tidak tersedia | Kunci salah/sudah diganti, atau aplikasi PTSP dinonaktifkan di Super App |
| Tiket login sudah kedaluwarsa / tidak berlaku | Lewat 60 detik atau halaman dimuat ulang; buka lagi dari Super App |
| Akun Anda belum terdaftar di PTSP | E-mail di Super App tidak ada di PTSP |
| Super App tidak dapat dihubungi | Server PTSP tidak bisa menjangkau alamat `SUPERAPP_SSO_URL` |
