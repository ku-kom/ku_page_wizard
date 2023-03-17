<?php

use TYPO3\CMS\Backend\Controller\AboutController;
use TYPO3\CMS\Backend\Controller\PageLayoutController;
use TYPO3\CMS\Backend\Controller\PageTsConfig\PageTsConfigActiveController;
use TYPO3\CMS\Backend\Controller\PageTsConfig\PageTsConfigIncludesController;
use TYPO3\CMS\Backend\Controller\PageTsConfig\PageTsConfigRecordsOverviewController;
use TYPO3\CMS\Backend\Controller\RecordListController;
use TYPO3\CMS\Backend\Controller\SiteConfigurationController;

/**
 * Definitions for modules provided by EXT:backend
 */
return [
    'web_page_wizard' => [
        'parent' => 'web',
        'position' => ['after' => 'web_layout'],
        'access' => 'user',
        'path' => '/module/web/page-wizard',
        'iconIdentifier' => 'module-form',
        'labels' => 'LLL:EXT:ku_page_wizard/Resources/Private/Language/Module/locallang_mod.xlf',
        'routes' => [
            '_default' => [
                'target' => \UniversityOfCopenhagen\KuPageWizard\Controller\PageWizardController::class . '::handleRequest',
            ],
        ]
    ]
];
