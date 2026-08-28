<?php
namespace DonasiYuk\Domain\WhatsApp;

interface WhatsAppProviderInterface {
    public function getId(): string;
    public function sendMessage(string $to, string $message): array;
}
