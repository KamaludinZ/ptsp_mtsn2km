Ini adalah rancangan yang sangat komprehensif. Permintaan Anda untuk melayani berbagai jenis pengguna (internal dan eksternal) serta menggabungkan layanan *online/offline* ke dalam satu alur tiket pemrosesan (tanpa antrian loket fisik) adalah inti dari PTSP modern.

Berikut adalah rancangan rinci modul aplikasi PTSP Sekolah yang memenuhi semua kriteria tersebut, dengan fokus utama pada kepatuhan Permen PANRB 15/2014.

---

### Konsep Arsitektur: Satu Pintu, Banyak Jalur, Satu Alur

Sistem ini memiliki satu *database* terpusat. Data bisa masuk melalui dua jalur (Online/Offline) dan melayani dua tipe pengguna utama (Tamu/Pemohon Layanan). Namun, semua permohonan layanan akan masuk ke satu alur kerja *back-office* yang sama.

NAMA APLIKASI : PTSP MTsN 2 KOTA MALANG
### Architecture & Technology
Frontend: Laravel 12, blade dan filament
Auth: Laravel Breeze
Backend: Laravel (untuk database/API)
Database: postgreSQL
Deployment : Shared Hosting / Cpanel / LARAGON LOKAL


### Kinerja
Kecepatan akses: Aplikasi harus memiliki waktu respons yang cepat, bahkan saat diakses oleh banyak pengguna secara bersamaan.
Skalabilitas: Aplikasi harus mampu menangani peningkatan jumlah pengguna dan permohonan di masa mendatang tanpa penurunan kinerja.
Ketersediaan tinggi: Sistem harus selalu dapat diakses dan tidak sering mengalami downtime yang mengganggu pelayanan. 
Integrasi dan interoperabilitas
Integrasi sistem: Kemampuan untuk terhubung dengan sistem lain di dalam atau di luar instansi, misalnya sistem pembayaran atau sistem data kependudukan.
Kompatibilitas perangkat: Aplikasi harus dapat diakses melalui berbagai perangkat, seperti komputer desktop, tablet, atau ponsel pintar.
Arsip elektronik: Sistem untuk mengarsipkan dokumen dan riwayat permohonan secara digital untuk memudahkan penelusuran di kemudian hari. 

### Aksesibilitas dan kualitas
Kemudahan penggunaan: Antarmuka (UI) aplikasi harus intuitif dan mudah digunakan oleh masyarakat dari berbagai latar belakang, termasuk fitur ramah disabilitas.
Pelayanan transparan: Semua tahapan pengambilan keputusan harus terdokumentasi dengan jelas dan dapat dilacak, sehingga meminimalkan potensi praktik korupsi, kolusi, dan nepotisme (KKN).
Kualitas pelayanan: Aplikasi harus dapat membantu mewujudkan pelayanan yang cepat, mudah, transparan, dan akuntabel sesuai standar yang telah ditetapkan.

### MODUL MENU TAMBAHAN NON FUNGSIONAL
FAQ dan alur permohonan: Bagian informasi yang berisi pertanyaan umum dan panduan lengkap tentang alur permohonan untuk setiap layanan.

### A. MODUL FRONT-DESK (LOKET FISIK SEKOLAH)

Modul ini dioperasikan oleh Petugas Loket/PTSP/Resepsionis. Ini adalah "Pintu Masuk" bagi pengunjung *walk-in*.

#### Modul 1: Triage (Pemilahan) & Buku Tamu

Modul ini adalah layar pertama yang dilihat petugas saat ada pengunjung datang.

