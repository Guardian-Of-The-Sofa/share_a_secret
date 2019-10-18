<?php

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') || die('Access denied.');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Hn.ShareASecret',
    'Secret',
    [
        'Secret' => 'new,create,showLink,inputPassword,show,pleaseLogin,deleteMessage',
    ],
    // non-cacheable actions
    [
        'Secret' => 'new,create,showLink,inputPassword,show,deleteMessage',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_shareasecret_secret[linkHash]';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables'] = array(
    'tx_shareasecret_domain_model_secret' => array(
        'dateField' => 'crdate',
        'expirePeriod' => '15'
    ),
);

if(TYPO3_MODE === 'BE') {
    /** @var PageRenderer $pageRenderer */
    $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    $pageRenderer->addRequireJsConfiguration([
        'paths' => [
            'highcharts' => 'https://code.highcharts.com',
            'highcharts/highstock' => 'https://code.highcharts.com/stock',
        ],
        'packages' => [
            1 => [
                'name' => 'highcharts',
                'main' => 'highcharts'
            ],
            2 => [
                'name' => 'highcharts/highstock',
                'main' => 'highstock'
            ],
        ],
    ]);
}
