# DonasiAja 3.0 — Product Requirements Document (PRD)

> **Status:** Draft v1.0
> **Tanggal:** 21 Agustus 2026
> **Pemilik Produk:** Tim Produk DonasiAja (Sinkronus Co)
> **Versi Acuan Saat Ini:** 2.2.5 (`donasiaja-plugins.php:5`)
> **Target Rilis Stabil:** Q4 2026 — Q1 2027

---

## 1. Executive Summary

DonasiAja adalah plugin donasi WordPress terpopuler di Indonesia yang digunakan oleh yayasan, masjid, pesantren, komunitas, dan individu untuk menggalang dana secara online. Sejak versi 1.0, produk ini telah berkembang menjadi solusi end-to-end: campaign builder, payment gateway aggregator (Midtrans, Tripay, iPaymu, Flip, RemitCepat), WhatsApp follow-up automation, fundraising/affiliate engine, hingga kalkulator Zakat & Qurban.

**Versi 3.0** adalah *rewrite strategis* yang mempertahankan distribusi WP plugin dan model monetisasi API key yang sudah battle-tested, sekaligus merombak internal menjadi arsitektur berlapis (layered architecture), schema database ternormalisasi, UI modern, dan engine pembayaran abstrak yang pluggable. Tujuannya: tetap menjadi pilihan termudah bagi organizer non-teknis, tapi cukup scalable dan maintainable untuk melayani 10x lipat organizer aktif dan GMV (gross merchandise value) donasi.

**Tiga outcome bisnis utama:**
1. **Conversion donasi naik 25%** lewat form UX yang lebih cepat, fewer steps, dan payment-method availability yang lebih tinggi.
2. **Waktu aktivasi organizer turun dari ~2 jam ke <15 menit** lewat campaign builder WYSIWYG dan template preset.
3. **Churn organizer turun 40%** lewat dashboard real-time, follow-up otomatis, dan insight yang actionable.

---

## 2. Analisis Produk Saat Ini (v2.2.5)

Hasil audit kode dan struktur plugin di `/Users/zuraidasafitri/Downloads/donasiaja 2/`:

### 2.1 Snapshot Teknis

| Aspek | Nilai |
|---|---|
| Bahasa | PHP 7+/8 + jQuery + HTML/CSS hand-written |
| Runtime | WordPress (semua hook WP: `wp_ajax_*`, `admin_post_*`, shortcode, menu page) |
| Database | MySQL/MariaDB via `$wpdb`, **29 tabel** ber-prefix `wp_dja_*` |
| File plugin utama | `donasiaja-plugins.php` — **±16.000 baris** (monolith) |
| Template tambahan | `donasiaja-form.php` (243KB), `donasiaja-campaign.php` (106KB), `donasiaja-campaign2.php` (128KB, legacy duplicate), `donasiaja-typ.php` (74KB) |
| Auth | WP user + extension `dja_users` (org/personal, verifikasi, KTP) |
| Lokalisasi | 2 locale: `id.php` (Indonesia) + `my.php` (Malaysia/MYR) |
| Mata uang | IDR, MYR |
| Payment gateway | Midtrans (Snap + Core), Tripay, iPaymu, Flip, RemitCepat, Moota, Stripe, PayPal, COD, manual transfer (BSI/BCA/BRI/Mandiri/BNI/CIMB), QRIS, OVO, DANA, GoPay, ShopeePay, LinkAja, Jenius |
| Donasi form | 7 form type: Donation Card, Typing, Package, Qurban, Zakat Fitrah, Zakat Maal/Penghasilan, Zakat Pertanian, Infaq/Wakaf |
| Tracking | Facebook Pixel (multi), TikTok Pixel, Google Tag Manager (per campaign) |
| WA automation | Wanotif (background service) + WhatsApp Cloud API + Telegram fallback |
| Reporting | Excel export (phpspreadsheet), print kuitansi, sertifikat |
| License model | API key → tier FREE/PRO/ULTIMATE (gating fundraising, members) |
| Update mechanism | Self-hosted via `plugin-update-checker` v4.9 |

### 2.2 Skema Database (29 tabel `wp_dja_*`)

| Tabel | Tujuan |
|---|---|
| `dja_settings` | KV-store konfigurasi (`type`, `data`, `created_at`) |
| `dja_campaign` | Master campaign (slug, target, image, location, form_type, currency, category, payment_status, FB/TT/GTM pixel, fundraiser config, dsb.) |
| `dja_campaign_update` | Kabar/updates per campaign |
| `dja_category` | Kategori publik & private |
| `dja_donate` | Transaksi donasi (invoice, nominal, status, payment_method, UTM, payment_trx_id, dst.) |
| `dja_donate_trash` | Soft-delete |
| `dja_group_list` / `dja_group_data` | Bulk-upload group donations |
| `dja_aff_code` / `dja_aff_click` / `dja_aff_submit` / `dja_aff_payout` | Funnel fundraiser |
| `dja_love` | Like campaign per IP/user |
| `dja_payment_list` | Daftar payment method |
| `dja_payment_log` / `dja_payment_callback` | Raw & parsed gateway callback |
| `dja_register` | Pre-registration request |
| `dja_shortcode` | CRUD shortcode preset (kategori, style, loadmore, grid) |
| `dja_users` | WP-user extension (org/personal, verifikasi, bio, alamat, bank, komisi) |
| `dja_user_logs` / `dja_user_type` | Audit log + katalog tipe user |
| `dja_verification_details` / `dja_verification_status` | Upload KTP/selfie & status |
| `dja_password_reset` / `dja_password_reset_log` | Reset token + audit |
| `dja_custom_followup_scheduler` | Cron WA follow-up |
| `dja_wilayah_malaysia` | Referensi provinsi MY |
| `dja_blocked_ip` / `dja_blocked_whatsapp` | Blocklist |

### 2.3 Kekuatan yang Harus Dipertahankan

- **Distribusi WP plugin** — installer existing, hosting organizer tidak perlu migrasi.
- **Model API key license** — proven monetization, langganan tahunan, aktivasi via `member.donasiaja.id`.
- **SDK payment sudah ter-vendor** — Midtrans (`library/Midtrans/*`) dan RemitCepat (`library/RemitCepat/*`) tinggal di-upgrade, tidak ditulis ulang.
- **Multi-payment aggregator** — 9+ gateway sudah jalan.
- **Zakat & Qurban module** — pembeda unik dari kompetitor generik.
- **WhatsApp automation end-to-end** — Wanotif + WA Cloud + link fallback.

