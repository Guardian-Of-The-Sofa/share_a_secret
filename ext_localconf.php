<?php
defined('TYPO3_MODE') || die('Access denied.');
//TODO: entferne unnötige actions
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Hn.HnShareSecret',
    'Secret',
    [
        'Secret' => 'new,create,showLink,showSecret,inputPassword,validatePassword,show',
    ],
    // non-cacheable actions
    //TODO: entferne unnötige actions
    [
        'Secret' => 'new,create,showLink,showSecret,inputPassword,validatePassword,show',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[action]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[linkHash]';

//TODO: Route enhancers.
