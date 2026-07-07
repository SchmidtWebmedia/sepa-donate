<?php

declare(strict_types=1);

use Schmidtwebmedia\SepaDonate\Controller\DonationController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'SepaDonate',
    'DonationForm',
    [
        DonationController::class => 'form',
    ],
);

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][900]
    = 'EXT:sepa_donate/Resources/Private/Templates/Email/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][900]
    = 'EXT:sepa_donate/Resources/Private/Layouts/Email/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths'][900]
    = 'EXT:sepa_donate/Resources/Private/Partials/Email/';
