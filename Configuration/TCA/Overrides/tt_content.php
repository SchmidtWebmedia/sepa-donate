<?php

declare(strict_types=1);

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::registerPlugin(
    'SepaDonate',
    'DonationForm',
    'SEPA Donate — Spendenformular',
    'EXT:sepa_donate/Resources/Public/Icons/Extension.svg'
);
