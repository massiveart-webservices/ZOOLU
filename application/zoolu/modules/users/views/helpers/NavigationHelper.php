<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    application.zoolu.modules.users.views.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class NavigationHelper {

  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * getModuleRootLevels
   * @param Zend_Db_Table_Rowset_Abstract $objRowset
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  function getModuleRootLevels($objRowset) {
    $this->core->logger->debug('users->views->helpers->NavigationHelper->getModuleRootLevels()');

    $strOutput = '';

    $strRootLevelType = '';
    foreach ($objRowset as $objRow) {

      switch($objRow->idRootLevelTypes){
        case $this->core->sysConfig->root_level_types->users:
          $strRootLevelType = 'user';
          break;
        case $this->core->sysConfig->root_level_types->groups:
          $strRootLevelType = 'group';
          break;
        case $this->core->sysConfig->root_level_types->resources:
          $strRootLevelType = 'resource';
          break;
        default:
          $strRootLevelType = 'user';
          break;
      }

      /**
       * get values of the row and create output
       */
      $strOutput .= '
            <div class="naviitemcontainer">
              <div id="naviitem'.$objRow->id.'top" class="top"><img height="4" width="230" src="/zoolu-statics/images/main/bg_box_230_top.png"/></div>
              <div id="naviitem'.$objRow->id.'" class="naviitem" onclick="myNavigation.getModuleRootLevelList('.$objRow->id.', \''.$strRootLevelType.'\'); return false;">
                <div class="'.$strRootLevelType.'icon"></div>
                <div class="itemtitle">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
                 <div class="clear"></div>
              </div>
              <div id="naviitem'.$objRow->id.'bottom" class="bottom"><img height="4" width="230" src="/zoolu-statics/images/main/bg_box_230_bottom.png"/></div>
            </div>';
    }

    return $strOutput;
  }

}

?>