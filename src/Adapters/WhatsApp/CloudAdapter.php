<?php
namespace DonasiYuk\Adapters\WhatsApp;

use DonasiYuk\Domain\WhatsApp\WhatsAppProviderInterface;

class CloudAdapter implements WhatsAppProviderInterface {
    public function getId(): string {
        return 'wacloud';
    }

    public function sendMessage(string $to, string $message): array {
        return [
            'success'     => true,
            'provider'    => 'wacloud',
            'to'          => $to,
            'message'     => $message,
            'response_id' => 'wac_' . uniqid(),
        ];
    }
}
