<?php
class Form_Helper_FormSnippetPreview extends Zend_View_Helper_FormElement
{
    function formSnippetPreview($name, $value = null, $attribs = null, $options = null) {

        $strOutput = '';

        #$info = $this->_getInfo($name, $value, $attribs, $options);
        #extract($info); // name, value, attribs, options

        $strOutput .= '
            <style>
                .seo_wrapper{ border: 1px solid #C0C0C0; padding: 5px; border-radius: 5px; float: left; }
                .seo_title { color: #1122CC; font-size: 16px; line-height: 19px; text-decoration: underline; }
                .seo_url { color: #009933; font-style: normal; font-size: 13px; color: #282; line-height: 15px; cursor: pointer; }
                .seo_desc { font-size: 13px; color: #000; line-height: 15px; width: 500px; }
            </style>
        ';

        $pageUrl = $_SERVER['SERVER_NAME'];

        $strOutput .= '<div class="seo_wrapper">';
        $strOutput .= '<a class="seo_title" id="snippet_seo_title"></a><br/>';
        $strOutput .= '<a class="seo_url" id="snippet_seo_url">' . $pageUrl . '</a><br/>';
        $strOutput .= '<p class="seo_desc" id="snippet_seo_desc"></p>';
        $strOutput .= '</div>';
        $strOutput .= '<div style="clear: both"></div>';

        $strOutput .= '
            <script type="text/javascript">/* <![CDATA[ */
                myForm.initSnippetPreview();
            /* ]]> */</script>
        ';

        return '<div id="seo_snippet_wrapper">' . $strOutput . '</div>';
    }

}