<?php

namespace app\Application\Message\UseCase;

use app\Domain\Message\Repository\MessageRepositoryInterface;
use DomainException;

final class DeleteMessageHandler implements DeleteMessageUseCaseInterface
{
    public function __construct(
        private readonly MessageRepositoryInterface $repository
    ) {}

    public function execute(string $deleteToken): void
    {
        $now = new \DateTimeImmutable();

        $message = $this->repository->findByDeleteToken($deleteToken);
        if ($message === null) {
            throw new DomainException('Message not found by delete token.');
        }

        $message->softDelete($now);

        $this->repository->save($message);
    }
}
