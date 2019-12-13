<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Hn.ShareASecret',
    'Secret',
    'Share a secret',
    ''
);

// Module System > Backend Users
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Hn.ShareASecret',
    'system',
    'tx_hnshareasecret',
    'bottom',
    [
        'Log' => 'list',
    ],
    [
        'access' => 'admin',
        'icon' => 'EXT:belog/Resources/Public/Icons/module-belog.svg',
        'labels' => 'LLL:EXT:share_a_secret/Resources/Private/Language/lang_mod.xlf',
    ]
);
