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
 * @package    library.massiveart.generic.forms.validators
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

require_once(dirname(__FILE__).'/Abstract.php');

/**
 * Form_Validator_UniqueUrl
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-20: Daniel Rotter
 * 
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Form_Validator_LandingPageUrl extends Form_Validator_Abstract {
  /**
   * @var array
   */
  protected $_arrMessages;
  
  /**
   * getMessages
   * @see Zend_Validate_Interface
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getMessages(){
    return $this->_arrMessages;
  }
  
  /**
   * addMessage
   * @param string $strKey
   * @param string $strMessage
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function addMessage($strKey, $strMessage){
    $this->_arrMessages[$strKey] = $strMessage;
  }
  
  /**
   * isValid
   * @see Zend_Validate_Interface
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function isValid($value){
    $strValue = strtolower($value);
    
    $isValid = true;
    
    //Load data
    $intElementId = ($this->Setup()->getElementLinkId()) ? $this->Setup()->getElementLinkId() : $this->Setup()->getElementId();
    
    //Load URL and check if it belongs to another entry
    
    if(!$isValid){
      $this->addMessage('errMessage', $this->core->translate->_('Err_existing_landingpageurl'));
    }
    
    return $isValid;
  }

  /**
   * getModelUrls
   * @return Model_Urls
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.1
   */
  protected function getModelUrls(){
    if (null === $this->objModelUrls) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Urls.php';
      $this->objModelUrls = new Model_Urls();
      $this->objModelUrls->setLanguageId($this->Setup()->getLanguageId());
    }

    return $this->objModelUrls;
  }
}
?>