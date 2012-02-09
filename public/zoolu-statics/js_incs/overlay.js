/**
 * overlay.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-24: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Overlay = Class.create({
  
  initialize: function() {
    this.updateContainer = 'olContent';
    this.myDraggables = [];
    this.myDroppables = [];
    
    this.areaId;
    this.fieldId;
    
    this.activeTabId = null;
    
    this.areaViewType = new Object();
    this.viewtype = null;
    this.lastFolderId = null;
    this.overlayCounter = 0;
  },
  
  /**
   * addItemToThumbArea
   * @param string itemId, integer id
   */
  addItemToThumbArea: function(itemId, id){
    //alert(this.areaId + ' :: ' + itemId + ' :: ' + id);
    
    if($(this.areaId) && $(itemId)){
      
      // get the hidden field id
      var fieldId = this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+id;
      
      // create new media item container
      //var mediaItemContainer = '<div id="'+fieldId+'_mediaitem_'+id+'" fileid="'+id+'" class="mediaitem" style="display:none; position:relative;">' + $(itemId).innerHTML + '</div>';
      var imgStr = $(itemId).down('img').up().innerHTML;
      imgStr = imgStr.replace('icon32', 'thumb');
      imgStr = imgStr.replace('="32"', '="100"');
      imgStr = imgStr.replace('=32', '=100');
      
      var mediaItemContainer = '<div id="'+fieldId+'_mediaitem_'+id+'" fileid="'+id+'" class="mediaitem" style="display:none; position:relative;"><table><tbody><tr><td>' + imgStr + '</td></tr></tbody></table><div id="'+iconRemoveId+'" class="itemremovethumb" style="display:none;"></div></div>';
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, mediaItemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('Img'+id)) $('Img'+id).removeAttribute('onclick');
      //if($('Remove'+id)) $('Remove'+id).writeAttribute('id', iconRemoveId);
           
      // insert file id to hidden field - only 1 insert is possible
      if($(fieldId).value.indexOf('[' + id + ']') == -1){
        $(fieldId).value = $(fieldId).value + '[' + id + ']';
      } 
      
      // add the scriptaculous sortable funcionality to the parent container
      myForm.initSortable(fieldId, this.areaId, 'mediaitem', 'div', 'fileid','both'); 
      
      $(fieldId+'_mediaitem_'+id).appear({duration: 0.5});
      $(itemId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myForm.removeItem(fieldId, fieldId+'_mediaitem_'+id, id);
        }
      }
    }
    
  },
  
  /**
   * addFileItemToListArea
   * @param string itemId, integer id
   */
  addFileItemToListArea: function(itemId, id){
        
    if($(this.areaId) && $(itemId)){
      
      // get the hidden field id
      var fieldId = this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+id;
      
      // create new media item container
      var mediaItemContainer = '<div id="'+fieldId+'_fileitem_'+id+'" fileid="'+id+'" class="fileitem" style="display:none;">' + $(itemId).innerHTML + '</div>'; 
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, mediaItemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('File'+id)) $('File'+id).removeAttribute('onclick');
      if($('Remove'+id)) $('Remove'+id).writeAttribute('id', iconRemoveId);
           
      // insert file id to hidden field - only 1 insert is possible
      if($(fieldId).value.indexOf('[' + id + ']') == -1){
        $(fieldId).value = $(fieldId).value + '[' + id + ']';
      }
      // add the scriptaculous sortable funcionality to the parent container
      myForm.initSortable(fieldId, this.areaId, 'fileitem', 'div', 'fileid','vertical'); 
            
      $(fieldId+'_fileitem_'+id).appear({duration: 0.5});
      $(itemId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myForm.removeItem(fieldId, fieldId+'_fileitem_'+id, id);
        }
      }
    }    
  },
  
  /**
   * addContactItemToListArea
   * @param string itemId, integer id
   */
  addContactItemToListArea: function(itemId, id){
        
    if($(this.areaId) && $(itemId)){
      
      // get the hidden field id
      var fieldId = this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+id;
      
      // create new media item container
      var mediaItemContainer = '<div id="'+fieldId+'_contactitem_'+id+'" fileid="'+id+'" class="contactitem" style="display:none;">' + $(itemId).innerHTML + '</div>'; 
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, mediaItemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('Contact'+id)) $('Contact'+id).removeAttribute('onclick');
      if($('Remove'+id)) $('Remove'+id).writeAttribute('id', iconRemoveId);
           
      // insert file id to hidden field - only 1 insert is possible
      if($(fieldId).value.indexOf('[' + id + ']') == -1){
        $(fieldId).value = $(fieldId).value + '[' + id + ']';
      }
      // add the scriptaculous sortable funcionality to the parent container
      myForm.initSortable(fieldId, this.areaId, 'contactitem', 'div', 'fileid','vertical'); 
            
      $(fieldId+'_contactitem_'+id).appear({duration: 0.5});
      $(itemId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myForm.removeItem(fieldId, fieldId+'_contactitem_'+id, id);
        }
      }
    }    
  },

  /**
   * selectDocumentFolders
   */
  selectDocumentFolders: function(){

    if($(this.areaId) && $(this.fieldId+'_Folders') && $('foderCheckboxTreeForm')){
      foldersFieldId = this.fieldId+'_Folders';
      $(this.areaId).innerHTML = '';
      $(foldersFieldId).value = '';

      if($(this.fieldId+'_RootLevel')){
        if($('rootLevelFolderCheckboxTree') && $('rootLevelFolderCheckboxTree').checked){
          $(this.fieldId+'_RootLevel').value = $F('rootLevelFolderCheckboxTree');
          $(this.areaId).update($('rootLevelFolderCheckboxTreeTitle').innerHTML);
        }else{
          $(this.fieldId+'_RootLevel').value = -1;
        }
      }      

      $('foderCheckboxTreeForm').getInputs('checkbox', 'folderCheckboxTree[]').each(function(el) {
        if(el.checked){
          $(foldersFieldId).value = $F(foldersFieldId) + '[' + $F(el) + ']';
          
          if($(this.areaId).innerHTML.blank()){
            $(this.areaId).update($('folderCheckboxTreeTitle-' + $F(el)).innerHTML);
          }else{
            $(this.areaId).update($(this.areaId).innerHTML + ', ' + $('folderCheckboxTreeTitle-' + $F(el)).innerHTML);
          }
        }
      }.bind(this));

      $('overlayGenContentWrapper').hide();
      myForm.loadFileFilterFieldContent(this.fieldId, 'documentFilter');
    }
  },
  
  /**
   * selectPage
   * @param integer idPage
   * @param string pageId
   */
  selectPage: function(idPage, pageId){
    
    myCore.addBusyClass('overlayGenContent');
    
    if('divLinkedPage_'+this.fieldId){
      new Ajax.Updater('divLinkedPage_'+this.fieldId, '/zoolu/cms/page/linkedpagefield', {
        parameters: { 
         pageId: idPage,
         fieldId: this.fieldId, 
         formId: $F('formId'),
         formVersion: $F('formVersion'),
         languageId: $F('languageId'),
         languageCode: (($('languageCode')) ? $F('languageCode') : ''),
         rootLevelLanguageId: ($('rootLevelLanguageId'+myNavigation.rootLevelId)) ? $F('rootLevelLanguageId'+myNavigation.rootLevelId) : ''
        },      
        evalScripts: true,     
        onComplete: function() {  
          $('overlayGenContentWrapper').hide(); 
          $('overlayBlack75').hide();                
          myCore.removeBusyClass('overlayGenContent'); 
        }.bind(this)
      });
    }
  },
  
  /**
   * addPageToListArea
   * @param integer idPage
   * @param string itemId
   */
  addPageToListArea: function(id, itemId){
        
    itemElementId = 'olItem'+itemId;
    
    if($(this.areaId) && $(itemElementId)){
      
      // get the hidden field id
      var fieldId = this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+id;
      
      // create new media item container
      var mediaItemContainer = '<div id="'+fieldId+'_item_'+itemId+'" itemid="'+itemId+'" class="elementitem" style="display:none;">' + $(itemElementId).innerHTML + '</div>';
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, mediaItemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('Item'+id)){
        $('Item'+id).removeAttribute('onclick');
        $('Item'+id).removeAttribute('style');
      }
      if($('Remove'+id)) $('Remove'+id).writeAttribute('id', iconRemoveId);
           
      // add the scriptaculous sortable funcionality to the parent container
      myForm.initSortable(fieldId, this.areaId, 'elementitem', 'div', 'itemid', 'vertical');
      
      // insert file id to hidden field - only 1 insert is possible
      if($(fieldId).value.indexOf('[' + itemId + ']') == -1){
        $(fieldId).value = $(fieldId).value + '[' + itemId + ']';
      } 
      
      $(fieldId+'_item_'+itemId).appear({duration: 0.5});
      $(itemElementId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myForm.removeItem(fieldId, fieldId+'_item_'+itemId, itemId);
        }
      }
    }    
  },

  /**
   * addElementToListArea
   * @param integer idElement
   * @param string productId
   */
  addElementToListArea: function(id, globalId){
    this.addPageToListArea(id, globalId);
  },
  
  /**
   * getRootNavItem
   * @param integer rootLevelId, integer viewtype
   */
  getRootNavItem: function(rootLevelId, viewtype){
    // if mediaFilter is active
    if($('mediaFilter_Folders')){
      $('mediaFilter_RootLevel').setValue(rootLevelId); 
      $('mediaFilter_Folders').setValue('');
    }else{
      // show all medias??? -> to much data -> only show all with filter
    }
    this.loadFileFilterContent(viewtype);
    
    // close all folder icons and hide all sub items
    $$('.olnavigationwrapper .img_folder_on_open').each(function(element){ element.removeClassName('img_folder_on_open'); });
    $$('.olnavigationwrapper .olsubnav').each(function(element){ element.hide(); });
    // reset current folder id
    this.lastFolderId = 0;
  },
  
  /**
   * resetNavItems
   */
  resetNavItems: function(){
    $$('.olnavigationwrapper .olnavchilditem span.selected').each(function(element, index){
      element.removeClassName('selected');
    });
    $$('.olnavigationwrapper .olnavrootitem span.selected').each(function(element, index){
      element.removeClassName('selected');
    });
  },
  
  /**
   * getNavItem
   * @param integer folderId, integer viewtype
   */
  getNavItem: function(folderId, viewtype, contenttype, selectOne){
    this.resetNavItems();
    
    $('olnavitemtitle'+folderId).addClassName('selected');
    
    if($('olsubnav'+folderId)){
      this.toggleSubNavItem(folderId);      
      // if mediaFilter is active
      if($('mediaFilter_Folders')){
        $('mediaFilter_Folders').value = folderId;
        this.loadFileFilterContent(viewtype, contenttype, selectOne);
      }else{
        if(typeof(contenttype) == 'undefined'){
          this.getMediaFolderContent(folderId, viewtype);
        }else if(contenttype == 'page'){
          this.getPortalFolderContent(folderId);
        }
      }
    }else{
      if(folderId != ''){
        var subNavContainer = '<div id="olsubnav'+folderId+'" class="olsubnav" style="display:none;"></div>'; 
        new Insertion.Bottom('olnavitem'+folderId, subNavContainer);
        
        var blnVisible = this.toggleSubNavItem(folderId);
        myCore.addBusyClass('olsubnav'+folderId);
        
        var languageId = null;
        if($('languageId')) {
          languageId = $F('languageId');
        }
        
        new Ajax.Updater('olsubnav'+folderId, '/zoolu/cms/overlay/childnavigation', {
          parameters: { 
            folderId: folderId, 
            viewtype: viewtype,
            languageId: languageId,
            contenttype: contenttype,
            selectOne: selectOne
          },      
          evalScripts: true,     
          onComplete: function() {
            // if mediaFilter is active
            if($('mediaFilter_Folders')){
              $('mediaFilter_Folders').value = folderId;
              this.loadFileFilterContent(viewtype, contenttype, selectOne);
            }else{
              if(typeof(contenttype) == 'undefined'){
               this.getMediaFolderContent(folderId, viewtype);
              }else if(contenttype == 'page'){
                this.getPortalFolderContent(folderId);
              }
            }
            myCore.removeBusyClass('olsubnav'+folderId);
          }.bind(this)
        });
      } 
    }
  },
  
  /**
   * getContactNavItem
   * @param integer unitId
   */
  getContactNavItem: function(unitId){
    
    if($('olsubnav'+unitId)){
      this.toggleSubNavItem(unitId);
      this.getUnitFolderContent(unitId); 
    }else{
      if(unitId != ''){
        var subNavContainer = '<div id="olsubnav'+unitId+'" style="display:none;"></div>'; 
        new Insertion.Bottom('olnavitem'+unitId, subNavContainer);
        
        this.toggleSubNavItem(unitId);
        myCore.addBusyClass('olsubnav'+unitId);
        
        new Ajax.Updater('olsubnav'+unitId, '/zoolu/cms/overlay/unitchilds', {
          parameters: { 
           unitId: unitId
          },      
          evalScripts: true,     
          onComplete: function() {        
            this.getUnitFolderContent(unitId);
            myCore.removeBusyClass('olsubnav'+unitId);      
          }.bind(this)
        });
      } 
    }
  },
  
  /**
   * getMediaFolderContent
   * @param integer folderId, integer viewtype
   */
  getMediaFolderContent: function(folderId, viewtype){
    var strAjaxAction = '';
    
    if(folderId != ''){
      this.lastFolderId = folderId;
      
      $(this.updateContainer).innerHTML = '';
      myCore.addBusyClass(this.updateContainer);
      
      /**
       * overrule given view type
       */
      if(this.areaViewType[this.areaId]){
        viewtype = this.areaViewType[this.areaId];
      }      
      
      if(viewtype == 1){
        strAjaxAction = '/zoolu/cms/overlay/thumbview';
      } else {
        strAjaxAction = '/zoolu/cms/overlay/listview';
      }
      
      var languageId = null;
      if($('languageId')) {
        languageId = $F('languageId');
      }
      
      var fieldname = this.areaId.substring(this.areaId.indexOf('_')+1);
      new Ajax.Updater(this.updateContainer, strAjaxAction, {
       parameters: { 
         folderId: folderId, 
         fileIds: $(fieldname).value,
         languageId: languageId
       },
       evalScripts: true,     
       onComplete: function() {        
         myCore.removeBusyClass(this.updateContainer);                    
       }.bind(this)
     });
    }
  },
  
  /**
   * getPortalFolderContent
   */
  getPortalFolderContent: function(folderId){
    $(this.updateContainer).innerHTML = '';
    myCore.addBusyClass(this.updateContainer);
    
    var languageId = null;
    if($('languageId')){
      languageId = $F('languageId');
    }
    var languageCode = null;
    if($('languageCode')){
      languageCode = $F('languageCode');
    }
    
    var fieldname = this.areaId.substring(this.areaId.indexOf('_')+1);
    new Ajax.Updater(this.updateContainer, '/zoolu/cms/overlay/listpage', {
      parameters: {
        folderId: folderId,
        pageIds: $(fieldname).value,
        languageId: languageId,
        languageCode: languageCode
      },
      evalScripts: true,
      onComplete: function(){
        myCore.removeBusyClass(this.updateContainer);
      }.bind(this)
    });
  },
  
  /**
   * getUnitFolderContent
   * @param integer unitId
   */
  getUnitFolderContent: function(unitId){
    $(this.updateContainer).innerHTML = '';
    myCore.addBusyClass(this.updateContainer);
          
    var fieldname = this.areaId.substring(this.areaId.indexOf('_')+1);
    new Ajax.Updater(this.updateContainer, '/zoolu/cms/overlay/contactlist', {
     parameters: { 
       unitId: unitId, 
       fileIds: $(fieldname).value 
     },
     evalScripts: true,     
     onComplete: function() {        
       myCore.removeBusyClass(this.updateContainer);                    
     }.bind(this)
    });
  },
  
  /**
   * loadFileFilterContent
   * @param integer viewType
   */
  loadFileFilterContent: function(viewType, contenttype, selectOne){
    if($('olContent')){
      if($('mediaFilter_Tags') && $('mediaFilter_Folders') && $('mediaFilter_RootLevel')){    
        $('olContent').update('');
      //if(!$F('mediaFilter_Tags').blank() || (!$F('mediaFilter_Folders').blank() && (!$F('mediaFilter_RootLevel').blank() && $F('mediaFilter_RootLevel') > 0))){        
        myCore.addBusyClass('olContent');
        
        /**
         * overrule given view type
         */
        if(this.areaViewType[this.areaId]){
          viewType = this.areaViewType[this.areaId];
        }
        
        var languageId = null;
        if($('languageId')){
          languageId = $F('languageId');
        }
                  
        var strAjaxAction;
        if(typeof(contenttype) == 'undefined'){
          strAjaxAction = '/zoolu/cms/page/getfilteredfiles';
        }else if(contenttype == 'page'){
          strAjaxAction = '/zoolu/cms/page/getfilteredpages';
        }
        
        if(selectOne){
          params = {
              tagIds: $F('mediaFilter_Tags'),
              folderIds: $F('mediaFilter_Folders'),
              rootLevelId: $F('mediaFilter_RootLevel'),
              languageId: languageId,
              viewtype: viewType,
              isOverlay: true,
              selectOne: selectOne
            }
        }else{
          var fieldname = this.areaId.substring(this.areaId.indexOf('_')+1);
          params = {
            tagIds: $F('mediaFilter_Tags'),
            folderIds: $F('mediaFilter_Folders'),
            rootLevelId: $F('mediaFilter_RootLevel'),
            fileFieldId: fieldname,
            fileIds: $(fieldname).value,
            languageId: languageId,
            viewtype: viewType,
            isOverlay: true
          }
        }
        
        new Ajax.Updater('olContent', strAjaxAction, {
          parameters: params,
          evalScripts: true,
          onComplete: function(){
            myCore.removeBusyClass('olContent');
          }.bind(this)
        });
      // }else{
        // $('olContent').update('');
      // }
      }
    }
  },

  
  /**
   * toggleSubNavItem
   * @param integer itemId
   */
  toggleSubNavItem: function(itemId){    
    if($('olsubnav'+itemId)){
      $('olsubnav'+itemId).toggle();
      
      if($('olnavitem'+itemId)){
	      if($('olnavitem'+itemId).down('.icon').hasClassName('img_folder_on_open')){
	        $('olnavitem'+itemId).down('.icon').removeClassName('img_folder_on_open');	    
	        return false;    
	      }else{
          $('olnavitem'+itemId).down('.icon').addClassName('img_folder_on_open');
          return true;
	      }
	    }
    }         
  },
  
  /**
   * selectTab
   * @param tabId
   */
  selectTab: function(tabId){
    if($('divTab_'+this.activeTabId)){
      $('divTab_'+this.activeTabId).hide();
      $('tabNavItem_'+this.activeTabId).removeClassName('selected');
    }
    $('divTab_'+tabId).show();
    $('tabNavItem_'+tabId).addClassName('selected');
    this.setActiveTab(tabId);
  },
  
  /**
   * selectTab
   */
  setActiveTab: function(tabId){
    this.activeTabId = tabId;    
  },
  
  /**
   * setViewType
   */
  setViewType: function(viewType, prefix){
    if(typeof(prefix) == 'undefined') prefix = '';
    if(typeof(this.areaId) != 'undefined'){
      this.areaViewType[this.areaId] = viewType;
    }
    
    // if mediaFilter is active
    if($('mediaFilter_Folders')){
      if(this.lastFolderId !== null) $('mediaFilter_Folders').value = this.lastFolderId;
      this.loadFileFilterContent(viewType);
    }else{
      if(this.lastFolderId !== null) this.getMediaFolderContent(this.lastFolderId, viewType);
    }
    
    this.updateViewTypeIcons(viewType, prefix)
  },
  
  /**
   * updateViewTypeIcons
   */
  updateViewTypeIcons: function(viewType, prefix){    
    if(typeof(prefix) == 'undefined') prefix = '';
    if(typeof(viewType) == 'undefined'){
      if(this.areaViewType[this.areaId]){
        viewType = this.areaViewType[this.areaId];
      }else{
        viewType = 1;
      }
    }
    
    if(viewType == 1){
      $(prefix+'divThumbView').removeClassName('iconthumbview_on');
      $(prefix+'divThumbView').addClassName('iconthumbview');      
      $(prefix+'divListView').removeClassName('iconlistview');
      $(prefix+'divListView').addClassName('iconlistview_on');      
    }else{
      $(prefix+'divThumbView').removeClassName('iconthumbview');
      $(prefix+'divThumbView').addClassName('iconthumbview_on');     
      $(prefix+'divListView').removeClassName('iconlistview_on');
      $(prefix+'divListView').addClassName('iconlistview');      
    }
  },
  
  /**
   * loadUserSettings
   */
  loadUserSettings: function(userId){
    if($('overlayBlack75')) $('overlayBlack75').show();    
    if($('overlayUserSettingsWrapper')){
      myCore.calcMaxOverlayHeight('overlayUserSettingsContent', true);
      myCore.putOverlayCenter('overlayUserSettingsWrapper');
      $('overlayUserSettingsWrapper').show();
     
      if($('overlayUserSettingsContent')){
        myCore.addBusyClass('overlayUserSettingsContent');
        new Ajax.Updater('overlayUserSettingsContent', '/zoolu/users/user/singleeditform', {
          parameters: { rootLevelId: 8, id: userId },      
          evalScripts: true,     
          onComplete: function() {
            myCore.removeBusyClass('overlayUserSettingsContent');
            // load medias
            myForm.loadFileFieldsContent('media'); 
            this.overlayCounter++;
          }.bind(this)
        });  
      }
    }
  },
  
  /**
   * saveMaintenance
   */
  saveMaintenance: function(){ 
    if($('maintenanceForm')){
      
      /**
       * serialize generic form
       */
      var serializedForm = $('maintenanceForm').serialize();
      
      myCore.addBusyClass('overlayMaintenanceContent');
      new Ajax.Request('/zoolu/cms/overlay/maintenance', {
        parameters: serializedForm+'&rootLevelId='+myNavigation.rootLevelId+'&operation=save',      
        evalScripts: true,     
        onComplete: function(transport) {
          if(transport.responseText.isJSON()){
            var response = transport.responseText.evalJSON();
            
            if(response.active == true){
              if($('spanMaintenanceStatus_'+myNavigation.rootLevelId)) $('spanMaintenanceStatus_'+myNavigation.rootLevelId).appear();
            }else{
              if($('spanMaintenanceStatus_'+myNavigation.rootLevelId)) $('spanMaintenanceStatus_'+myNavigation.rootLevelId).fade();
            }
          }
          this.closeMaintenanceOverlay();
          myCore.removeBusyClass('overlayMaintenanceContent');
        }.bind(this)
      });  
    }
  },
  
  /**
   * close
   */
  close: function(id){
    if($(id)){
      $(id).hide();
    }
    if(this.overlayCounter > 1 && id != undefined) {
      this.overlayCounter--;
    }else{
      if($('overlayGenContentWrapper')){
        $('overlayGenContentWrapper').hide();
        $('overlayGenContentWrapper').setStyle({height: 'auto'});
        $('overlayGenContent').setStyle({height: 'auto'});
        $('overlayGenContentWrapper').setStyle({width: '460px'});
      }
      if($('overlayUpload')) $('overlayUpload').hide();
      if($('overlaySingleEdit')) $('overlaySingleEdit').hide();
      if($('overlayBlack75')) $('overlayBlack75').hide();
      if($('overlayGenContent')) $('overlayGenContent').innerHTML = '';
      if($('overlaySingleEditContent')) $('overlaySingleEditContent').innerHTML = '';
      if($('overlayMaintenanceWrapper')) $('overlayMaintenanceWrapper').hide();
      if($('overlayUserSettingsWrapper')) $('overlayUserSettingsWrapper').hide();
      if($('overlaySendToDashbaordWrapper')) $('overlaySendToDashbaordWrapper').hide();
      if($('overlayMediaWrapperUpload')) $('overlayMediaWrapperUpload').innerHTML = '';
      //this.lastFolderId = null;
      this.overlayCounter = 0;
    }
  },
  
  /**
   * closeMaintenanceOverlay
   */
  closeMaintenanceOverlay: function(){
    if($('overlayMaintenanceWrapper')) $('overlayMaintenanceWrapper').hide();
    if($('overlayBlack75')) $('overlayBlack75').hide();    
  },
  
  /**
   * closeFilterOverlay
   */
  closeFilterOverlay: function(){
    if($('overlayFilterWrapper')) $('overlayFilterWrapper').hide();
    if($('overlayBlack75')) $('overlayBlack75').hide();    
  },
  
  /**
   * closeUserSettingsOverlay
   */
  closeUserSettingsOverlay: function(){
    if($('overlayUserSettingsWrapper')) $('overlayUserSettingsWrapper').hide();
    if($('overlayBlack75')) $('overlayBlack75').hide();    
  }
});