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
 * @package    application.website.default.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Page output functions
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

/**
 * getCoreObject
 * @return Core
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function getCoreObject(){
  return Zend_Registry::get('Core');
}

/**
 * getPageHelperObject
 * @return PageHelper
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function getPageHelperObject(){
  return Zend_Registry::get('PageHelper');
}

/**
 * get_template_file
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_template_file(){
  return getPageHelperObject()->getTemplateFile();
}

/**
 * get_portal_title
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_portal_title(){
  echo getPageHelperObject()->getRootLevelTitle();
}

/**
 * get_static_component_domain
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_static_component_domain(){
  echo getCoreObject()->config->domains->static->components;
}

/**
 * get_meta_keywords
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_meta_keywords(){
  echo getPageHelperObject()->getMetaKeywords();
}

/**
 * get_meta_description
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_meta_description(){
  echo getPageHelperObject()->getMetaDescription();
}

function get_meta_robots(){
  echo getPageHelperObject()->getMetaRobots();
}

/**
 * get_zoolu_header
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_zoolu_header(){
  echo getPageHelperObject()->getZooluHeader();
}

/**
 * get_elementId
 * @param boolean $blnReturnBool
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_elementId($blnReturnBool = false){
  if($blnReturnBool){
    return getPageHelperObject()->getElementId(); 
  }else{
    echo getPageHelperObject()->getElementId();  
  }
}

/**
 * get_title
 * @param string $strTag
 * @param boolean $blnTitleFallback
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_title($strTag = '', $blnTitleFallback = true){
  echo getPageHelperObject()->getTitle($strTag, $blnTitleFallback);
}

/**
 * get_meta_title
 * @param string $strTag
 * @param boolean $blnTitleFallback
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_meta_title($strTag = '', $blnTitleFallback = true){
  echo getPageHelperObject()->getMetaTitle($strTag, $blnTitleFallback);
}

/**
 * get_canonical_tag
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
function get_canonical_tag(){
  echo getPageHelperObject()->getCanonicalTag();
}

/**
 * get_canonical_tag_for_segmentation
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_canonical_tag_for_segmentation(){
  echo getPageHelperObject()->getCanonicalTagForSegmentation();
}

/**
 * get_parent_title
 * @param string $strTag
 * @param boolean $blnTitleFallback
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_parent_title($strTag = '', $blnTitleFallback = true){
  echo getPageHelperObject()->getParentTitle($strTag, $blnTitleFallback);
}

/**
 * get_description
 * @param string $strContainerClass
 * @param boolean $blnContainer
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_description($strContainerClass = 'description', $blnContainer = true){
  echo getPageHelperObject()->getDescription($strContainerClass, $blnContainer);
}

/**
 * get_abstract
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_abstract(){
  echo getPageHelperObject()->getAbstract();
}

/**
 * get_sidebar
 * @param string $strContainerClass
 * @param string $strBlockClass
 * @param string $strImageFolder
 * @param integer $intRegionId
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_sidebar($strContainerClass = 'sidebar', $strBlockClass = 'block', $strImageFolder = '219x', $intRegionId = 14){
  echo getPageHelperObject()->getSidebar($strContainerClass, $strBlockClass, $strImageFolder, $intRegionId);
}

/**
 * get_image_main
 * @param string $strImageFolder
 * @param boolean $blnZoom
 * @param boolean $blnUseLightbox
 * @param string $strImageFolderZoom
 * @param string $strContainerClass
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_image_main($strImageFolder = '420x', $blnZoom = false, $blnUseLightbox = false, $strImageFolderZoom = '660x', $strContainerClass = 'img'){
  echo getPageHelperObject()->getImageMain($strImageFolder, $blnZoom, $blnUseLightbox, $strImageFolderZoom, $strContainerClass);
}

/**
 * get_image_gallery_title
 * @param string $strElement
 * @param boolean $blnShowDefaultTitle
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_image_gallery_title($strElement = 'h3', $blnShowDefaultTitle = true){
  echo getPageHelperObject()->getImageGalleryTitle($strElement, $blnShowDefaultTitle);
}

/**
 * get_image_gallery
 * @param integer $intLimitNumber
 * @param string $strImageGalleryFolder
 * @param boolean $blnZoom
 * @param boolean $blnUseLightbox
 * @param string $strImageFolderZoom
 * @param string $strContainerClass
 * @param string $strThumbContainerClass
 * @param integer $intColNumber
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_image_gallery($intLimitNumber = 0, $strImageGalleryFolder = '', $blnZoom = true, $blnUseLightbox = true, $strImageFolderZoom = '', $strContainerClass = 'gallery', $strThumbContainerClass = 'item', $intColNumber = 0){
  echo getPageHelperObject()->getImageGallery($intLimitNumber, $strImageGalleryFolder, $blnZoom, $blnUseLightbox, $strImageFolderZoom, $strContainerClass, $strThumbContainerClass, $intColNumber);
}

/**
 * has_image_gallery
 * @return boolean
 * @author C
 * ornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function has_image_gallery(){
  return getPageHelperObject()->hasImageGallery();
}


/**
 * get_image_slogan_main
 * @param string $strImageFolder        
 * @param boolean $blnZoom              
 * @param boolean $blnUseLightbox       
 * @param string $strImageFolderZoom    
 * @param string $strContainerClass    
 * @param string $strImageContainerClass
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_image_slogan_main($strImageFolder = '', $blnZoom = true, $blnUseLightbox = true, $strImageFolderZoom = '', $strContainerClass = '', $strImageContainerClass = ''){
  echo getPageHelperObject()->getImageMainSlogan($strImageFolder, $blnZoom, $blnUseLightbox, $strImageFolderZoom, $strContainerClass, $strImageContainerClass);
}

/**
 * get_text_blocks_extended
 * @param string $strImageFolder
 * @param boolean $blnZoom
 * @param boolean $blnUseLightbox
 * @param string $strImageFolderZoom
 * @param string $strContainerClass
 * @param string $strImageContainerClass
 * @param string $strDescriptionContainerClass
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_text_blocks_extended($strImageFolder = '', $blnZoom = true, $blnUseLightbox = true, $strImageFolderZoom = '', $strContainerClass = 'divTextBlock', $strImageContainerClass = 'divImgLeft', $bln2Columned = false){
  echo getPageHelperObject()->getTextBlocksExtended($strImageFolder, $blnZoom, $blnUseLightbox, $strImageFolderZoom, $strContainerClass, $strImageContainerClass, $bln2Columned);
}

/**
 * get_block_documents
 * @param integer $intRegionId
 * @param boolean $blnFileFilterDocs
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_block_documents($intRegionId = 47, $blnFileFilterDocs = true){
  echo getPageHelperObject()->getBlockDocuments($intRegionId, $blnFileFilterDocs);
}

/**
 * get_video_title
 * @param string $strElement
 * @param boolean $blnShowDefaultTitle
 * @return string $strVideoTitle
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_video_title($strElement = 'h3', $blnShowDefaultTitle = true){
  echo getPageHelperObject()->getVideoTitle($strElement, $blnShowDefaultTitle);
}

/**
 * get_video
 * @param integer $intVideoWidth
 * @param integer $intVideoHeight
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_video($intVideoWidth = 420, $intVideoHeight = 236, $blnShowVideoTitle = true){
  echo getPageHelperObject()->getVideo($intVideoWidth, $intVideoHeight, $blnShowVideoTitle);
}

/**
 * get_videos
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com> 
 */
