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
 * @package    application.plugins.Gmaps.forms.decorators
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Plugin_FormDecorator_Gmaps
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-23: Florian Mathis
 * 
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.0
 */

class Plugin_FormDecorator_Gmaps extends Zend_Form_Decorator_Abstract {
  
  /**
   * @var Core
   */
  private $core;
  
  /**
   * Constructor 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct($options = null){        
    $this->core = Zend_Registry::get('Core');
    parent::__construct($options);
  } 
  
  /**
   * buildLabel
   */
  public function buildLabel(){
    
    $element = $this->getElement();
    $label = $element->getLabel();
    
    if (empty($label)){
      return '';
    }
    
    if ($element->isRequired()) {
      $label .= ' *';
    }
    
    return $element->getView()->formLabel($element->getName(), $label, array('class' => 'fieldtitle')).'<br/>';
  }
  
  /**
   * buildDescription
   */
  public function buildDescription(){
    $element = $this->getElement();
    $desc    = $element->getDescription();
    
    if (empty($desc)){
      return '';
    }
    
    return '<div class="description">'.$desc.'</div>';
  }
  
  /**
   * buildInput
   */
  public function buildInput(){
    
    $element = $this->getElement();
    $helper  = $element->helper;
    
    switch ($helper){
      case 'form'.ucfirst(GenericSetup::FIELD_TYPE_TEXTEDITOR) :
        return $element->getView()->$helper($element->getName(), $element->getValue(), $element->getAttribs(), $element->options, $element->regionId);
      case 'form'.ucfirst(GenericSetup::FIELD_TYPE_INTERNALLINK) :              
        return $element->getView()->$helper($element->getName(), $element->getValue(), $element->getAttribs(), $element->options, $element);
      default:
        return $element->getView()->$helper($element->getName(), $element->getValue(), $element->getAttribs(), $element->options);
    }
  }
  
  /**
   * buildErrors
   */
  public function buildErrors(){
    
    $element  = $this->getElement();
    $messages = $element->getMessages();
    
    if (empty($messages)){
      return '';
    }
    
    return '<div class="errors">'.$element->getView()->formErrors($messages).'</div>';
  }
  
  /**
   * render
   */
  public function render($content){
    
    $element = $this->getElement();
       
    if (!$element instanceof Zend_Form_Element) {
      return $content;
    }
        
    if (null === $element->getView()) {
      return $content;
    }
    
    /**
     * is empty element
     */
    $blnIsEmpty = false;
    if(array_key_exists('isEmptyField', $element->getAttribs()) && $element->getAttrib('isEmptyField') == 1){
      $blnIsEmpty = true;  
    }

    $separator = $this->getSeparator();
    $placement = $this->getPlacement();
    $label     = $this->buildLabel();
    $input     = $this->buildInput();
    $errors    = $this->buildErrors();
    $desc      = $this->buildDescription();
    
    $output = '<div class="field-'.$element->getAttrib('columns').'">';
    $output .= '<div class="field">'
                    .$label
                    .$desc
                    .$input
                    .$errors
              .'</div>';
    $output .= '</div>';
    
    if($element->isRegionTitle == 1){
      if($blnIsEmpty == true){
        $output .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.addRegionTitle(\''.$element->getName().'\', \''.$element->regionId.'\');         
        //]]>
        </script>';
      }else{
        $output .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.initRegionTitleObserver(\''.$element->getName().'\', \''.$element->regionId.$element->regionExtension.'\');         
        //]]>
        </script>';
      }  
    }

    switch ($placement) {
      case (self::PREPEND):
        return $output . $separator . $content;
      case (self::APPEND):
      default:
        return $content . $separator . $output;
    }
  }
  
}

?>