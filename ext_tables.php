<?php

defined('TYPO3') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'web',
    'txkupagewizard',
    'after:web_layout',
    null,
    [
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'routeTarget' => \UniversityOfCopenhagen\KuPageWizard\Controller\PageWizardController::class . '::handleRequest',
        'access' => 'user,group',
        'name' => 'web_txkupagewizard',
        'iconIdentifier' => 'ku-page-wizard-backend-module',
        'labels' => 'LLL:EXT:ku_page_wizard/Resources/Private/Language/Module/locallang_mod.xlf'
    ]
);
