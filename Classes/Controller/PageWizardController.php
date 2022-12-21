<?php

declare(strict_types=1);

namespace UniversityOfCopenhagen\KuPageWizard\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Routing\UriBuilder;
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
        $id = (int) $request->getQueryParams()['id'];

        /**
         * Define allowed actions
         */
        if (!in_array($action, ['index'], true)) {
            return new HtmlResponse('Action not allowed', 400);
        }

        /**
         * Configure template paths for your backend module
         */
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplateRootPaths(['EXT:ku_page_wizard/Resources/Private/Templates/']);
        $this->view->setPartialRootPaths(['EXT:ku_page_wizard/Resources/Private/Partials/']);
        $this->view->setLayoutRootPaths(['EXT:ku_page_wizard/Resources/Private/Layouts/']);
        $this->view->setTemplate($action);
        $this->view->assign('pageid', $id);
        $this->view->assign('edit', ['pages' => [$id => 'new']]);

        /**
         * Call the passed in action
         */
        $result = $this->{$action . 'Action'}($request);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        /**
         * Render template and return html content
         */
        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
 
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $shortCutButton = $buttonBar->makeShortcutButton()->setRouteIdentifier('web_txkupagewizard');
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

        $link = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('web_txkupagewizard');
        $reloadButton = $buttonBar->makeLinkButton()
            ->setHref($link)
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($reloadButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);

        $moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($moduleTemplate->renderContent());
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
