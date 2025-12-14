<?php

namespace app\Infrastructure\Message\Persistence;

use app\Domain\Message\Entity\Message as DomainMessage;
use app\Domain\Message\Repository\MessageRepositoryInterface;
use app\Infrastructure\Message\Persistence\ActiveRecord\MessageAR;
use RuntimeException;

class MessageRepository implements MessageRepositoryInterface
{
    public function save(DomainMessage $message): DomainMessage
    {
        $ar = $this->mapToAR($message);

        if (!$ar->save(false)) {
            throw new RuntimeException('Failed to save message.');
        }
        // после сохранения AR гарантированно имеет id
        if ($message->getId() === null) {
            $message->setId((int)$ar->id);
        }

        return $message;
    }

    public function findByEditToken(string $token): ?DomainMessage
    {
        $ar = MessageAR::find()
            ->andWhere(['edit_token' => $token])
            ->andWhere(['deleted_at' => null])
            ->one();

        if ($ar === null) {
            return null;
        }

        return $this->mapToDomain($ar);
    }

    public function findByDeleteToken(string $token): ?DomainMessage
    {
        $ar = MessageAR::find()
            ->andWhere(['delete_token' => $token])
            ->one();

        if ($ar === null) {
            return null;
        }

        return $this->mapToDomain($ar);
    }

    public function findLastByIp(string $ip): ?DomainMessage
    {
        $ar = MessageAR::find()
            ->andWhere(['ip' => $ip])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        return $ar ? $this->mapToDomain($ar) : null;
    }
        public function findLastByEmail(string $email): ?DomainMessage
    {
        $ar = MessageAR::find()
            ->andWhere(['email' => $email])
            ->andWhere(['deleted_at' => null])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        return $ar ? $this->mapToDomain($ar) : null;
    }

    public function findLastByIpOrEmail(string $ip, string $email): ?DomainMessage
    {
        $ar = MessageAR::find()
            ->andWhere(['deleted_at' => null])
            ->andWhere(['or', ['ip' => $ip], ['email' => $email]])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        return $ar ? $this->mapToDomain($ar) : null;
    }

    public function countByIp(string $ip): int
    {
        return (int) MessageAR::find()
            ->andWhere(['ip' => $ip])
            ->andWhere(['deleted_at' => null])
            ->count();
    }
    private function mapToDomain(MessageAR $ar): DomainMessage
    {
        return new DomainMessage(
            $ar->id !== null ? (int)$ar->id : null,
            (string)$ar->author,
            (string)$ar->email,
            (string)$ar->message,
            (string)$ar->ip,
            (int)$ar->created_at,
            $ar->deleted_at !== null ? (int)$ar->deleted_at : null,
            (string)$ar->edit_token,
            (string)$ar->delete_token,
            $ar->image_path ?? null
        );
    }
    private function mapToAR(DomainMessage $message): MessageAR
    {
        if ($message->getId() !== null) {
            $ar = MessageAR::findOne($message->getId());
            if ($ar === null) {
                throw new RuntimeException('Message AR not found for id ' . $message->getId());
            }
        } else {
            $ar = new MessageAR();
        }

        $ar->author = $message->getAuthor();
        $ar->email = $message->getEmail();
        $ar->message = $message->getMessage();
        $ar->ip = $message->getIp();
        $ar->created_at = $message->getCreatedAt();
        $ar->deleted_at = $message->getDeletedAt();
        $ar->edit_token = $message->getEditToken();
        $ar->delete_token = $message->getDeleteToken();
        $ar->image_path = $message->getImagePath();

        return $ar;
    }
}
