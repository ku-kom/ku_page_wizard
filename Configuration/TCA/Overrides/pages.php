<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['pages']['columns'] = array_replace_recursive(
    $GLOBALS['TCA']['pages']['columns'],
    [
        'tx_kupagewizard_page_template' => [
            'config' => [
                'type' => 'passthrough',
            ]
        ]
    ]
);

ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_kupagewizard_page_template');