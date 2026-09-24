### 🏛️ Rangkuman Arsitektur & Package

Berikut adalah rincian setiap komponen, fungsinya, dan di mana komponen tersebut akan diimplementasikan.

### 1. Stack Inti (Core Stack)

* **Filament**
    * **Fungsi:** Framework Admin Panel lengkap (CRUD, Form, Tabel).
    * **Implementasi:** **Panel Internal** (Admin & Petugas).

* **Livewire**
    * **Fungsi:** Komponen interaktif (real-time, tanpa *refresh*).
    * **Implementasi:** **Portal Pemohon** (Utama) & **Panel Internal** (digunakan oleh Filament).

* **Tailwind CSS**
    * **Fungsi:** Framework *utility-first* CSS (Dasar *styling*).
    * **Implementasi:** **Semua Bagian** (Publik, Portal, Panel Internal).

* **DaisyUI**
    * **Fungsi:** Komponen UI di atas Tailwind (Tombol, Card, dll).
    * **Implementasi:** **Situs Publik** & **Portal Pemohon**.

---

### 2. Otentikasi & Role

* **Laravel Breeze (Stack Livewire)**
    * **Fungsi:** Starter kit untuk Login, Registrasi, Lupa Password.
    * **Implementasi:** **Portal Pemohon**.

* **`spatie/laravel-permission`**
    * **Fungsi:** Mengelola *role* (Admin, Petugas, Pemohon) & Izin.
    * **Implementasi:** **Backend** (diterapkan di semua area), Dikelola di **Panel Internal**.

---

### 3. Fitur & Tambahan

* **`power-components/livewire-powergrid`**
    * **Fungsi:** Datatable (tabel) interaktif untuk Livewire.
    * **Implementasi:** **Portal Pemohon** (Misal: halaman riwayat permohonan).

* **SweetAlert2**
    * **Fungsi:** Notifikasi Pop-up (Alert) yang modern.
    * **Implementasi:** **Portal Pemohon** (Dipicu oleh *event* Livewire).

* **`spatie/laravel-medialibrary`**
    * **Fungsi:** Mengelola *file upload* (KTP, PDF, Dokumen).
    * **Implementasi:** **Portal Pemohon** (Upload) & **Panel Internal** (Manajemen).

* **`spatie/laravel-activitylog`**
    * **Fungsi:** Mencatat log audit internal (siapa, apa, kapan).
    * **Implementasi:** **Backend** (Mencatat data dari Panel Internal).

* **`saade/filament-activitylog`**
    * **Fungsi:** *Plugin* untuk menampilkan log dari `spatie/laravel-activitylog`.
    * **Implementasi:** **Panel Internal** (Sebagai tab/halaman riwayat di Filament).

* **Model `StatusHistory` (Manual)**
    * **Fungsi:** Tabel kustom untuk riwayat status yang dilihat pengguna.
    * **Implementasi:** **Portal Pemohon** (Ditampilkan sebagai *timeline*).

* **Filament-Tiptap-Editor**
    * **Fungsi:** *Rich text editor* (WYSIWYG).
    * **Implementasi:** **Panel Internal** (Misal: untuk menulis berita atau deskripsi layanan).

---

### 4. Fitur Bawaan Filament (Pengganti)

* **Filament Table Builder**
    * **Fungsi:** Datatable bawaan Filament (Sangat *powerful*).
    * **Implementasi:** **Panel Internal** (Ini adalah pengganti PowerGrid di area Admin).

* **Filament Notifications**
    * **Fungsi:** Notifikasi *toast* (Misal: "Data tersimpan").
    * **Implementasi:** **Panel Internal** (Ini adalah pengganti SweetAlert di area Admin).