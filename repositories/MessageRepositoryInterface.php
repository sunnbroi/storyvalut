<?php

namespace app\repositories;

use app\models\Message;

/**
 * Репозиторий для работы с сообщениями гостевой книги.
 *
 * Отвечает только за доступ к данным (БД) и выборки.
 * Никакой бизнес-логики (TTL, антиспам) здесь нет.
 */
interface MessageRepositoryInterface
{
    /**
     * Создаёт новое сообщение и сохраняет его в БД.
     *
     * Ожидается, что данные уже провалидированы на уровне формы/сервиса.
     * Валидация AR здесь ОТКЛЮЧЕНА для эффективности.
     *
     * @return Message|null Сохранённая модель или null при ошибке сохранения.
     */
    public function create(
        string $author,
        string $email,
        string $message,
        string $ip,
        string $editToken,
        string $deleteToken
    ): ?Message;

    /**
     * Сохраняет изменения существующей модели сообщения.
     *
     * @return bool true при успешном сохранении.
     */
    public function save(Message $message): bool;

    /**
     * Ищет активное (не удалённое) сообщение по токену редактирования.
     */
    public function findByEditToken(string $token): ?Message;

    /**
     * Ищет активное (не удалённое) сообщение по токену удаления.
     */
    public function findByDeleteToken(string $token): ?Message;

    /**
     * Возвращает последнее сообщение по IP (для будущего антиспама).
     */
    public function findLastByIp(string $ip): ?Message;

    /**
     * Логическое удаление сообщения (soft delete).
     */
    public function softDelete(Message $message): bool;
}