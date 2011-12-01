<?php

/**
 * Search
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Search
 */

class Search {

  /**
   * @var Core
   */
  protected $core;

  const FIELD_TYPE_NONE = 1;
  const FIELD_TYPE_KEYWORD = 2;
  const FIELD_TYPE_UNINDEXED = 3;
  const FIELD_TYPE_BINARY = 4;
  const FIELD_TYPE_TEXT = 5;
  const FIELD_TYPE_UNSTORED = 6;
  const FIELD_TYPE_SUMMARY_INDEXED = 7;
  const ZO_NODE_SUMMARY = 'zo_node_summary';

  protected $strSearchValue;
  protected $intLimitSearch;
  protected $intLimitLiveSearch;
  protected $intRootLevelId;
  protected $intLanguageId;
  protected $strParentFolderId;

  /**
   * Constructor
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
    Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
    Zend_Search_Lucene_Search_Query_Wildcard::setMinPrefixLength(0);
  }

  /**
   * search
   * @return object $objHits
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function search(){
    $this->core->logger->debug('massiveart->website->search->search()');
    
    $objHits = array();
    if(is_dir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId))){
      if(count(scandir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId))) > 2){
        $objHits = $this->searchByPath(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId));
      }  
    }
    
    if(is_dir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId))){
      if(count(scandir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId))) > 2){
        $objGlobalHits = $this->searchByPath(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId));
        $objHits = array_merge($objHits, $objGlobalHits);
        usort($objHits, array($this, 'cmp'));
      }  
    } 
    return $objHits;
  }
  
  /**
   * searchByPath
   * @param string $strIndexPath
   * @return Zend_Search_Lucene_Search_QueryHit
   */
  private function searchByPath($strIndexPath){
    if($this->intLimitLiveSearch > 0 && $this->intLimitLiveSearch != ''){
      Zend_Search_Lucene::setResultSetLimit($this->intLimitLiveSearch);
    }
    $objIndex = Zend_Search_Lucene::open($strIndexPath);
    $strQuery = '';
    if(strlen($this->strSearchValue) < 3){
      $strQuery = $this->strSearchValue;
    }else{
      $arrSearchValue = explode(' ',  $this->strSearchValue);
      foreach($arrSearchValue as $strSearchValue){
        $strQuery .= '+('.Search::ZO_NODE_SUMMARY.':'.$strSearchValue.' OR ';
        $strSearchValue = preg_replace('/([^\pL\s\d])/u', '?', $strSearchValue);
        $strQuery .= Search::ZO_NODE_SUMMARY.':'.$strSearchValue.'* OR ';
        $strSearchValue = str_replace('?', '', $strSearchValue);
        $strQuery .= Search::ZO_NODE_SUMMARY.':'.$strSearchValue.'~)';
      }
    }
    
    $strQuery = $strQuery.' +(languageId:'.$this->intLanguageId.') +(rootLevelId:'.$this->intRootLevelId.')';
    $objQuery = Zend_Search_Lucene_Search_QueryParser::parse($strQuery, $this->core->sysConfig->encoding->default);
    
    return $objIndex->find($objQuery);
  }