---

## 3. Pain Points & Gap Analysis

### 3.1 Kode & Arsitektur
- ❌ **`donasiaja-plugins.php` 16.000+ baris** — bootstrap, AJAX handler, admin pages, hooks semua di satu file. Tidak ada class, tidak ada namespace, globals everywhere.
- ❌ **Duplikasi** — `donasiaja-campaign.php` dan `donasiaja-campaign2.php` adalah versi paralel; keduanya hidup berdampingan.
- ❌ **JSON-blob di kolom DB** — `method_status`, `bank_account`, `form_text`, `cs_rotator`, `unique_number_value`, `flying_button_page_settings`, `payment_setting`, `custom_field_setting`, `additional_formula`, `additional_field`, `icon_setting`, `fb_event`, `metapixel_convertion_data` semuanya TEXT berisi JSON. Tidak ada validasi schema, tidak ada index.
- ❌ **Tidak ada test sama sekali** — nol PHPUnit, nol JS test, nol CI config.
- ❌ **Vendor SDK tidak via Composer terpusat** — Midtrans & RemitCepat di-copy paste ke `library/`. phpspreadsheet & plugin-update-checker punya Composer sendiri-sendiri.
- ❌ **License logic tersebar** — check `ULTIMATE` di banyak titik (`donasiaja-plugins.php:87`, `:28507`), tanpa class terpusat.
- ❌ **Form submission via URL params** — `?total=…&opt=…&select=…&gram=…&kg=…&pendapatan1=…&pengeluaran=…&option_zakat=…` di `donasiaja-form.php:578-613`. Rentan manipulasi client-side.
- ❌ **Hardcoded Bootstrap CDN** di `library/f_additional_function.php:33-34` untuk zakat shortcode — masalah keamanan (SRI hash), bundle size, dan offline mode.

### 3.2 UX & Produk
- ❌ **Form donasi multi-step** tanpa autosave, jika user refresh data hilang.
- ❌ **Captcha popup** (`popup-captcha-overlay`) terpisah dari form utama, friction tinggi.
- ❌ **Social proof** hanya static list, tidak ada personalisasi.
- ❌ **Campaign builder** masih form-field biasa, bukan drag-drop.
- ❌ **Dashboard admin** sangat panjang (`f_donasiaja_dashboard.php` 444KB) — semua data dimuat sekaligus, lambat di campaign besar.
- ❌ **Receipt (kuitansi) print** masih HTML biasa, bukan PDF yang bisa di-branding.
- ❌ **Mobile responsiveness** masih ada breakpoint 380/480px manual — tidak ada design token.
- ❌ **Fundraising** masih gated ULTIMATE — barrier adopsi.

### 3.3 Operasional
- ❌ **Logging** tersebar, tidak ada audit trail terstruktur untuk debugging payment dispute.
- ❌ **Refund & dispute handling** belum ada alur jelas di Midtrans notification handler.
- ❌ **Backup & migration** schema dilakukan manual via dbDelta, tidak ada versioning.
- ❌ **i18n** hanya ID & MY; belum ada EN, AR (untuk donor Timur Tengah).
- ❌ **Aksesibilitas** — tidak ada ARIA labeling, kontras warna tidak dijamin WCAG.

---

## 4. Visi & Tujuan v3.0

### 4.1 Visi Produk
> **"Platform donasi digital paling mudah di Asia Tenggara — bagi organizer non-teknis, scalable untuk power user."**

### 4.2 Prinsip Desain
1. **Donatur first** — form donasi < 60 detik dari landing sampai payment initiated.
2. **Organizer empowerment** — campaign live dalam <15 menit, dashboard insight tanpa training.
3. **Developer friendly** — composer-based, hook-able, white-label-ready.
4. **Compliant by default** — PCI scope minimal, GDPR-friendly, UU PDP Indonesia aware.
5. **Observable** — setiap transaksi, webhook, dan cron job punya trace ID.

### 4.3 North-Star Metric
- **Weekly Active Organizer (WAO)** yang menerima donasi ≥1 dalam 7 hari.
- Target: 5x lipat dalam 12 bulan pasca-rilis.

---

## 5. Persona & Use Case

### 5.1 Persona

| Persona | Deskripsi | Job-to-be-Done | Frustrasi Saat Ini |
|---|---|---|---|
| **Pak Asep — Organizer Yayasan** | Admin yayasan Islam di Bandung, 45 tahun, bukan tech-savvy. Kelola 8 campaign (yatim, qurban, dakwah). | "Saya mau fokus ke program, bukan debug plugin." | Dashboard lambat, campaign builder ribet, WA followup harus manual. |
| **Sari — Fundraiser Individu** | Guru SD yang galang dana untuk kelas, 28 tahun, aktif sosmed. | "Saya butuh link custom dan bukti komisi otomatis." | Fundraising cuma di ULTIMATE, leaderboard tidak real-time. |
| **Budi — Donatur Rutin** | Karyawan Jakarta, 32 tahun, rutin donasi via WA group. | "Saya mau donasi tanpa harus isi form panjang." | Form banyak field, payment method favorit (DANA/OVO) kadang tidak muncul. |
| **Rina — Admin Platform (Sinkronus)** | Tech lead DonasiAja, maintain plugin, handle support ticket. | "Saya butuh log yang bisa di-trace dan rollback aman." | Kode monolith, refund flow tidak jelas, schema migration manual. |

### 5.2 Use Case Prioritas (MVP)
- **UC-01:** Organizer create campaign qurban dalam <10 menit dengan template preset + 5 paket hewan.
- **UC-02:** Donatur landing di `/campaign/{slug}`, pilih nominal, bayar via QRIS, dapat kuitansi PDF dalam 30 detik.
- **UC-03:** Fundraiser dapat link referral, dashboard komisi real-time, payout request via sistem.
- **UC-04:** Admin yayasan terima WA otomatis setiap donasi masuk + reminder bulanan.
- **UC-05:** Admin platform investigasi payment dispute via trace ID, lihat semua event dari create sampai callback.

---

## 6. Ruang Lingkup

