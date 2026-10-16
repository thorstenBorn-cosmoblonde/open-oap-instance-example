<?php

defined('TYPO3') || die('Access denied.');

// Registers the static TypoScript template of this instance extension so it can
// be included in a page's `sys_template` record. It MUST be included AFTER the
// `open_oap` static template (see README → "Static template order").
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'oap_instance_example',
    'Configuration/TypoScript',
    'OAP Instance: Example'
);
