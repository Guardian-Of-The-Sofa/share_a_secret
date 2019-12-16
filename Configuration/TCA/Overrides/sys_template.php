<?php
$extKey = 'share_a_secret';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript/CssJs', 'CSS/JS');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript/Libraries', 'Optional jQuery and Bootstrap libraries');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript/BootstrapV3Compatibility', 'CSS styles for Bootstrap v. 3 compatibility');
