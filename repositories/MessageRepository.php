<?php

namespace app\repositories;

use app\models\Message;

/**
 * Реализация репозитория сообщений через ActiveRecord Message.
 */
class MessageRepository implements MessageRepositoryInterface
{
    public function create(
        string $author,
        string $email,
        string $message,
        string $ip,
        string $editToken,
        string $deleteToken
    ): ?Message {
        $model = new Message();
        $model->author = $author;
        $model->email = $email;
        $model->message = $message;
        $model->ip = $ip;
        $model->edit_token = $editToken;
        $model->delete_token = $deleteToken;

        // Валидация уже выполнена на уровне формы, поэтому save(false).
        if (!$model->save(false)) {
            return null;
        }

        return $model;
    }

    public function save(Message $message): bool
    {
        // Любая бизнес-логика (TTL, антиспам) должна быть в сервисе,
        // здесь только "сохранить в БД".
        return (bool)$message->save(false);
    }

    public function findByEditToken(string $token): ?Message
    {
        return Message::find()
            ->andWhere(['edit_token' => $token])
            ->andWhere(['deleted_at' => null])
            ->one();
    }

    public function findByDeleteToken(string $token): ?Message
    {
        return Message::find()
            ->andWhere(['delete_token' => $token])
            ->andWhere(['deleted_at' => null])
            ->one();
    }

    public function findLastByIp(string $ip): ?Message
    {
        return Message::find()
            ->andWhere(['ip' => $ip])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function softDelete(Message $message): bool
    {
        // Предполагаем, что SoftDeleteBehavior уже подключён к модели Message
        // и метод softDelete() доступен.
        $result = $message->softDelete();

        return $result !== false;
    }
}
