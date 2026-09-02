<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'SEPA Donate',
    'description'      => 'SEPA donation form with EPC GiroCode QR generation for TYPO3',
    'category'         => 'plugin',
    'author'           => 'Marco Schmidt',
    'author_email'     => 'typo@schmidt-webmedia.de',
    'author_company'   => 'Marco Schmidt - Webmedia',
    'state'            => 'beta',
    'version'          => '1.0.0-beta.4',
    'constraints'      => [
        'depends' => [
            'typo3' => '13.0.0-14.99.99',
            'extbase' => '13.0.0-14.99.99',
            'fluid'   => '13.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
