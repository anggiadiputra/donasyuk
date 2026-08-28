# MIGRATION.md — Cara Upgrade dari DonasiAja v2.x ke DonasiYuk 3.0

> **PENTING:** Backup database Anda sebelum melakukan migrasi.

---

## Ringkasan

DonasiYuk adalah fork rebranded dari DonasiAja v2.2.5. Saat plugin diaktifkan, secara otomatis
akan:

1. **Rename 29 tabel DB** dari `{prefix}dja_*` ke `{prefix}dyk_*`.
2. **Idempotent** — aman dijalankan berulang, tidak akan duplikasi atau kehilangan data.
3. **License tetap aktif** — API key dari DonasiAja original masih berlaku karena license server
   (`member.donasiaja.id`) tidak berubah.

---

## Skenario Upgrade

### Skenario A: Fresh Install (Tidak Pernah Pakai DonasiAja)

```bash
# 1. Copy plugin ke wp-content/plugins/
cp -R donasiyuk/ /path/to/wp-content/plugins/

# 2. Aktifkan di WP Admin → Plugins → "DonasiYuk"

# Plugin akan auto-create 29 tabel baru dengan prefix dyk_.
```

Tidak ada data existing untuk dimigrasi. Selesai.

### Skenario B: Upgrade dari DonasiAja v2.x (Tabel dja_* Sudah Ada)

```bash
# 1. DEACTIVATE plugin DonasiAja original dulu
#    WP Admin → Plugins → "DonasiAja" → Deactivate

# 2. Copy plugin DonasiYuk ke wp-content/plugins/
cp -R donasiyuk/ /path/to/wp-content/plugins/donasiyuk

# 3. Aktifkan DonasiYuk
#    WP Admin → Plugins → "DonasiYuk" → Activate
```

**Apa yang terjadi saat activation:**

1. Plugin load file `migrations/2026_08_21_001_rename_tables_dja_to_dyk.php`.
2. Migration `donasiyuk_migrate_rename_tables_dja_to_dyk()` jalan:
   - Untuk setiap 29 tabel `dja_*`: cek apakah `dyk_*` sudah ada. Jika ya, skip.
   - Jika `dja_*` ada dan `dyk_*` belum ada: jalankan `RENAME TABLE` (atomic MySQL).
   - Log hasil ke WP option `donasiyuk_last_migration`.
3. Setelah rename, plugin create tabel baru via `dbDelta` jika ada schema baru (saat ini belum, semua tabel sudah ada).
4. Plugin aktif normal.

**Verifikasi:**

```sql
-- Di phpMyAdmin / MySQL CLI:
SHOW TABLES LIKE 'wp_dyk_%';
-- Harusnya menampilkan 29 tabel dyk_*

SHOW TABLES LIKE 'wp_dja_%';
-- Harusnya KOSONG (semua sudah di-rename)
```

**Rollback (jika perlu kembali ke DonasiAja):**

```sql
-- Manual rename semua tabel balik
RENAME TABLE wp_dyk_settings TO wp_dja_settings;
RENAME TABLE wp_dyk_campaign TO wp_dja_campaign;
-- ... (29 tabel)
```

Atau restore dari backup database.

---

## Skenario C: Install Paralel (DonasiAja + DonasiYuk Sekaligus)

Plugin ini **bisa** di-install berdampingan dengan DonasiAja original **selama folder berbeda**:

```bash
# Di wp-content/plugins/
/wp-content/plugins/donasiaja/      ← original, folder tetap
/wp-content/plugins/donasiyuk/      ← fork, folder sudah rename
```

**Konflik potensial:**

| Konflik | Status |
|---|---|
| Header plugin | ✅ Aman — header beda (`DonasiAja` vs `DonasiYuk`) |
| Fungsi PHP | ❌ **KONFLIK** — nama fungsi internal masih shared (kami refactor bertahap) |
| Tabel DB | ✅ Aman (kalau prefix WP sama, tabel bentrok — lihat Solusi) |
| Option WP | ⚠️ Beberapa option name masih shared |
| Hook WP | ⚠️ Beberapa custom hook masih shared |

