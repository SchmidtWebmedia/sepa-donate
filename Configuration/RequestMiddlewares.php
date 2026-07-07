<?php

declare(strict_types=1);

return [
    'frontend' => [
        'schmidtwebmedia/sepa-donate/qr-code-endpoint' => [
            'target' => \Schmidtwebmedia\SepaDonate\Middleware\QrCodeEndpoint::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
        ],
    ],
];
