<?php

namespace app\Application\Message\UseCase;

use app\Application\Message\DTO\CreateMessageDTO;
use app\Application\Message\DTO\MessageResultDTO;

interface CreateMessageUseCaseInterface
{
    public function execute(CreateMessageDTO $dto): MessageResultDTO;
}
