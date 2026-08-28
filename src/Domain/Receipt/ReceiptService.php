<?php
namespace DonasiYuk\Domain\Receipt;

class ReceiptService implements ReceiptServiceInterface {
    public function generateReceiptHtml(array $donationData, array $campaignData): string {
        $donorName   = htmlspecialchars($donationData['donor_name'] ?? 'Donatur', ENT_QUOTES, 'UTF-8');
        $amount      = number_format((float)($donationData['amount'] ?? 0), 0, ',', '.');
        $campaign    = htmlspecialchars($campaignData['title'] ?? 'Penggalangan Dana', ENT_QUOTES, 'UTF-8');
        $trxId       = htmlspecialchars($donationData['transaction_id'] ?? ('DYK-' . time()), ENT_QUOTES, 'UTF-8');
        $date        = htmlspecialchars($donationData['created_at'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kuitansi Donasi - {$trxId}</title>
    <style>
        body { font-family: sans-serif; margin: 40px; color: #333; }
        .box { border: 2px solid #7680ff; padding: 25px; border-radius: 8px; }
        .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { color: #7680ff; margin: 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .amount { font-size: 20px; font-weight: bold; color: #10b981; margin-top: 15px; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">
            <h2>KUITANSI DONASI RESMI</h2>
            <p>No: {$trxId}</p>
        </div>
        <p>Telah terima dari: <strong>{$donorName}</strong></p>
        <p>Untuk Program: <strong>{$campaign}</strong></p>
        <p>Tanggal: {$date}</p>
        <div class="amount">Jumlah: Rp {$amount}</div>
        <div class="footer">
            <p>Terima kasih atas donasi Anda.</p>
            <p><em>DonasiYuk Official Receipt</em></p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    public function generateCertificateHtml(array $donationData, array $campaignData): string {
        $donorName = htmlspecialchars($donationData['donor_name'] ?? 'Donatur', ENT_QUOTES, 'UTF-8');
        $campaign  = htmlspecialchars($campaignData['title'] ?? 'Penggalangan Dana', ENT_QUOTES, 'UTF-8');
        $date      = htmlspecialchars($donationData['created_at'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Penghargaan</title>
    <style>
        body { font-family: 'Georgia', serif; text-align: center; padding: 50px; background: #fafafa; }
        .cert-container { border: 10px double #7680ff; padding: 40px; background: #fff; }
        h1 { font-size: 36px; color: #7680ff; }
        h2 { font-size: 28px; color: #111; margin-top: 20px; }
        p { font-size: 18px; color: #555; line-height: 1.6; }
        .date { margin-top: 30px; font-style: italic; }
    </style>
</head>
<body>
    <div class="cert-container">
        <h1>SERTIFIKAT PENGHARGAAN</h1>
        <p>Diberikan kepada:</p>
        <h2>{$donorName}</h2>
        <p>Atas partisipasi dan kontribusi donasi pada program penggalangan dana:</p>
        <h3>{$campaign}</h3>
        <p class="date">Diterbitkan pada: {$date}</p>
    </div>
</body>
</html>
HTML;
    }

    public function renderPdf(string $html): string {
        // Fallback / Stub PDF renderer returning HTML byte stream when DOMPDF engine is uninstalled
        if (class_exists('\\Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        return $html;
    }
}