**Solusi untuk prefix tabel terpisah:**

Tambahkan di `wp-config.php` **sebelum** plugin DonasiYuk aktif:

```php
// Saat ini belum di-support — TODO di refactor berikutnya.
// Untuk sekarang, jangan install kedua plugin bersamaan dalam WP yang sama.
```

> **Rekomendasi:** Jangan install paralel. Pilih salah satu: DonasiAja original atau DonasiYuk.
> Jika ingin test, gunakan WP multisite dengan dua site berbeda.

---

## Yang Diubah Saat Migrasi

### Tabel DB (29 tabel, semua prefix `dja_` → `dyk_`)

```
wp_dja_settings               → wp_dyk_settings
wp_dja_campaign               → wp_dyk_campaign
wp_dja_campaign_update        → wp_dyk_campaign_update
wp_dja_category               → wp_dyk_category
wp_dja_donate                 → wp_dyk_donate
wp_dja_aff_code               → wp_dyk_aff_code
wp_dja_love                   → wp_dyk_love
wp_dja_payment_list           → wp_dyk_payment_list
wp_dja_register               → wp_dyk_register
wp_dja_shortcode              → wp_dyk_shortcode
wp_dja_users                  → wp_dyk_users
wp_dja_user_logs              → wp_dyk_user_logs
wp_dja_user_type              → wp_dyk_user_type
wp_dja_verification_details   → wp_dyk_verification_details
wp_dja_verification_status    → wp_dyk_verification_status
wp_dja_payment_log            → wp_dyk_payment_log
wp_dja_password_reset         → wp_dyk_password_reset
wp_dja_password_reset_log     → wp_dyk_password_reset_log
wp_dja_aff_click              → wp_dyk_aff_click
wp_dja_aff_submit             → wp_dyk_aff_submit
wp_dja_aff_payout             → wp_dyk_aff_payout
wp_dja_payment_callback       → wp_dyk_payment_callback
wp_dja_wilayah_malaysia       → wp_dyk_wilayah_malaysia
wp_dja_donate_trash           → wp_dyk_donate_trash
wp_dja_group_list             → wp_dyk_group_list
wp_dja_group_data             → wp_dyk_group_data
wp_dja_custom_followup_scheduler → wp_dyk_custom_followup_scheduler
wp_dja_blocked_ip             → wp_dyk_blocked_ip
wp_dja_blocked_whatsapp       → wp_dyk_blocked_whatsapp
```

### Option WP

Option name yang prefix `dja_*` atau `donasiaja_*` sebagian besar **ikut berubah** karena
kode sudah di-rename. Audit spesifik perlu dilakukan per environment.

### Shortcode (Penting untuk Konten WP)

| Shortcode lama | Shortcode baru | Status |
|---|---|---|
| `[donasiaja]` | `[donasiaja]` | ⚠️ **Tidak berubah** (tag user-facing) |
| `[donasiaja_campaign]` | `[donasiyuk_campaign]` | ⚠️ **Berubah** — perlu update di halaman WP |
| `[donasiaja_socialproof]` | `[donasiyuk_socialproof]` | ⚠️ **Berubah** — perlu update |
| `[donasiaja_zakat]` | `[donasiyuk_zakat]` | ⚠️ **Berubah** — perlu update |

**Catatan tentang `[donasiaja]`:** Tag shortcode ini sengaja TIDAK di-rename karena banyak halaman WP
existing sudah pakai tag ini. Jika Anda buat WP page baru dengan shortcode `[donasiyuk]`, tidak akan
jalan (kecuali ada handler). Solusi: tetap pakai `[donasiaja]` atau ganti semua page manual ke
`[donasiyuk]` setelah tambah handler-nya.

