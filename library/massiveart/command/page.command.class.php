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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * PageCommand
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage PageCommand
 */

require_once(dirname(__FILE__) . '/command.interface.php');

class PageCommand implements CommandInterface
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Templates
     */
    protected $objModelTemplates;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * onCommand
     * @param string $strName
     * @param array $arrArgs
     * @return boolean
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function onCommand($strName, $arrArgs)
    {
        switch ($strName) {
            case 'addFolderStartElement':
                return $this->addFolderStartPage($arrArgs);
            case 'editFolderStartElement':
                return $this->editFolderStartPage($arrArgs);
            default:
                return true;
        }
    }

    /**
     * addFolderStartPage
     * @param array $arrArgs
     * @return boolean
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function addFolderStartPage($arrArgs)
    {
        try {
            if (array_key_exists('GenericSetup', $arrArgs) && $arrArgs['GenericSetup'] instanceof GenericSetup) {
                $objGenericSetup = $arrArgs['GenericSetup'];

                $intTemplateId = $this->core->sysConfig->page_types->page->startpage_templateId;
                $objTemplateData = $this->getModelTemplates()->loadTemplateById($intTemplateId);

                if (count($objTemplateData) == 1) {
                    $objTemplate = $objTemplateData->current();

                    /**
                     * set form id from template
                     */
                    $strFormId = $objTemplate->genericFormId;
                    $intFormVersion = $objTemplate->version;
                    $intFormTypeId = $objTemplate->formTypeId;
                } else {
                    throw new Exception('Not able to create a generic data object, because there is no form id!');
                }

                $objGenericData = new GenericData();
                $objGenericData->Setup()->setFormId($strFormId);
                $objGenericData->Setup()->setFormVersion($intFormVersion);
                $objGenericData->Setup()->setFormTypeId($intFormTypeId);
                $objGenericData->Setup()->setTemplateId($intTemplateId);
                $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->add);
                $objGenericData->Setup()->setLanguageId($arrArgs['LanguageId']);
                $objGenericData->Setup()->setLanguageCode($arrArgs['LanguageCode']);
                $objGenericData->Setup()->setFormLanguageId($this->core->intZooluLanguageId);

                $objGenericData->Setup()->setParentId($arrArgs['ParentId']);
                $objGenericData->Setup()->setRootLevelId($objGenericSetup->getRootLevelId());
                $objGenericData->Setup()->setElementTypeId($this->core->sysConfig->page_types->page->id);
                $objGenericData->Setup()->setCreatorId($objGenericSetup->getCreatorId());
                $objGenericData->Setup()->setStatusId($objGenericSetup->getStatusId());
                $objGenericData->Setup()->setShowInNavigation($objGenericSetup->getShowInNavigation());
                $objGenericData->Setup()->setHideInSitemap($objGenericSetup->getHideInSitemap());
                $objGenericData->Setup()->setModelSubPath('cms/models/');

                $arrPageAttributes = array('segmentId' => $objGenericSetup->getSegmentId());
                $objGenericData->addFolderStartElement($objGenericSetup->getCoreField('title')->getValue(), $arrPageAttributes);

                return true;
            } else {
                throw new Exception('There is no GenericSetup in the args array!');
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            return false;
        }
    }

    /**
     * editFolderStartPage
     * @param array $arrArgs
     * @return boolean
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function editFolderStartPage($arrArgs)
    {
        try {
            if (array_key_exists('GenericSetup', $arrArgs) && $arrArgs['GenericSetup'] instanceof GenericSetup) {
                $objGenericSetup = $arrArgs['GenericSetup'];

                $intFolderId = $objGenericSetup->getElementId();
                $intUserId = Zend_Auth::getInstance()->getIdentity()->id;


                $arrProperties = array(
                    'idUsers'          => $intUserId,
                    'creator'          => $objGenericSetup->getCreatorId(),
                    'idStatus'         => $objGenericSetup->getStatusId(),
                    'showInNavigation' => $objGenericSetup->getShowInNavigation(),
                    'hideInSitemap'    => $objGenericSetup->getHideInSitemap(),
                    'changed'          => date('Y-m-d H:i:s')
                );

                $arrTitle = array(
                    'idUsers'     => $intUserId,
                    'creator'     => $objGenericSetup->getCreatorId(),
                    'title'       => $objGenericSetup->getCoreField('title')->getValue(),
                    'idLanguages' => $objGenericSetup->getLanguageId(),
                    'changed'     => date('Y-m-d H:i:s')
                );

                $arrPageAttributes = array(
                    'idUsers'    => $intUserId,
                    'creator'    => $objGenericSetup->getCreatorId(),
                    'changed'    => date('Y-m-d H:i:s'),
                    'idSegments' => $objGenericSetup->getSegmentId()
                );
                
                $this->getModelPages($arrArgs)->updateStartPageMainData($intFolderId, $arrProperties, $arrTitle, $arrPageAttributes, $objGenericSetup->getRootLevelId());
                return true;
            } else {
                throw new Exception('There is no GenericSetup in the args array!');
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            return false;
        }
    }

    /**
     * getModelPages
     * @param array $arrArgs
     * @return Model_Pages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages($arrArgs)
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($arrArgs['LanguageId']);
        }

        return $this->objModelPages;
    }

    /**
     * getModelTemplates
     * @return Model_Templates
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelTemplates()
    {
        if (null === $this->objModelTemplates) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Templates.php';
            $this->objModelTemplates = new Model_Templates();
        }

        return $this->objModelTemplates;
    }
}

?>