function get_videos(){
  echo getPageHelperObject()->getVideos();
}

/**
 * get_documents_title
 * @param string $strElement
 * @param boolean $blnShowDefaultTitle
 * @return string $strDocumentsTitle
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_documents_title($strElement = 'h3', $blnShowDefaultTitle = true){
  echo getPageHelperObject()->getDocumentsTitle($strElement, $blnShowDefaultTitle);
}

/**
 * get_documents
 * @param string $strContainerCss
 * @param string $strIconCss
 * @param string $strTitleCss
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_documents($strContainerCss = 'documents', $strItemCss = 'item', $strIconCss = 'icon', $strTitleCss = 'text', $strTheme = 'default'){
  echo getPageHelperObject()->getDocuments($strContainerCss, $strItemCss, $strIconCss, $strTitleCss, $strTheme);
}

/**
 * has_documents
 * @return boolean
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function has_documents(){
  return getPageHelperObject()->hasDocuments();
}

/**
 * get_internal_links_title
 * @param string $strElement
 * @return string $strDocumentsTitle
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_internal_links_title($strElement = 'h3', $blnShowDefaultTitle = false){
  echo getPageHelperObject()->getInternalLinksTitle($strElement, $blnShowDefaultTitle);
}

/**
 * get_internal_links
 * @param string $strContainerCss
 * @param string $strIconCss
 * @param string $strTitleCss
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_internal_links($strContainerCss = 'internalLinks', $strItemCss = 'item', $strIconCss = 'icon', $strTitleCss = 'text', $strHeadlineElement = 'h3', $blnShowDefaultTitle = false){
  echo getPageHelperObject()->getInternalLinks($strContainerCss, $strItemCss, $strIconCss, $strTitleCss, $strHeadlineElement, $blnShowDefaultTitle);
}

/**
 * has_internal_links
 * @return boolean
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function has_internal_links(){
  return getPageHelperObject()->hasInternalLinks();
}

/**
 * get_text_blocks
 * @param string $strImageFolder
 * @param boolean $blnZoom
 * @param boolean $blnUseLightbox
 * @param string $strImageFolderZoom
 * @param string $strContainerClass
 * @param string $strImageContainerClass
 * @param string $strDescriptionContainerClass
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_text_blocks($strImageFolder = '', $blnZoom = true, $blnUseLightbox = true, $strImageFolderZoom = '', $strContainerClass = 'blocks', $strImageContainerClass = 'imgLeft'){
  echo getPageHelperObject()->getTextBlocks($strImageFolder, $blnZoom, $blnUseLightbox, $strImageFolderZoom, $strContainerClass, $strImageContainerClass);
}

/**
 * get_overview
 * @param string $strImageFolder
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_overview($strImageFolderCol1 = '220x', $strImageFolderCol2 = '220x', $strImageFolderList = '40x40'){
  echo getPageHelperObject()->getOverview($strImageFolderCol1, $strImageFolderCol2, $strImageFolderList);
}

/**
 * get_product_overview
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_product_overview(){
  echo getPageHelperObject()->getProductOverview();
}

/**
 * get_product_carousel
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_product_carousel(){
  echo getPageHelperObject()->getProductCarousel();
}

/**
 * get_course_overview
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_course_overview(){
  echo getPageHelperObject()->getCourseOverview();
}

/**
 * get_course_detail
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_course_detail(){
  echo getPageHelperObject()->getCourseDetail();
}

/**
 * get_similar_courses
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_similar_courses(){
  echo getPageHelperObject()->getSimilarCourses();
}

/**
 * get_event_overview
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_event_overview(){
  echo getPageHelperObject()->getEventOverview();
}

/**
 * get_sub_pages_overview
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_sub_pages_overview(){
  echo getPageHelperObject()->getSubPagesOverview();
}

/**
 * get_press_overview
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_press_overview(){
  echo getPageHelperObject()->getPressOverview();
}

/**
 * get_download_center
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_download_center(){
  echo getPageHelperObject()->getDownloadCenter();
}

/**
 * get_portal_language_chooser
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_portal_language_chooser(){
  echo getPageHelperObject()->getLanguageChooser();
}

/**
 * get_collection
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_collection($strImageFolder = '80x80'){
  echo getPageHelperObject()->getCollection($strImageFolder);
}

/**
 * get_pages_overview
 * @param string $strImageFolder
 * @param string $strThumbImageFolder
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_pages_overview($strImageFolder = '80x80', $strThumbImageFolder = '40x40'){
  echo getPageHelperObject()->getPagesOverview($strImageFolder, $strThumbImageFolder);
}

/**
 * get_googlemapLatitude
 * @return string $strHtmlOutput
 * @author Michael Trawetzky <mtr@massiveart.com>
 * @version 1.0
 */
