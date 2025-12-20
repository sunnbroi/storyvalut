<?php

namespace app\Application\Message\DTO;

final class CreateMessageDTO
{
    public function __construct(
        public readonly string $author,
        public readonly string $email,
        public readonly string $message,
        public readonly string $ip,
        public readonly ?string $imagePath = null
    ) {}
}
