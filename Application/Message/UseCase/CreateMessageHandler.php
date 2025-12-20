<?php

namespace app\Application\Message\UseCase;

use app\Application\Message\DTO\CreateMessageDTO;
use app\Application\Message\DTO\MessageResultDTO;
use app\Domain\Message\Entity\Message;
use app\Domain\Message\Repository\MessageRepositoryInterface;
use app\Domain\Message\Service\ContentSanitizerInterface;
use app\Domain\Message\Service\IpMaskerInterface;
use app\Domain\Message\Service\RateLimitPolicyInterface;
use app\Domain\Message\Service\TokenGeneratorInterface;

final class CreateMessageHandler implements CreateMessageUseCaseInterface
{
    public function __construct(
        private readonly MessageRepositoryInterface $repository,
        private readonly RateLimitPolicyInterface $rateLimitPolicy,
        private readonly ContentSanitizerInterface $sanitizer,
        private readonly IpMaskerInterface $ipMasker,
        private readonly TokenGeneratorInterface $tokenGenerator
    ) {}

    public function execute(CreateMessageDTO $dto): MessageResultDTO
    {
        $now = time();

        // 1) Rate-limit (3 минуты) по конфигу: ip/email/combined
        $this->rateLimitPolicy->assertCanPost($dto->ip, $dto->email, $now);

        // 2) Sanitize (b,i,s)
        $cleanMessage = $this->sanitizer->sanitize($dto->message);

        // 3) Tokens
        $editToken = $this->tokenGenerator->generateHex(32);   // 64 hex
        $deleteToken = $this->tokenGenerator->generateHex(32); // 64 hex

        // 4) Domain entity
        $message = Message::createNew(
            $dto->author,
            $dto->email,
            $cleanMessage,
            $dto->ip,
            $editToken,
            $deleteToken,
            $dto->imagePath,
            $now
        );

        // 5) Persist
        $message = $this->repository->save($message);

        // 6) Read side data (posts count)
        $postsCount = $this->repository->countByIp($dto->ip);

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
