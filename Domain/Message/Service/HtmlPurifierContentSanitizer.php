<?php

namespace app\Infrastructure\Message\Service;

use app\Domain\Message\Service\ContentSanitizerInterface;
use HTMLPurifier;
use HTMLPurifier_Config;

final class HtmlPurifierContentSanitizer implements ContentSanitizerInterface
{
    private HTMLPurifier $purifier;

    public function __construct(string $allowedTags = 'b,i,s')
    {
        $config = HTMLPurifier_Config::createDefault();

        // Разрешаем только нужные теги
        $config->set('HTML.Allowed', $allowedTags);

        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitize(string $raw): string
    {
        return (string)$this->purifier->purify($raw);
    }
}
