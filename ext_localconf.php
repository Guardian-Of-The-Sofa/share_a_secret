<?php
defined('TYPO3_MODE') || die('Access denied.');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Hn.HnShareSecret',
    'Secret',
    [
        'Secret' => 'new,create,showLink,inputPassword,show',
    ],
    // non-cacheable actions
    [
        'Secret' => 'create,show,showLink',
    ]
);

//$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[action]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[linkHash]';
