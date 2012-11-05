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
 * @package    application.zoolu.modules.core.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ContentchooserHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-11-05: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

require_once (dirname(__FILE__) . '/../../../media/views/helpers/ViewHelper.php');

class ContentchooserHelper
{
    /**
     * @var Core
     */
    private $core;

    /**
     * @var Translate
     */
    private $objTranslate;


    /**
     * Constructor
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * getModuleListView
     * @param object $objElements
     * @param string $strOverlayTitle
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getModuleListView($objElements, $strOverlayTitle)
    {
        $this->core->logger->debug('core->views->helpers->DashboardHelper->getModuleListView()');

        $strReturn = '';

        if (count($objElements) > 0) {
            /**
             * create header of list output
             */
            $strReturn .= '
                <div id="olModules">
                    <div id="olModules_title" style="display:none;">' . $strOverlayTitle . '</div>
                    <div class="olcontacttop">
                        ' . $this->objTranslate->_('Name') . '
                    </div>
                    <div class="olcontactitemcontainer">';

            foreach ($objElements as $objRow) {
                // only PORTALS, GLOBAL, MEDIA visible
                if ($objRow->resourceKey == 'portals' || $objRow->resourceKey == 'global' || $objRow->resourceKey == 'media') { // || $objRow->resourceKey == 'media'
                    $strReturn .= '
                        <div class="olcontactitem" id="olModuleItem' . $objRow->id . '" onclick="myContentchooser.getModule(' . $objRow->id . '); return false;">
                            <div class="olcontactleft"></div>
                            <div style="display:none;" id="Remove' . $objRow->id . '" class="itemremovelist"></div>
                            <div class="olcontactitemtitle">' . $this->objTranslate->_($objRow->resourceKey) . '</div>
                            <div class="olcontactright"></div>
                            <div class="clear"></div>
                        </div>';
                }
            }

            /**
             * list footer
             */
            $strReturn .= '
                        <div class="clear"></div>
                    </div>
                    <div class="olcontactbottom">
                    </div>
                </div>';
        }

        return $strReturn;
    }

    /**
     * setTranslate
     * @param object $objTranslate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setTranslate($objTranslate)
    {
        $this->objTranslate = $objTranslate;
    }

    /**
     * getTranslate
     * @param object $objTranslate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTranslate()
    {
        return $this->objTranslate;
    }
}
