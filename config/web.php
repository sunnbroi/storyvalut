<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
if (file_exists(__DIR__ . '/db-local.php')) {
    $db = array_merge(
        $db,
        require __DIR__ . '/db-local.php'
    );
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'EJvqPGBhfm12yFoYosE14D-mG_o_eTeZ',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            '' => 'site/index',
            'POST message'          => 'message/create',   // POST /message
            'GET message/edit'      => 'message/edit',     // GET  /message/edit?token=...
            'POST message/update'   => 'message/update',   // POST /message/update?token=...
            'GET message/delete'    => 'message/delete',   // GET  /message/delete?token=...
            'POST message/destroy'  => 'message/destroy',
            ],
        ],
    ],

    'container' => [
'container' => [
    'definitions' => [
    ],
    'singletons' => [
        
        \app\components\captcha\CaptchaVerifierInterface::class => fn () =>
            new \app\components\captcha\CloudflareTurnstileVerifier($params['turnstile']['secretKey']),

        \app\repositories\MessageRepositoryInterface::class =>
            \app\repositories\MessageRepository::class,

        \app\Domain\Message\Repository\MessageRepositoryInterface::class =>
            \app\Infrastructure\Message\Persistence\MessageRepository::class,

        \app\Domain\Message\Service\RateLimitPolicyInterface::class =>
            \app\Infrastructure\Message\Service\ConfigurableRateLimitPolicy::class,

        \app\Domain\Message\Service\IpMaskerInterface::class =>
            \app\Domain\Message\Service\IpMasker::class,

        \app\Domain\Message\Service\ContentSanitizerInterface::class =>
            \app\Infrastructure\Message\Service\HtmlPurifierContentSanitizer::class,

        \app\Application\Message\UseCase\CreateMessageUseCaseInterface::class =>
            \app\Application\Message\UseCase\CreateMessageHandler::class,

        \app\Application\Message\UseCase\EditMessageUseCaseInterface::class =>
            \app\Application\Message\UseCase\EditMessageHandler::class,

        \app\Application\Message\UseCase\DeleteMessageUseCaseInterface::class =>
            \app\Application\Message\UseCase\DeleteMessageHandler::class,
    ],
],

],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
