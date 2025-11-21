<?php

namespace app\models\forms;

use app\components\captcha\CaptchaVerifierInterface;
use Yii;
use yii\base\Model;
use yii\helpers\HtmlPurifier;

class MessageForm extends Model
{
    public ?string $author = null;
    public ?string $email = null;
    public ?string $message = null;

    public ?string $captcha = null;

    private CaptchaVerifierInterface $captchaVerifier;

    public function __construct(
        CaptchaVerifierInterface $captchaVerifier,
        $config = []
    ) {
        $this->captchaVerifier = $captchaVerifier;
        parent::__construct($config);
    }

    public function attributeLabels(): array
    {
        return [
            'author'  => 'Имя',
            'email'   => 'E-mail',
            'message' => 'Сообщение',
            'captcha' => 'Подтверждение',
        ];
    }

    public function rules(): array
    {
        return [
            [['author', 'email', 'message', 'captcha'], 'required'],
            [['author'], 'string', 'max' => 15],
            [['email'], 'string', 'max' => 255],
            [['message'], 'string'],
            ['email', 'email'],
            [['author', 'email', 'message'], 'filter', 'filter' => 'trim'],

            [['message'], 'filter', 'filter' => [$this, 'sanitizeMessage']],

            ['captcha', 'validateCaptcha'],
        ];
    }

    public function validateCaptcha(string $attribute): void
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $token = $this->$attribute ?? '';

        if (!$this->captchaVerifier->verify($token, Yii::$app->request->userIP)) {
            $this->addError($attribute, 'Капча не пройдена.');
        }
    }

    protected function sanitizeMessage(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        return HtmlPurifier::process($value, [
            'HTML.Allowed' => 'b,i,s,br',
        ]);
    }
}