function get_googlemapLatitude(){
  echo getPageHelperObject()->getGoogleMapLat();
}

/**
 * get_googlemapLongitude
 * @return string $strHtmlOutput
 * @author Michael Trawetzky <mtr@massiveart.com>
 * @version 1.0
 */
function get_googlemapLongitude(){
  echo getPageHelperObject()->getGoogleMapLng();
}

/**
 * get_press_contact
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_press_contact(){
 echo getPageHelperObject()->getPressContact();
}

/**
 * get_contact
 * @author Cornelius Hansjakob <cha@massiveart.com>
 */
function get_contact($strTitle = ''){
 echo getPageHelperObject()->getContact($strTitle);
}

/**
 * get_speakers
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_speakers($strTitle = ''){
 echo getPageHelperObject()->getSpeakers($strTitle);
}

/**
 * get_press_pics
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_press_pics(){
 echo getPageHelperObject()->getPressPics();
}

/**
 * get_external_links
 * @author Thomas Schedler <tsh@massiveart.com>
 */
function get_external_links(){
 echo getPageHelperObject()->getExternalLinks();
}

/**
 * get_location_contacts
 * @author Cornelius Hansjakob <cha@massiveart.com>
 */
function get_location_contacts($strCountry = '', $strProvince = ''){
 echo getPageHelperObject()->getLocationContacts($strCountry, $strProvince);
}

