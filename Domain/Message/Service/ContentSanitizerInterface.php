<?php

namespace app\Domain\Message\Service;

interface ContentSanitizerInterface
{
    public function sanitize(string $raw): string;
}
