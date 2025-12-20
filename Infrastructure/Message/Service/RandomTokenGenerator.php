<?php

namespace app\Infrastructure\Message\Service;

use app\Domain\Message\Service\TokenGeneratorInterface;

final class RandomTokenGenerator implements TokenGeneratorInterface
{
    public function generateHex(int $bytesLength): string
    {
        return bin2hex(random_bytes($bytesLength));
    }
}
