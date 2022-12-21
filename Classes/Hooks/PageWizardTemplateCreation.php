<?php

namespace UniversityOfCopenhagen\KuPageWizard\Hooks;

/**
 * Hook to save timestamp on parent page
 * whenever a content element is added or modified.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

class PageWizardTemplateCreation
{
    /**
     * @param string $status
     * @param string $table
     * @param string $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $dataHandler)
    {
        if (!($table === 'pages' && $status === 'new' && isset($fieldArray['tx_kupagewizard_page_template']))) {
            return;
        }

        $id = (int) $dataHandler->substNEWwithIDs[$id];


        $newId = 'NEW_' . rand();
        $dataMap = [
            'tt_content' => [
                $newId => [
                    'pid' => $id,
                    'CType' => 'header',
                    'header' => 'Fængende overskrift',
                    'colPos' => 77
                ]
            ]
        ];

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($dataMap, []);
        $dataHandler->process_datamap();

    }

}