### 6.1 In-Scope (v3.0)
- Refactor `donasiaja-plugins.php` → layered: `Core`, `Domain`, `Adapters`, `Presentation`.
- Normalisasi schema: JSON-blob → tabel relasional (`dja_campaign_bank`, `dja_campaign_payment_method`, `dja_campaign_cs`, `dja_form_field`, dst.).
- Campaign builder WYSIWYG (React island di admin, render server tetap PHP).
- Donation form modern (progressive enhancement, vanilla JS + React island untuk interactivity).
- Payment abstraction layer (interface + adapter Midtrans/Xendit/DOKU/Stripe/Tripay/iPaymu).
- WhatsApp engine (template, scheduler, opt-in/out, delivery report) — multi-provider: Wanotif, Whacenter, Wagateway, WA Cloud.
- Receipt & sertifikat PDF (DOMPDF).
- Zakat & Qurban calculator modern (replace inline Bootstrap CDN).
- Fundraising engine — free untuk semua tier, ULTIMATE dapat leaderboard + custom commission.
- License & multi-tenant (API key, webhook aktivasi, member area sync).
- Composer-based dependency management (`composer.json` di root).
- PHPUnit test untuk service layer, Playwright untuk E2E form.
- GitHub Actions CI (lint, test, build assets).
- Lokalisasi EN, AR tambahan; ID & MY tetap.
- Observability: structured log, trace ID, audit trail.
- Design system (token, komponen dasar) dengan dokumentasi internal.

### 6.2 Out-of-Scope (v3.0)
- Native mobile app (Fase 2).
- AI-powered fraud detection (Fase 2, adopsi rule-based dulu).
- Marketplace publik multi-organizer seperti Kitabisa (Fase 4 SaaS hosted).
- Cryptocurrency donation (eksplorasi Fase 3).
- On-chain receipt (eksplorasi Fase 3).

---

## 7. Arsitektur Target

### 7.1 Diagram Komponen

```
┌─────────────────────────────────────────────────────────────────────┐
│ PRESENTATION                                                        │
│  • Admin:  React island (Vite build) mounted di WP admin page       │
│  • Front:  PHP templates + progressive-enhanced JS                  │
│  • Email:  MJML templates (compile to HTML)                         │
└─────────────────────────┬───────────────────────────────────────────�
                          │
┌─────────────────────────▼───────────────────────────────────────────┐
│ DOMAIN (Use Cases & Business Rules)                                  │
│  • CampaignService       • DonationService                          │
│  • PaymentService        • FundraiserService                        │
│  • ZakatService          • WhatsAppService                          │
│  • ReceiptService        • AnalyticsService                         │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────────────┐
│ ADAPTERS (Boundary Translators)                                     │
│  • PaymentGatewayInterface → MidtransAdapter, XenditAdapter,        │
│                               DokuAdapter, StripeAdapter, ...       │
│  • WhatsAppProviderInterface → WanotifAdapter, WhacenterAdapter,    │
│                                  WagatewayAdapter, CloudAdapter     │
│  • TrackingInterface → FacebookPixel, TikTokPixel, GTM              │
│  • LicenseProviderInterface → MemberAreaAdapter                     │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
┌─────────────────────────▼───────────────────────────────────────────┐
│ CORE (Framework & Infrastructure)                                   │
│  • HooksRegistry     • MigrationRunner    • SettingsRepository      │
│  • EventBus          • Logger (Psr\Log)   • CacheAdapter (WP trans) │
│  • AuthContext       • I18n               • Validator               │
└─────────────────────────────────────────────────────────────────────┘
```

### 7.2 Struktur Direktori (Target)

```
donasiaja/
├── donasi-aja.php                 # Plugin header (rename dari donasiaja-plugins.php)
├── composer.json                  # Dep terpusat
├── phpunit.xml.dist
├── .github/workflows/ci.yml
├── src/
│   ├── Core/
│   │   ├── Bootstrap.php          # Container, hook registry
│   │   ├── Migration/             # Schema version + migrator
│   │   ├── Event/                 # PSR-14 EventBus
│   │   ├── Logging/               # Structured logger
│   │   ├── Cache/                 # WP transients adapter
│   │   └── I18n/                  # Locale loader (replace library/locale)
│   ├── Domain/
│   │   ├── Campaign/              # Entity + Service + Repository
│   │   ├── Donation/              # Entity + Service + Repository
│   │   ├── Payment/
│   │   │   ├── Gateway/           # Interface + Adapters
│   │   │   └── Receipt/
│   │   ├── Fundraiser/
│   │   ├── Zakat/
│   │   ├── WhatsApp/
│   │   └── User/
│   ├── Adapters/
│   │   ├── Payment/
│   │   │   ├── Midtrans/          # upgrade library/Midtrans/*
│   │   │   ├── Xendit/
│   │   │   ├── Tripay/
│   │   │   ├── Doku/
│   │   │   ├── Stripe/
│   │   │   ├── Ipaymu/
│   │   │   ├── Flip/
│   │   │   └── RemitCepat/        # upgrade library/RemitCepat/*
│   │   ├── WhatsApp/
│   │   │   ├── Wanotif/
│   │   │   ├── Whacenter/
│   │   │   ├── Wagateway/
│   │   │   └── Cloud/
│   │   └── Tracking/
│   ├── Presentation/
│   │   ├── Admin/                 # React island + PHP page
│   │   ├── Front/                 # PHP templates (replace donasiaja-*.php)
│   │   └── Shortcodes/            # [donasiaja], [donasiaja_campaign], dll.
│   └── Templates/                 # Twig-like (.tpl) → compile to PHP
├── assets/
│   ├── css/                       # Design system tokens
│   ├── js/                        # compiled front-end
│   └── images/                    # bank logos, qurban, emoji
├── admin/
│   ├── plugins/                   # Admin-only vendored libs (DataTables, dll)
│   ├── custom/                    # Print templates
│   └── plugin-update-checker/     # tetap, update via Composer mirror
├── migrations/                    # SQL & PHP migrator classes
├── tests/
│   ├── Unit/                      # PHPUnit
│   ├── Integration/
│   └── E2E/                       # Playwright
├── docs/
│   ├── adr/                       # Architecture Decision Records
│   └── api/
└── prd.md                         # this file
```

