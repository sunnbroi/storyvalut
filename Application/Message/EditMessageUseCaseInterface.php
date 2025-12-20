<?php

namespace app\Application\Message\UseCase;

use app\Application\Message\DTO\EditMessageDTO;
use app\Application\Message\DTO\MessageResultDTO;

interface EditMessageUseCaseInterface
{
    public function execute(EditMessageDTO $dto): MessageResultDTO;
}
