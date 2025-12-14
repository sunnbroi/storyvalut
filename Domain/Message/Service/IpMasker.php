<?php

namespace app\Domain\Message\Service;

final class IpMasker implements IpMaskerInterface
{
    public function mask(string $ip): string
    {
        if (strpos($ip, ':') !== false) {
            return $this->maskIpv6($ip);
        }

        return $this->maskIpv4($ip);
    }

    private function maskIpv4(string $ip): string
    {
        $parts = explode('.', $ip);

        // Если формат неожиданный — возвращаем как есть (без падения)
        if (count($parts) !== 4) {
            return $ip;
        }

        return $parts[0] . '.' . $parts[1] . '.**.**';
    }

    private function maskIpv6(string $ip): string
    {
        // Упрощённое правило по ТЗ: скрыть последние 4 секции.
        // Если IP в сжатом виде (::), мы не пытаемся полностью нормализовать —
        // просто скрываем хвостовые части, насколько можем, без тяжёлых преобразований.

        $parts = explode(':', $ip);

        // Если слишком коротко — безопасно возвращаем как есть
        if (count($parts) < 5) {
            return $ip;
        }

        $keep = max(0, count($parts) - 4);
        $visible = array_slice($parts, 0, $keep);
        $masked = array_fill(0, 4, '****');

        return implode(':', array_merge($visible, $masked));
    }
}
