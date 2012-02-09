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
 * @package    application.zoolu.modules.global.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ListHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-29: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class OverlayHelper {

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

  public function getConfirmationText(){
    return $this->core->translate->_('send_newsletter');
  }
  
  public function getOverlayTitle($blnTest){
    if($blnTest == 'true'){
      return $this->core->translate->_('Testsend_header');
    }else{
      return $this->core->translate->_('Send_header');
    }
  }
  
  /**
   * getInformation
   * @param string $strSubject
   * @param string $strFilter
   * @param number $intRecipients
   * @return string
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getInformation($strSubject, $strRecipients, $strFilter = ''){
    $strOutput = '';
    
    $strOutput .= '<p>';
    $strOutput .= $this->core->translate->_('subject').': '.$strSubject.'<br />';
    $strOutput .= $this->core->translate->_('filter').': '.(($strFilter != '') ? $strFilter : $this->core->translate->_('none')).'<br />';
    $strOutput .= $this->core->translate->_('recipients').': '.$strRecipients;
    $strOutput .= '</p>';
    
    return $strOutput;
  }
  
  public function getTestInformation($strSubject, $strRecipient){
    $strOutput = '';
    
    $strOutput .= '
    			<div class="editbox" id="editboxmaingroup">
            <div class="cornertl">
              <div id="pointermaingroup"></div>
            </div>
            <div class="cornertr"></div>
            <div class="editboxtitlecontainer">
              <div class="editboxtitle"></div><div class="clear"></div>
            </div>
            <div style="" class="editboxfields" id="fieldsboxmaingroup">
							<div class="field-12"><div class="field"><label class="fieldtitle" for="subject">Betreff *</label><br><input type="text" class="text" columns="6" helper="formText" value="'.$strSubject.'" id="subject" name="subject" readonly="true"></div></div>
							<div class="field-12"><div class="field"><label class="fieldtitle" for="testemail">Empfänger *</label><br><input type="text" class="text" columns="6" helper="formText" value="'.$strRecipient.'" id="testemail" name="testemail"></div></div>
            </div>
            <div class="cornerbl" id="cornerblmaingroup"></div>
            <div class="cornerbr" id="cornerbrmaingroup"></div>
          </div>';
    
    return $strOutput;
  }
}

?>