  /**
   * livesearch
   * @return object $objHits
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function livesearch(){
    $this->core->logger->debug('massiveart->website->search->livesearch()');
    
    $objHits = array();
    if(is_dir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId))){
      if(count(scandir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId))) > 2){
        $objHits = $this->searchByPath(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->page.'/'.sprintf('%02d', $this->intLanguageId));
      }
    }
    
    if(is_dir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId))){
      if(count(scandir(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId))) > 2){
        $objGlobalHits = $this->searchByPath(GLOBAL_ROOT_PATH.$this->core->sysConfig->path->search_index->global.'/'.sprintf('%02d', $this->intLanguageId));
        $objHits = array_merge($objHits, $objGlobalHits);
        usort($objHits, array($this, 'cmp'));
      }
    }
    return $objHits;
  }
  
  /**
   * livesearchByPath
   * @param string $strIndexPath
   * @return Zend_Search_Lucene_Search_QueryHit
   */
  private function livesearchByPath($strIndexPath){
    if($this->intLimitLiveSearch > 0 && $this->intLimitLiveSearch != ''){
      Zend_Search_Lucene::setResultSetLimit($this->intLimitLiveSearch);
    }
    $objIndex = Zend_Search_Lucene::open($strIndexPath);
    $strQuery = '';
    if(strlen($this->strSearchValue) < 3){
      $strQuery = $this->strSearchValue;
    }else{
      $arrSearchValue = explode(' ',  $this->strSearchValue);
      foreach($arrSearchValue as $strSearchValue){
        $strQuery .= '+('.$strSearchValue.' OR ';
        $strSearchValue = preg_replace('/([^\pL\s\d])/u', '?', $strSearchValue);
        $strQuery .= $strSearchValue.'* OR ';
        $strSearchValue = str_replace('?', '', $strSearchValue);
        $strQuery .= $strSearchValue.'~) ';
        
        /*$strQuery .= '+(title:'.$strSearchValue.' OR articletitle:'.$strSearchValue.' OR page_tags:'.$strSearchValue.' OR ';
        $strSearchValue = preg_replace('/([^\pL\s\d])/u', '?', $strSearchValue);
        $strQuery .= 'title:'.$strSearchValue.'* OR articletitle:'.$strSearchValue.'* OR page_tags:'.$strSearchValue.'* OR ';
        $strSearchValue = str_replace('?', '', $strSearchValue);
        $strQuery .= 'title:'.$strSearchValue.'~ OR articletitle:'.$strSearchValue.'~ OR page_tags:'.$strSearchValue.'~)';*/
      }
    }
    
    $strQuery = $strQuery.' +(languageId:'.$this->intLanguageId.') +(rootLevelId:'.$this->intRootLevelId.')';
    if($this->strParentFolderId != '') $strQuery .= ' +(parentFolderIds:'.$this->strParentFolderId.')';
    $objQuery = Zend_Search_Lucene_Search_QueryParser::parse($strQuery, $this->core->sysConfig->encoding->default);
    
    return $objIndex->find($objQuery);
  }
  
  /**
   * compare search hits
   * @param Zend_Search_Lucene_Search_QueryHit $objHitA
   * @param Zend_Search_Lucene_Search_QueryHit $objHitB
   * @return integer
   */
  private function cmp($objHitA, $objHitB){
    if ($objHitA->score == $objHitB->score) {
      return 0;
    }
    return ($objHitA->score < $objHitB->score) ? 1 : -1;
  }

  /**
   * setSearchValue
   * @param string $strSearchValue
   */
  public function setSearchValue($strSearchValue){
    $this->strSearchValue = $strSearchValue;
  }

  /**
   * getSearchValue
   * @return string $strSearchValue
   */
  public function getSearchValue(){
    return $this->strSearchValue;
  }

  /**
   * setLimitSearch
   * @param integer $intLimitSearch
   */
  public function setLimitSearch($intLimitSearch){
    $this->intLimitSearch = $intLimitSearch;
  }

  /**
   * getLimitSearch
   * @return integer $intLimitSearch
   */
  public function getLimitSearch(){
    return $this->intLimitSearch;
  }

  /**
   * setLimitLiveSearch
   * @param integer $intLimitLiveSearch
   */
  public function setLimitLiveSearch($intLimitLiveSearch){
    $this->intLimitLiveSearch = $intLimitLiveSearch;
  }

  /**
   * getLimitLiveSearch
   * @return integer $intLimitLiveSearch
   */
  public function getLimitLiveSearch(){
    return $this->intLimitLiveSearch;
  }
  
  /**
   * setRootLevelId
   * @param integer $intRootLevelId
   */
  public function setRootLevelId($intRootLevelId){
    $this->intRootLevelId = $intRootLevelId;
  }

  /**
   * getRootLevelId
   * @param integer $intRootLevelId
   */
  public function getRootLevelId(){
    return $this->intRootLevelId;
  }
  
  /**
   * setLanguageId
   * @param integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;
  }

  /**
   * getLanguageId
   * @param integer $intLanguageId
   */
  public function getLanguageId(){
    return $this->intLanguageId;
  }
  
  /**
   * setParentFolderId
   * @param string $strParentFolderId
   */
  public function setParentFolderId($strParentFolderId){
    $this->strParentFolderId = $strParentFolderId;
  }

  /**
   * getParentFolderId
   * @return string $strParentFolderId
   */
  public function getParentFolderId(){
    return $this->strParentFolderId;
  }
}
?>