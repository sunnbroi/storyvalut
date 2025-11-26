<?php

namespace app\services;

use app\models\Message;
use app\models\forms\MessageForm;
use app\repositories\MessageRepositoryInterface;

/**
 * Сервис доменной логики для сообщений гостевой книги.
 *
 * Здесь:
 * - антиспам: 1 пост / 3 минуты / IP;
 * - генерация токенов редактирования и удаления;
 * - проверка TTL токенов (edit_token: 12ч, delete_token: 14 дней);
 * - операции create / update / delete поверх репозитория.
 *
 * НЕТ:
 * - работы c HTTP (request/response);
 * - рендеринга;
 * - валидации формы (это делает MessageForm).
 */
class MessageService
{
    /**
     * TTL токена редактирования: 12 часов.
     */
    private const EDIT_TOKEN_TTL = 12 * 60 * 60; // 12h

    /**
     * TTL токена удаления: 14 дней.
     */
    private const DELETE_TOKEN_TTL = 14 * 24 * 60 * 60; // 14d

    /**
     * Антиспам: 1 пост / 3 минуты / IP.
     */
    private const RATE_LIMIT_SECONDS = 3 * 60; // 3min

    private MessageRepositoryInterface $messages;

    public function __construct(MessageRepositoryInterface $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Создаёт новое сообщение.
     *
     * Предполагается, что:
     * - форма уже провалидирована (MessageForm::validate() вызван в контроллере);
     * - капча уже пройдена;
     * - message уже очищено HtmlPurifier в форме.
     *
     * @param MessageForm $form Валидная форма.
     * @param string      $ip   IP-адрес пользователя.
     *
     * @return Message|null Сохранённое сообщение или null при нарушении антиспама/ошибке сохранения.
     */
    public function create(MessageForm $form, string $ip): ?Message
    {
        // Антиспам: проверка по IP.
        if (!$this->canCreateFromIp($ip)) {
            return null;
        }

        $editToken = $this->generateToken();
        $deleteToken = $this->generateToken();

        return $this->messages->create(
            $form->author ?? '',
            $form->email ?? '',
            $form->message ?? '',
            $ip,
            $editToken,
            $deleteToken
        );
    }

    /**
     * Проверяет, можно ли создавать новое сообщение с указанного IP
     * с точки зрения антиспама (1 пост / 3 минуты).
     */
    public function canCreateFromIp(string $ip): bool
    {
        $last = $this->messages->findLastByIp($ip);
        if ($last === null) {
            return true;
        }

        if (!$last->created_at) {
            return true;
        }

        $now = time();

        return ($last->created_at + self::RATE_LIMIT_SECONDS) <= $now;
    }

    /**
     * Возвращает сообщение для редактирования по токену редактирования.
     *
     * Учитывает:
     * - soft delete (deleted_at IS NULL);
     * - TTL токена (12 часов от created_at).
     */
    public function getMessageForEdit(string $editToken): ?Message
    {
        $message = $this->messages->findByEditToken($editToken);
        if ($message === null) {
            return null;
        }

        if ($this->isEditTokenExpired($message)) {
            return null;
        }

        return $message;
    }

    /**
     * Обновляет текст сообщения на основе валидной формы.
     *
     * Проверка того, что токен ещё действителен, должна быть сделана
     * либо через getMessageForEdit(), либо отдельной проверкой сервиса.
     */
    public function update(Message $message, MessageForm $form): bool
    {
        // Дополнительная защита: не даём редактировать просроченное сообщение.
        if ($this->isEditTokenExpired($message)) {
            return false;
        }

        $message->author = $form->author ?? $message->author;
        $message->email = $form->email ?? $message->email;
        $message->message = $form->message ?? $message->message;

        return $this->messages->save($message);
    }

    /**
     * Возвращает сообщение для удаления по delete_token.
     *
     * TTL токена удаления: 14 дней от created_at.
     */
    public function getMessageForDelete(string $deleteToken): ?Message
    {
        $message = $this->messages->findByDeleteToken($deleteToken);
        if ($message === null) {
            return null;
        }

        if ($this->isDeleteTokenExpired($message)) {
            return null;
        }

        return $message;
    }

    /**
     * Логическое удаление сообщения по токену удаления.
     */
    public function deleteByToken(string $deleteToken): bool
    {
        $message = $this->getMessageForDelete($deleteToken);
        if ($message === null) {
            return false;
        }

        return $this->messages->softDelete($message);
    }

    /**
     * Проверка истечения TTL токена редактирования.
     */
    private function isEditTokenExpired(Message $message): bool
    {
        if (!$message->created_at) {
            // Если по какой-то причине нет created_at, считаем, что токен ещё валиден.
            return false;
        }

        $expiresAt = $message->created_at + self::EDIT_TOKEN_TTL;

        return $expiresAt < time();
    }

    /**
     * Проверка истечения TTL токена удаления.
     */
    private function isDeleteTokenExpired(Message $message): bool
    {
        if (!$message->created_at) {
            return false;
        }

        $expiresAt = $message->created_at + self::DELETE_TOKEN_TTL;

        return $expiresAt < time();
    }

    /**
     * Генерация криптостойкого токена.
     */
    private function generateToken(): string
    {
        // 32 байта = 64 hex-символа → подходит под CHAR(64)
        return bin2hex(random_bytes(32));
    }
}
