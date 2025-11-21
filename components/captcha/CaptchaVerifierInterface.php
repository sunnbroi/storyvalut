<?php

namespace app\components\captcha;

/**
 * Интерфейс для проверки капчи.
 * Позволяет подменять реализацию (Turnstile, заглушка, тестовая и т.п.).
 */
interface CaptchaVerifierInterface
{
    /**
     * Проверяет капчу по токену.
     *
     * @param string      $token Ответ капчи с клиента.
     * @param string|null $ip    Опционально IP пользователя (для логирования/верификации).
     *
     * @return bool true, если капча успешно пройдена.
     */
    public function verify(string $token, ?string $ip = null): bool;
}
