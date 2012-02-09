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
 * @package    library.massiveart.generic.forms.decorators
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Form_Decorator_Region
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-07-22: Florian Mathis
 *
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.0
 */

class Form_Decorator_Tab extends Zend_Form_Decorator_Fieldset {

  protected $_helper = 'tab';

	public function getHelper(){
	  if(null !== ($helper = $this->getOption('helper'))){
	    $this->setHelper($helper);
	    $this->removeOption('helper');
	  }

	  return $this->_helper;
  }

  /**
   * Render a region
   *
   * @param  string $content
   * @return string
   */
  public function render($content) {
    $form = $this->getElement();
    $view = $form->getView();

    if(null === $view){
      return $content;
    }

    $helper        = $this->getHelper();
    $attribs       = $this->getOptions();
    $name          = $form->getFullyQualifiedName();
    $attribs['id'] = $form->getId();

    $strHideTab = ($form->getHide() == true) ? ' style="display:none;"' : '';
    
    $strContent = '';
    $strAction = $form->getAction();
    if($strAction == null){
      $strContent = $content;
    }else{
      $strContent = '<script type="text/javscript">
        myCore.addBusyClass("div'.$name.'");
        new Ajax.Updater("div'.$name.'", "'.$strAction.'", {
       	  evalScripts: true,
          parameters: {
            id: $F("id")
          },
          onComplete: function(){
            myCore.removeBusyClass("div'.$name.'");
          }
        });
      </script>';
    }
    
    $strOutput = '<div class="tab" id="div'.$name.'"'.$strHideTab.'>'.$strContent.'</div>';
    return $strOutput;
  }
}

?>