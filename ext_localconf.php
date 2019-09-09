<?php
defined('TYPO3_MODE') || die('Access denied.');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Hn.HnShareSecret',
    'Secret',
    [
        'Secret' => 'new,create,showLink,inputPassword,show,pleaseLogin,deleteMessage',
    ],
    // non-cacheable actions
    [
        'Secret' => 'new,create,showLink,inputPassword,show,deleteMessage',
    ]
);

//$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[action]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_hnsharesecret_secret[linkHash]';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables'] = array(
    'tx_hnsharesecret_domain_model_secret' => array(
        'dateField' => 'crdate',
        'expirePeriod' => '15'
    ),
);