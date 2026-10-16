<?php

defined('TYPO3') || die();

// This is the hook the upstream open-oap package points you to: instead of
// registering instance PageTSConfig inside the vendor package, do it here, in
// your own instance extension.
$pluginSignature = 'oap_instance_example';

/**
 * Register PageTSConfig files.
 *
 * `oap--default.tsconfig` restricts the backend record selectors of the OAP
 * form/proposal models to the correct sysfolders (via PAGE_TSCONFIG_ID). Adjust
 * the UIDs in that file to match your instance's folder structure.
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
    $pluginSignature,
    'Configuration/TSconfig/oap--default.tsconfig',
    'OAP Example Base - Page Configuration'
);
