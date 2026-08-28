<?php
namespace DonasiYuk\Domain\I18n;

interface I18nServiceInterface {
    public function translate(string $key, string $locale = 'id', array $placeholders = []): string;
    public function isRtl(string $locale): bool;
}
