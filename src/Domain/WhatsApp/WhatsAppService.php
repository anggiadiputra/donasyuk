<?php
namespace DonasiYuk\Domain\WhatsApp;

class WhatsAppService implements WhatsAppServiceInterface {
    /** @var array<string, WhatsAppProviderInterface> */
    private array $providers = [];

    public function registerProvider(WhatsAppProviderInterface $provider): void {
        $this->providers[$provider->getId()] = $provider;
    }

    public function send(string $to, string $template, array $vars, string $providerId = 'wanotif'): array {
        $provider = $this->providers[$providerId] ?? (reset($this->providers) ?: null);
        if (!$provider) {
            return [
                'success' => false,
                'message' => 'No WhatsApp provider registered.',
            ];
        }

        $message = $this->renderTemplate($template, $vars);
        return $provider->sendMessage($to, $message);
    }

    private function renderTemplate(string $template, array $vars): string {
        $search = array_map(fn($k) => '{{' . $k . '}}', array_keys($vars));
        return str_replace($search, array_values($vars), $template);
    }
}
