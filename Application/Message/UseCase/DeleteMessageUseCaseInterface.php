<?php

namespace app\Application\Message\UseCase;

interface DeleteMessageUseCaseInterface
{
    public function execute(string $deleteToken): void;
}
