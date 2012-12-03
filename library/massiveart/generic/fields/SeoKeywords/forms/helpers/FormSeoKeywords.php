<?php
class Form_Helper_FormSeoKeywords extends Zend_View_Helper_FormElement
{
    function formSeoKeywords($name, $value = null, $attribs = null) {

        $core = Zend_Registry::get('Core');

        $max_keywords = 5;

        $info = $this->_getInfo($name, $value, $attribs);
        extract($info);

        if( $value == '' ) {
            $keywords_count = 0;
        } else {
            $keywords_count = count(explode(',', $value));
        }

        $strOutput = '';
        $strOutput .= '<textarea rows="80" cols="24" name="'.$name.'" id="'.$name.'"
                                onKeyDown="myForm.limitKeywords(\''.$name.'\', '.$max_keywords.')"
                                onKeyUp="myForm.limitKeywords(\''.$name.'\', '.$max_keywords.')"
                           >'
                            . $this->view->escape($value) .
                      '</textarea>';
        $strOutput .= str_replace('%s', $keywords_count, $core->translate->_('Seo_max_keywords', false));
        $strOutput .= '<script type="text/javascript">myForm.limitKeywords(\''.$name.'\', '.$max_keywords.');</script>';

        return $strOutput;
    }
}
?>