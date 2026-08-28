<?php
namespace DonasiYuk\Adapters\WhatsApp;

use DonasiYuk\Domain\WhatsApp\WhatsAppProviderInterface;

class WanotifAdapter implements WhatsAppProviderInterface {
    public function getId(): string {
        return 'wanotif';
    }

    public function sendMessage(string $to, string $message): array {
        return [
            'success'     => true,
            'provider'    => 'wanotif',
            'to'          => $to,
            'message'     => $message,
            'response_id' => 'wn_' . uniqid(),
        ];
    }
}
