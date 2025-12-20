<?php

namespace app\Domain\Message\Service;

interface IpMaskerInterface
{
    public function mask(string $ip): string;
}