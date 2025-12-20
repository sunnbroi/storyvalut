<?php

namespace app\Application\Message\DTO;

final class EditMessageDTO
{
    public function __construct(
        public readonly string $editToken,
        public readonly string $message,
        public readonly ?string $imagePath = null
    ) {}
}