### 7.3 Prinsip Arsitektur
- **Hexagonal / Ports & Adapters** — Domain tidak tahu MySQL, Midtrans, atau WP detail.
- **Event-driven** — `DonationPaid` event di-emit sekali, listener WA, Receipt, Analytics masing-masing handle.
- **Single Responsibility** — setiap class punya satu alasan untuk berubah.
- **Dependency Injection** — pakai Pimple atau PHP-DI; service container di-bootstrap sekali per request.
- **Fail-safe** — payment callback gagal retry 3x dengan exponential backoff; idempotency key berbasis invoice_id.

---

## 8. Data Model Baru

### 8.1 Migrasi dari JSON-Blob ke Tabel Relasional

| Kolom JSON Lama (v2.x) | Tabel Baru (v3.0) |
|---|---|
| `dja_campaign.method_status` (JSON: instant/va per bank) | `dja_campaign_payment_method (campaign_id, payment_method_id, method_type, status, config_json)` |
| `dja_campaign.bank_account` (JSON: list bank) | `dja_campaign_bank_account (campaign_id, bank_code, account_number, account_name, qris_image)` |
| `dja_campaign.cs_rotator` (JSON: priority cs) | `dja_campaign_cs (campaign_id, user_id, priority, weight)` |
| `dja_campaign.form_text` | `dja_campaign_form_text (campaign_id, locale, key, value)` |
| `dja_campaign.unique_number_value` | `dja_campaign_unique_number (campaign_id, mode, fixed_value)` |
| `dja_campaign.fb_event` | `dja_campaign_pixel (campaign_id, channel, event_name, payload_template)` |
| `dja_campaign.tiktok_*` & `gtm_*` | gabung ke `dja_campaign_pixel` dengan discriminator |
| `dja_campaign.fundraiser_*` | `dja_campaign_fundraiser (campaign_id, commission_type, commission_value)` |
| `dja_campaign.additional_field` & `additional_formula` | `dja_form_field (id, campaign_id, type, label, formula, validation)` |
| `dja_campaign.custom_field_setting` | gabung ke `dja_form_field` |
| `dja_campaign.icon_setting` | `dja_campaign_icon (campaign_id, position, icon_key, value)` |
| `dja_campaign.socialproof_*` | `dja_socialproof_config (campaign_id, mode, sample_size, refresh_sec)` |
| `dja_campaign.flying_button_*` | `dja_floating_button (campaign_id, type, page_target, content)` |
| `dja_campaign.payment_setting` | `dja_campaign_payment_config (campaign_id, gateway_id, config_json, priority)` |
| `dja_campaign.metapixel_convertion_data` | `dja_pixel_conversion (campaign_id, channel, event, mapping_json)` |
| `dja_campaign.notification_*` & `wanotif_message` | `dja_notification_template (campaign_id, channel, trigger, template_id)` |
| `dja_campaign.allocation_title` | `dja_campaign_allocation (campaign_id, title, target_amount)` |
| `dja_donate.f0..f5` (JSON: custom field values) | `dja_donation_field_value (donate_id, field_id, value)` |
| `dja_donate.info_donate/qurban/package2/zfitrah/zmaal` | `dja_donation_meta (donate_id, key, value)` (key-value) |
| `dja_settings.type='fundraiser_on'` dll | tetap KV, tapi tambah tabel `dja_settings_group` untuk pengelompokan |

### 8.2 Tabel Baru Pendukung

- `dja_payment_method_catalog` — katalog payment method (sebelumnya hardcoded di JS).
- `dja_gateway_catalog` — katalog gateway (Midtrans/Xendit/dll.) + config default.
- `dja_event_log` — observability: setiap event penting (donation.created, payment.callback, wa.sent).
- `dja_audit_trail` — actor, action, before, after (untuk user & donation).
- `dja_subscription_plan` — paket langganan.
- `dja_license` — API key, tier, activated_at, expires_at, signature.
- `dja_refund` — refund flow (request → gateway execute → status).
- `dja_dispute` — payment dispute ticket.
- `dja_webhook_endpoint` — outbound webhook per organizer (Fase 2 prep).

### 8.3 Schema Versioning
- `dja_schema_version (version INT, applied_at TIMESTAMP)` — single source of truth.
- Migration classes di `migrations/` — `Migration_2026_08_22_001_Normalize_Campaign_Fields.php`.
- Up & Down method setiap migration untuk rollback aman.
- Run saat activation hook + manual trigger via WP-CLI.

### 8.4 Deprecations
- ❌ Hapus `donasiaja-campaign2.php` setelah migration selesai (Campaign v2 dipindah sebagai "classic theme", tidak default lagi).
- ❌ Hapus `library/f_additional_function.php:33-34` inline Bootstrap CDN — bundle via Vite.
- � Hapus kolom JSON-blob setelah data di-migrasi ke tabel baru.

---

## 9. Fitur MVP (Fase 1) — Q1–Q3 2026

### 9.1 Campaign Builder v2
- **Mode:** Drag-drop section (Hero, Story, Update, Donation Form, Fundraiser, Social Proof, FAQ).
- **Preset:** 8 template (Qurban, Zakat Fitrah, Yatim Piatu, Medis, Bencana, Pendidikan, Wakaf, Custom).
- **Editor:** WYSIWYG dengan auto-save per 5 detik, version history.
- **Asset:** Upload image via WP media, embed YouTube, gallery.
- **Publish:** Schedule publish, draft preview, A/B variant (Fase 2).
- **Acceptance:** Organizer dapat create & publish campaign qurban dalam ≤10 menit tanpa reload.

### 9.2 Donation Form Modern
- **Single-page, progressive step:** Pilih Nominal → Pilih Metode → Data Diri → Bayar.
- **Sticky summary** di desktop, **bottom-sheet** di mobile.
- **Save & resume** — localStorage + URL token, user bisa balik dalam 7 hari.
- **Smart payment routing** — sembunyikan method yang sering gagal di device/IP tsb.
- **Captcha** — Cloudflare Turnstile (tanpa friction), fallback invisible reCAPTCHA.
- **Anonymous toggle** + **doa/comment** optional.
- **Tracking event** lengkap ke FB/TT/GTM (InitiateCheckout, AddPaymentInfo, Purchase).
- **Acceptance:** TTFB <300ms, full submission <60 detik.

### 9.3 Payment Abstraction Layer
- **Interface `PaymentGatewayInterface`:**
  ```php
  interface PaymentGatewayInterface {
      public function createCharge(ChargeRequest $req): ChargeResult;
      public function getStatus(string $gatewayTrxId): PaymentStatus;
      public function cancel(string $gatewayTrxId): bool;
      public function refund(RefundRequest $req): RefundResult;
      public function handleWebhook(Request $req): WebhookEvent; // verified signature
  }
  ```
