# Dokumen Rencana Teknis: Penanganan Status Transaksi Expired / Kedaluwarsa Payment Gateway (fixbayar.md)

**Plugin:** DonasiYuk  
**Topik:** Penanganan Transaksi Kedaluwarsa (*Expired / Batal*) pada Payment Gateway (Duitku, Tripay, Midtrans, Xendit, dll.)  
**Status:** *Draft / Menunggu Diskusi Tim*  
**Tanggal:** 25 Agustus 2026  

---

## 1. Latar Belakang & Permasalahan

Saat ini pada sistem DonasiYuk:
* Transaksi yang dibuat melalui Payment Gateway (VA, QRIS, E-Wallet, Retail) memiliki batas waktu pembayaran (*expiry period*, misal: 24 jam).
* Ketika waktu pembayaran telah habis di server Payment Gateway, status pembayaran di server gateway berubah menjadi **EXPIRED** / **FAILED** dan nomor bayar/VA ditutup.
* Namun, di tabel dashboard DonasiYuk, status transaksi tersebut **tetap berstatus `Waiting`**, karena:
  1. Skema status pada database `wp_dyk_donate` saat ini berbasis biner:
     * `status = 0` : Menunggu Pembayaran (*Waiting*)
     * `status = 1` : Pembayaran Berhasil (*Success / Received*)
  2. Endpoint webhook Payment Gateway di DonasiYuk umumnya hanya memproses payload pembayaran sukses (`resultCode == '00'` atau `PAID`).
  3. Belum ada mekanisme terjadwal (*scheduled job / cron*) lokal yang otomatis mendeteksi transaksi yang telah melampaui batas waktu.

Hal ini dapat menimbulkan kerancuan bagi tim CS/Admin yang mengira donatur masih dalam proses pembayaran, padahal kode bayarnya sudah kedaluwarsa.

---

## 2. Analisis Akar Masalah (Root Causes)

| No | Kemungkinan Penyebab | Probabilitas | Deskripsi & Cara Verifikasi |
| :--- | :--- | :---: | :--- |
| **1** | **Tidak ada handler webhook status Expired/Failed** | **60%** | Webhook gateway mengirimkan sinyal kedaluwarsa (misal Duitku mengirim callback status gagal / Xendit mengirim event `invoice.expired`), tetapi kode webhook di `donasiyuk.php` hanya memiliki percabangan `if ($resultCode == '00')` dan mengabaikan status lainnya.<br><br>**Verifikasi:** Pasang log sementara `error_log(file_get_contents('php://input'))` di endpoint webhook untuk melihat payload saat transaksi kedaluwarsa. |
| **2** | **Gateway tidak mengirim webhook untuk status Expired** | **25%** | Beberapa gateway atau metode bayar tertentu (misal QRIS dinamis di bank tertentu) tidak mentrigger callback saat kadaluwarsa, melainkan hanya membiarkan transaksi hangus di server mereka.<br><br>**Verifikasi:** Buka dashboard merchant (Duitku/Tripay), cek menu *Webhook/Callback Logs* pada transaksi yang expired. |
| **3** | **Ketiadaan Background Auto-Expire Task (WP-Cron)** | **15%** | Tanpa webhook eksternal, sistem lokal harus memiliki cron task berkala untuk memeriksa `created_at + expiry_period < NOW()` dan mengupdate statusnya secara otomatis.<br><br>**Verifikasi:** Cek daftar cron terdaftar di WordPress (via plugin WP Crontrol atau `wp cron event list`). |

---

## 3. Rekomendasi Solusi Teknis

Terdapat 2 opsi arsitektur yang bisa dipilih tim:

### Opsi A: Solusi Komprehensif (Database State + Webhook Handler + WP-Cron) — *Direkomendasikan*

1. **Standarisasi Kode Status di Database (`wp_dyk_donate`)**:
   * `status = 0` : **Waiting** (Menunggu Pembayaran)
   * `status = 1` : **Success** (Pembayaran Berhasil / Lunas)
   * `status = 2` : **Expired** (Batas Waktu Pembayaran Habis)
   * `status = 3` : **Cancelled / Failed** (Dibatalkan Donatur / Gagal Sistem)

2. **Perluasan Webhook Handler**:
   * Tambahkan penanganan kondisi `else` / `resultCode != '00'` pada callback Duitku/Tripay/Midtrans/Xendit untuk mengubah status menjadi `2` (*Expired*) atau `3` (*Failed*).

3. **Fitur Auto-Expire WP-Cron (`dyk_check_expired_donations`)**:
   * Buat scheduled job WP-Cron berjalan setiap **15 menit** atau **1 jam**.
   * Query mencari donasi berstatus `0` yang umurnya melebihi waktu kedaluwarsa gateway (misal `> 24 jam` atau sesuai setting `$duitku_expiry_period`).
   * Mengubah status donasi tersebut menjadi `2` (*Expired*) secara aman tanpa membebani server (*batch limit 50 rows per run*).

4. **Tampilan Dashboard & Label Status**:
   * Dashboard Admin menampilkan badge warna yang jelas:
     * 🟢 `Success` (Hijau)
     * 🟡 `Waiting` (Kuning)
     * 🔴 `Expired` (Abu-abu / Merah Muted)
     * ⚪ `Cancelled` (Merah Gelap)

---

### Opsi B: Solusi Ringan (Dynamic Display on Read / Tanpa Cron DB)

Jika tim tidak ingin mengubah logika database atau menjalankan cron:
* Di tabel dashboard, sistem mengecek selisih waktu `created_at` dengan waktu sekarang:
  * Jika `status == 0` dan `(waktu_sekarang - created_at) > batas_waktu`: Dashboard otomatis menampilkan badge **`Expired`** secara visual, meskipun di database nilainya tetap `0`.
* **Kelebihan:** Sangat cepat diimplementasikan, tanpa migrasi DB.
* **Kekurangan:** Filter pencarian database untuk transaksi expired tidak bisa di-query langsung via SQL query `status = 2`.

---

## 4. Rencana Kerja & File yang Terlibat

Jika tim telah menyetujui salah satu opsi, berikut modul yang akan disesuaikan:

1. **`donasiyuk.php`**:
   * Penambahan handler callback non-success pada fungsi webhook Duitku, Tripay, dan gateway lainnya.
   * Pendaftaran event hook WP-Cron `dyk_cron_auto_expire_transactions`.
   * Penyesuaian render badge status di tabel dashboard donasi.
2. **`donasiyuk-typ.php` (Thank You Page)**:
   * Menampilkan pesan ramah jika donatur membuka halaman invoice yang sudah berstatus *Expired* (*"Batas waktu pembayaran untuk transaksi ini telah berakhir. Silakan lakukan donasi baru"*).
3. **`admin/f_donasiyuk_data_campaign.php` & `f_donasiyuk_dashboard.php`**:
   * Penyesuaian filter tab: *Semua, Berhasil, Menunggu, Kedaluwarsa*.

---

## 5. Checklist Verifikasi Tim Sebelum Eksekusi

- [ ] Tentukan batas waktu kedaluwarsa standar (misal: 24 jam / 1440 menit).
- [ ] Pilih arsitektur yang diinginkan (**Opsi A** atau **Opsi B**).
- [ ] Tentukan apakah perlu notifikasi WhatsApp otomatis saat donasi expired (misal: pesan ramah *"Waktu pembayaran donasi Anda telah habis, klik link ini jika ingin mengulang"*).
- [ ] Uji coba simulasi transaksi di mode sandbox Payment Gateway.

---
*Dokumen ini disimpan di root folder repositori untuk referensi meeting dan diskusi teknis tim.*