* **Pengguna:** Petugas Loket.
* **Tujuan & Fungsi Inti:**
    * Petugas melakukan *triage* (pemilahan) instan: "Apakah Anda datang sebagai Tamu atau Pemohon Layanan?"
    * **Jika sebagai TAMU (misal: Rapat, Bertemu Guru, Vendor, Tamu Dinas):**
        1.  **Formulir Check-in Tamu:** Petugas menginput data tamu.
        2.  **Input Data:** Nama, Instansi/Keperluan (Instansi Luar, Masyarakat Umum), Pihak yang Dituju (dropdown nama Guru/Pegawai).
        3.  **Ambil Foto:** Menggunakan *webcam* di loket untuk mengambil foto tamu.
        4.  **Kirim Kartu Tamu (Visitor Pass):** Sistem mengirimkan kartu tamu *badge* pengunjung sementara (Nama, Foto, Tujuan, Stempel Waktu) melalui email atau WA secara otomatis dengan membuka new tab pengiriman email atau melalui whatsapp web.
        5.  **Dashboard Tamu Aktif:** Petugas bisa melihat siapa saja tamu yang sedang berada di dalam lingkungan sekolah.
        6.  **Check-out:** Petugas mencatat jam pulang tamu saat Tamu keluar atau konfirmasi melalui membalas email atau whatsapp.
    * **Jika sebagai PEMOHON LAYANAN (misal: Siswa minta surat, Wali murid legalisir):**
        * Petugas mengarahkan pengguna ke Modul 2 (Registrasi Layanan Offline).

#### Modul 2: Registrasi Layanan Offline (Walk-In)

Modul ini digunakan ketika pengunjung *walk-in* ingin mengurus sebuah layanan.

* **Pengguna:** Petugas Loket.
* **Tujuan & Fungsi Inti:**
    * Petugas bertindak sebagai "operator" yang menginputkan permohonan atas nama pemohon.
    * **Identifikasi Pemohon:** Petugas memilih tipe pemohon: `Guru`, `Pegawai`, `Siswa`, `Wali Murid`, `Alumni`, `Instansi Luar`, `Masyarakat Umum`.
    * **Pilih Layanan:** Berdasarkan tipe pemohon, sistem menampilkan katalog layanan yang relevan (misal: "Pengajuan Cuti" hanya muncul untuk Guru/Pegawai).
    * **Formulir Digital:** Petugas mengisi formulir layanan sesuai data dari pemohon.
    * **Scan & Unggah Berkas:** Petugas memindai (scan) dokumen fisik (misal: fotokopi rapor) dan mengunggahnya ke sistem.
    * **Generate Tiket:** Sistem secara otomatis membuat **Nomor Tiket Pemrosesan** (Contoh: `LAYANAN-202510-001`).
    * **Cetak Tanda Terima:** Petugas mengirimkan melalui email atau WA secara otomatis dengan membuka new tab pengiriman email atau melalui whatsapp web berupa tanda terima yang berisi Nomor Tiket, jenis layanan, dan estimasi waktu selesai (sesuai Standar Pelayanan) untuk diberikan kepada pemohon.

---

### B. MODUL PORTAL PUBLIK (ONLINE)

Ini adalah *website* atau aplikasi *mobile* yang bisa diakses oleh semua pengguna dari mana saja.

#### Modul 3: Autentikasi dan Manajemen Peran (Multi-User)

* **Pengguna:** Semua Tipe Pengguna (Internal & Eksternal).
* **Tujuan & Fungsi Inti:**
    * **Internal (Guru, Pegawai, Siswa):** Melakukan registrasi akun sederhana dengan memasukkan kode registrasi 6 digit angka khusus lalu memilih tipe pengguna dan memasukkan nama lengkap, password, Email untuk konfirmasi akun untuk bisa *login* dan melacak permohonan.
    * **Eksternal (Wali Murid, Alumni, Instansi, Umum):** Melakukan registrasi akun sederhana dengan memilih tipe pengguna dan memasukkan nama lengkap, password, Email untuk konfirmasi akun untuk bisa *login* dan melacak permohonan.
    * Sistem mengenali peran pengguna dan akan menampilkan layanan yang sesuai untuknya.

#### Modul 4: Katalog Standar Pelayanan (Kepatuhan Permen PANRB 15/2014)

Ini adalah "etalase" digital semua layanan, yang wajib transparan.