- **Adapter list (MVP):**
  - **Midtrans** (Snap + Core + Notification) — upgrade `library/Midtrans/*`.
  - **Xendit** — invoice + virtual account + e-wallet + QRIS.
  - **DOKU** — backup untuk organizer yang butuh.
  - **Tripay** — upgrade existing handler.
  - **iPaymu** — upgrade existing handler.
  - **Stripe** — CC global.
  - **PayPal** (Fase 2 — international donors).
  - **Manual Transfer** — tetap untuk organizer tanpa gateway.
- **Idempotency:** `idempotency_key = sha256(campaign_id + invoice_id + amount + ts_bucket_5min)`.
- **Retry:** Exponential backoff 1s, 4s, 16s; total max 3x.
- **Fallback chain:** organizer bisa set primary + fallback gateway; otomatis pindah jika primary timeout/gagal.
- **Currency:** IDR primary, MYR supported, USD via Stripe (Fase 2).
- **Acceptance:** Switching gateway ≤1 config change, no code edit.

### 9.4 Dashboard Real-Time
- **WebSocket/SSE** (atau WP heartbeat long-poll sebagai fallback) untuk event "donasi masuk".
- **Cards:** Total Donasi, Jumlah Donasi, Conversion Rate, Donatur Unik.
- **Charts:** Trend 7/30/90 hari (ApexCharts / Chart.js).
- **Top campaigns, top fundraiser, top payment method.**
- **Export:** CSV + Excel (.xlsx via phpspreadsheet).
- **Acceptance:** Update <2 detik setelah payment success webhook.

### 9.5 WhatsApp Automation Engine
- **Template engine:** Twig-like syntax `{{donatur.name}}`, `{{campaign.title}}`, `{{nominal.formatted}}`.
- **Trigger:** Donation created, payment success, payment expired, X days after (cron).
- **Multi-provider:** Wanotif, Whacenter, Wagateway, WA Cloud API.
- **Opt-in/Opt-out:** reply `STOP` auto-handle, simpan di `dja_wa_optout`.
- **Delivery report:** callback provider → update `dja_wa_log (status, provider_msg_id, error)`.
- **Auto-reply:** keyword-based (`INFO`, `KUITANSI`, `STATUS`).
- **Template library:** preset (terima kasih, kuitansi, reminder, re-engagement 30/60/90 hari).
- **Acceptance:** WA terkirim dalam ≤30 detik setelah trigger, delivery report 95% akurat.

### 9.6 Receipt & Sertifikat PDF
- **Library:** DOMPDF atau mPDF.
- **Template:** drag-drop sederhana (header, logo, isi, footer, signature).
- **Auto-generate** setiap payment success; simpan di WP media.
- **Email + WA link** ke donatur.
- **Custom branding** per organizer (Pro/ULTIMATE).
- **Acceptance:** PDF <2 detik, ukuran <500KB, signed URL 30 hari.

### 9.7 Zakat & Qurban Calculator Modern
- **Zakat Maal:** nisab 85x harga emas, opsi pengeluaran, support Pertanian (5% jika irigasi, 10% jika non).
- **Zakat Fitrah:** uang/beras/gabah, harga configurable.
- **Zakat Penghasilan:** gross/nett method.
- **Qurban:** 5 hewan (Domba/Kambing/Sapi/Kerbau/Unta), individu/kolektif, 7 orang/sapi.
- **Inline shortcode** `[donasiaja_zakat type="maal" link="/campaign/zakat"]` — embed di page mana pun.
- **Acceptance:** Bundle inline ≤20KB gzipped, no CDN dependency.

### 9.8 Fundraising Engine (Demokratisasi)
- **Free untuk semua tier** — menurunkan barrier adopsi.
- **Link builder:** organizer atau individu generate link referral custom.
- **Komisi:** percent atau fixed per campaign; default 5% (configurable).
- **Leaderboard:** real-time per campaign, top 10.
- **Dashboard fundraiser:** klik, konversi, komisi earned, payout history.
- **Payout request** → admin approve → transfer manual atau auto (jika gateway support).
- **Acceptance:** Fundraiser onboard ≤2 menit via WA/email magic link.

### 9.9 License & Multi-Tenant
- **API key model** tetap (kompatibilitas) — disimpan di `dja_license`.
- **Tier:** Free / Pro / ULTIMATE dengan feature matrix jelas (lihat §16).
- **Aktivasi:** online via `member.donasiaja.id` webhook, atau offline license file untuk enterprise.
- **Grace period:** 7 hari setelah expired sebelum feature dipangkas.
- **Multi-tenant prep:** schema support multi-org dalam satu install (siap untuk SaaS Fase 4).
- **Acceptance:** Aktivasi ≤30 detik (online), 0 downtime saat deactivate/reactivate.

---

## 10. Fase 2 (Post-MVP) — Q4 2026 – Q2 2027

| Fitur | Tujuan | Effort |
|---|---|---|
| **Recurring Donation** | Donatur bisa set donasi bulanan otomatis via Midtrans/Xendit subscription API. | M |
| **Donation Matching (CSR)** | Perusahaan bisa match donasi karyawan 1:1 atau capped. | L |
| **A/B Testing Payment Method** | Test ordering payment method per segment untuk naikkan konversi. | M |
| **Analytics Cohort** | Retention donatur per cohort campaign. | M |
| **Outbound Webhook** | Organizer bisa subscribe event ke sistem internal (CRM, accounting). | M |
| **Mobile SDK (React Native)** | Embed donation form di app organizer. | L |
| **AI Categorization** | Auto-tag campaign untuk discovery & fraud signal. | L |
| **Localization EN, AR** | Donor global, komunitas Timur Tengah. | S |
| **Push Notification (Web)** | Organizer dapat notif donasi masuk di browser. | S |

---

## 11. Payment Gateway Abstraction — Detail Kontrak

