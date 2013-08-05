<?php

/**
 * SearchController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-20: Cornelius Hansjakob
 * 1.1, 2013-08-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class SearchController extends WebControllerAction
{

    /**
     * @var boolean
     */
    protected $blnHasSegments;

    /**
     * init
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function init()
    {
        parent::init();
        $this->validateLanguage();

        $this->view->setScriptPath(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/scripts');
    }

    /**
     * searchAction
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function searchAction()
    {
        $this->core->logger->debug('website->controllers->SearchController->searchAction()');

        $this->initPageView();

        $request = $this->getRequest();

        $subject = strip_tags($request->getParam('q'));
        $rootLevelId = $request->getParam('rootLevelId');
        $arrSegmentationInfos = $request->getParam('segmentation');

        $this->blnHasSegments = (array_key_exists('hasSegments', $arrSegmentationInfos)) ? $arrSegmentationInfos['hasSegments'] : false;
        $this->intSegmentId = (array_key_exists('id', $arrSegmentationInfos)) ? $arrSegmentationInfos['id'] : null;
        $this->strSegmentCode = (array_key_exists('code', $arrSegmentationInfos)) ? $arrSegmentationInfos['code'] : null;

        // TODO : SEARCH
        /*$objSearch = New Search();
        $objSearch->setSearchValue($strSearchValue);
        $objSearch->setLanguageId($this->intLanguageId);
        $objSearch->setRootLevelId($intRootLevelId);*/

        // output to view
        $this->view->hasSegments = $this->blnHasSegments;
        $this->view->segmentId = $this->intSegmentId;
        $this->view->segmentCode = $this->strSegmentCode;
        $this->view->languageDefinitionType = $request->getParam('languageDefinitionType', '1');
        $this->view->rootLevelId = $rootLevelId;
        $this->view->subject = $subject;
        $this->view->hits = null; // $objSearch->search()
    }

    /**
     * livesearchAction
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function livesearchAction()
    {
        $this->core->logger->debug('website->controllers->SearchController->livesearchAction()');

        $request = $this->getRequest();

        $this->setTranslate();

        $this->initPageView(false);

        if ($request->isXmlHttpRequest() && strip_tags($request->getParam('q')) != '') {

            $subject = strip_tags($request->getParam('q'));
            $rootLevelId = $request->getParam('rootLevelId');

            $this->intSegmentId = intval($request->getParam('segmentId', null));
            $this->strSegmentCode = $request->getParam('segmentCode', null);
            $this->blnHasSegments = ($this->intSegmentId !== null && $this->strSegmentCode !== null) ? true : false;

            /* TODO : SEARCH
            $objSearch = New Search();
            $objSearch->setSearchValue($strSearchValue);
            $objSearch->setLanguageId($this->intLanguageId);
            $objSearch->setRootLevelId($intRootLevelId);
            $objSearch->setLimitLiveSearch(5);*/

            $this->view->hasSegments = $this->blnHasSegments;
            $this->view->segmentId = $this->intSegmentId;
            $this->view->segmentCode = $this->strSegmentCode;
            $this->view->languageDefinitionType = $request->getParam('languageDefinitionType', '1');
            $this->view->rootLevelId = $rootLevelId;
            $this->view->subject = $subject;
            $this->view->base = $request->getParam('searchBase', '/');
            $this->view->hits = null; // $objSearch->livesearch()

        } else {
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    /**
     * initPageView
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function initPageView($initLayout = true)
    {
        if ($initLayout === true) {
            Zend_Layout::startMvc(array(
                'layout'     => 'search',
                'layoutPath' => GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path
            ));
            Zend_Layout::getMvcInstance()->setViewSuffix('php');

            $this->setTranslate();

            $this->initNavigation();
        }

        // Initialize SearchHelper
        if (file_exists(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SearchHelper.php')) {
            require_once(GLOBAL_ROOT_PATH . 'public/website/themes/' . $this->objTheme->path . '/helpers/SearchHelper.php');
            $strSearchHelper = ucfirst($this->objTheme->path) . '_SearchHelper';
            $objSearchHelper = new $strSearchHelper();
        } else {
            require_once(dirname(__FILE__) . '/../helpers/SearchHelper.php');
            $objSearchHelper = new SearchHelper();
        }

        $objSearchHelper->setTranslate($this->translate)
            ->setTheme($this->objTheme->path);

        Zend_Registry::set('SearchHelper', $objSearchHelper);
    }
}