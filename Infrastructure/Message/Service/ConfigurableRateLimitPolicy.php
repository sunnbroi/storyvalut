<?php

namespace app\Infrastructure\Message\Service;

use app\Domain\Message\Repository\MessageRepositoryInterface;
use app\Domain\Message\Service\RateLimitPolicyInterface;
use DomainException;

final class ConfigurableRateLimitPolicy implements RateLimitPolicyInterface
{
    public const MODE_IP = 'ip';
    public const MODE_EMAIL = 'email';
    public const MODE_COMBINED = 'combined';

    private MessageRepositoryInterface $repository;
    private string $mode;
    private int $cooldownSeconds;

    public function __construct(
        MessageRepositoryInterface $repository,
        string $mode = self::MODE_IP,
        int $cooldownSeconds = 60 * 3
    ) {
        $this->repository = $repository;
        $this->mode = $mode;
        $this->cooldownSeconds = $cooldownSeconds;
    }

    public function assertCanPost(string $ip, string $email, int $nowUnix): void
    {
        $lastCreatedAt = $this->getLastCreatedAt($ip, $email);

        if ($lastCreatedAt === null) {
            return;
        }

        $nextAllowedAt = $lastCreatedAt + $this->cooldownSeconds;

        if ($nowUnix < $nextAllowedAt) {
            // Сообщение в ошибке должно содержать "когда можно следующее"
            throw new DomainException('Next post allowed at: ' . $nextAllowedAt);
        }
    }

    private function getLastCreatedAt(string $ip, string $email): ?int
    {
        if ($this->mode === self::MODE_IP) {
            $m = $this->repository->findLastByIp($ip);
            return $m ? $m->getCreatedAt() : null;
        }

        if ($this->mode === self::MODE_EMAIL) {
            $m = $this->repository->findLastByEmail($email);
            return $m ? $m->getCreatedAt() : null;
        }

        // combined
        $m = $this->repository->findLastByIpOrEmail($ip, $email);
        return $m ? $m->getCreatedAt() : null;
    }
}
