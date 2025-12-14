<?php

namespace app\Domain\Message\Entity;

use DateTimeImmutable;

class Message
{
    private ?int $id;
    private string $author;
    private string $email;
    private string $message;
    private string $ip;
    private int $createdAt;     // unix timestamp
    private ?int $deletedAt;    // null = not deleted
    private string $editToken;
    private string $deleteToken;
    private ?string $imagePath; // опционально

    public function __construct(
        ?int $id,
        string $author,
        string $email,
        string $message,
        string $ip,
        int $createdAt,
        ?int $deletedAt,
        string $editToken,
        string $deleteToken,
        ?string $imagePath
    ) {
        $this->id = $id;
        $this->author = $author;
        $this->email = $email;
        $this->message = $message;
        $this->ip = $ip;
        $this->createdAt = $createdAt;
        $this->deletedAt = $deletedAt;
        $this->editToken = $editToken;
        $this->deleteToken = $deleteToken;
        $this->imagePath = $imagePath;
    }

    // -----------------------------
    //       Фабрика создания
    // -----------------------------

    public static function createNew(
        string $author,
        string $email,
        string $message,
        string $ip,
        string $editToken,
        string $deleteToken,
        ?string $imagePath = null,
        ?int $createdAtUnix = null
    ): self {
        return new self(
            null,
            $author,
            $email,
            $message,
            $ip,
            $createdAtUnix ?? time(),
            null,
            $editToken,
            $deleteToken,
            $imagePath
        );
    }

    // -----------------------------
    //     Доменные правила
    // -----------------------------

    public function canEdit(DateTimeImmutable $now): bool
    {
        // можно редактировать 12 часов после создания
        $limit = $this->createdAt + 12 * 3600;

        return $this->deletedAt === null && $now->getTimestamp() <= $limit;
    }

    public function canDelete(DateTimeImmutable $now): bool
    {
        // можно удалять 14 дней после создания
        $limit = $this->createdAt + 14 * 24 * 3600;

        return $now->getTimestamp() <= $limit;
    }

    public function edit(string $newMessage, ?string $newImage, DateTimeImmutable $now): void
    {
        if (!$this->canEdit($now)) {
            throw new \DomainException('Editing message is not allowed anymore.');
        }

        $this->message = $newMessage;

        if ($newImage !== null) {
            $this->imagePath = $newImage;
        }
    }

    public function softDelete(DateTimeImmutable $now): void
    {
        if (!$this->canDelete($now)) {
            throw new \DomainException('Deleting message is not allowed anymore.');
        }

        $this->deletedAt = $now->getTimestamp();
    }

    // -----------------------------
    //       Геттеры для DTO
    // -----------------------------

    public function getId(): ?int          { return $this->id; }
    public function getAuthor(): string    { return $this->author; }
    public function getEmail(): string     { return $this->email; }
    public function getMessage(): string   { return $this->message; }
    public function getIp(): string        { return $this->ip; }
    public function getCreatedAt(): int    { return $this->createdAt; }
    public function getDeletedAt(): ?int   { return $this->deletedAt; }
    public function getEditToken(): string { return $this->editToken; }
    public function getDeleteToken(): string { return $this->deleteToken; }
    public function getImagePath(): ?string { return $this->imagePath; }

    // -----------------------------
    //      Сеттер id (после save)
    // -----------------------------

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