```php
namespace DonasiAja\Payment;

interface PaymentGatewayInterface
{
    public function getId(): string;            // 'midtrans', 'xendit', ...
    public function getDisplayName(): string;
    public function supports(Capability $cap): bool;
    public function createCharge(ChargeRequest $req): ChargeResult;
    public function getTransaction(string $gatewayTrxId): TransactionStatus;
    public function cancel(string $gatewayTrxId): CancelResult;
    public function refund(RefundRequest $req): RefundResult;
    public function verifyWebhookSignature(Request $req): bool;
    public function parseWebhook(Request $req): WebhookEvent;
}

interface PaymentGatewayRegistryInterface
{
    public function register(PaymentGatewayInterface $gw): void;
    public function get(string $id): PaymentGatewayInterface;
    public function forCampaign(int $campaignId): PaymentGatewayInterface; // pick by config
    public function withFallback(int $campaignId): PaymentGatewayChain;   // chain w/ fallback
}
```

**Capability flags:** `SNAP_REDIRECT`, `WEBHOOK`, `REFUND`, `RECURRING`, `QRIS`, `VIRTUAL_ACCOUNT`, `EWALLET`, `CREDIT_CARD`, `MANUAL_TRANSFER`.

**Webhook contract:** Setiap webhook masuk → normalisasi ke `WebhookEvent { event_type, gateway_trx_id, amount, status, raw, received_at }` → dispatch ke `EventBus` → listener update `Donation.status`, kirim WA, generate receipt.

---

## 12. WhatsApp Automation Engine — Detail

### 12.1 Arsitektur
```
┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
│ Trigger Sources  │───▶│  Template Engine │───▶│  Provider Router │
│ - donation.*     │    │  (Twig-like)     │    │  (Adapter pick)  │
│ - cron scheduled │    │                  │    │                  │
│ - inbound reply  │    │                  │    │                  │
└──────────────────┘    └──────────────────┘    └────────┬─────────┘
                                                         │
                                                         ▼
                                              ┌──────────────────┐
                                              │  Delivery Report │
                                              │  + Auto-retry    │
                                              │  + Opt-out scan  │
                                              └──────────────────┘
```

### 12.2 Kontrak
```php
interface WhatsAppProviderInterface {
    public function send(WhatsAppMessage $msg): SendResult;
    public function supports(WhatsAppCapability $cap): bool;
}

interface WhatsAppMessage {
    public string $to;          // E.164
    public string $templateId;  // registered template
    public array $vars;         // template variables
    public ?string $replyToId;  // for threading
}
```

### 12.3 Template Library (MVP)
| Template ID | Trigger | Channel |
|---|---|---|
| `donation.thanks` | payment success | WA + Email |
| `donation.receipt` | payment success | WA (PDF link) |
| `donation.kuitansi` | on demand | WA + Email |
| `reminder.unpaid` | +1 jam setelah create, status pending | WA |
| `reengage.30d` | 30 hari setelah donation terakhir | WA |
| `reengage.60d` | 60 hari | WA |
| `reengage.90d` | 90 hari | WA |
| `fundraiser.welcome` | fundraiser onboard | WA + Email |
| `fundraiser.payout` | payout approved | WA + Email |

---

## 13. Design System

### 13.1 Token (CSS variables)
```css
:root {
  --color-primary: #7680ff;       /* default brand */
  --color-primary-rgb: 118, 128, 255;
  --color-success: #10b981;
  --color-warning: #f59e0b;
  --color-danger: #ef4444;
  --color-text: #111827;
  --color-text-muted: #6b7280;
  --color-bg: #ffffff;
  --color-bg-alt: #f9fafb;

  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 16px;

  --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
  --shadow-md: 0 4px 6px rgba(0,0,0,0.07);
  --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);

  --font-sans: 'Inter', system-ui, sans-serif;
  --font-arabic: 'Amiri', serif;
  --font-mono: 'JetBrains Mono', monospace;

  --space-1: 4px; --space-2: 8px; --space-3: 12px;
  --space-4: 16px; --space-5: 24px; --space-6: 32px; --space-7: 48px;

  --bp-sm: 480px; --bp-md: 768px; --bp-lg: 1024px; --bp-xl: 1280px;
}
```

### 13.2 Komponen Inti (Storybook internal)
- `<Card>` — campaign card, donate card, receipt card.
- `<Button>` variants: primary, secondary, ghost, danger; sizes: sm, md, lg.
- `<FormField>` — input, textarea, select, radio-card, checkbox, file.
- `<Modal>` + `<BottomSheet>` — responsive.
- `<Stepper>` — donation funnel.
- `<Toast>` — feedback.
- `<ProgressBar>` — campaign progress.
- `<Countdown>` — campaign end date.
- `<Avatar>`, `<Badge>`, `<Chip>`, `<Tooltip>`.

### 13.3 Prinsip
- Mobile-first.
- WCAG 2.1 AA (kontras, ARIA, keyboard nav).
- RTL ready (untuk AR).
- Reduced motion respect (`prefers-reduced-motion`).

---

## 14. Non-Functional Requirements

### 14.1 Performance
| Metric | Target |
|---|---|
| TTFB halaman campaign | <300 ms |
| Form donation interaktif (TTI) | <1.5 s |
| Dashboard load | <2 s untuk 10K donasi |
| Bundle JS front-end | <120 KB gzipped |
| Bundle CSS | <40 KB gzipped |
| Webhook processing | <500 ms p95 |
| Lighthouse Performance | ≥90 |

### 14.2 Keamanan
- **CSRF:** nonce untuk setiap form/AJAX (WP built-in + custom untuk non-AJAX).
- **Sanitasi:** `sanitize_text_field`, `esc_url`, `wp_kses_post` di semua output + input boundary.
- **SQL Injection:** 100% via `$wpdb->prepare()` — code review CI step.
- **XSS:** semua user-content melalui `esc_html` / template engine auto-escape.
- **Payment:** idempotency key + signature verification wajib untuk webhook.
- **Rate limit:** form submit, login, password reset.
- **Blocklist:** IP & WhatsApp (existing `dja_blocked_*`) + fingerprint device (Fase 2).
- **Audit trail:** setiap perubahan state donation & user punya `before/after` JSON.
- **PCI scope:** tidak ada data kartu yang pernah menyentuh server (tokenized via Snap/Stripe).
- **UU PDP Indonesia:** consent untuk data collection (nama, WA, email), retention policy, right-to-delete.
- **Dependabot** untuk Composer + npm CVE monitoring.

### 14.3 Aksesibilitas
- WCAG 2.1 AA.
- ARIA labeling pada form.
- Keyboard navigation (Tab order, Enter submit, Esc close modal).
- Screen reader tested (NVDA, VoiceOver).
- Focus visible (tidak pernah `outline: none` tanpa alternatif).

