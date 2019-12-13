<?php
$extKey = 'share_a_secret';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript/CssJs', 'Include CSS/JS');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript/Libraries', 'Include CSS/JS libraries');
