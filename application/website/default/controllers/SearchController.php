<?php

/**
 * SearchController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class SearchController extends Zend_Controller_Action {

    /**
     * @var Core
     */
    private $core;

    /**
     * @var integer
     */
    protected $intLanguageId;

    /**
     * @var string
     */
    protected $strLanguageCode;

    /**
     * @var HtmlTranslate
     */
    private $translate;

    /**
     * @var boolean
     */
    private $blnHasSegments;

    /**
     * @var integer
     */
    private $intSegmentId;

    /**
     * @var string
     */
    private $strSegmentCode;
     
    /**
     * init index controller and get core obj
     */
    public function init(){
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * indexAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function indexAction(){
        $this->core->logger->debug('website->controllers->SearchController->indexAction()');

        $request = $this->getRequest();
        $strSearchValue = strip_tags($request->getParam('q'));
        $intRootLevelId = $request->getParam('rootLevelId');
        $arrSegmentationInfos = $request->getParam('segmentation');

        $this->blnHasSegments = (array_key_exists('hasSegments', $arrSegmentationInfos)) ? $arrSegmentationInfos['hasSegments'] : false;
        $this->intSegmentId = (array_key_exists('id', $arrSegmentationInfos)) ? $arrSegmentationInfos['id'] : null;
        $this->strSegmentCode = (array_key_exists('code', $arrSegmentationInfos)) ? $arrSegmentationInfos['code'] : null;

        $this->intLanguageId = $this->core->intLanguageId;
        $this->strLanguageCode = $this->core->strLanguageCode;

        /**
         * set for output
         */
        $this->view->strLanguageCode = $this->strLanguageCode;
         
        /**
         * set up zoolu translate obj
         */
        if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo')){
            $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo');
        }else{
            $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->sysConfig->languages->default->code.'.mo');
        }

        $this->view->translate = $this->translate;

        $objSearch = New Search();
        $objSearch->setSearchValue($strSearchValue);
        $objSearch->setLanguageId($this->intLanguageId);
        $objSearch->setRootLevelId($intRootLevelId);

        /**
         * set for output
        */
        $this->view->objHits = $objSearch->search();
        $this->view->strSearchValue = $strSearchValue;
        $this->view->languageCode = $this->strLanguageCode;
        $this->view->rootLevelId = $intRootLevelId;
        $this->view->hasSegments = $this->blnHasSegments;
        $this->view->segmentId = $this->intSegmentId;
        $this->view->segmentCode = $this->strSegmentCode;

        $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$request->getParam('theme').'/');
        $this->renderScript('search.php');
    }

    /**
     * livesearchAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function livesearchAction(){
        $this->core->logger->debug('website->controllers->SearchController->livesearchAction()');

        if (isset($_GET['q']) && $_GET['q'] != '') {

            $request = $this->getRequest();
            $strSearchValue = $request->getParam('q');
            $this->intLanguageId = $request->getParam('languageId');
            $this->strLanguageCode = $request->getParam('languageCode', $this->core->strLanguageCode);
            $this->intSegmentId = intval($request->getParam('segmentId'));
            $this->strSegmentCode = $request->getParam('segmentCode');
            $intRootLevelId = $request->getParam('rootLevelId');

            /**
             * set up zoolu translate obj
            */
            if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo')){
                $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo');
            }else{
                $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->sysConfig->languages->default->code.'.mo');
            }

            $this->view->translate = $this->translate;

            $objSearch = New Search();
            $objSearch->setSearchValue($strSearchValue);
            $objSearch->setLanguageId($this->intLanguageId);
            $objSearch->setRootLevelId($intRootLevelId);
            $objSearch->setLimitLiveSearch(5);

            $this->view->objHits = $objSearch->livesearch();
            $this->view->rootLevelId = $intRootLevelId;
            $this->view->segmentId = $this->intSegmentId;
            $this->view->segmentCode = $this->strSegmentCode;
            $this->view->searchTerm = $strSearchValue;
        } else {
            $this->_helper->viewRenderer->setNoRender();
        }
    }
}
?>