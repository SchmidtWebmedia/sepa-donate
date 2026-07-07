<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'SEPA Donate',
    'description'      => 'SEPA donation form with EPC GiroCode QR generation for TYPO3',
    'category'         => 'plugin',
    'author'           => 'Schmidt Web Media',
    'author_email'     => '',
    'state'            => 'alpha',
    'version'          => '0.1.0',
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
