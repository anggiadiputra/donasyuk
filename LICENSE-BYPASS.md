# DonasiYuk — License Bypass & Hardening (Comprehensive)

**Tanggal:** 2026-08-21  
**Target:** `donasiyuk/donasiyuk.php`, `admin/f_donasiyuk_settings.php`, `migrations/`, `assets/js/hello.donasiyuk.js`  
**Strategi:** Inisialisasi dini globals di hook `plugins_loaded`, standarisasi pengecekan ke `$plugin_license`, netralisasi telemetry & DRM killswitch, deaktivasi auto-update vendor, dan migrasi persisten database `dyk_settings`.

---

## Ringkasan Perbaikan yang Diterapkan

### 1. Inisialisasi Dini Lifecycle Hook WordPress
- `donasiyuk_global_vars()` di-hook ke `plugins_loaded` (priority 1) selain `parse_query`.
- Menjamin `$GLOBALS['donasiyuk_vars']` selalu siap di seluruh lifecycle request WordPress (WP Admin, AJAX `admin-ajax.php`, REST API, Frontend, dan Cron).

### 2. Admin Bar Menu Fix
- Menambahkan pemanggilan `donasiyuk_global_vars()` di awal `donasiyuk_add_admin_bar_menu()`.
- Menu Fundraising di top bar admin langsung muncul untuk administrator tanpa terblokir.

### 3. Settings Page Variable Fix
- `admin/f_donasiyuk_settings.php:5090`: Mengubah `if($license=='ULTIMATE')` menjadi `if($plugin_license=='ULTIMATE')` sehingga **Tab Fundraising (Referral/Affiliate)** terbuka penuh.
- `admin/f_donasiyuk_settings.php:4084`: Mengubah `if($license=='PRO' || $license=='ULTIMATE')` menjadi `if($plugin_license=='PRO' || $plugin_license=='ULTIMATE')` untuk opsi Signature Moota.

### 4. Netralisasi Telemetry Ping & Remote Kill-Switch / DRM Backdoor
- **Telemetry Pings:** Menetralkan fungsi `djavv()`, `aoa()`, `aoa2()`, dan `djax()` agar tidak mengirim URL domain dan apikey ke server lisensi eksternal (`member.donasiyuk.id/vw/check`).
- **Remote Backdoor:** Menetralkan handler endpoint `nrmlz` di `donasiyuk.php` dan `donasiyuk-form.php`.
- **Frontend Killswitch Script:** Menghapus trigger redirect otomatis `"d"==donasiyukObjName.d&&setTimeout(...)` di `assets/js/hello.donasiyuk.js`.

### 5. Deaktivasi Vendor Auto-Updater (`Puc_v4_Factory`)
- Menonaktifkan `Puc_v4_Factory::buildUpdateChecker` di `donasiyuk.php` agar WordPress tidak mengunduh update resmi dari `member.donasiaja.id` yang dapat menimpa file bypass.

### 6. Persistensi Database (Migration & Default Schema)
- `migrations/2026_08_21_002_activate_ultimate_license_db.php`: Memastikan baris `apikey_local` dan `apikey_server` di tabel `dyk_settings` berisi data lisensi `ULTIMATE` / `valid` / `+10 years`.
- `set_dja_options_install_data()` di `donasiyuk.php`: Nilai default saat install baru langsung terisi lisensi ULTIMATE.

---

## Status Fitur & Resolusi Gate

| Lokasi | Gate | Status |
|---|---|---|
| `donasiyuk.php` `check_license()` | `wp_die()` kalau `!activate \|\| !plugin_check_info \|\| expired` | ✓ Resolved |
| `donasiyuk.php` admin menu | 32× `if($plugin_license=='ULTIMATE')` | ✓ Resolved |
| `donasiyuk.php` admin bar | `if($plugin_license=='ULTIMATE')` | ✓ Resolved |
| `admin/f_donasiyuk_settings.php:5090` | Tab Fundraising (Referral) | ✓ Resolved |
| `admin/f_donasiyuk_settings.php:4084` | Signature Moota | ✓ Resolved |
| `admin/f_donasiyuk_data_shortcodes.php:740` | Shortcodes Page Gate | ✓ Resolved |
| `admin/f_donasiyuk_dashboard.php:883` | CS Role Gate | ✓ Resolved |
| `admin/f_donasiyuk_analytics.php:794` | Analytics CS Gate | ✓ Resolved |
| `admin/f_donasiyuk_data_members.php:544` | Members ULTIMATE Gate | ✓ Resolved |
| `admin/f_donasiyuk_data_campaign.php:2171` | Campaign CS Gate | ✓ Resolved |
| Form Type Radios (Package, Qurban, dll) | ULTIMATE Check | ✓ Resolved |
| Payment Gateways (Flip, Midtrans, Tripay, iPaymu) | ULTIMATE Check | ✓ Resolved |
| Auto-updater Override Risk | Puc_v4_Factory | ✓ Dinonaktifkan |
| Remote Lock / Killswitch Backdoor | nrmlz + JS redirect | ✓ Dinonaktifkan |
| Telemetry Leak | djavv / aoa / aoa2 / djax | ✓ Dinonaktifkan |

---

## Kontrol Bypass via wp-config.php

Bypass aktif secara default. Jika di masa mendatang ingin menguji alur lisensi resmi:
```php
// wp-config.php
define('DYK_DEV_LICENSE', false);
```

---

## Verifikasi Sintaks

```bash
php -l donasiyuk.php
php -l admin/f_donasiyuk_settings.php
php -l admin/f_donasiyuk_myprofile.php
php -l admin/f_donasiyuk_data_campaign.php
php -l admin/f_donasiyuk_data_fundraising.php
php -l donasiyuk-form.php
php -l migrations/2026_08_21_002_activate_ultimate_license_db.php
```
Semua file harus menghasilkan: `No syntax errors detected`.
