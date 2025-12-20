<?php

namespace app\Domain\Message\Service;

interface RateLimitPolicyInterface
{
    /**
     * @throws \DomainException если публиковать нельзя (раньше 3 минут)
     */
    public function assertCanPost(
        string $ip,
        string $email,
        int $nowUnix
    ): void;
}
