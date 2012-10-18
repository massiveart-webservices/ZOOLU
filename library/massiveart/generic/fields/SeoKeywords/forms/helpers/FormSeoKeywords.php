<?php
class Form_Helper_FormSeoKeywords extends Zend_View_Helper_FormElement
{
    function formSeoKeywords($name, $value = null, $attribs = null) {

        $max_keywords = 5;

        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        $strOutput = '';

        $strOutput .= '<textarea rows="80" cols="24" name="'.$name.'" id="'.$name.'"
                                onKeyDown="myForm.limitKeywords(\''.$name.'\', '.$max_keywords.')"
                                onKeyUp="myForm.limitKeywords(\''.$name.'\', '.$max_keywords.')"
                           >'
                            . $this->view->escape($value) .
                      '</textarea>';

        return $strOutput;
    }
}
?>