### 14.4 i18n
- Bahasa: `id` (default), `en`, `ar`, `my`.
- Domain teks: `donasiaja` (WP standard).
- Right-to-left untuk `ar`.
- Currency-aware formatting (`Intl.NumberFormat` PHP, `Intl.NumberFormat` JS).
- Date Hijri opsional untuk `ar`.

### 14.5 Observability
- **Structured log:** JSON line per event (`timestamp`, `level`, `trace_id`, `actor`, `event`, `payload`).
- **Trace ID:** UUID per request → propagasi ke webhook, cron, WA, payment.
- **EventBus:** semua domain event tercatat di `dja_event_log` (untuk audit & debugging).
- **Metrics:** counter donation.created, donation.paid, wa.sent.failed, webhook.retry (Fase 2 → Prometheus exporter).
- **Health check:** WP-CLI command `wp donasiaja doctor` (cek schema, gateway connectivity, WA provider).

---

## 15. Testing & Quality

### 15.1 Strategi
- **Unit (PHPUnit):** service layer, gateway adapter, validator, calculator. Target coverage 70% core.
- **Integration:** repository against real MySQL (test DB container).
- **E2E (Playwright):** donation flow per payment gateway (mock), campaign CRUD admin, fundraising onboard.
- **Static analysis:** PHPStan level 6.
- **Lint:** PHPCS (WordPress coding standard), ESLint untuk JS.
- **Security scan:** Composer audit, OWASP ZAP untuk E2E.

### 15.2 CI/CD (GitHub Actions)
```
PR opened    →  lint + phpunit + phpstan + bundle build
Merge main   →  full E2E + security scan + deploy staging
Tag release  →  build distributable .zip + push to release server (mirror.donasiaja.id)
```

### 15.3 Code Quality Gates
- Test coverage tidak boleh turun dari baseline.
- Tidak ada critical/high CVE di dependency.
- Build size budget: JS ≤120KB, CSS ≤40KB.
- Lighthouse ≥90 di halaman campaign demo.

---

## 16. Monetisasi

### 16.1 Paket Langganan (API Key Model)

| Fitur | **Free** | **Pro** (Rp 1.5jt/thn) | **ULTIMATE** (Rp 4.5jt/thn) |
|---|:---:|:---:|:---:|
| Campaign aktif | 3 | 25 | Unlimited |
| Payment gateway | Manual, QRIS (1) | + Midtrans, Xendit, Tripay | + Semua gateway |
| Donation form templates | Basic | + Qurban, Zakat | + Custom field |
| WhatsApp automation | 50 pesan/bulan | 1.000 pesan/bulan | 10.000 pesan/bulan |
| Receipt PDF | Basic | + Custom branding | + Custom branding + signature |
| Fundraising | ✅ | ✅ + Leaderboard | ✅ + Custom commission + payout auto |
| Multi-admin | ❌ | 3 user | Unlimited |
| API access | ❌ | Read-only | Read + Write |
| White-label | ❌ | ❌ | ✅ |
| Priority support | ❌ | 24 jam | 4 jam |
| Transaction fee | 0.7% | 0.5% | 0% |

### 16.2 Add-On
- **White-label setup fee:** Rp 500rb (sekali, untuk semua tier).
- **Custom integration** (CRM, accounting, custom gateway): Rp 2.5jt – Rp 15jt per project.
- **Training onsite** untuk korporat/yayasan besar: negosiasi.

### 16.3 Transaction Fee (Opsional, On/Off per Organizer)
- Default: 0.7% untuk Free, 0.5% Pro, 0% ULTIMATE.
- Ditampilkan transparan di receipt donatur (PDS compliant).
- Bisa di-disable untuk organizer dengan kerja sama khusus.

---

## 17. Roadmap & Milestone

```
Q3 2026 (saat ini)
  └─ Audit & PRD v1.0 ✅

Q4 2026 (3 bulan)
  ├─ M1: Composer + CI scaffold, schema_version, migration runner
  ├─ M2: Service layer skeleton (Campaign, Donation, Payment interfaces)
  ├─ M3: Normalisasi schema (5 tabel utama)
  ├─ M4: PHPUnit setup, target 30% coverage
  └─ Release: v3.0-alpha (internal)

Q1 2027 (3 bulan)
  ├─ M5: Campaign Builder v2 (WYSIWYG React island)
  ├─ M6: Donation Form modern (progressive, 60-detik flow)
  ├─ M7: Midtrans adapter refactor + idempotency + retry
  ├─ M8: Xendit adapter baru
  └─ Release: v3.0-beta (closed beta untuk 50 organizer)

Q2 2027 (3 bulan)
  ├─ M9: WhatsApp engine (template + multi-provider)
  ├─ M10: Receipt & sertifikat PDF
  ├─ M11: Fundraising engine demokratisasi
  ├─ M12: Dashboard real-time (SSE/long-poll)
  └─ Release: v3.0-rc (public release candidate)

Q3 2027 (3 bulan)
  ├─ M13: Zakat & Qurban calculator rewrite
  ├─ M14: Tripay/iPaymu/Flip/Stripe adapters
  ├─ M15: i18n EN, AR
  ├─ M16: Audit trail + observability
  ├─ M17: [DEPRECATED / SKIPPED] License activation flow upgrade (Bypassed via ULTIMATE hardcode)
  └─ Release: v3.0 GA ✅

Q4 2027 (3 bulan)
  ├─ M18: Fase 2 — Recurring donation, donation matching
  ├─ M19: Outbound webhook
  ├─ M20: Mobile SDK beta
  └─ Planning v3.5 (SaaS hosted beta)

2028
  └─ v3.5 SaaS hosted (multi-tenant cloud)
```

---

## 18. KPI Sukses

### 18.1 North-Star
- **Weekly Active Organizer (WAO)** yang menerima ≥1 donasi dalam 7 hari.
- Baseline (v2.2.5 estimasi): 800 organizer/minggu.
- Target v3.0 (12 bulan): 4.000 organizer/minggu.

