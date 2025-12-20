<?php

namespace app\Domain\Message\Service;

interface TokenGeneratorInterface
{
    /**
     * Генерирует криптостойкий token в hex-формате нужной длины.
     */
    public function generateHex(int $bytesLength): string;
}
