<?php

namespace app\Application\Message\DTO;

final class MessageResultDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $author,
        public readonly string $message,
        public readonly int $createdAt,
        public readonly string $maskedIp,
        public readonly int $postsCountByIp,
        public readonly string $editToken,
        public readonly string $deleteToken,
        public readonly ?string $imagePath
    ) {}
}