### 18.2 KPI Operasional
| KPI | Baseline | Target v3.0 |
|---|---|---|
| Conversion rate (visitor → donate) | ~2.5% | ≥4% |
| Time-to-publish (campaign) | ~2 jam | <15 menit |
| Dashboard load (10K donation) | ~8 detik | <2 detik |
| Payment success rate | ~88% | ≥95% |
| WA delivery rate | ~85% | ≥97% |
| Time-to-first-donation (TTFD) organizer baru | ~5 hari | <2 hari |
| Organizer churn (3 bulan) | ~25% | <15% |
| GMV per organizer/bulan | ~Rp 8jt | ~Rp 15jt |
| NPS organizer | ~30 | ≥55 |

### 18.3 KPI Teknis
- Uptime plugin update server: ≥99.9%.
- Critical bug rate: <1 per rilis.
- Mean time to recovery (MTTR) payment issue: <4 jam.

---

## 19. Risiko & Mitigasi

| Risiko | Dampak | Probabilitas | Mitigasi |
|---|---|:---:|---|
| **Backwards-incompatible** dengan organizer v2.x | Tinggi | Tinggi | Migration script otomatis + compatibility shim + 1 versi LTS. |
| **Regulasi zakat (Baznas, BWI)** | Tinggi | Sedang | Konsultasi ahli, dokumentasi compliance, fitur opsional "sertifikasi Baznas". |
| **PCI scope expansion** saat add fitur card storage | Tinggi | Rendah | JANGAN simpan PAN; selalu pakai tokenized gateway (Snap, Stripe Elements). |
| **WA provider rate limit / banned** | Sedang | Sedang | Multi-provider fallback, queue dengan retry, respect opt-out. |
| **Dependency supply chain (Composer)** | Sedang | Sedang | Lock file commit, dependabot, private mirror untuk critical lib. |
| **Performance regression di migration** | Sedang | Sedang | Benchmark suite di CI, canary release ke 5% organizer dulu. |
| **Lisensi ULTIMATE organizer churn ke kompetitor** | Sedang | Rendah | Kampanye retensi, feature parity Free vs Pro, transparansi roadmap. |
| **Multi-currency akurasi rounding** | Rendah | Sedang | Decimal/bigint internal, format only at boundary. |
| **GDPR/UU PDP compliance** untuk donor EU/NA | Sedang | Rendah (Fase 2) | Consent banner, data export, right-to-delete endpoint. |
| **Skill gap tim refactor** | Sedang | Sedang | Pair programming, ADR untuk keputusan kunci, external review bulanan. |

---

## 20. Appendix

### 20.1 Glossary
- **Campaign:** Halaman donasi dengan target, cerita, dan form pembayaran.
- **Organizer:** User WP yang membuat & mengelola campaign (yayasan/individu).
- **Donatur:** User yang berdonasi (bisa anonim, tanpa akun).
- **Fundraiser:** User yang promote campaign orang lain via link referral, dapat komisi.
- **CS:** Customer Service, PIC yang handle pertanyaan donatur.
- **Snap:** Tipe integrasi Midtrans — redirect ke halaman hosted payment.
- **Core API:** Tipe integrasi Midtrans — custom UI di sisi organizer.
- **Webhook:** Callback HTTP dari payment gateway saat ada event.
- **Idempotency Key:** Identifier unik untuk cegah double-charge saat retry.
- **Kuitansi:** Bukti terima donasi (PDF printable).
- **Wanotif:** Background service WA pihak ketiga.
- **Nisab:** Batas minimum harta wajib zakat (85 gram emas).

### 20.2 SDK & Library yang Sudah Ada (untuk di-upgrade)
- `library/Midtrans/` — Midtrans PHP SDK (Config, ApiRequestor, SnapApiRequestor, Snap, CoreApi, Transaction, Notification, Sanitizer).
- `library/RemitCepat/AccessToken.php` — RemitCepat OAuth helper.
- `library/instructions.json` — payment-instruction steps per `{pg, method, payment}`.
- `library/locale/id.php` & `my.php` — associative string table.
- `library/f_additional_function.php` — zakat shortcode renderer.
- `library/f_translation_lang.php` — translation helper.
- `admin/plugin-update-checker/` — self-hosted plugin update.
- `admin/plugins/phpspreadsheet/` — Excel I/O.

### 20.3 Referensi Tabel DB v2 (untuk Migrasi)
Lihat §2.2 di atas.

### 20.4 Kompetitor & Positioning
| Kompetitor | Diferensiasi DonasiAja |
|---|---|
| **Kitabisa** | DonasiAja = self-hosted (data milik organizer), lebih murah (no fee transaksi ke platform). |
| **GalangDana** | DonasiAja = white-label ready, multi-payment aggregator lebih luas. |
| **OpenCollective** | DonasiAja = lebih mudah untuk non-tech, fokus ID/MY market. |
| **Stripe Fundraise / GoFundMe** | DonasiAja = lokal (QRIS, VA, e-wallet ID), Zakat/Qurban built-in. |

### 20.5 Sumber Daya
- Website: https://donasiaja.id
- Member area: https://member.donasiaja.id/login
- Dokumentasi: docs.donasiaja.id (target Q4 2026)
- Status page: status.donasiaja.id (target Q4 2026)
- GitHub: TBD (private repo untuk source)

---

## 21. Lampiran: Tabel Keputusan (Open Questions)

| # | Pertanyaan | Pemegang Keputusan | Target Keputusan |
|---|---|---|---|
| 1 | Pilih primary DB migrator (Phinx vs custom) | Tech Lead | M1 |
| 2 | Pilih PHP-DI vs Pimple | Tech Lead | M1 |
| 3 | Pilih Vite vs Webpack untuk React island build | Frontend Lead | M1 |
| 4 | Pilih DOMPDF vs mPDF untuk receipt | Backend Lead | M10 |
| 5 | Pilih Twig vs Plates untuk template | Backend Lead | M2 |
| 6 | Strategi kompatibilitas v2 → v3 (auto-migrate vs side-by-side) | Product Owner | M3 |
| 7 | Pricing Pro & ULTIMATE (final) | Business Lead | Pre-M5 |
| 8 | Inisiasi partnership dengan Baznas untuk compliance | Business Lead | Pre-M13 |
| 9 | Branding v3 (rename atau tetap "DonasiAja") | Marketing | Pre-M5 |
| 10 | Roadmap publik vs internal | Product Owner | Pre-M1 |

---

**Akhir PRD v1.0.**

*Dokumen ini akan di-update setiap quarter sesuai progress dan feedback dari organizer & donor.*
*Pemilik: Tim Produk DonasiAja (Sinkronus Co). Hubungi: product@donasiaja.id*
