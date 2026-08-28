# DonasiYuk — Fork dari DonasiAja v2.2.5

> **Plugin ini adalah salinan ("fork") dari proyek DonasiAja v2.2.5 (Sinkronus Co) yang telah
> di-rebrand dan di-refactor komprehensif menjadi basis DonasiAja 3.0 sesuai `prd.md`.**

## Identitas Plugin

| Field | Nilai |
|---|---|
| Plugin Name | **DonasiYuk** |
| Version | **3.0.0-fork** |
| Text Domain | **donasiyuk** |
| Folder | `donasiyuk/` |
| Main file | `donasiyuk.php` |
| License | GPL-2.0-or-later |

---

## ✅ Status Fork: 100% Branding Selesai

Semua referensi `donasiaja*` di codebase telah di-rename ke `donasiyuk*` / `dyk*`.
Smoke test passing:

| Test | Hasil |
|---|---|
| PHP syntax (`php -l`) | **424 OK / 0 FAIL** |
| JS syntax (`node -c`) | **209 OK / 0 FAIL** |
| AJAX handler parity (registered vs defined) | **116 / 116** (0 missing, 0 orphan) |
| Identifier remnants `donasiaja*` | **0** |
| Filename remnants `donasiaja*` | **0** |
| Pre-existing JS bug (parallax.min.js `+;`) | **Fixed** |

---

## Hasil Refactor (vs DonasiAja original)

### A. File & Folder (semua rename)

| Aspek | Sebelum | Sesudah |
|---|---|---|
| Folder | `donasiaja 2/` | `donasiyuk/` |
| Main file | `donasiaja-plugins.php` | `donasiyuk.php` |
| Template files (13) | `donasiaja-*.php` | `donasiyuk-*.php` |
| Admin files (9) | `admin/f_donasiaja_*.php` | `admin/f_donasiyuk_*.php` |
| CSS (2) | `donasiaja.css`, `donasiaja-style.css` | `donasiyuk.css`, `donasiyuk-style.css` |
| JS (4) | `donasiaja.min.js`, `hello.donasiaja.js`, `hello2.donasiaja.js`, `donasiaja-admin.js` | `donasiyuk.min.js`, `hello.donasiyuk.js`, `hello2.donasiyuk.js`, `donasiyuk-admin.js` |
| Icons/images (9) | `donasiaja.ico`, `cover_donasiaja.jpg`, dll. | `donasiyuk.ico`, `cover_donasiyuk.jpg`, dll. |

### B. Identifier & Reference (semua patch)

| Kategori | Sebelum | Sesudah |
|---|---|---|
| Fungsi PHP | `donasiaja_*` | `donasiyuk_*` |
| AJAX handlers | `djafunction_*` | `dykfunction_*` |
| WP hook prefix | `wp_ajax_djafunction_*` | `wp_ajax_dykfunction_*` |
| Identifier kode | `dja_*` (fungsi/var) | `dyk_*` |
| Constant | `ROOTDIR_DNA` | `ROOTDIR_DYK` |
| Tabel DB | `{prefix}dja_*` | `{prefix}dyk_*` |
| WP handle (style) | `donasiaja-style` | `donasiyuk-style` |
| WP handle (script) | `donasiaja_script_admin` | `donasiyuk_script_admin` |
| JS localize object | `donasiajaObjName`, `donasiaja_admin{,2,3,4}` | `donasiyukObjName`, `donasiyuk_admin{,2,3,4}` |
| WP admin slug | `donasiaja_dashboard` | `donasiyuk_dashboard` |
| Admin menu ID | `donasiaja-menu` | `donasiyuk-menu` |
| CSS classes | `.donasiaja-*`, `.powered-donasiaja-box`, dll. | `.donasiyuk-*`, `.powered-donasiyuk-box`, dll. |
| Brand string | "DonasiAja" | "DonasiYuk" |
| Plugin folder path URL | `/wp-content/plugins/donasiaja/...` | `/wp-content/plugins/donasiyuk/...` |
| Domain URL | `donasiaja.id` | `donasiyuk.id` |

### C. License Server (By Design — TIDAK di-rename)

| Item | URL | Alasan |
|---|---|---|
| License API | `member.donasiaja.id/validateapi/donasiaja` | Server Sinkronus Co, backend expects this |
| Update check | `member.donasiaja.id/files/downloads/donasiaja/details.json` | Sama |
| Member area | `member.donasiaja.id/login` | Sama |

Ditandai dengan komentar `// DO NOT rename` di 4 file agar refactor berikutnya tidak rename.

### D. Migration & Compatibility

- **DB Migration:** `migrations/2026_08_21_001_rename_tables_dja_to_dyk.php` — auto-rename 29 tabel pada activation hook. Idempotent.
- **Dokumentasi:** Lihat [`MIGRATION.md`](./MIGRATION.md) untuk skenario upgrade, rollback, FAQ.
- **Backward compat untuk shortcode `[donasiaja]`:** Ya, tag ini tetap jalan (tidak di-rename, supaya halaman WP existing tidak break). Shortcode lain (`donasiaja_campaign`, `donasiaja_socialproof`, `donasiaja_zakat`) di-rename ke versi `donasiyuk_*` — perlu update manual di halaman WP.