* **Pengguna:** Semua Tipe Pengguna.
* **Tujuan & Fungsi Inti:**
    * Menampilkan daftar layanan yang *tersedia* untuk peran pengguna yang sedang *login*.
    * **Wajib Menampilkan 14 Komponen (Permen PANRB 15/2014):**
        1.  **Dasar Hukum:** (Contoh: SK Kepala Sekolah No. X)
        2.  **Persyaratan:** (Contoh: Scan KTP, Scan Rapor Terakhir)
        3.  **Sistem, Mekanisme, Prosedur:** (Bagan alir digital: *Input -> Verifikasi TU -> Persetujuan Kepsek -> Selesai*)
        4.  **Jangka Waktu Penyelesaian:** (Contoh: "2 Hari Kerja")
        5.  **Biaya/Tarif:** (Contoh: "Rp 0,-")
        6.  **Produk Pelayanan:** (Contoh: "PDF Surat Keterangan Siswa Aktif ber-TTE")
        7.  **Sarana, Prasarana:** (Contoh: "Layanan Online via Website, Pengambilan di Loket PTSP")
        8.  **Kompetensi Pelaksana:** (Contoh: "Petugas Administrasi TU")
        9.  **Pengawasan Internal:** (Contoh: "Oleh Kepala TU")
        10. **Penanganan Pengaduan:** (Link ke Modul 10: Pengaduan)
        11. **Jumlah Pelaksana:** (Contoh: "2 orang")
        12. **Jaminan Pelayanan:** (Contoh: "Jaminan layanan selesai tepat waktu")
        13. **Jaminan Keamanan:** (Contoh: "Jaminan kerahasiaan data pemohon")
        14. **Evaluasi Kinerja:** (Link ke Modul 11: SKM & SPAK)

#### Modul 5: Pengajuan Layanan Online (e-Form)

* **Pengguna:** Semua Tipe Pengguna (via Portal).
* **Tujuan & Fungsi Inti:**
    * Pemohon memilih layanan dari katalog (Modul 4).
    * Mengisi formulir digital dan mengunggah dokumen persyaratan.
    * **Generate Tiket:** Setelah *submit*, sistem otomatis membuat **Nomor Tiket Pemrosesan** (Contoh: `LAYANAN-202510-002`).
    * Nomor tiket ini identik dengan yang didapat dari jalur *offline* dan masuk ke alur yang sama.

#### Modul 6: Dashboard Pemohon (Pelacakan Tiket)
Pelacakan dapat dilakukan tanpa login dengan syarat mengetahui nomor tiket, jika berupa produk layanan digital maka wajib untuk login terlebih dahulu untuk dapat mengunduh produk layanan.

* **Pengguna:** Semua Tipe Pengguna (via Portal).
* **Tujuan & Fungsi Inti:**
    * Tempat pemohon melihat riwayat pengajuan layanan mereka.
    * Melacak status tiket pemrosesan secara *real-time* (Misal: "Sedang Diverifikasi", "Menunggu Persetujuan", "Selesai", "Ditolak").
    * Melakukan login jika ingin Mengunduh produk layanan digital (jika sudah selesai).

---

### C. MODUL BACK-OFFICE (DAPUR PROSES)

Modul ini dioperasikan oleh Staf Internal Sekolah (TU, Waka, Kepsek).

#### Modul 7: Antrian Tugas (Workflow Engine)

Ini adalah jantung dari sistem, tempat semua tiket layanan (online & offline) bertemu.

* **Pengguna:** Petugas TU, Staf Terkait.
* **Tujuan & Fungsi Inti:**
    * Menampilkan *daftar tugas* (bukan antrian orang) dari semua tiket pemrosesan yang masuk.
    * **Unified Inbox:** Tiket `LAYANAN-202510-001` (Offline) dan `LAYANAN-202510-002` (Online) tampil di satu antrian yang sama.
    * **Fitur:** Verifikasi berkas, validasi data, eskalasi/disposisi (meneruskan tugas ke unit lain, misal: ke Waka Kesiswaan).
    * Setiap langkah terekam (log) untuk audit dan pengawasan.

#### Modul 8: Persetujuan (Approval) Pimpinan

* **Pengguna:** Kepala Sekolah, Kepala TU, Pimpinan unit (Waka Kesiswaan, Waka Humas, Waka Kurikulum, Waka Sarana Prasarana).
* **Tujuan & Fungsi Inti:**
    * Dashboard khusus untuk pimpinan melihat daftar permohonan yang membutuhkan persetujuan.
    * Fitur "Setujui" atau "Tolak" (dengan catatan).
    * **Menentukan Tanda Tangan menggunakan TTE atau tidak:** memilih jenis tandatangan oleh kepala untuk menandatangani produk layanan apakah dengan TTE atau TTD (misal: PDF surat keterangan) lalu hasil dokumen diupload secara digital apabila melayani jenis layanan yang bisa dikirim secara digital.

#### Modul 9: Manajemen Produk Layanan

