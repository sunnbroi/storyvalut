<?php

namespace app\Application\Message\UseCase;

use app\Application\Message\DTO\EditMessageDTO;
use app\Application\Message\DTO\MessageResultDTO;
use app\Domain\Message\Repository\MessageRepositoryInterface;
use app\Domain\Message\Service\ContentSanitizerInterface;
use app\Domain\Message\Service\IpMaskerInterface;
use DomainException;

final class EditMessageHandler implements EditMessageUseCaseInterface
{
    public function __construct(
        private readonly MessageRepositoryInterface $repository,
        private readonly ContentSanitizerInterface $sanitizer,
        private readonly IpMaskerInterface $ipMasker
    ) {}

    public function execute(EditMessageDTO $dto): MessageResultDTO
    {
        $now = new \DateTimeImmutable();

        $message = $this->repository->findByEditToken($dto->editToken);
        if ($message === null) {
            throw new DomainException('Message not found by edit token.');
        }

        $clean = $this->sanitizer->sanitize($dto->message);

        $message->edit($clean, $dto->imagePath, $now);

        $message = $this->repository->save($message);

        $postsCount = $this->repository->countByIp($message->getIp());

        return new MessageResultDTO(
            id: (int)$message->getId(),
            author: $message->getAuthor(),
            message: $message->getMessage(),
            createdAt: $message->getCreatedAt(),
            maskedIp: $this->ipMasker->mask($message->getIp()),
            postsCountByIp: $postsCount,
            editToken: $message->getEditToken(),
            deleteToken: $message->getDeleteToken(),
            imagePath: $message->getImagePath()
        );
    }
}
