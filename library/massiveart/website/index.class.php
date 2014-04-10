<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    library.massiveart.website
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Index
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Index
 */

class Index
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Pages
     */
    private $objModelPages;

    /**
     * @var Model_Globals
     */
    private $objModelGlobals;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * @param $strPageId
     * @param $intPageVersion
     * @param $intLanguageId
     * @param $intRootLevelId
     */
    public function indexPage($strPageId, $intPageVersion, $intLanguageId, $intRootLevelId)
    {
        try {
            $this->core->logger->debug('massiveart->website->index->indexPage(' . $strPageId . ', ' . $intPageVersion . ', ' . $intLanguageId . ', ' . $intRootLevelId . ')');

            $objPage = $this->getPage();
            $objPage->setPageId($strPageId);
            $objPage->setPageVersion($intPageVersion);
            $objPage->setLanguageId($intLanguageId);
            $objPage->setRootLevelId($intRootLevelId);
            $objPage->setType('page');
            $objPage->setModelSubPath('cms/models/');

            $objPage->loadPage();

            $objPage->indexPage();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $key
     */
    public function indexRemovePages($key)
    {
        try {
            $search = new \Sulu\Search\Search($this->core->sysConfig->search->toArray(), 'page');
            $search->getIndex()->delete($key);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $strGlobalId
     * @param $intGlobalLinkId
     * @param $intGlobalVersion
     * @param $intLanguageId
     * @param $intRootLevelId
     */
    public function indexGlobal($strGlobalId, $intGlobalLinkId, $intGlobalVersion, $intLanguageId, $intRootLevelId)
    {
        try {
            $this->core->logger->debug(
                'massiveart->website->index->indexGlobal(' . $strGlobalId . ', ' . $intGlobalLinkId . ', ' . $intGlobalVersion . ', ' . $intLanguageId . ', ' . $intRootLevelId . ')'
            );

            $objPage = $this->getPage();
            $objPage->setPageId($strGlobalId);
            $objPage->setPageVersion($intGlobalVersion);
            $objPage->setLanguageId($intLanguageId);
            $objPage->setElementLinkId($intGlobalLinkId);
            $objPage->setRootLevelId($intRootLevelId);
            $objPage->setType('global');
            $objPage->setModelSubPath('global/models/');

            $objPage->loadPage();

            $objPage->indexGlobal($intLanguageId);

            $objPage = null;
            unset($objPage);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $key
     */
    public function indexRemoveGlobals($key)
    {
        try {
            $search = new \Sulu\Search\Search($this->core->sysConfig->search->toArray(), 'global');
            $search->getIndex()->delete($key);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

/**
     * indexAllPublicPages
     */
    public function indexAllPublicPages()
    {
        try {
            $arrTreeTypes = array( 
                $this->core->sysConfig->page_types->product_tree->id,
                $this->core->sysConfig->page_types->press_area->id,
                $this->core->sysConfig->page_types->courses->id,
                $this->core->sysConfig->page_types->events->id
            );
            
            $this->getModelPages();
            $objPagesData = $this->objModelPages->loadAllPublicPages();

            $i = 0;
            foreach ($objPagesData as $objPageData) {
                echo ++$i . "/" . count($objPagesData) . "  Pagetype: " . $objPageData->idPageTypes . "  index: ";
                if (!in_array($objPageData->idPageTypes, $arrTreeTypes)) {
                    echo "yes";
                    $this->indexPage(
                        $objPageData->pageId,
                        $objPageData->version,
                        $objPageData->idLanguages,
                        ((int)$objPageData->idRootLevels > 0) ? $objPageData->idRootLevels : $objPageData->idParent
                    );
                }
                echo "\n";
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param null $intRootLevelId
     * @param null $intLanguageId
     */
    public function indexAllPublicGlobals($intRootLevelId = null, $intLanguageId = null)
    {
        try {
            $this->getModelGlobals();

            $strIndexGlobalFilePath = GLOBAL_ROOT_PATH . 'cli/IndexGlobal.php';

            $objGlobalsData = $this->objModelGlobals->loadAllPublicGlobals($intRootLevelId, $intLanguageId);

            $intTotal = count($objGlobalsData);
            $intCounter = 0;

            foreach ($objGlobalsData as $objGlobalData) {
                $intCounter++;
                echo $intCounter . "/" . $intTotal . " - mem usage is: " . memory_get_usage() . "\n";
                //$this->indexGlobal($objGlobalData->globalId, $objGlobalData->idLink, $objGlobalData->version, $objGlobalData->idLanguages, ((int) $objGlobalData->idRootLevels > 0) ? $objGlobalData->idRootLevels : $objGlobalData->idParent);
                exec(
                    "php $strIndexGlobalFilePath --globalId='" . $objGlobalData->globalId . "' --linkId='" . $objGlobalData->idLink . "' --version=" . $objGlobalData->version . " --languageId=" . $objGlobalData->idLanguages . " --rootLevelId=" . (((int)$objGlobalData->idRootLevels > 0) ? $objGlobalData->idRootLevels : $objGlobalData->idParent)
                );
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @return Client_Page|Page
     */
    protected function getPage()
    {
        if (file_exists(GLOBAL_ROOT_PATH . 'client/website/page.class.php')) {
            require_once(GLOBAL_ROOT_PATH . 'client/website/page.class.php');
            $objPage = new Client_Page();
        } else {
            $objPage = new Page();
        }

        return $objPage;
    }

    /**
     * @return Model_Pages
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
        }

        return $this->objModelPages;
    }

    /**
     * @return Model_Globals
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
        }

        return $this->objModelGlobals;
    }
}
