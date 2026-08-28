<?php
namespace DonasiYuk\Domain\Receipt;

interface ReceiptServiceInterface {
    public function generateReceiptHtml(array $donationData, array $campaignData): string;
    public function generateCertificateHtml(array $donationData, array $campaignData): string;
    public function renderPdf(string $html): string;
}
