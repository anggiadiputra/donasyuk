<?php
namespace DonasiYuk\Domain\Audit;

interface AuditLogServiceInterface {
    public function log(string $action, array $context = [], string $level = 'info'): bool;
    public function getLogs(int $limit = 50): array;
}