/**
 * get_user_creator
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_user_creator(){
  echo getPageHelperObject()->getCreatorName();
}

/**
 * get_user_changer
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_user_changer(){
  echo getPageHelperObject()->getChangeUserName();
}

/**
 * get_user_publisher
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_user_publisher(){
  echo getPageHelperObject()->getPublisherName();
}

/**
 * get_date_created
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_date_created(){
  echo getPageHelperObject()->getCreateDate();
}

/**
 * get_date_changed
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_date_changed(){
  echo getPageHelperObject()->getChangeDate();
}

/**
 * get_date_published
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function get_date_published(){
  echo getPageHelperObject()->getPublishDate();
}

/**
 * get_iframe
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com> 
 */
function get_iframe($strQueryString = '', $strWidth = '580px', $strHeight = '800px'){
  echo getPageHelperObject()->getIframe($strQueryString, $strWidth, $strHeight);
}

/**
 * get_form
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com> 
 */
function get_form($strFormId = 'contactForm', $intRootLevelId = 0, $intPageId = 0, $arrAddonFields = array()){
  echo getPageHelperObject()->getForm($strFormId, $intRootLevelId, $intPageId, $arrAddonFields);
}

/**
 * get_form
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com> 
 */
function get_dyn_form($strFormId = 'contactForm', $intRootLevelId = 0, $intPageId = 0, $arrAddonFields = array()){
  echo getPageHelperObject()->getDynForm($strFormId, $intRootLevelId, $intPageId, $arrAddonFields);
}

/**
 * get_form_success
 * @return string $strHtmlOutput
 * @author Cornelius Hansjakob <cha@massiveart.com> 
 */
function get_form_success(){
  echo getPageHelperObject()->getFormSuccess();
}

/**
 * get_bottom_content
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_bottom_content(){
  echo getPageHelperObject()->BottomContent();
}

/**
 * get_dom_loaded_js
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_dom_loaded_js(){
  echo getPageHelperObject()->DomLoadedJs();
}

/**
 * get_category_icons
 * @return string
 * @author Thomas Schedler <tsh@massiveart.com> 
 */
function get_category_icons(){
  echo getPageHelperObject()->getCategoryIcons();
}

/**
 * has_categories
 * @return boolean
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function has_categories(){
  return getPageHelperObject()->hasCategories();
  
}

/**
 * has_tags
 * @return boolean
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */
function has_tags(){
  return getPageHelperObject()->hasTags();
  
}

/**
 * get_page_similar_page_links
 * @param integer $intNumber
 * @return string $strHtmlOutput
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
function get_page_similar_page_links($intNumber = 5){
  echo getPageHelperObject()->getPageSimilarPageLinks($intNumber);
}

?>