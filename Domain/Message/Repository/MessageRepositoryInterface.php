<?php

namespace app\Domain\Message\Repository;

use app\Domain\Message\Entity\Message;

interface MessageRepositoryInterface
{
    public function save(Message $message): Message;
    public function findByEditToken(string $token): ?Message;
    public function findByDeleteToken(string $token): ?Message;
    public function findLastByIp(string $ip): ?Message;
    public function countByIp(string $ip): int;
}
