<?php
class Form_Helper_FormSnippetPreview extends Zend_View_Helper_FormElement
{
    function formSnippetPreview($name, $value = null, $attribs = null, $options = null) {

        $strOutput = '';

        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // name, value, attribs, options

        $strOutput .= '<div id="seosnippet">';
        $strOutput .= '<a class="pagetitle" style="color: #1122CC"></a>';
        $strOutput .= '<a class="pageurl" style="color: #009933; font-style: normal;"></a>';
        $strOutput .= '<a class="pagedesc"></a>';
        $strOutput .= '</div>';

        return $strOutput;
    }
}