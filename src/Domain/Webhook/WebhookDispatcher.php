<?php
namespace DonasiYuk\Domain\Webhook;

class WebhookDispatcher implements WebhookDispatcherInterface {
    public function dispatch(string $event, array $payload, string $targetUrl, string $secret = ''): array {
        $body = json_encode([
            'event'     => $event,
            'timestamp' => time(),
            'data'      => $payload,
        ]);

        $signature = $secret ? hash_hmac('sha256', $body, $secret) : '';

        // If wp_remote_post exists, use WP HTTP API
        if (function_exists('wp_remote_post')) {
            $response = wp_remote_post($targetUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-DonasiYuk-Signature' => $signature,
                ],
                'body' => $body,
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                return ['success' => false, 'error' => $response->get_error_message()];
            }

            return ['success' => true, 'status' => wp_remote_retrieve_response_code($response)];
        }

        return ['success' => true, 'signature' => $signature];
    }
}