* **Pengguna:** Petugas TU/Operator.
* **Tujuan & Fungsi Inti:**
    * Menghasilkan *output* layanan (Komponen 6 Permen PANRB).
    * Jika produknya digital (PDF), sistem mengirimkannya otomatis ke Dashboard Pemohon (Modul 6).
    * Jika produknya fisik (misal: Ijazah yang dilegalisir), sistem mengirim notifikasi ke pemohon ("Layanan Selesai. Produk bisa diambil di Loket PTSP").

---

### D. MODUL PENGAWASAN & EVALUASI (WAJIB)

Modul ini adalah implementasi langsung dari Komponen 10 dan 14 Permen PANRB.

#### Modul 10: Penanganan Pengaduan Masyarakat dan Whistleblowing
Terdapat 2 jenis pilihan pengaduan ini:
yaitu Pengaduan Masyarakat untuk masyarakat umum (dengan pilihan berupa pengaduan atau saran masukan) dan Whistleblowing untuk Laporan pengaduan yang bersifat resmi atau sangat rahasia dari publik atau internal sekolah.

* **Pengguna:** Publik (via Portal) dan Petugas Internal (via Portal atau Back-Office).
* **Tujuan & Fungsi Inti:**
    * **Publik:** Formulir untuk mengirim pengaduan, saran, atau masukan (terkait layanan *atau* umum).
    * **Internal:** Pelapor dapat memilih modul Whistleblowing atau menghubungi Petugas khusus menerima tiket pengaduan, mengelola, dan menindaklanjutinya sesuai SLA (Jangka Waktu) penanganan pengaduan.

#### Modul 11: Survei Otomatis (SKM & SPAK)

* **Pengguna:** Pemohon Layanan.
* **Tujuan & Fungsi Inti:**
    * Mengukur kinerja pelaksana (Komponen 14 Permen PANRB).
    * **Trigger:** Sistem secara otomatis mengirimkan link survei (via Notifikasi Portal/Email) kepada pemohon *setelah* tiket layanan ditutup (status "Selesai").
    * **Survei Kepuasan Masyarakat (SKM):** Berisi 9 unsur standar (sesuai Permen PANRB 14/2017).
    * **Survei Persepsi Anti Korupsi (SPAK):** Berisi pertanyaan terkait pungli, gratifikasi, diskriminasi, dan percaloan.
    * **Kiosk Survei (Opsional):** Sediakan tablet di loket agar pemohon *walk-in* bisa langsung mengisi survei ini setelah mengambil produk fisik mereka.

---

### E. MODUL ADMINISTRATOR & PIMPINAN

#### Modul 12: Super Admin (Master Konfigurasi)

* **Pengguna:** Administrator Sistem.
* **Tujuan & Fungsi Inti:**
    * **Manajemen Master Layanan (Pusat Kepatuhan Permen PANRB):** Di sinilah Admin *mendefinisikan* semua 14 komponen standar pelayanan untuk setiap layanan yang ada di Katalog (Modul 4).
    * **Manajemen Peran Pengguna:** Mendefinisikan hak akses untuk `Guru`, `Pegawai`, `Siswa`, `Wali Murid`, `Alumni`, `Instansi`, `Umum`.
    * **Konfigurasi Alur Kerja (Workflow):** Mengatur alur persetujuan untuk setiap layanan (misal: "Surat Siswa Aktif" hanya perlu persetujuan Kepala TU, tapi "Izin Kegiatan" perlu persetujuan Waka Kesiswaan *dan* Kepala Sekolah).

#### Modul 13: Dashboard Eksekutif (Pimpinan)

* **Pengguna:** Kepala Sekolah, Kepala TU.
* **Tujuan & Fungsi Inti:**
    * Menyajikan data analitik untuk pengawasan (Komponen 9 Permen PANRB).
    * **Laporan Kinerja Layanan:** Waktu penyelesaian rata-rata vs. Jangka Waktu Standar (Komponen 4). Jumlah layanan (online vs offline).
    * **Laporan SKM:** Menampilkan Indeks Kepuasan Masyarakat (IKM) secara *real-time*.
    * **Laporan SPAK:** Menampilkan Indeks Persepsi Anti Korupsi (IPAK).
    * **Laporan Buku Tamu:** Menampilkan statistik kunjungan tamu (dari Modul 1).