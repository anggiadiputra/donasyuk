<?php
namespace DonasiYuk\Domain\I18n;

class I18nService implements I18nServiceInterface {
    private array $dictionary = [
        'id' => [
            'donate_now' => 'Donasi Sekarang',
            'thank_you'  => 'Terima kasih atas donasi Anda',
        ],
        'en' => [
            'donate_now' => 'Donate Now',
            'thank_you'  => 'Thank you for your donation',
        ],
        'ar' => [
            'donate_now' => 'تبرع الآن',
            'thank_you'  => 'شكرا لك على تبرعك',
        ],
    ];

    public function translate(string $key, string $locale = 'id', array $placeholders = []): string {
        $text = $this->dictionary[$locale][$key] ?? $this->dictionary['id'][$key] ?? $key;
        foreach ($placeholders as $placeholder => $val) {
            $text = str_replace('{' . $placeholder . '}', (string) $val, $text);
        }
        return $text;
    }

    public function isRtl(string $locale): bool {
        return $locale === 'ar';
    }
}
