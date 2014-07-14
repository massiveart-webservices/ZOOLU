<?php
/**
 * GenericDataHelperVideoSelect
 *
 * Helper to save and load the "VideoSelect" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-06: Thomas Schedler
 * 1.1, 2009-06-05: Thomas Schedler
 *                  add multi video channel clients/users
 *                  ALTER TABLE `pageVideos` ADD `userId` VARCHAR( 32 ) NOT NULL AFTER `idLanguages`
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.helpers
 * @subpackage GenericDataHelper_VideoSelect
 */

require_once(dirname(__FILE__).'/../../../../../library/massiveart/generic/data/helpers/Abstract.php');

class Plugin_DataHelper_VideoSelect extends GenericDataHelperAbstract  {

    /**
     * @var Model_Pages|Model_Globals
     */
    private $objModel;
    
    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;

    private $strType;

    /**
     * save()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param integet $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function save($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $intVideoTypeId = 0;
            if (array_key_exists($this->objElement->name.'TypeCur', $_POST)) {
                $intVideoTypeId = $_POST[$this->objElement->name.'TypeCur'];
            }

            $strVideoUserId = '';
            if (array_key_exists($this->objElement->name.'UserCur', $_POST)) {
                $strVideoUserId = $_POST[$this->objElement->name.'UserCur'];
            }

            $strVideoThumb = '';
            if (array_key_exists($this->objElement->name.'Thumb', $_POST)) {
                $strVideoThumb = $_POST[$this->objElement->name.'Thumb'];
            }

            $strVideoTitle = '';
            if (array_key_exists($this->objElement->name.'Title', $_POST)) {
                $strVideoTitle = $_POST[$this->objElement->name.'Title'];
            }

            if ($intVideoTypeId > 0 && $strVideoThumb != '' && $strVideoTitle != '') {
                $this->objModel->addVideo($intElementId, $this->objElement->getValue(), $intVideoTypeId, $strVideoUserId, $strVideoThumb, $strVideoTitle);
                $this->objElement->intVideoTypeId = $intVideoTypeId;
                $this->objElement->strVideoUserId = $strVideoUserId;
                $this->objElement->strVideoThumb = $strVideoThumb;
                $this->objElement->strVideoTitle = $strVideoTitle;
            } else {
                $this->objModel->removeVideo($intElementId);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * load()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param integet $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $elementId = $this->strType.'Id';
            $objVideoSelectData = $this->objModel->loadVideo($intElementId);

            if (count($objVideoSelectData) > 0) {
                $objVideoSelect = $objVideoSelectData->current();
                $this->objElement->setValue($objVideoSelect->videoId);
                $this->objElement->intVideoTypeId = $objVideoSelect->idVideoTypes;
                $this->objElement->strVideoUserId = $objVideoSelect->userId;
                $this->objElement->strVideoThumb = $objVideoSelect->thumb;
                $this->objElement->strVideoTitle = $objVideoSelect->title;
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * saveInstanceData
     * @param string $strType
     * @param string $strElementId
     * @param GenericElementRegion $objRegion
     * @param number $idRegionInstance
     * @param number $intRegionInstanceId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function saveInstanceData($strType, $strElementId, $objRegion, $idRegionInstance, $intRegionInstanceId, $intVersion)
    {
        try {
            
            $strGenForm = $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion();
            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $strGenForm . '-Region' . $objRegion->getRegionId() . '-InstanceVideos');
            
            // get value of field
            $strValue = $this->objElement->getInstanceValue($intRegionInstanceId);
            
            $intVideoTypeId = 0;
            if (array_key_exists($this->objElement->name . '_' . $intRegionInstanceId . 'TypeCur', $_POST)) {
                $intVideoTypeId = $_POST[$this->objElement->name . '_' . $intRegionInstanceId . 'TypeCur'];
            }

            $strVideoUserId = '';
            if (array_key_exists($this->objElement->name . '_' . $intRegionInstanceId . 'UserCur', $_POST)) {
                $strVideoUserId = $_POST[$this->objElement->name . '_' . $intRegionInstanceId . 'UserCur'];
            }

            $strVideoThumb = '';
            if (array_key_exists($this->objElement->name . '_' . $intRegionInstanceId . 'Thumb', $_POST)) {
                $strVideoThumb = $_POST[$this->objElement->name . '_' . $intRegionInstanceId . 'Thumb'];
            }

            $strVideoTitle = '';
            if (array_key_exists($this->objElement->name . '_' . $intRegionInstanceId . 'Title', $_POST)) {
                $strVideoTitle = $_POST[$this->objElement->name . '_' . $intRegionInstanceId . 'Title'];
            }
            
            if (!empty($strValue) && $intVideoTypeId > 0 && $strVideoThumb != '' && $strVideoTitle != '') {                
                $arrData = array(
                    $strType . 'Id'      => $strElementId,
                    'version'            => $intVersion,
                    'idLanguages'        => $this->objElement->Setup()->getLanguageId(),
                    'idRegionInstances'  => $idRegionInstance,
                    'userId' 			 => $strVideoUserId,
                    'videoId' 			 => $this->objElement->getInstanceValue($intRegionInstanceId),
                    'title' 			 => $strVideoTitle,
                    'thumb' 			 => $strVideoThumb,
                    'idVideoTypes'		 => $intVideoTypeId,
                    'idFields'           => $this->objElement->id
                );
                
                $objGenTable->insert($arrData);
            }
            
            // load instance data
            $this->loadInstanceData($strType, $strElementId, $objRegion, $intVersion);
            
        } catch (Excpetion $exc) {
            $this->core->logger->err($exc);
        }
    }
    
    /**
     * loadInstanceData
     * @param string $strType
     * @param string $strElementId
     * @param GenericElementRegion $objRegion
     * @param number $intVersion
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadInstanceData($strType, $strElementId, $objRegion, $intVersion)
    {
        try {
            $this->strType = $strType;

            $strGenForm = $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion();
            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $strGenForm . '-Region' . $objRegion->getRegionId() . '-InstanceVideos');

            $objSelect = $objGenTable->select();
            $objSelect->setIntegrityCheck(false);

            $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('id', 'userId', 'videoId', 'idVideoTypes', 'thumb', 'title', 'idFields'));
            $objSelect->join($strType . '-' . $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-Instances AS regionInstance', '`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idRegionInstances = regionInstance.id', array('sortPosition'));
            $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . $strType . 'Id = ?', $strElementId);
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'version = ?', $intVersion);
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'idLanguages = ?', $this->objElement->Setup()->getLanguageId());
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'idFields = ?', $this->objElement->id);

            $objRawInstanceData = $objGenTable->fetchAll($objSelect);
            
            if (count($objRawInstanceData) > 0) {
                $this->objElement->objInstanceVideos = $objRawInstanceData;
            }

            $arrRawInstanceData = $objRawInstanceData->toArray();
            $arrInstanceData = array();
            $arrInstanceFieldNames = array();

            foreach ($arrRawInstanceData as $arrInstanceDataRow) {
                $arrTmp = array($arrInstanceDataRow['sortPosition'] => array());
                $arrInstanceData += $arrTmp;
            }

            //Group the field values together (multiply instance)
            foreach ($arrRawInstanceData as $arrInstanceDataRow) {
                $arrInstanceData[$arrInstanceDataRow['sortPosition']] = array(
                                                                            'userId'        => $arrInstanceDataRow['userId'],
                                                                            'videoId'       => $arrInstanceDataRow['videoId'],
                                                                            'idVideoTypes'  => $arrInstanceDataRow['idVideoTypes'],
                                                                            'thumb'         => $arrInstanceDataRow['thumb'],
                                                                            'title'         => $arrInstanceDataRow['title'],
                                                                        );
                $arrInstanceFieldNames[$arrInstanceDataRow['sortPosition']] = $arrInstanceDataRow['name'];
            }

            $arrRawInstanceData = $arrInstanceData;
            $arrInstanceData = array();

            //Generate value-string array
            foreach ($arrRawInstanceData as $intInstanceDataId => $arrInstanceDataRow) {
                $strValue = $arrInstanceDataRow['videoId'];
                $properties = array(
                    'intVideoTypeId' => $arrInstanceDataRow['idVideoTypes'],
                    'strVideoUserId' => $arrInstanceDataRow['userId'],
                    'strVideoThumb' => $arrInstanceDataRow['thumb'],
                    'strVideoTitle' => $arrInstanceDataRow['title'],
                );

                $arrInstanceData[$intInstanceDataId] = array('name' => $arrInstanceFieldNames[$intInstanceDataId], 'value' => $strValue, 'properties' => $properties);
            }

            return $arrInstanceData;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }


    /**
     * getModel
     * @return type Model
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModel()
    {
        if ($this->objModel === null) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            $strModelFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.$this->objElement->Setup()->getModelSubPath().((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s').'.php';
            if (file_exists($strModelFilePath)) {
                require_once $strModelFilePath;
                $strModel = 'Model_'.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s');
                $this->objModel = new $strModel();
                $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
            } else {
                throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "'.$this->strType.'"');
            }
        }
        return $this->objModel;
    }
  
    /**
     * getModelGenericData
     * @return Model_GenericData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericData()
    {
        if (null === $this->objModelGenericData) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericData.php';
            $this->objModelGenericData = new Model_GenericData();
        }

        return $this->objModelGenericData;
    }
}
?>
