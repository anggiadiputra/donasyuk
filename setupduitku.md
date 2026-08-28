# Panduan Konfigurasi Duitku Payment Gateway pada DonasiYuk

Dokumen ini berisi panduan langkah demi langkah untuk mengonfigurasi dan mengaktifkan **Payment Gateway Duitku API v2.0** pada plugin DonasiYuk.

---

## 📋 Prasyarat

Sebelum memulai konfigurasi, pastikan Anda telah memiliki akun merchant Duitku:
- **Sandbox (Development)**: Halaman pendaftaran [Duitku Sandbox](https://sandbox.duitku.com)
- **Production (LIVE)**: Halaman pendaftaran [Duitku Production](https://passport.duitku.com)

---

## 🛠️ Langkah 1: Pengaturan Kredensial API Duitku

1. Masuk ke Dashboard **WP-Admin** WordPress Anda.
2. Buka menu **DonasiYuk** > **Settings** > tab **Payment**.
3. Klik sub-tab **Duitku** (terletak di sebelah kanan *Remit Cepat* pada bilah menu sub-tab).
4. Isikan formulir konfigurasi Duitku:
   - **Duitku Mode**: Pilih **Sandbox** *(untuk uji coba)* atau **LIVE (Production)** *(jika sudah siap menerima pembayaran nyata)*.
   - **Merchant Code**: Masukkan Kode Merchant yang didapat dari Dashboard Duitku (contoh: `D1234`).
   - **API Key**: Masukkan API Key dari Dashboard Duitku.
   - **Waktu Kedaluwarsa Transaksi**: Masukkan durasi kedaluwarsa dalam menit (Default: `1440` menit / 24 jam untuk Virtual Account, atau `60` menit untuk QRIS).
   - **Metode QRIS Duitku**: Pilih jenis QRIS yang diaktifkan di akun Duitku Anda (*ShopeePay QRIS / Universal QRIS [SP]* atau *Nobu QRIS [NQ]*).

5. **Pengaturan Callback URL (Webhook)**:
   - Salin URL Callback yang tertera pada halaman tersebut:
     - Mode Production: `https://domain-anda.com/callback_duitku`
     - Mode Sandbox: `https://domain-anda.com/callback_duitku_sandbox`
   - Buka Dashboard Merchant Duitku Anda, masuk ke menu **Proyek** > **Pengaturan Callback URL**, lalu tempelkan (*paste*) Callback URL tersebut.
6. Klik tombol **Update Duitku** di bagian bawah.

---

## 🏦 Langkah 2: Menghubungkan Channel Pembayaran ke Duitku

Setelah kredensial API tersimpan, langkah selanjutnya adalah menambahkan bank / metode pembayaran Duitku ke daftar metode pembayaran DonasiYuk.

1. Buka sub-tab **General** pada menu **Payment Settings**.

2. **Aktifkan Kategori Pembayaran:**
   - Pada bagian **Payment Method**, pastikan opsi yang ingin digunakan berada dalam kondisi **Active**:
     - Toggle **Virtual Account**: Ubah ke **Active** *(apabila ingin menerima VA Bank)*.
     - Toggle **Instant**: Ubah ke **Active** *(apabila ingin menerima QRIS / E-Wallet)*.
     - Toggle **Transfer**: Ubah ke **Active** *(apabila ingin menerima Retail / CC)*.

3. **Tambahkan Rekening / Channel Pembayaran:**
   Pada bagian **Bank Account**, klik tombol **+ Add Bank**, lalu isi kolom-kolomnya sesuai ketentuan berikut:

> [!IMPORTANT]
> **Kunci Identifikasi Gateway Duitku:**
> Pada kolom **Rek Atas Nama**, wajib mengetik kata **`duitku`** (huruf kecil). Kata ini digunakan oleh sistem DonasiYuk untuk mengarahkan transaksi secara otomatis ke gateway Duitku.

---

## 📌 Tabel Pemetaan Kode Channel Duitku (Cheat Sheet)

Berikut adalah panduan pengisian kolom **Bank Account** di sub-tab **General**:

### 1. Virtual Account (VA)

| Pilih Bank | No Rekening | Rek Atas Nama | Method | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Bank BCA** | `bca` *(atau `BC`)* | **`duitku`** | **Virtual Account** | BCA Virtual Account |
| **Bank Mandiri** | `mandiri` *(atau `M2`)* | **`duitku`** | **Virtual Account** | Mandiri Virtual Account |
| **Bank BRI** | `bri` *(atau `BR`)* | **`duitku`** | **Virtual Account** | BRIVA (BRI Virtual Account) |
| **Bank BNI** | `bni` *(atau `I1`)* | **`duitku`** | **Virtual Account** | BNI Virtual Account |
| **Bank Syariah Indonesia** | `bsi` *(atau `BV`)* | **`duitku`** | **Virtual Account** | BSI Virtual Account |
| **Permata Bank** | `permata` *(atau `BT`)* | **`duitku`** | **Virtual Account** | Permata Virtual Account |
| **Bank CIMB Niaga** | `cimb_niaga` *(atau `B1`)* | **`duitku`** | **Virtual Account** | CIMB Niaga Virtual Account |
| **Bank Danamon** | `danamon` *(atau `DM`)* | **`duitku`** | **Virtual Account** | Danamon Virtual Account |
| **Maybank** | `maybank` *(atau `VA`)* | **`duitku`** | **Virtual Account** | Maybank Virtual Account |
| **Bank Sahabat Sampoerna** | `sampoerna` *(atau `S1`)* | **`duitku`** | **Virtual Account** | Sampoerna Virtual Account |
| **Bank Artha Graha** | `bag` *(atau `AG`)* | **`duitku`** | **Virtual Account** | BAG Virtual Account |
| **Bank Neo Commerce** | `nc` | **`duitku`** | **Virtual Account** | Neo Commerce Virtual Account |

### 2. QRIS & E-Wallet

| Pilih Bank | No Rekening | Rek Atas Nama | Method | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **QRIS** | `qris` *(atau `SP`)* | **`duitku`** | **Instant** | Universal QRIS (ShopeePay / Nobu) |
| **ShopeePay** | `shopeepay` *(atau `SA`)* | **`duitku`** | **Instant** | ShopeePay App Direct |
| **OVO** | `ovo` *(atau `OV`)* | **`duitku`** | **Instant** | OVO |
| **DANA** | `dana` *(atau `DA`)* | **`duitku`** | **Instant** | DANA |
| **LinkAja** | `linkaja` *(atau `LA`)* | **`duitku`** | **Instant** | LinkAja |

### 3. Retail Outlet & Kartu Kredit

| Pilih Bank | No Rekening | Rek Atas Nama | Method | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| **Alfamart** | `alfamart` *(atau `FT`)* | **`duitku`** | **Transfer** | Payment via Indomaret / Alfamart |
| **Indomaret** | `indomaret` *(atau `IR`)* | **`duitku`** | **Transfer** | Payment via Indomaret |
| **Credit Card** | `cc` *(atau `VC`)* | **`duitku`** | **Transfer** | Kartu Kredit / Debit Online |

4. Klik tombol **Update** di bagian bawah sub-tab **General** untuk menyimpan perubahan.

---

## 🧪 Langkah 3: Pengujian Transaksi (Testing)

1. Buka salah satu halaman campaign donasi di website Anda.
2. Lakukan simulasi donasi dan pilih salah satu metode pembayaran Duitku (misal: *BCA Virtual Account* atau *QRIS*).
3. Klik **Donasi Sekarang**.
4. Di Halaman Terima Kasih (Invoice / Thank You Page):
   - Untuk **Virtual Account**: Nomor VA resmi Duitku akan tampil beserta tombol salin 1-klik.
   - Untuk **QRIS**: Barcode QRIS dinamis akan otomatis di-generate dan siap di-scan melalui e-wallet / m-banking.
   - Untuk **E-Wallet / CC**: Tombol *"Bayar Sekarang"* akan muncul mengarahkan langsung ke proses pembayaran.
5. Jika menggunakan **Mode Sandbox**, lakukan pembayaran simulasi di simulator Duitku.
6. Setelah pembayaran disimulasikan lunas, status donasi di DonasiYuk akan otomatis berubah dari **Pending** menjadi **Lunas (Status 1)** dan pengiriman notifikasi WhatsApp/Email tanda terima akan terhitung secara otomatis.

---

## ❓ Pertanyaan Sering Diajukan (FAQ) & Troubleshooting

### 1. Kenapa status donasi tidak berubah jadi lunas secara otomatis setelah dibayar?
- Pastikan Callback URL di dashboard Duitku sudah sesuai dengan URL website Anda (`https://domain-anda.com/callback_duitku` atau `/callback_duitku_sandbox`).
- Pastikan **API Key** yang dimasukkan di WP-Admin sama persis dengan yang ada di dashboard Duitku.

### 2. Kenapa opsi pembayaran Duitku tidak muncul di form donasi?
- Pastikan kategori pembayaran yang bersangkutan (*Virtual Account* atau *Instant*) dalam kondisi **Active** pada sub-tab **General**.
- Pastikan pada kolom **Rek Atas Nama** Anda menginput kata **`duitku`** (tanpa spasi dan huruf kecil).

---
*Dikembangkan & Dikelola untuk Plugin DonasiYuk.*