---

## Yang TIDAK Berubah (By Design)

| Item | Alasan |
|---|---|
| `member.donasiaja.id` (license server URL) | Server Sinkronus Co handle aktivasi license. Rename = aktivasi gagal. Ditandai dengan komentar "DO NOT rename" di 4 file. |
| `/validateapi/donasiaja` (API endpoint path) | Sama — server endpoint. |
| `/files/downloads/donasiaja/details.json` (plugin update URL) | Sama — server endpoint. |
| Folder `library/Midtrans/`, `library/RemitCepat/` (SDK vendor) | Library code milik provider, tidak boleh dimodifikasi. |
| Library `phpoffice/phpspreadsheet`, `plugin-update-checker` | Sama — vendor. |

---

## Verifikasi Pasca-Migrasi

```php
// Di WP-CLI atau PHP script
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}dyk_%'");
echo "DonasiYuk tables: " . count($tables) . " (expected: 29)\n";

$old_tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}dja_%'");
echo "Legacy dja_* tables: " . count($old_tables) . " (expected: 0)\n";

// Cek migration log
$log = get_option('donasiyuk_last_migration');
print_r($log);
```

Output yang diharapkan:
```
DonasiYuk tables: 29 (expected: 29)
Legacy dja_* tables: 0 (expected: 0)

Array (
  [migration] => rename_tables_dja_to_dyk
  [run_at] => 2026-08-21 12:34:56
  [migrated] => 29
  [skipped] => 0
  [errors] => Array ()
  ...
)
```

---

## Rollback Plan

Jika migrasi gagal atau Anda ingin kembali ke DonasiAja:

### Option 1: Restore Database Backup

```bash
# Restore dari backup sebelum migrasi
mysql -u user -p database < backup-before-donasiyuk.sql
```

### Option 2: Manual RENAME TABLE

```sql
-- Rename 29 tabel balik
RENAME TABLE wp_dyk_settings TO wp_dja_settings;
RENAME TABLE wp_dyk_campaign TO wp_dja_campaign;
-- ... dst untuk 29 tabel

-- Deactivate DonasiYuk
-- Activate DonasiAja original
```

### Option 3: Fresh Start

Drop semua tabel dyk_* dan install ulang DonasiAja:

```sql
DROP TABLE wp_dyk_settings, wp_dyk_campaign, ...;
-- Deactivate DonasiYuk, delete folder, activate DonasiAja
```

---

## FAQ

**Q: Apakah data donation lama saya aman?**
A: Ya. Migration hanya rename tabel, tidak modify data. Semua campaign, donation, user tetap utuh.

**Q: Apakah license key saya masih berlaku?**
A: Ya. License server tidak berubah. API key dari DonasiAja original masih bisa dipakai.

**Q: Bisa downgrade dari DonasiYuk ke DonasiAja?**
A: Bisa. Lihat Rollback Plan di atas. Tapi beberapa shortcode harus di-update manual di halaman WP.

**Q: Apakah perlu install ulang Midtrans/Tripay credential?**
A: Tidak. Settings disimpan di `wp_dyk_settings` (hasil rename dari `wp_dja_settings`). Semua credential tetap ada.

**Q: Berapa lama proses migrasi?**
A: ±1-5 detik untuk 29 tabel RENAME TABLE. Tidak ada downtime karena rename atomic.

**Q: Apakah campaign URL berubah?**
A: Tidak. Campaign slug disimpan per-campaign di tabel, tidak ter-impact rename tabel.

---

## Kontak Support

- **Bug report:** Buat issue di repo DonasiYuk
- **License/membership:** https://member.donasiaja.id (server asli, tidak berubah)
- **Dokumentasi:** Lihat `prd.md` di root untuk roadmap lengkap

---

*Dokumen ini bagian dari DonasiYuk 3.0.0-fork. Lisensi: GPL-2.0-or-later.*
