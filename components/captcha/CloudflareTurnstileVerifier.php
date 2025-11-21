<?php

namespace app\components\captcha;

/**
 * Заглушка для интеграции с Cloudflare Turnstile.
 *
 * Сейчас verify() не делает HTTP-запросов и всегда возвращает true,
 * если токен не пустой. Реальную интеграцию добавим на шаге конфигурации.
 */
class CloudflareTurnstileVerifier implements CaptchaVerifierInterface
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        // TODO: Реализовать реальный запрос к Cloudflare Turnstile API.
        // Пока — простая заглушка для разработки и тестов.

        if ($token === '') {
            return false;
        }

        return true;
    }
}
