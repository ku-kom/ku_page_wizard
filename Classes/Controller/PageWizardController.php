<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\KuPageWizard\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class PageWizardController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;

    protected StandaloneView $view;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory = null,
        IconFactory $iconFactory = null
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory ?? GeneralUtility::makeInstance(ModuleTemplateFactory::class);
        $this->iconFactory = $iconFactory ?? GeneralUtility::makeInstance(IconFactory::class);
    }


    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($request->getQueryParams()['id']) || (int) $request->getQueryParams()['id'] === 0) {
            return new HtmlResponse('Please select a page', 400);
        }

        $action = (string)($request->getQueryParams()['action'] ?? $request->getParsedBody()['action'] ?? 'index');

        /**
         * Define allowed actions
         */
        if (!in_array($action, ['index', 'copy'], true)) {
            return new HtmlResponse('Action not allowed', 400);
        }
        /**
         * Configure template paths for your backend module
         */
        /*
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplateRootPaths(['EXT:ku_page_wizard/Resources/Private/Templates/']);
        $this->view->setPartialRootPaths(['EXT:ku_page_wizard/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:ku_page_wizard/Resources/Private/Layouts/']);
        $this->view->setTemplate($action);
        $this->view->assign('id', $id);
        */

        /**
         * Call the passed in action
         */
        return $this->{$action . 'Action'}($request);

    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        /** @var PageRepository $pageRepository */
        $models = $this->getModelPages();
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
 
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        /*
        $shortCutButton = $buttonBar->makeShortcutButton()->setRouteIdentifier('web_page_wizard');
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
        */

        $link = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_page_wizard');
        $reloadButton = $buttonBar->makeLinkButton()
            ->setHref($link)
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($reloadButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);


        $view = $this->moduleTemplateFactory->create($request);
        $view->assignMultiple([
            'models' => $models,
            'id' => (int) $request->getQueryParams()['id']
        ]);
        return $view->renderResponse('List');
    }

    public function copyAction(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $request->getQueryParams();
        $source = (int) $parameters['source'];
        $destination = (int) $parameters['destination'];

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start(
            [],
            [
                'pages' => [
                    $source => [
                        'copy' => [
                            'aciton' => 'paste',
                            'target' => $destination,
                            'update' => [
                                'hidden' => 1
                            ]
                        ]
                    ]
                ]
            ]
        );
        $dataHandler->process_cmdmap();
        BackendUtility::setUpdateSignal('updatePageTree');

        $uid = $dataHandler->copyMappingArray_merged['pages'][$source];

        return new RedirectResponse(
            GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_layout', ['id' => $uid])
        );
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getModelPages(): array
    {
        /** @var ConnectionPool $pool */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class);
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $connection->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);

        $modelsPid = (int) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('ku_page_wizard', 'modelsPid');

            return $queryBuilder->select('uid', 'title', 'rowDescription AS description')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->in(
                        'pid',
                        $modelsPid
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();
    }
}
