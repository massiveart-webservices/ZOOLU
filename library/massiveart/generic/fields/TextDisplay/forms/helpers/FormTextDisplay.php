<?php

/**
 * Form_Helper_FormTextDisplay
 * 
 * Helper to generate a "tag" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-19: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormTextDisplay
 */

class Form_Helper_FormTextDisplay extends Zend_View_Helper_FormElement {
  
  /**
   * formTextDisplay
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options   
   * @version 1.0
   */
  public function formTextDisplay($name, $value = null, $attribs = null, $options = null){
    $info = $this->_getInfo($name, $value, $attribs);
    extract($info); // name, value, attribs, options, listsep, disable
    
    // XHTML or HTML end tag
    $endTag = ' />';
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }
    
    $strOutput = '';
    if($value != ''){
      // build the element
      $strOutput = '
                  <div class="textdisplaywrapper">
                    <span class="gray666 bigger bold">'.$this->view->escape($value).'</span>
                    <input type="hidden" value="'.$this->view->escape($value).'" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" '.$endTag.'
                  </div>';
    }
    
    return $strOutput;
  }
}

?>