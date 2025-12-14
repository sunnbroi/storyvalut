<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    
    'turnstile' => [
        'secretKey' => 'YOUR_SECRET_KEY_HERE',
        'siteKey' => 'YOUR_SITE_KEY_HERE',
    ],

    'message' => [
    'rateLimit' => [
        'mode' => 'ip',          // ip | email | combined
        'cooldownSeconds' => 180, // 3 минуты
    ],
    'sanitize' => [
        'allowedTags' => 'b,i,s', // или 'b,i,s,br' если хочешь оставить br
    ],
],
];