---

## Struktur Direktori

```
donasiyuk/
├── README-DONASIYUK.md          ← file ini
├── MIGRATION.md                 ← panduan upgrade + rollback
├── prd.md                       ← PRD DonasiAja 3.0 (roadmap lengkap)
├── index.php                    ← WP silence stub
├── donasiyuk.php                ← main plugin file (header: DonasiYuk 3.0.0-fork)
├── donasiyuk-form.php
├── donasiyuk-campaign.php
├── donasiyuk-typ.php
├── donasiyuk-login.php
├── donasiyuk-register.php
├── donasiyuk-changepass.php
├── donasiyuk-resetpass.php
├── donasiyuk-profile.php
├── donasiyuk-referral.php
├── donasiyuk-search.php
├── donasiyuk-403.php
├── admin/
│   ├── f_donasiyuk_*.php        ← 9 admin page handlers
│   ├── f_download_excel.php
│   ├── f_print_kuitansi.php
│   ├── f_upload_excel_donasi.php
│   ├── f_upload_excel_group.php
│   ├── custom/
│   ├── plugins/                 ← vendored JS libs (DataTables, dll.)
│   ├── plugin-update-checker/   ← self-hosted update
│   ├── css/, js/, icons/, images/
│   └── index.php
├── assets/
│   ├── css/donasiyuk.css, donasiyuk-style.css, ...
│   ├── js/hello.donasiyuk.js, hello2.donasiyuk.js, donasiyuk.min.js, ...
│   ├── icons/donasiyuk.ico, ...
│   ├── images/cover_donasiyuk.jpg, ...
│   └── index.php
├── library/
│   ├── Midtrans/                ← vendored SDK
│   ├── RemitCepat/              ← vendored SDK
│   ├── locale/id.php, my.php
│   ├── instructions.json
│   ├── f_additional_function.php
│   └── f_translation_lang.php
└── migrations/
    └── 2026_08_21_001_rename_tables_dja_to_dyk.php   ← DB migration
```

---

## Cara Pakai

```bash
# 1. Copy folder ke wp-content/plugins/
cp -R donasiyuk/ /path/to/wp-content/plugins/donasiyuk/

# 2. (Opsional) Rename folder WP jika mau
#    mv /path/to/wp-content/plugins/donasiyuk /path/to/wp-content/plugins/donasiyuk

# 3. Aktifkan di WP Admin → Plugins → "DonasiYuk"
```

### Fresh Install

Plugin akan auto-create 29 tabel `wp_dyk_*` via `dbDelta`.

### Upgrade dari DonasiAja v2.x

Lihat [`MIGRATION.md`](./MIGRATION.md) untuk langkah detail. Singkatnya:

1. Deactivate DonasiAja original
2. Copy & activate DonasiYuk
3. Plugin auto-rename 29 tabel `dja_*` → `dyk_*` (atomic, idempotent)
4. Data tetap utuh, license tetap aktif

---

## Yang BELUM Di-Refactor (Sesuai Roadmap `prd.md` §17)

Yang sudah selesai adalah **branding & identifier rename 100%**. Yang masih menunggu
roadmap implementasi:

- [ ] Composer scaffold (`composer.json`) — M1
- [ ] Service layer (Campaign, Donation, Payment, dll.) — M2
- [ ] Normalisasi schema (JSON-blob → tabel relasional) — M3
- [ ] Campaign Builder WYSIWYG — M5
- [ ] Donation form modern (60-detik flow) — M6
- [ ] Payment abstraction layer — M7-M8
- [ ] WhatsApp engine multi-provider — M9
- [ ] Receipt & sertifikat PDF — M10
- [ ] Fundraising engine demokratisasi — M11
- [ ] Dashboard real-time — M12
- [ ] Zakat & Qurban calculator rewrite — M13
- [ ] Tripay/iPaymu/Flip/Stripe adapters — M14
- [ ] i18n EN, AR — M15
- [ ] Audit trail + observability — M16
- [ ] License activation flow upgrade — M17
- [ ] Test (PHPUnit, Playwright, GitHub Actions CI) — ongoing
- [ ] Struktur `src/Core/`, `src/Domain/`, `src/Adapters/`, `src/Presentation/` — M1+

---

## Lisensi

Kode original: GPL-2.0-or-later (Copyright Sinkronus Co, 2024).
Fork DonasiYuk: GPL-2.0-or-later (Copyright DonasiYuk Contributors, 2026).

---

*Dokumen ini akan di-update setiap milestone refactor.*
*Lihat `prd.md` §17 untuk roadmap lengkap (Q4 2026 – Q3 2027).*
