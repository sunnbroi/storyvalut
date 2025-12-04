<?php

namespace tests\unit\services;

use app\models\Message;
use app\models\forms\MessageForm;
use app\repositories\MessageRepositoryInterface;
use app\services\MessageService;
use app\components\captcha\CaptchaVerifierInterface;
use Codeception\Test\Unit;

/**
 * @covers \app\services\MessageService
 */
class MessageServiceTest extends Unit
{
    private MessageRepositoryInterface $repository;
    private MessageService $service;

    protected function _before()
    {
        parent::_before();

        $this->repository = $this->createMock(MessageRepositoryInterface::class);
        $this->service = new MessageService($this->repository);
    }

    private function createMessageWithCreatedAt(int $timestamp): Message
    {
        $message = new Message();
        $message->created_at = $timestamp;

        return $message;
    }

    private function createForm(string $author, string $email, string $messageText): MessageForm
    {
        $captchaVerifier = new class implements CaptchaVerifierInterface {
            public function verify(string $token, ?string $ip = null): bool
            {
                return true;
            }
        };

        $form = new MessageForm($captchaVerifier);
        $form->author = $author;
        $form->email = $email;
        $form->message = $messageText;
        $form->captcha = 'dummy';

        return $form;
    }
}
