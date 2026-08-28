<?php
if (!defined('ABSPATH')) {
    exit;
}

function dyk_render_modern_donation_form($campaign_id = 0) {
    wp_nonce_field('dyk_donation_form_action', 'dyk_form_nonce');
    ?>
    <div class="dyk-modern-form" data-campaign-id="<?php echo esc_attr($campaign_id); ?>">
        <div class="dyk-step dyk-step-1 active" data-step="1">
            <h4>Pilih Nominal Donasi</h4>
            <div class="dyk-preset-amounts">
                <button type="button" class="dyk-preset-btn" data-amount="10000">Rp 10.000</button>
                <button type="button" class="dyk-preset-btn" data-amount="50000">Rp 50.000</button>
                <button type="button" class="dyk-preset-btn" data-amount="100000">Rp 100.000</button>
            </div>
            <input type="number" id="dyk_nominal" name="dyk_nominal" placeholder="Atau masukan nominal lain" min="10000" class="dyk-input" />
            <button type="button" class="dyk-next-btn" data-next="2">Lanjut Pilih Metode</button>
        </div>

        <div class="dyk-step dyk-step-2" data-step="2" style="display:none;">
            <h4>Metode Pembayaran</h4>
            <select id="dyk_payment_method" name="dyk_payment_method" class="dyk-select">
                <option value="qris">QRIS Instant</option>
                <option value="midtrans">Midtrans Virtual Account</option>
                <option value="manual">Transfer Manual Bank</option>
            </select>
            <button type="button" class="dyk-next-btn" data-next="3">Lanjut Data Diri</button>
        </div>

        <div class="dyk-step dyk-step-3" data-step="3" style="display:none;">
            <h4>Data Diri & Bayar</h4>
            <input type="text" id="dyk_donor_name" name="dyk_donor_name" placeholder="Nama Lengkap" class="dyk-input" required />
            <input type="text" id="dyk_donor_phone" name="dyk_donor_phone" placeholder="Nomor WhatsApp" class="dyk-input" required />
            <label><input type="checkbox" id="dyk_is_anon" name="dyk_is_anon" value="1" /> Donasi sebagai Hamba Allah (Anonim)</label>
            <button type="submit" id="dyk_submit_donation" class="dyk-submit-btn">Bayar Sekarang</button>
        </div>
    </div>
    <?php
}
