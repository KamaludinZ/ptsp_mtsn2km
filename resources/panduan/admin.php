<?php

/*
 * Isi Panduan Menu panel petugas (/cp), dibaca App\Support\PanelGuide.
 * Kunci: kelas halaman/resource Filament. Setiap entri: ringkas (1–2 kalimat),
 * langkah (urutan pemakaian), tips (opsional).
 */

return [

    // ── Umum ────────────────────────────────────────────────────────────

    \App\Filament\Pages\Dashboard::class => [
        'ringkas' => 'Halaman pertama setelah masuk. Isinya menyesuaikan peran aktif Anda: pimpinan melihat disposisi yang menunggu, loket melihat tamu dan berkas siap diambil, back office melihat antrian, dan administrator melihat rekap seluruh sistem.',
        'langkah' => [
            'Periksa penanda "Peran aktif" di header (kanan atas). Bila Anda memegang lebih dari satu peran, klik penanda itu lalu pilih peran lain di daftar "Ganti peran"; isi dasbor dan menu ikut berubah.',
            'Baca kartu "Layanan Hari Ini" dan "Kondisi Layanan" untuk melihat jumlah permohonan dan yang melewati target.',
            'Pakai kotak pintasan (mis. Registrasi Layanan, Catat Tamu, Disposisi, Permohonan) untuk langsung membuka pekerjaan yang paling sering dipakai.',
            'Pimpinan: tabel "Menunggu keputusan Anda" berisi permohonan yang perlu didisposisi; klik "Buka menu Persetujuan" untuk ke antrean disposisi.',
            'Loket dan back office: cek tabel "Siap diambil di loket", "Tamu hari ini", atau "Antrian terdekat dengan target waktu" lalu klik barisnya untuk membuka tiket.',
        ],
        'tips' => 'Tombol "Panduan" di header selalu membawa Anda ke halaman ini untuk melihat cara memakai setiap menu.',
    ],

    \App\Filament\Pages\Notifications::class => [
        'ringkas' => 'Kotak masuk pemberitahuan Anda: permohonan baru, disposisi, perubahan status, dan pengingat tindak lanjut. Sama dengan isi ikon lonceng, tetapi lebih lengkap.',
        'langkah' => [
            'Pilih tab "Semua" atau "Belum dibaca" untuk menyaring notifikasi.',
            'Klik baris notifikasi atau tombol "Buka" untuk langsung ke tiket terkait; notifikasi otomatis ditandai dibaca.',
            'Pakai "Tandai dibaca" / "Tandai belum dibaca" pada baris bila ingin mengingat sesuatu untuk nanti.',
            'Klik "Tandai semua dibaca" di atas tabel untuk membersihkan semua sekaligus.',
            'Centang beberapa baris lalu pilih "Hapus" bila notifikasi sudah tidak diperlukan.',
        ],
        'tips' => 'Notifikasi juga bisa dibuka lewat menu akun (kanan atas) > Notifikasi.',
    ],

    // ── Pimpinan ────────────────────────────────────────────────────────

    \App\Filament\Pages\Leadership\Approvals::class => [
        'ringkas' => 'Antrean disposisi untuk Kepala Madrasah dan Kepala Tata Usaha: permohonan yang berkasnya sudah diverifikasi petugas TU dan menunggu keputusan Anda. Menu ini hanya muncul saat peran aktif Anda peran pimpinan.',
        'langkah' => [
            'Pastikan peran aktif di header adalah Kepala Madrasah atau Kepala Tata Usaha. Bila belum, klik penanda "Peran aktif" lalu pilih peran pimpinan; panel "Memutuskan sebagai" di atas tabel juga punya tombol "Putuskan sebagai …".',
            'Buka menu Disposisi Masuk. Tabel menampilkan No. Tiket, Layanan (beserta mode disposisinya), Pemohon, Status, Anjuran TTD/TTE, Diajukan, dan Target selesai.',
            'Klik baris permohonan untuk membuka halaman tiket dan memeriksa berkas serta keterangan pemohon, atau langsung klik tombol "Disposisikan" pada baris tersebut.',
            'Di jendela disposisi, cek "Didisposisi sebagai" (peran aktif Anda), lalu centang unit tujuan di "Diteruskan kepada" (Waka Humas, Waka Kesiswaan, Waka Kurikulum, Waka Sarpras, Tata Usaha, Penjamin Mutu).',
            'Isi instruksi: klik salah satu "Pilihan cepat instruksi" atau ketik sendiri di kolom "Instruksi disposisi", lalu tambahkan "Catatan (opsional)" bila perlu.',
            'Pilih "Model tanda tangan": TTD (cetak, tanda tangani, unggah), TTE (unduh, tanda tangani elektronik, unggah), atau tanda "telah didisposisi oleh" (tanpa berkas, isi kolom "Telah didisposisi oleh"). Untuk TTD/TTE, klik "Cetak / unduh lembar disposisi", tanda tangani, lalu unggah hasilnya di kolom berkas (boleh menyusul).',
            'Klik "Kirim" untuk menyimpan disposisi. Bila permohonan tidak dapat dilayani, pakai tombol "Tolak" dan tulis "Alasan penolakan".',
        ],
        'tips' => 'Tabel "Keputusan terakhir Anda" di bawah antrean menampilkan disposisi yang baru Anda buat; klik "Lihat semua riwayat" untuk membuka Riwayat Disposisi.',
    ],

    \App\Filament\Pages\Leadership\DispositionHistory::class => [
        'ringkas' => 'Arsip semua disposisi dan penolakan pimpinan beserta peran yang dipakai, model tanda tangan, dan instruksinya. Dipakai pimpinan dan pengawas; riwayat tidak dapat diubah.',
        'langkah' => [
            'Buka menu Riwayat Disposisi (di grup Pimpinan saat peran aktif pimpinan, atau di grup Pengawasan untuk pengawas).',
            'Baca kolom Waktu, No. Tiket, Layanan, Pejabat, Sebagai, Keputusan, Tanda tangan, dan Instruksi / catatan.',
            'Klik ikon filter untuk menyaring menurut Keputusan, Pejabat, Sebagai peran, Model tanda tangan, atau rentang Dari tanggal – Sampai tanggal.',
            'Klik nomor tiket untuk membuka detail permohonan (bila Anda berhak melihatnya).',
            'Pakai kotak cari untuk menemukan nomor tiket, nama pejabat, atau isi instruksi.',
        ],
    ],

    // ── Loket ───────────────────────────────────────────────────────────

    \App\Filament\Pages\FrontDesk\RegisterService::class => [
        'ringkas' => 'Formulir petugas loket (front desk) untuk mendaftarkan permohonan pemohon yang datang langsung ke PTSP.',
        'langkah' => [
            'Di bagian "Pemohon", pilih "Status pemohon": "Belum terdaftar" untuk pemohon baru atau yang belum tahu, "Sudah terdaftar" bila pemohon sudah punya akun.',
            'Belum terdaftar: isi Kategori pemohon, Nama lengkap, Nomor WhatsApp, dan Email (opsional). Kotak "Hasil pencarian akun" memberi tahu bila email atau nomor itu sudah terdaftar (permohonan masuk ke akun tersebut) atau akun baru akan dibuat.',
            'Sudah terdaftar: pilih "Tipe pengguna", lalu cari dan pilih nama di "Akun pemohon". Nama, WhatsApp, dan email terisi otomatis dan terkunci; periksa "Ringkasan pemohon". Data yang keliru diperbaiki administrator di menu Pengguna, tab Pemohon.',
            'Di bagian "Permohonan", pilih "Layanan" (hanya layanan yang terbuka untuk tipe pemohon itu); ringkasan "Standar pelayanan" dan "Template berkas" layanan itu akan tampil.',
            'Tulis "Keperluan / keterangan" dan pilih "Prioritas".',
            'Unggah "Berkas persyaratan" yang dibawa pemohon (hasil pindai atau foto).',
            'Klik "Daftarkan permohonan". Pemberitahuan menampilkan nomor permohonan dan akun yang dipakai; klik "Cetak tanda terima" untuk diberikan kepada pemohon. Bila gagal, alasannya tampil dan isian tetap ada untuk diperbaiki.',
        ],
        'tips' => 'Berganti status pemohon tidak menghapus isian bagian Permohonan. Akun yang dinonaktifkan atau milik petugas tidak bisa dipakai untuk permohonan loket.',
    ],

    \App\Filament\Resources\VisitorResource::class => [
        'ringkas' => 'Buku tamu digital untuk petugas loket: mencatat siapa yang datang, keperluannya, siapa yang ditemui, serta jam masuk dan keluar.',
        'langkah' => [
            'Klik "Daftarkan tamu" di kanan atas.',
            'Isi "Identitas tamu" (Nama lengkap, Nomor telepon / WhatsApp, Email, Instansi / asal) dan bagian "Kunjungan" (Keperluan, Bertemu dengan, Foto tamu bila perlu).',
            'Simpan dengan tombol "Buat", lalu klik "Cetak kartu tamu" pada pemberitahuan bila tamu perlu kartu.',
            'Pakai tab "Hari ini", "Masih di lokasi", atau "Semua" untuk melihat daftar tamu.',
            'Saat tamu pulang, klik "Check-out" pada barisnya. Tombol "Catatan" dipakai untuk menambah catatan kunjungan.',
        ],
        'tips' => 'Menu titik tiga di setiap baris berisi Lihat, Cetak kartu tamu, Ubah, dan Hapus.',
    ],

    // ── Back Office ─────────────────────────────────────────────────────

    \App\Filament\Resources\TicketResource::class => [
        'ringkas' => 'Daftar semua permohonan (online dan loket) dalam satu tempat. Dipakai seluruh petugas untuk mencari, membuka, dan memproses tiket; pimpinan juga mendisposisi dari halaman tiket.',
        'langkah' => [
            'Pilih tab yang sesuai: Antrian, Tugas Saya, Disposisi unit saya, Terlambat, Menunggu Persetujuan, Siap Diambil, atau Semua (tab yang tampil mengikuti peran Anda).',
            'Cari dengan kotak "Cari no. tiket, pemohon, WA, layanan…" atau buka saringan (Status, Jenis layanan, Jalur, Prioritas, Kategori layanan masuk, Unit tujuan disposisi, Petugas, Melewati target, Tanggal pengajuan). Klik "Reset pencarian & filter" untuk kembali.',
            'Untuk mengubah status cepat tanpa membuka tiket, klik tombol "Status" di baris lalu pilih status baru (Diverifikasi, Diproses, Selesai, Ditolak, dll.).',
            'Klik "Buka" untuk masuk ke halaman tiket. Panel "Aksi untuk peran aktif" menunjukkan tombol mana yang tersedia; bila tertulis "Butuh peran lain", klik "Ganti ke …".',
            'Di halaman tiket, menu "Proses tiket" berisi Tugaskan petugas, Ubah status, Ubah prioritas, Selesaikan langkah workflow, Catatan tindak lanjut, Kategori layanan masuk, dan Unggah berkas. Pakai "Unggah hasil layanan" untuk hasil akhir dan "Serahkan ke pemohon" saat produk diambil.',
            'Pimpinan (peran aktif Kepala Madrasah/Kepala TU): pada tiket yang menunggu keputusan, klik "Disposisikan", pilih unit di "Diteruskan kepada", isi instruksi dan catatan, pilih "Model tanda tangan", klik "Cetak / unduh lembar disposisi" bila memakai TTD/TTE, lalu klik "Kirim". Pakai "Tolak" bila permohonan tidak dapat dilayani.',
            'Klik "Tanda terima" untuk mencetak bukti permohonan.',
        ],
        'tips' => 'Status "Diverifikasi" berarti berkas sudah dicek lengkap; untuk layanan yang perlu disposisi, permohonan lalu masuk ke antrean Disposisi Masuk pimpinan.',
    ],

    \App\Filament\Pages\Services\IncomingServices::class => [
        'ringkas' => 'Daftar permohonan yang sudah didisposisi pimpinan dan sampai ke back office, dikelompokkan menurut instruksi disposisi. Dipakai staf unit (Waka, Tata Usaha, Penjamin Mutu) untuk menindaklanjuti.',
        'langkah' => [
            'Pilih tab kategori: Semua, Disposisi (untuk diproses), Tembusan (untuk diketahui), Koordinasi (perlu koordinasi antarunit), atau Arahan (pimpinan minta saran).',
            'Aktifkan saringan "Hanya untuk unit saya" agar hanya permohonan untuk unit Anda yang tampil; saringan "Layanan" mempersempit menurut jenis layanan.',
            'Baca kolom Unit tujuan dan "Instruksi pimpinan" untuk mengetahui apa yang diminta.',
            'Klik baris permohonan untuk membuka halaman tiket dan mulai memprosesnya lewat menu "Proses tiket".',
        ],
    ],

    \App\Filament\Resources\ServiceHistoryResource::class => [
        'ringkas' => 'Jejak setiap tahap permohonan: siapa melakukan apa, kapan, perubahan status, dan catatannya. Untuk back office dan pengawas; riwayat tidak dapat diubah atau dihapus.',
        'langkah' => [
            'Baca kolom Waktu, No. Tiket, Layanan, Kegiatan, Status, Oleh, dan Catatan.',
            'Buka saringan untuk memilih Layanan, Pelaku, Kegiatan, Status, atau rentang Dari tanggal – Sampai tanggal.',
            'Klik "Detail" pada baris untuk melihat catatan lengkap dan berkas yang terkait.',
            'Klik "Ekspor Excel" di kanan atas untuk mengunduh riwayat sesuai saringan.',
        ],
    ],

    \App\Filament\Pages\Reports\Performance::class => [
        'ringkas' => 'Ringkasan kinerja pelayanan: jumlah permohonan, tingkat penyelesaian, ketepatan waktu, indeks kepuasan (IKM/IPAK), dan pengaduan. Untuk pimpinan, pengawas, dan back office.',
        'langkah' => [
            'Pilih "Periode" di bagian atas halaman.',
            'Baca kartu angka: Permohonan, Tingkat penyelesaian, Tepat waktu, Melewati target, IKM (SKM), IPAK (SPAK), Pengaduan tertangani, dan Responden survei.',
            'Lihat tabel "Kinerja per layanan" dan grafik "Permohonan per status" untuk mengetahui layanan yang perlu perhatian.',
            'Periksa tabel "Melewati target waktu" lalu buka tiketnya untuk ditindaklanjuti.',
        ],
    ],

    \App\Filament\Pages\Reports\MonthlyReport::class => [
        'ringkas' => 'Laporan bulanan layanan PTSP: jumlah permohonan, penyelesaian, ketepatan waktu, kepuasan, dan pengaduan per bulan. Untuk pimpinan, pengawas, dan back office.',
        'langkah' => [
            'Di "Periode laporan", pilih bulan dan tahun, atau pakai tombol panah untuk bulan sebelumnya/berikutnya dan tombol "Bulan ini".',
            'Baca ringkasan angka, grafik "Permohonan masuk per hari", dan "Status permohonan bulan ini".',
            'Lihat tabel "Rekap jenis layanan" untuk jumlah per layanan.',
            'Klik "Cetak laporan" untuk mencetak, atau "Unduh" lalu pilih PDF atau Excel (.xlsx).',
        ],
    ],

    // ── Persuratan ──────────────────────────────────────────────────────

    \App\Filament\Resources\SuratKeluarResource::class => [
        'ringkas' => 'Buku register surat keluar untuk petugas pemroses naskah: meminta nomor surat otomatis, melengkapi data surat, dan menyimpan lampirannya. Nomor berurutan dan dimulai ulang setiap tahun.',
        'langkah' => [
            'Klik "Minta Nomor Surat" di kanan atas.',
            'Isi "Jumlah nomor yang dibutuhkan" dan "Tanggal surat", lalu di bagian "Penomoran" pilih Jenis surat, isi variabel tambahan bila diminta, dan pilih Klasifikasi.',
            'Bila sudah tahu, buka "Data surat (opsional)" untuk mengisi Tujuan surat, Perihal, Lampiran, dan Keterangan. Periksa "Nomor yang akan diberikan", lalu kirim.',
            'Klik "Salin nomor" pada pemberitahuan untuk menempelkan nomor ke surat Anda.',
            'Surat yang datanya belum lengkap ditandai "Belum dilengkapi"; klik "Lengkapi" pada barisnya untuk mengisi perihal, tujuan, dan berkas lampiran.',
            'Pakai saringan Tahun, Jenis surat, Klasifikasi, Pembuat, tanggal, atau Kelengkapan, lalu "Cetak / Unduh" (Unduh Excel atau Cetak / Unduh PDF) untuk laporan register.',
        ],
        'tips' => 'Tombol "Detail" menampilkan data surat lengkap; tombol "Lampiran (n)" membuka berkas yang sudah diunggah.',
    ],

    \App\Filament\Resources\PersuratanMasterResource::class => [
        'ringkas' => 'Daftar pilihan baku untuk form surat keluar dan disposisi (tujuan naskah, jenis surat, tembusan, klasifikasi, instruksi disposisi) serta pengaturan format nomor per jenis surat.',
        'langkah' => [
            'Pilih tab daftar: Tujuan Naskah, Jenis Surat, Tembusan, Klasifikasi Surat, Instruksi Disposisi, Penomoran Otomatis, atau Variabel Tambahan.',
            'Klik "Tambah pilihan" untuk menambah isian baru; isi Daftar, Kode klasifikasi (bila ada), Nama, Urutan, dan "Tampilkan sebagai pilihan".',
            'Isian yang diketik petugas tercatat sebagai pilihan nonaktif; nyalakan kolom "Aktif" (atau centang beberapa baris lalu "Aktifkan") agar muncul di form.',
            'Di tab Penomoran Otomatis, klik "Atur penomoran" pada jenis surat untuk mengubah format nomornya; "Kembalikan ke format bawaan" bila ingin mengulang.',
            'Di tab Variabel Tambahan, klik "Tambah variabel" / "Ubah variabel" untuk meminta isian tambahan (mis. kelas) saat petugas meminta nomor.',
        ],
        'tips' => 'Daftar Instruksi Disposisi menjadi "Pilihan cepat instruksi" di jendela disposisi pimpinan.',
    ],

    // ── Pengawasan ──────────────────────────────────────────────────────

    \App\Filament\Resources\ComplaintResource::class => [
        'ringkas' => 'Tempat menindaklanjuti pengaduan masyarakat dan laporan whistleblowing (WBS). Dipakai administrator, Kepala Madrasah, dan Kepala TU.',
        'langkah' => [
            'Pilih tab "Pengaduan" atau "Whistleblowing"; angka pada tab menunjukkan laporan baru.',
            'Saring menurut Status, Prioritas, atau Layanan terkait bila perlu, lalu klik "Buka" pada laporan.',
            'Baca isi laporan, data pelapor, dan bukti. Laporan berlabel "Rahasia" tidak boleh diteruskan di luar proses tindak lanjut.',
            'Klik "Tindak lanjut", pilih Tahap (Diterima, Ditelaah, Ditindaklanjuti, Selesai, Ditutup), Prioritas, dan Penangan, lalu tulis "Tanggapan kepada pelapor" dan "Catatan internal".',
            'Bila identitas pelapor perlu dilindungi, klik "Rahasiakan identitas" dan isi alasannya.',
        ],
    ],

    \App\Filament\Resources\SuggestionResource::class => [
        'ringkas' => 'Kotak saran dari pengunjung situs. Dibaca oleh pimpinan dan administrator.',
        'langkah' => [
            'Saring dengan "Status" untuk melihat saran Baru atau yang Sudah dibaca.',
            'Klik "Baca" untuk membuka isi saran.',
            'Klik "Tandai dibaca" setelah saran ditindaklanjuti; bisa juga mencentang beberapa baris lalu "Tandai dibaca" sekaligus.',
            'Hapus saran yang tidak relevan dengan tombol "Hapus".',
        ],
    ],

    \App\Filament\Pages\Reports\SurveyReport::class => [
        'ringkas' => 'Hasil Survei Kepuasan Masyarakat (SKM) dan Survei Persepsi Anti Korupsi (SPAK): indeks, nilai per pertanyaan, dan demografi responden. Untuk pimpinan, pengawas, dan administrator.',
        'langkah' => [
            'Pilih tab "SKM (Kepuasan Masyarakat)" atau "SPAK (Persepsi Anti Korupsi)".',
            'Pilih "Periode" dan "Edisi survei" (atau "Semua edisi").',
            'Baca indeks dan jumlah responden, lalu bagian "Nilai per pertanyaan" dan "Demografi responden".',
            'Lihat bagian "Arsip triwulan" untuk rekap yang sudah diarsipkan.',
            'Klik "Cetak", "Unduh PDF", atau "Unduh Excel" untuk laporan.',
        ],
    ],

    \App\Filament\Pages\Reports\SurveyRespondents::class => [
        'ringkas' => 'Daftar pemohon yang sudah mengisi survei kepuasan, dan permohonan selesai yang belum dinilai. Untuk pimpinan, pengawas, dan administrator.',
        'langkah' => [
            'Tab "Sudah mengisi" menampilkan setiap pengisian survei (Diisi, Survei, Edisi, Responden, Tiket, Saran); saring menurut Survei, Edisi, atau tanggal.',
            'Tab "Belum mengisi" menampilkan permohonan selesai yang belum dinilai pemohonnya.',
            'Di tab "Belum mengisi", klik "Kirim pengingat" pada baris, atau centang beberapa baris lalu "Kirim pengingat" sekaligus.',
        ],
        'tips' => 'Pengingat dikirim lewat kanal notifikasi yang aktif, paling sering sekali sehari per permohonan.',
    ],

    \App\Filament\Pages\Reports\SurveyArchives::class => [
        'ringkas' => 'Arsip rekap SKM dan SPAK per triwulan. Angka arsip tidak berubah meski pertanyaan survei diperbarui.',
        'langkah' => [
            'Saring menurut "Survei" (SKM/SPAK) dan "Tahun".',
            'Baca kolom Triwulan, Periode, Responden, Nilai rata-rata, dan Indeks.',
            'Klik "Rincian" untuk melihat nilai per pertanyaan pada triwulan tersebut.',
        ],
    ],

    // ── Manajemen Layanan ───────────────────────────────────────────────

    \App\Filament\Resources\ServiceResource::class => [
        'ringkas' => 'Katalog layanan PTSP yang dapat diajukan pemohon: syarat, alur, standar pelayanan, template berkas, dan pengaturan disposisi. Dikelola administrator.',
        'langkah' => [
            'Pakai tab Semua, Aktif, atau Nonaktif untuk melihat layanan; klik "Tambah layanan" untuk membuat yang baru.',
            'Isi tab "Informasi" (Nama layanan, Kode layanan, Kategori, Jalur pengajuan, Standar waktu penyelesaian, Biaya, Deskripsi, dan sakelar Aktif).',
            'Isi tab "Persyaratan & Alur" dan "Standar Pelayanan", lalu di tab "Pemohon" centang siapa saja yang "Dapat diajukan oleh".',
            'Di tab "Disposisi", pilih Mode disposisi, unit "Diteruskan kepada unit", dan "Anjuran tanda tangan berkas". Simpan.',
            'Template berkas layanan dikelola di bagian "Template Berkas" pada halaman ubah layanan (tombol "Tambah template").',
            'Klik "Lihat di portal" untuk melihat tampilan layanan bagi pemohon; pakai "Aktifkan"/"Nonaktifkan" untuk menampilkan atau menyembunyikan layanan.',
        ],
    ],

    \App\Filament\Resources\ServiceCategoryResource::class => [
        'ringkas' => 'Kelompok layanan yang dipakai untuk menyusun katalog layanan di portal. Dikelola administrator.',
        'langkah' => [
            'Klik tombol "Buat" di kanan atas untuk menambah kategori.',
            'Isi "Nama Kategori", pilih "Kategori Induk" bila kategori ini bagian dari kategori lain, dan atur "Urutan".',
            'Nyalakan "Aktif" dan isi "Deskripsi" bila perlu, lalu simpan.',
            'Pakai "Lihat" atau "Ubah" di baris untuk memeriksa atau memperbarui kategori.',
        ],
    ],

    \App\Filament\Resources\ServiceTemplateResource::class => [
        'ringkas' => 'Semua template berkas (formulir, contoh surat) yang ditawarkan kepada pemohon, dikelompokkan per layanan. Dikelola administrator.',
        'langkah' => [
            'Klik "Tambah template", pilih "Layanan", isi "Nama template" dan "Petunjuk pengisian".',
            'Unggah "Berkas template"; saat mengubah, unggah di "Ganti berkas" agar versinya naik otomatis.',
            'Atur sakelar "Aktif — ditawarkan kepada pemohon" dan "Wajib dilengkapi pemohon", serta "Urutan".',
            'Pakai "Unduh" untuk memeriksa berkas, dan saringan Layanan, Wajib dilengkapi, atau Status untuk mencari template.',
        ],
    ],

    \App\Filament\Pages\Services\DispositionSettings::class => [
        'ringkas' => 'Pengaturan disposisi per layanan: siapa pejabat yang mendisposisi, unit penerima, dan anjuran tanda tangan. Hanya untuk administrator dengan peran aktif Administrator.',
        'langkah' => [
            'Lihat kolom Mode disposisi, Penerima disposisi, dan Anjuran tanda tangan setiap layanan.',
            'Pakai saringan "Belum diatur (aturan bawaan)", "Butuh disposisi", "Penerima disposisi", atau "Status layanan" untuk menemukan layanan yang perlu diatur.',
            'Klik "Atur" pada baris layanan.',
            'Pilih "Mode disposisi" (Kepala Madrasah + Kepala TU, Kepala Madrasah saja, Kepala TU saja, atau Tanpa disposisi).',
            'Centang "Penerima disposisi (Back Office)" dan pilih "Anjuran tanda tangan pada berkas layanan", lalu simpan.',
        ],
        'tips' => 'Penerima yang diatur di sini menjadi centang bawaan "Diteruskan kepada" saat pimpinan mendisposisi.',
    ],

    \App\Filament\Resources\FaqResource::class => [
        'ringkas' => 'Pertanyaan yang sering diajukan (FAQ) yang tampil di halaman FAQ dan beranda situs. Dikelola administrator.',
        'langkah' => [
            'Klik "Tambah FAQ".',
            'Isi "Pertanyaan", "Jawaban", "Kelompok", dan "Urutan", lalu nyalakan "Tampilkan di situs".',
            'Simpan, lalu klik "Lihat di situs" pada halaman ubah untuk memeriksa hasilnya.',
            'Di daftar, seret baris untuk mengubah urutan dan pakai sakelar "Tampil" untuk menyembunyikan/menampilkan FAQ.',
        ],
    ],

    // ── Manajemen Survey ────────────────────────────────────────────────

    \App\Filament\Resources\SurveyEditionResource::class => [
        'ringkas' => 'Edisi survei per triwulan. Jawaban survei dicatat pada edisi yang sedang aktif. Dikelola administrator.',
        'langkah' => [
            'Klik tombol "Buat" di kanan atas.',
            'Pilih "Triwulan", isi "Tahun" dan "Keterangan".',
            'Nyalakan "Jadikan edisi aktif" bila edisi ini langsung dipakai, lalu simpan.',
            'Untuk berganti edisi, klik "Aktifkan" pada baris edisi yang diinginkan.',
        ],
    ],

    \App\Filament\Resources\SurveyQuestionResource::class => [
        'ringkas' => 'Daftar pertanyaan survei SKM dan SPAK yang diisi pemohon. Dikelola administrator.',
        'langkah' => [
            'Klik tombol "Buat" di kanan atas.',
            'Pilih "Bagian survei" dan "Unsur", lalu tulis "Pertanyaan".',
            'Pilih "Jenis jawaban", isi "Urutan" dan "Pilihan jawaban" bila diperlukan.',
            'Atur sakelar "Wajib diisi" dan "Aktif", lalu simpan.',
            'Di daftar, pakai saringan "Bagian" dan sakelar "Aktif" untuk menonaktifkan pertanyaan tanpa menghapusnya.',
        ],
    ],

    \App\Filament\Resources\SurveyUnsurResource::class => [
        'ringkas' => 'Unsur penilaian survei (mis. unsur SKM sesuai Permenpan RB) yang menjadi pengelompokan pertanyaan. Dikelola administrator.',
        'langkah' => [
            'Klik tombol "Buat" di kanan atas.',
            'Pilih "Survei", isi "Kode", "Nama unsur", "Urutan", dan "Keterangan".',
            'Nyalakan "Aktif", lalu simpan.',
            'Kolom "Pertanyaan" menunjukkan berapa pertanyaan yang memakai unsur tersebut.',
        ],
    ],

    // ── Manajemen Pengguna ──────────────────────────────────────────────

    \App\Filament\Resources\UserResource::class => [
        'ringkas' => 'Akun petugas dan pemohon. Peran menentukan menu yang bisa dibuka; akun nonaktif tidak bisa masuk. Dikelola administrator.',
        'langkah' => [
            'Pakai tab Semua, Petugas, Pemohon, atau Nonaktif dan saringan (Tipe User, Peran pemohon, Peran, Hanya Aktif, Belum pernah masuk, Email Terverifikasi). Tab Pemohon berisi akun berperan Siswa, Wali Murid, Alumni, Masyarakat Umum, Instansi, Guru, atau Pegawai.',
            'Untuk memperbaiki data pemohon yang dipakai loket, buka tab Pemohon, cari nama/email/WhatsApp-nya, lalu klik barisnya dan simpan perubahan.',
            'Klik "Tambah pengguna", isi "Informasi Pengguna" (Nama Lengkap, Email, Nomor WhatsApp, Kategori, sakelar "Aktif (boleh masuk)").',
            'Isi "Password" dan "Ulangi password", lalu pilih "Role" di bagian "Role & Permission". Simpan.',
            'Untuk banyak akun sekaligus, klik "Unggah berkas akun", klik "Unduh template", isi, lalu unggah di "Berkas Excel/CSV".',
            'Klik "Unduh Excel" untuk mengekspor daftar pengguna sesuai tab yang aktif.',
        ],
    ],

    \App\Filament\Resources\RoleResource::class => [
        'ringkas' => 'Daftar peran beserta izin aksesnya, dan pengaturan kode registrasi civitas. Dikelola administrator.',
        'langkah' => [
            'Tab "Peran" menampilkan setiap peran, jumlah pengguna, dan jumlah izinnya.',
            'Klik "Peta izin" untuk melihat izin semua peran dalam satu tabel.',
            'Klik "Ubah" pada peran untuk mencentang atau menghapus centang izin di bagian "Izin", lalu simpan.',
            'Klik "Buat" untuk menambah peran baru (isi "Kode peran" dan pilih izinnya).',
            'Tab "Kode Registrasi" dipakai untuk mengatur kode yang harus dimasukkan civitas saat mendaftar; klik "Simpan" setelah mengubah.',
        ],
    ],

    \App\Filament\Pages\System\ProcessorRoleList::class => [
        'ringkas' => 'Daftar unit back office penerima disposisi (Waka, Tata Usaha, Penjamin Mutu) dan staf pemegangnya. Dikelola administrator.',
        'langkah' => [
            'Setiap kartu menunjukkan satu unit: jumlah Pemegang dan jumlah Layanan penerima.',
            'Klik "Atur pemegang", pilih "Staf pemegang", lalu simpan.',
            'Klik "Atur izin" untuk membuka pengaturan izin peran tersebut.',
            'Perhatikan label "Belum ada pemegang" atau "Peran belum dibuat" agar disposisi tidak tertahan.',
        ],
    ],

    \App\Filament\Pages\System\Impersonation::class => [
        'ringkas' => 'Masuk sementara sebagai pengguna lain untuk melihat tampilan atau masalah yang ia alami. Hanya untuk administrator dengan peran aktif Administrator; setiap sesi dicatat.',
        'langkah' => [
            'Cari pengguna dengan kotak cari, atau saring menurut "Peran" dan "Status akun".',
            'Klik "Masuk sebagai" pada baris pengguna.',
            'Isi "Alasan" dan konfirmasi.',
            'Setelah selesai, klik "Kembali ke akun admin" (di spanduk atas atau menu akun) untuk kembali ke akun Anda.',
            'Tabel "Riwayat ganti akun" di bawah menampilkan sesi yang pernah dilakukan.',
        ],
    ],

    // ── Manajemen Pengumuman ────────────────────────────────────────────

    \App\Filament\Pages\Content\ContentManagement::class => [
        'ringkas' => 'Ringkasan konten situs publik: jumlah pengumuman per status, FAQ aktif, pengumuman yang segera berakhir, dan yang terakhir diubah. Untuk administrator.',
        'langkah' => [
            'Lihat kartu ringkasan pengumuman (Tayang, Dijadwalkan, Draf, Berakhir) dan jumlah FAQ aktif.',
            'Periksa bagian "Segera berakhir" untuk pengumuman yang berakhir dalam 7 hari.',
            'Klik "Buat pengumuman" untuk menulis pengumuman baru atau "Kelola FAQ" untuk mengubah FAQ.',
            'Klik "Lihat situs" untuk memeriksa tampilan di situs publik.',
        ],
    ],

    \App\Filament\Resources\PengumumanResource::class => [
        'ringkas' => 'Pengumuman yang tayang di situs publik antara tanggal tayang dan tanggal berakhirnya. Dikelola administrator.',
        'langkah' => [
            'Klik "Buat pengumuman".',
            'Isi "Isi pengumuman" (Judul, Kategori, Isi, Penulis) dan periksa bagian "Pratinjau".',
            'Atur "Jadwal tayang": Tanggal tayang, Tanggal berakhir, dan sakelar "Tayangkan".',
            'Tambahkan "Berkas lampiran" atau "Tautan luar" bila ada, lalu simpan.',
            'Di daftar, pakai tab status (Semua, Tayang, Dijadwalkan, Draf, Berakhir) dan menu "Aksi" pada baris: Tayangkan sekarang, Jadikan draf, Akhiri sekarang.',
        ],
    ],

    \App\Filament\Resources\HeroSliderResource::class => [
        'ringkas' => 'Banner bergilir di bagian atas beranda situs. Tanpa slide aktif, beranda memakai tampilan bawaan. Dikelola administrator.',
        'langkah' => [
            'Klik "Tambah slide".',
            'Unggah "Gambar (komputer)" dan, bila perlu, "Gambar HP (opsional)"; gambar dipotong otomatis sesuai ukuran.',
            'Isi Judul, Subjudul, dan Deskripsi, lalu bagian "Tombol" (Tombol utama, Tombol kedua beserta Tautan).',
            'Atur "Efek" dan "Tampilan" bila perlu, nyalakan "Tampilkan di beranda", lalu simpan.',
            'Di daftar, seret baris untuk mengubah urutan dan pakai sakelar "Tampil" untuk menyembunyikan slide.',
        ],
    ],

    // ── Manajemen Sistem ────────────────────────────────────────────────

    \App\Filament\Pages\System\Settings::class => [
        'ringkas' => 'Identitas instansi, kontak, jam layanan, tautan, format nomor, kop surat, dan pengaturan situs yang dipakai di situs, portal, dan dokumen cetak.',
        'langkah' => [
            'Pilih tab bagian: Identitas, Kontak & Jam Layanan, Media Sosial & Tautan, Format & Penomoran, Kop Surat, atau Situs.',
            'Ubah isian yang diperlukan, mis. "Ganti logo" di tab Identitas atau "Format tanggal dokumen" di tab Format & Penomoran (lihat "Contoh").',
            'Di tab Situs, "Mode perawatan" dan "Pesan perawatan" dipakai saat situs sedang diperbaiki.',
            'Klik "Simpan pengaturan".',
        ],
    ],

    \App\Filament\Resources\AppSettingResource::class => [
        'ringkas' => 'Pengaturan lanjutan berbentuk kunci–nilai untuk kebutuhan teknis. Hanya untuk administrator; ubah bila Anda tahu fungsinya.',
        'langkah' => [
            'Cari pengaturan menurut Kunci atau Nama, atau saring menurut "Tipe".',
            'Klik "Ubah" pada baris, ubah "Nilai", lalu simpan.',
            'Untuk menambah pengaturan baru, klik "Buat", isi Kunci, Nama Tampilan, Kategori, Tipe, dan Nilai.',
        ],
        'tips' => 'Pengaturan umum (logo, kontak, kop surat) lebih mudah diubah di menu Pengaturan.',
    ],

    \App\Filament\Pages\System\NotificationIntegrations::class => [
        'ringkas' => 'Pengaturan gateway pengiriman notifikasi lewat Email (SMTP) dan WhatsApp. Hanya untuk administrator.',
        'langkah' => [
            'Di bagian "Email (SMTP)", nyalakan "Aktifkan notifikasi email" lalu isi Host SMTP, Port, Enkripsi, Username, Password, Alamat pengirim, dan Nama pengirim.',
            'Di bagian "WhatsApp", nyalakan "Aktifkan notifikasi WhatsApp", pilih Gateway, lalu isi URL API gateway, Token API, dan Nomor / ID pengirim.',
            'Klik "Simpan pengaturan".',
            'Klik "Uji email" atau "Uji WhatsApp" untuk mengirim pesan percobaan ke alamat atau nomor Anda.',
        ],
    ],

    \App\Filament\Resources\NotificationTemplateResource::class => [
        'ringkas' => 'Isi pesan setiap notifikasi per kanal (email/WhatsApp). Pesan hanya terkirim bila templatnya dan kanalnya aktif. Untuk administrator.',
        'langkah' => [
            'Klik "Atur kanal & pemicu" untuk menyalakan kanal ("Kanal aktif") dan memilih "Pemicu yang dikirim".',
            'Saring daftar menurut Kanal, Notifikasi, atau Status.',
            'Klik "Ubah" pada template, perbarui "Subjek email" dan "Isi pesan" memakai "Placeholder tersedia", lalu cek "Pratinjau" dan simpan.',
            'Pakai sakelar "Kirim" untuk mematikan satu notifikasi, dan "Uji kirim" untuk mencoba mengirimkannya.',
        ],
    ],

    \App\Filament\Resources\NotificationDeliveryResource::class => [
        'ringkas' => 'Catatan setiap notifikasi email dan WhatsApp yang dikirim beserta statusnya. Untuk administrator memantau pesan yang gagal.',
        'langkah' => [
            'Pakai tab "Semua", "Gagal", atau "Terkirim".',
            'Saring menurut Status, Kanal, Pemicu, atau tanggal.',
            'Klik "Isi pesan" untuk melihat pesan yang dikirim.',
            'Untuk pesan gagal, periksa pengaturan di Integrasi Notifikasi lalu klik "Kirim ulang".',
        ],
    ],

    \App\Filament\Pages\System\Security::class => [
        'ringkas' => 'Pemantauan keamanan: perlindungan dasar, hasil pemindaian, dan daftar IP yang diblokir. Hanya untuk administrator dengan peran aktif Administrator.',
        'langkah' => [
            'Periksa bagian "Perlindungan dasar" dan "Hasil pemindaian"; klik "Jalankan pemindaian" untuk memperbarui.',
            'Klik "Blokir IP", isi Alamat IP, Alasan, dan Durasi (jam) untuk memblokir akses mencurigakan; pakai tombol "Buka blokir" di "Daftar IP diblokir" untuk membatalkannya.',
            'Klik "Aktifkan mode pemeliharaan" (dengan Kode akses) saat sistem sedang diperbaiki, dan "Matikan mode pemeliharaan" setelah selesai.',
            'Klik "Bersihkan cache" dan pilih "Jenis cache" bila tampilan tidak memperbarui.',
        ],
    ],

    \App\Filament\Pages\System\SystemMonitor::class => [
        'ringkas' => 'Kondisi teknis aplikasi dan server, diperbarui otomatis setiap 30 detik. Hanya untuk administrator dengan peran aktif Administrator.',
        'langkah' => [
            'Lihat "Status sistem" di bagian atas.',
            'Pilih tab Ringkasan, Aplikasi, Server, Keamanan, Log & Audit, atau Update Aplikasi untuk rincian.',
            'Di tab Update Aplikasi, klik "Cek pembaruan" untuk melihat versi terbaru.',
            'Laporkan ke pengembang bila ada status merah atau peringatan yang terus muncul.',
        ],
    ],

    \App\Filament\Pages\System\ActivityTrail::class => [
        'ringkas' => 'Rekam jejak siapa melakukan apa, dengan peran aktif apa, dan pada tiket mana (perpindahan peran, ganti akun, aksi tiket). Catatan tidak dapat diubah atau dihapus.',
        'langkah' => [
            'Saring dengan Pengguna, Peran aktif, Dari tanggal, Sampai tanggal, dan Aksi (mis. disposisi, unggah).',
            'Pilih tab jenis aktivitas: Perpindahan peran, Ganti akun, atau Aksi tiket.',
            'Lihat tabel "Rekap per pengguna & peran" untuk jumlah aktivitas setiap orang.',
            'Klik "Lihat detail" pada entri untuk membaca rinciannya.',
        ],
    ],

];
