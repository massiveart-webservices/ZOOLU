/**
 * form.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Form = Class.create({
  
  initialize: function() {
    this.formId = 'genForm';  
    this.updateContainer = 'genFormContainer';
    this.updateOverlayContainer = 'overlayGenContent';
    this.updateNewsletterContainer = 'overlayNewsletterContent';
    this.updateTableListContainer = 'genTableListContainer';
    this.updateNewsletterPreview = 'newsletterPreview';
    
    this.requestNewsletterPreview = '/zoolu/newsletters/newsletter/preview';
    this.requestNewsletterSend = '/zoolu/newsletters/newsletter/send';
    
    this.theme = 'default';
    
    this.portalId = 0;
    this.preSelectedPortal = '';
    this.selectedPortal = '';
    
    this.preSelectedItem = '';
    this.selectedItem = '';
    this.currLevel = 0;
    
    this.levelArray = [];
    
    this.regionTexteditorObj = new Object;
    this.texteditorArray = [];
    
    this.regionTitleObj = new Object;
    this.titleArray = [];
    
    this.regionTagObj = new Object;
    this.tagArray = [];
    
    this.blnShowFormAlert = true;
    this.selectNavigationItemNow = false;
    
    this.activeTabId = null;
  },
  
  /**
   * save
   */
  save: function(){
    if($(this.formId)){
      
      /**
       * write/save texteditor content to generic form
       */
      if($$('.texteditor')){
        tinyMCE.triggerSave();
        myCore.resetTinyMCE(true);
      }
      
      var blnFolderAdd = false;
      if($F('formType') == 'folder' && $(this.formId).readAttribute('action').endsWith('add')){
        blnFolderAdd = true;
      }
      
      /**
       * serialize generic form
       */
      var serializedForm = $(this.formId).serialize();
      
      // loader
      this.getFormSaveLoader();
      
      new Ajax.Updater(this.updateContainer, $(this.formId).readAttribute('action'), {
        parameters: serializedForm,
        evalScripts: false,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();
          
          if(this.blnShowFormAlert){
            if($('rootLevelId') && $F('rootLevelId') != '' && $F('rootLevelId') > 0){
              myNavigation.updateNavigationLevel();
            }
            //saved
            this.getFormSaveSucces();
            
            if($('isStartPage') && $F('isStartPage') == 0){
              //$('buttondelete').show();
            }
            
            if($('isStartGlobal') && $F('isStartGlobal') == 0){
              //$('buttondelete').show();
            }
          }else{
            this.getFormSaveError();
          }
          // load medias
          this.loadFileFieldsContent('media');
          // load documents
          this.loadFileFieldsContent('document');
          // load videos
          this.loadFileFieldsContent('video');
          // load contacts
          this.loadContactFieldsContent();
          this.loadGroupFieldsContent();
          
          //load article variants
          this.loadArticleVariantsContent();

          if(this.selectNavigationItemNow == true){
            myNavigation.selectItem(blnFolderAdd);
          }
                   
          this.writeMetaInfos();
        }.bind(this)
      });
    }
  },
  
  /**
   * deleteElement
   */
  deleteElement: function(){    
    if($(this.formId)){

      var tmpKey = 'Delete_' + $('elementType').getValue();
      if(myCore.translate[tmpKey]){
        var key = tmpKey;
      }else if($('rootLevelGroupKey' + myNavigation.rootLevelGroupId)){
        tmpKey = 'Delete_' + $('rootLevelGroupKey' + myNavigation.rootLevelGroupId).getValue();
        var key = (myCore.translate[tmpKey]) ? tmpKey : 'Delete_';
      }else{
        var key = 'Delete_';
      }
      
      myCore.deleteAlertSingleMessage = myCore.translate[key];
      myCore.showDeleteAlertMessage(1);

      $('buttonOk').observe('click', function(event){
        
        var intPosLastSlash = $(this.formId).readAttribute('action').lastIndexOf('/');
        var strAjaxActionBase = $(this.formId).readAttribute('action').substring(0, intPosLastSlash + 1);
        var elType = $('elementType').getValue();
        var elementId = $('id').getValue();
        var linkId = ($('linkId')) ? $F('linkId') : -1;
        var parentFolderId = ($('parentFolderId')) ? $F('parentFolderId') : 0;
              
        // loader
        this.getFormSaveLoader();
        
        myCore.resetTinyMCE(true);
        
        new Ajax.Updater(this.updateContainer, strAjaxActionBase + 'delete', {
          parameters: {
            id: elementId, 
            linkId: linkId,
            rootLevelId: $F('rootLevelId'),
            languageId: $F('languageId'),
            languageCode: (($('languageCode')) ? $F('languageCode') : '')
          },
          evalScripts: true,
          onComplete: function() {
            myCore.hideDeleteAlertMessage();
            
            //deleted
            this.getFormDeleteSucces();
                        
            new Effect.Highlight(elType+elementId, {startcolor: '#ffd300', endcolor: '#ffffff'});
            $(elType+elementId).fade({duration: 0.5});
            //setTimeout('$("'+elType+elementId+'").remove()', 500);
            
            $(myNavigation.genFormContainer).hide();
            $(myNavigation.genFormSaveContainer).hide();   
            
            if(parentFolderId > 0){
              myNavigation.itemId = parentFolderId;
              myNavigation.selectItem();
            }else{
              myNavigation.hideCurrentFolder();
            }      
          }.bind(this)
        });      
      }.bind(this));
      
      $('buttonCancel').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
      }.bind(this));
    }
  },
  
  /**
   * saveUserSettings
   */
  saveUserSettings: function(){
   if($(this.formId)){
      /**
       * serialize generic form
       */
      var serializedForm = $(this.formId).serialize();
      
      new Ajax.Updater('overlayUserSettingsContent', $(this.formId).readAttribute('action'), {
        parameters: serializedForm,
        evalScripts: true,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();          
          myOverlay.closeUserSettingsOverlay();
        }.bind(this)
      });
    }
  },
  
  /**
   * loadFileFieldsContent
   * @param string strType
   */
  loadFileFieldsContent: function(strType){	
    if(strType != ''){
    	
      var strViewType = 0;
      if(strType == 'document' || strType == 'video'){
        strViewType = 2; // viewtypes->list constant of config.xml
      }else{
        strViewType = 1; // viewtypes->thumb constant of config.xml
      }
      
      var languageId = null;
      if($('languageId')) {
        languageId = $F('languageId');
      }
      var languageCode = null;
      if($('languageCode')) {
        languageCode = $F('languageCode');
      }
      
      $$('#genForm .'+strType).each(function(elDiv){   
        if($(elDiv.id)){          
          var fileFieldId = elDiv.id.substring(elDiv.id.indexOf('_')+1);
          if($(fileFieldId).value != ''){
            myCore.addBusyClass(elDiv.id);            
            new Ajax.Updater(elDiv.id, '/zoolu/cms/page/getfiles', {
  	          parameters: { 
  	            fileIds: $(fileFieldId).value,
  	            fileFieldId: fileFieldId,
  	            viewtype: strViewType,
  	            languageId: languageId,
  	            languageCode: languageCode
  	          },
  	          evalScripts: true,
  	          onComplete: function(){
  	            // add the scriptaculous sortable functionality to the parent container
  	            switch(strViewType){
    	            case 1:
    	              this.initSortable(fileFieldId, elDiv.id, 'mediaitem', 'div', 'fileid', 'both');  
		                break;
    	            case 2:
    	              this.initSortable(fileFieldId, elDiv.id, 'fileitem', 'div', 'fileid', 'vertical');  
    	              break;
  	            }
  	            myCore.removeBusyClass(elDiv.id);	
  	          }.bind(this)
            });
          }          
        }
      }.bind(this));
    }    
  },

  /**
   * loadFileFilterFieldsContent
   * @param string strType
   */
  loadFileFilterFieldsContent: function(type){
    if(type != ''){
      
      $$('#genFormContainer .'+type).each(function(elDiv){
        if($(elDiv.id)){
          var fileFieldId = elDiv.id.substring(elDiv.id.indexOf('_')+1);
          this.loadFileFilterFieldContent(fileFieldId, type);
        }
      }.bind(this));
    }
  },
  
  /**
   * loadFileFilterFieldContent
   * @param string fieldId
   * @param string type
   */
  loadFileFilterFieldContent: function(fileFieldId, type){
    var containerId = type + 'Container_' + fileFieldId;
    
    if($(containerId)){
      var viewType = 0;
      if(type == 'documentFilter'){
        viewType = 2; // viewtypes->list constant of config.xml
      }else{
        viewType = 1; // viewtypes->thumb constant of config.xml
      }

      if($(fileFieldId + '_Tags') && $(fileFieldId + '_Folders') && $(fileFieldId + '_RootLevel')){
        $(containerId).innerHTML = '';
        if(!$F(fileFieldId + '_Folders').blank() || (!$F(fileFieldId + '_RootLevel').blank() && $F(fileFieldId + '_RootLevel') > 0)){
          myCore.addBusyClass(containerId);
          new Ajax.Updater(containerId, '/zoolu/cms/page/getfilteredfiles', {
            parameters: {
              tagIds: $F(fileFieldId + '_Tags'),
              folderIds: $F(fileFieldId + '_Folders'),
              rootLevelId: $F(fileFieldId + '_RootLevel'),
              fileFieldId: fileFieldId,
              languageId: $F('languageId'),
              languageCode: (($('languageCode')) ? $F('languageCode') : ''),
              viewtype: viewType
            },
            evalScripts: true,
            onComplete: function(){
              myCore.removeBusyClass(containerId);
            }.bind(this)
          });
        }
      }
    }
  },

  /**
   * loadContactFieldsContent
   */
  loadContactFieldsContent: function(){    
    $$('#genFormContainer .contact').each(function(elDiv){    
      if($(elDiv.id)){          
        var fieldId = elDiv.id.substring(elDiv.id.indexOf('_')+1);
        if($(fieldId).value != ''){
          myCore.addBusyClass(elDiv.id);            
          new Ajax.Updater(elDiv.id, '/zoolu/cms/page/getcontacts', {
            parameters: { 
              contactIds: $(fieldId).value,
              fieldId: fieldId 
            },
            evalScripts: true,
            onComplete: function() {
              myCore.removeBusyClass(elDiv.id);                
            }.bind(this)
          });
        }          
      }
    }.bind(this));
  }, 
  
  /**
   * loadArticleVariantsContent
   */
  loadArticleVariantsContent: function(){    
    $$('#genFormContainer .articlewrapper').each(function(elDiv){    
      if($(elDiv.id)){          

      }
    }.bind(this));
  }, 
  
  /**
   * loadGroupFieldsContent
   * @param string strType
   */
  loadGroupFieldsContent: function(){    
    $$('#genFormContainer .group').each(function(elDiv){    
      if($(elDiv.id)){          
        var fieldId = elDiv.id.substring(elDiv.id.indexOf('_')+1);
        if($(fieldId).value != ''){
          myCore.addBusyClass(elDiv.id);            
          new Ajax.Updater(elDiv.id, '/zoolu/cms/page/getgroups', {
            parameters: { 
              groupIds: $(fieldId).value,
              fieldId: fieldId 
            },
            evalScripts: true,
            onComplete: function() {
              myCore.removeBusyClass(elDiv.id);                
            }.bind(this)
          });
        }          
      }
    }.bind(this));
  },
  
  /**
   * isAuthorizedToDelete
   */
  isAuthorizedToDelete: function(authorized){
    if(authorized == true){
      $('buttondelete').show();
    }else{
      $('buttondelete').hide();
    }
  },
  
  /**
   * isAuthorizedToUpdate
   */
  isAuthorizedToUpdate: function(authorized){
    if(authorized == true){
      $('buttonsave').show();
    }else{
      $('buttonsave').hide();
    }
  },
    
  /**
   * writeMetaInfos
   */
  writeMetaInfos: function(){    
    var strOutput = '';
    
    if($('divMetaInfos') && $('divMetaInfos').innerHTML != ''){
      strOutput = $('divMetaInfos').innerHTML;
      if($('divWidgetMetaInfos')){
        $('divWidgetMetaInfos').innerHTML = strOutput;
        $('divMetaInfos').innerHTML = '';
        return true;
      }
    }    
    return false;    
  },
  
  /**
   * getAddMediaOverlay
   */
  getAddMediaOverlay: function(areaId){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/media', { 
        evalScripts: true,
        onComplete: function(){
          $('olContent').addClassName('oldocuments');
          myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
          myOverlay.overlayCounter++;
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
          myOverlay.updateViewTypeIcons();          
        } 
      });
    }    
  },
  
  /**
   * getAddDocumentOverlay
   */
  getAddDocumentOverlay: function(areaId){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/document', { 
        evalScripts: true,
        onComplete: function(){
          $('olContent').addClassName('oldocuments');
          myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
          myOverlay.overlayCounter++;
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
          myOverlay.updateViewTypeIcons();
        } 
      });
    }    
  },
  
  /**
   * getExportDynFormOverlay
   */
  getExportDynFormOverlay: function(){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayButtons').show();
    $('overlayGenContentWrapper').show();
    new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/exportdynform', { 
      evalScripts: true,
      onComplete: function(){
        myOverlay.overlayCounter++;
        myCore.putOverlayCenter('overlayGenContentWrapper');
        
        $('buttonOk').observe('click', function(event){
          myPage.exportDynFormEntries($F('id'), $F('from'), $F('to'), $F('headline'), $F('startdate'), $F('enddate'));
        });
        
        $('buttonCancel').observe('click', function(event){
          myOverlay.close('overlayGenContentWrapper');
          $('overlayButtons').hide();
        });
      } 
    });
  },
  
  /**
   * getAddInternalLinksOverlay
   */
  getAddInternalLinksOverlay: function(areaId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    if($(areaId)){
      
      var ajaxRequestUrl; 
      if(myNavigation.module == 5){ //global
        ajaxRequestUrl = '/zoolu/global/overlay/internallink';
      }else{
        ajaxRequestUrl = '/zoolu/cms/overlay/internallink';
      }
      
      new Ajax.Updater(this.updateOverlayContainer, ajaxRequestUrl, { 
        parameters: {
          rootLevelId: $F('rootLevelId'),
          languageId: $F('languageId')
        },
        evalScripts: true,
        onComplete: function(){
          myOverlay.overlayCounter++;
          $('olContent').addClassName('oldocuments');
          myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
          myOverlay.updateViewTypeIcons();
        } 
      });
    }    
  },
  
  /**
   * getAddVideoOverlay
   */
  getAddVideoOverlay: function(areaId){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/video', { 
        evalScripts: true,
        onComplete: function(){
          myOverlay.overlayCounter++;
          $('olContent').addClassName('oldocuments');
          myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
          myOverlay.updateViewTypeIcons();          
        } 
      });
    }    
  },

  /**
   * getDocumentFolderChooserOverlay
   */
  getDocumentFolderChooserOverlay: function(areaId, fieldId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/core/folder/documentcheckboxtree', {
        parameters: { 
          rootLevelId: ($(fieldId+'_RootLevel') ? $F(fieldId+'_RootLevel') : -1),
          folderIds: ($(fieldId+'_Folders') ? $F(fieldId+'_Folders') : '[]') 
        },
        evalScripts: true,
        onComplete: function(){
          myOverlay.overlayCounter++;
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
          myOverlay.fieldId = fieldId;
        }
      });
    }
  },
  
  /**
   * getAddTreeOverlay
   */
  getAddTreeOverlay: function(areaId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();    
    if($(areaId)){
      if(myNavigation.module == 5){ //global
        ajaxRequestUrl = '/zoolu/global/overlay/elementtree';
        itemAction = 'myOverlay.addElementToListArea';
      }else{
        ajaxRequestUrl = '/zoolu/cms/overlay/pagetree';
        itemAction = 'myOverlay.addPageToListArea';
      }
      
      var intRootLevelGroupId = $('rootLevelGroupId') ? $F('rootLevelGroupId') : 0;

      var fieldname = areaId.substring(areaId.indexOf('_')+1);
      new Ajax.Updater(this.updateOverlayContainer, ajaxRequestUrl, {
        parameters: { 
          portalId: myNavigation.rootLevelId,
          rootLevelId: $F('rootLevelId'),
          rootLevelGroupId: intRootLevelGroupId,
          rootLevelGroupKey: (($('rootLevelGroupKey'+intRootLevelGroupId)) ? $F('rootLevelGroupKey'+intRootLevelGroupId) : ''),
          languageId: $F('languageId'),
          languageCode: (($('languageCode')) ? $F('languageCode') : ''),
          itemAction: itemAction,
          itemIds: $(fieldname).value,
          fieldId: $(fieldname).readAttribute('fieldid')
        },
        evalScripts: true,
        onComplete: function(){
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
        } 
      });
    }    
  },
  
  /**
   * getAddContactOverlay
   */
  getAddContactOverlay: function(areaId){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();    
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/contact', { 
        evalScripts: true,
        onComplete: function(){
          myCore.calcMaxOverlayHeight('overlayGenContentWrapper');
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
        } 
      });
    }    
  },
  
  /**
   * getAddGroupOverlay
   */
  getAddGroupOverlay: function(areaId, fieldname){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();    
    if($(areaId)){
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/group', { 
        evalScripts: true,
        parameters: {
          fieldname: fieldname,
          groupIds: $(fieldname).value,
          fieldId: $(fieldname).readAttribute('fieldid')
        },
        onComplete: function(){
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
        } 
      });
    }    
  },
  
  /**
   * getAddSitemapLinkOverlay
   */
  getAddSitemapLinkOverlay: function(fieldId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/sitemaplink', { 
      parameters: {
        rootLevelId: $F('rootLevelId')
      },
      evalScripts: true,
      onComplete: function(){
        myOverlay.overlayCounter++;
        $('olContent').addClassName('ollandingpage');
        myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
        myCore.putOverlayCenter('overlayGenContentWrapper');
        myOverlay.fieldId = fieldId;
      } 
    });
  },
  
  /**
   * searchGroupEnter
   */
  searchGroupEnter: function(event, fieldname){
    if(event.keyCode == Event.KEY_RETURN) {
      this.searchGroup(fieldname);
    }
  },
  
  /**
   * searchGroup
   */
  searchGroup: function(fieldname){    
    new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/group', { 
        evalScripts: true,
        parameters: {
          fieldname: fieldname,
          groupIds: $(fieldname).value,
          fieldId: $(fieldname).readAttribute('fieldid'),
          search: $F('groupSearch')
        },
        onComplete: function(){
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myOverlay.areaId = areaId;
        } 
      });
  },
  
  /**
   * resetGroupSearch
   */
  resetGroupSearch: function(fieldname){
    $('groupSearch').setValue('');
    myForm.searchGroup(fieldname);
  },
  
  /**
   * getLinkedPageOverlay
   */
  getLinkedPageOverlay: function(fieldId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    $('overlayGenContent').setStyle({height:'100%'});
    myOverlay.fieldId = fieldId;
    new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/internallink', { 
      parameters: {
        rootLevelId: myNavigation.rootLevelId,
        portalLanguageId: (($('rootLevelLanguageId'+myNavigation.rootLevelId)) ? $F('rootLevelLanguageId'+myNavigation.rootLevelId) : ''),
        selectOne: true
      },
      evalScripts: true,
      onComplete: function(){
        $('olContent').addClassName('oldocuments');
        myCore.calcMaxOverlayHeight('overlayGenContentWrapper', true);
        myCore.putOverlayCenter('overlayGenContentWrapper');
        myOverlay.updateViewTypeIcons();
      } 
    });
  },
  
//  /**
//   * getLinkedPageOverlay
//   */
//  getLinkedPageOverlay: function(fieldId){  
//    $(this.updateOverlayContainer).innerHTML = '';
//    $('overlayGenContentWrapper').setStyle({width: '560px'});
//
//    myCore.putCenter('overlayGenContentWrapper');
//    $('overlayGenContentWrapper').show();    
//    if($(fieldId)){
//      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/cms/overlay/pagetree', { 
//        parameters: {
//          portalId: myNavigation.rootLevelId, 
//          portalLanguageId: (($('rootLevelLanguageId'+myNavigation.rootLevelId)) ? $F('rootLevelLanguageId'+myNavigation.rootLevelId) : '')
//        },
//        evalScripts: true,
//        onComplete: function(){
//          myCore.putOverlayCenter('overlayGenContentWrapper');
//          myOverlay.fieldId = fieldId;
//        } 
//      });
//    }  
//  },
  
  /**
   * removeItem
   */
  removeItem: function(fieldId, elementId, id){
    if($(fieldId) && $(elementId)){     
      itemId = $(elementId).readAttribute('fileid');
      if(itemId == null){
        itemId = $(elementId).readAttribute('itemid');
      }
      if($(fieldId).value.indexOf('[' + itemId + ']') > -1){
        $(fieldId).value = $(fieldId).value.replace('[' + itemId + ']', '');
 
        // delete element out of field area (media, doc)
        $(elementId).fade({duration: 0.5});
        setTimeout('$(\''+elementId+'\').remove()', 500);
        if($('divMediaContainer_'+fieldId)) new Effect.Highlight('divMediaContainer_'+fieldId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        if($('divDocumentContainer_'+fieldId)) new Effect.Highlight('divDocumentContainer_'+fieldId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        if($('divInternalLinksContainer_'+fieldId)) new Effect.Highlight('divInternalLinksContainer_'+fieldId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        if($('divContactContainer_'+fieldId)) new Effect.Highlight('divContactContainer_'+fieldId, {startcolor: '#ffd300', endcolor: '#ffffff'});
        
        // display deleted element in overlay (media, doc)
        if($('olMediaItem'+id)) $('olMediaItem'+id).appear({duration: 0.5});
        if($('olFileItem'+id)) $('olFileItem'+id).appear({duration: 0.5});
        if($('olItem'+id)) $('olItem'+id).appear({duration: 0.5});
        if($('olContactItem'+id)) $('olContactItem'+id).appear({duration: 0.5});
        if($('olUserItem'+id)) $('olUserItem'+id).appear({duration: 0.5});
      }    
    }    
  },
  
  /**
   * toggleFieldsBox
   */
  toggleFieldsBox: function(elementId){
    
    if($('fieldsbox'+elementId)){
      $('fieldsbox'+elementId).toggle();

      if($('pointer'+elementId).hasClassName('closed')){
        $('pointer'+elementId).removeClassName('closed');
        $('pointer'+elementId).addClassName('opened');
      }else{
        $('pointer'+elementId).removeClassName('opened');
        $('pointer'+elementId).addClassName('closed');
      }
      
      if($('editbox'+elementId).hasClassName('editbox')){
        $('editbox'+elementId).removeClassName('editbox');
        $('editbox'+elementId).addClassName('editbox-closed');
      }else{
        $('editbox'+elementId).removeClassName('editbox-closed');
        $('editbox'+elementId).addClassName('editbox');
      }
      
//      if($('cornerbl'+elementId).hasClassName('cornerbl')){
//        $('cornerbl'+elementId).removeClassName('cornerbl');
//        $('cornerbl'+elementId).addClassName('cornerbl-closed');
//      }else{
//        $('cornerbl'+elementId).removeClassName('cornerbl-closed');
//        $('cornerbl'+elementId).addClassName('cornerbl');
//      }

      if($('editbox'+elementId).hasClassName('configbox')){
        $('editbox'+elementId).removeClassName('configbox');
        $('editbox'+elementId).addClassName('configbox-closed');
      }
      else if($('editbox'+elementId).hasClassName('configbox-closed')){
        $('editbox'+elementId).removeClassName('configbox-closed');
        $('editbox'+elementId).addClassName('configbox');
      }     
    } 
  },
  
  /**
   * changeTemplate
   */
  changeTemplate: function(newTemplateId){
    
    // loader
    myCore.addBusyClass(myNavigation.genFormContainer);
    this.getFormSaveLoader();
    
    myCore.resetTinyMCE(true);
    
    var intPosLastSlash = $(this.formId).readAttribute('action').lastIndexOf('/');
    var strAjaxActionBase = $(this.formId).readAttribute('action').substring(0, intPosLastSlash + 1);
    var intRootLevelGroupId = $('rootLevelGroupId') ? $F('rootLevelGroupId') : 0;
    
    new Ajax.Updater(this.updateContainer, strAjaxActionBase + 'changeTemplate', {
      parameters: {
        newTemplateId: newTemplateId, 
        templateId: $F('templateId'),
        formId: $F('formId'),
        formVersion: $F('formVersion'),
        formTypeId: $F('formTypeId'),
        id: $F('id'),
        linkId: ($('linkId')) ? $F('linkId') : -1,
        languageId: $F('languageId'),
        languageCode: (($('languageCode')) ? $F('languageCode') : ''),
        currLevel: $F('currLevel'),
        rootLevelId: $F('rootLevelId'),
        rootLevelGroupId: intRootLevelGroupId,
        rootLevelGroupKey: (($('rootLevelGroupKey'+intRootLevelGroupId)) ? $F('rootLevelGroupKey'+intRootLevelGroupId) : ''),
        parentFolderId: (($('parentFolderId')) ? $F('parentFolderId') : -1),
        elementType: $('elementType') ? $F('elementType') : '', 
        elementTypeId:($('elementTypeId')) ? $F('elementTypeId') : null,
        pageTypeId:($('pageTypeId')) ? $F('pageTypeId') : null,
        parentTypeId: ($('parentTypeId')) ? $F('parentTypeId') : -1,
        sent: ($('sent')) ? $F('sent') : -1   //newsletters
      },
      evalScripts: true,
      onComplete: function() {    
        if(Number($F('id')) > 0){
          if($F('rootLevelId') != '' && $F('rootLevelId') > 0){
            myNavigation.updateNavigationLevel();
          }                    
          //saved
          this.getFormSaveSucces();
          // load medias
          this.loadFileFieldsContent('media');
          // load documents
          this.loadFileFieldsContent('document');
          // load videos
          this.loadFileFieldsContent('video');
          // load filter documents
          this.loadFileFilterFieldsContent('documentFilter');
          // load contacts
          this.loadContactFieldsContent();
        }
        
        myCore.removeBusyClass(myNavigation.genFormContainer);
        this.cancleFormSaveLoader();
      }.bind(this)
    });
  },
  
  /**
   * changeLanguage
   */
  changeLanguage: function(newLanguageId, backLink){
    //check if backLink is assigned
    backLink = (typeof(backLink) != 'undefined' || backLink == null) ? backLink : false;
    
    myCore.addBusyClass(this.updateContainer);    
    myCore.resetTinyMCE(true);
    var intPosLastSlash = $(this.formId).readAttribute('action').lastIndexOf('/');
    var strAjaxActionBase = $(this.formId).readAttribute('action').substring(0, intPosLastSlash + 1);
    var intRootLevelGroupId = $('rootLevelGroupId') ? $F('rootLevelGroupId') : 0;
    
    new Ajax.Updater(this.updateContainer, strAjaxActionBase + 'changeLanguage', {
      parameters: {
        templateId: $F('templateId'),
        formId: $F('formId'),
        formVersion: $F('formVersion'),
        formTypeId: $F('formTypeId'),
        id: $F('id'),
        linkId: ($('linkId')) ? $F('linkId') : -1,
        zoolu_module: myNavigation.module,
        languageId: newLanguageId,
        currLevel: $F('currLevel'),
        rootLevelId: $F('rootLevelId'),
        rootLevelTypeId: $F('rootLevelTypeId'),
        rootLevelGroupId: intRootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+intRootLevelGroupId)) ? $F('rootLevelGroupKey'+intRootLevelGroupId) : '',
        parentFolderId: $F('parentFolderId'),
        elementType: $F('elementType'),
        elementTypeId:($('elementTypeId') ? $F('elementTypeId') : null),
        pageTypeId:($('pageTypeId') ? $F('pageTypeId') : null),
        backLink: backLink
      },      
      evalScripts: true,
      onComplete: function() {    
        myCore.removeBusyClass(this.updateContainer);
        this.writeMetaInfos();

        // load medias
        this.loadFileFieldsContent('media');
        // load documents
        this.loadFileFieldsContent('document');
        // load videos
        this.loadFileFieldsContent('video');
        // load filter documents
        this.loadFileFilterFieldsContent('documentFilter');
        // load contacts
        this.loadContactFieldsContent();
      }.bind(this)
    });    
    
  },
  
  /**
   * addRegion
   */
  addRegion: function(regionId){
    
    var arrWidgets = [];
    $('Region_'+regionId+'_Instances').value.scan(/\[\d*\]/, function(widgets){arrWidgets.push(widgets[0].gsub(/\[/, '').gsub(/\]/, ''))});
    widgetId = Number(arrWidgets[arrWidgets.length - 1]) + 1;

    var emptyRegion = $('divRegion_'+regionId+'_REPLACE_n');
    var newRegion = new Element(emptyRegion.tagName);

    newRegion.update(emptyRegion.innerHTML.gsub(/REPLACE_n/, widgetId));

    newRegion['id'] = 'divRegion_'+regionId+'_'+widgetId;
    newRegion.addClassName(emptyRegion.className);

    arrWidgets.each(function(wId){
      if($('divAddRegion_'+regionId+'_'+wId)) $('divAddRegion_'+regionId+'_'+wId).show();
      if($('divRemoveRegion_'+regionId+'_'+wId)) $('divRemoveRegion_'+regionId+'_'+wId).show();
    });

    new Insertion.Before(emptyRegion, newRegion);

    if(this.regionTexteditorObj[regionId]){
      this.regionTexteditorObj[regionId].each(function(elementId){
        this.initTexteditor(elementId.gsub(/REPLACE_n/, widgetId));
      }.bind(this));
    }

    if(this.regionTitleObj[regionId]){
      this.regionTitleObj[regionId].each(function(elementId){
        this.initRegionTitleObserver(elementId.gsub(/REPLACE_n/, widgetId), regionId+'_'+widgetId);
      }.bind(this));
    }

    if(this.regionTagObj[regionId]){
      this.regionTagObj[regionId].each(function(tagElement){
        this.initTag(tagElement.elementId.gsub(/REPLACE_n/, widgetId), tagElement.autocompleteFeed);
      }.bind(this));
    }

    $('Region_'+regionId+'_Instances').value =  $('Region_'+regionId+'_Instances').value + '['+widgetId+']';

    this.createSortableRegion(regionId);

    var regionPos = $('divRegion_'+regionId+'_'+widgetId).cumulativeOffset();
    var containerPos = $('genFormContainer').cumulativeOffset();
    $('genFormContainer').scrollTop = (regionPos.top - containerPos.top - 50);

    if($('editbox'+regionId+'_'+widgetId) && $('editbox'+regionId+'_'+widgetId).hasClassName('editbox-closed')){
      this.toggleFieldsBox(regionId+'_'+widgetId);
    }
  },

  /**
   * removeRegion
   */
  removeRegion: function(regionId, widgetId){

    // remove tiny mce control
    if(this.regionTexteditorObj[regionId]){
      this.regionTexteditorObj[regionId].each(function(elementId){
        tinyMCE.execCommand('mceRemoveControl', false, elementId.gsub(/REPLACE_n/, widgetId));
      }.bind(this));
    }

    $('divRegion_'+regionId+'_'+widgetId).remove();
    regEx = "["+widgetId+"]";
    $('Region_'+regionId+'_Instances').value = $('Region_'+regionId+'_Instances').value.replace(regEx, '');
    var arrWidgets = [];
    $('Region_'+regionId+'_Instances').value.scan(/\[\d*\]/, function(widgets){arrWidgets.push(widgets[0].gsub(/\[/, '').gsub(/\]/, ''))});

    if(arrWidgets.length == 1){
      if($('divRemoveRegion_'+regionId+'_'+arrWidgets[arrWidgets.length - 1])) $('divRemoveRegion_'+regionId+'_'+arrWidgets[arrWidgets.length - 1]).hide();
    }

    $('Region_'+regionId+'_Order').value = Sortable.serialize('divRegion_'+regionId);
  },

  /**
   * createSortableRegion
   */
  createSortableRegion: function(regionId) {
    SortableRegionId = 'divRegion_'+regionId;
    Sortable.destroy(SortableRegionId);
    if($(SortableRegionId)){
      Position.includeScrollOffsets = true;
      Sortable.create(SortableRegionId,{
        tag:'div',
        scroll:'genFormContainer',
        only: 'sortablebox',
        handle:'editboxdrag',
        onUpdate: function(el){
          rId = el.id.replace('divRegion_', '');
          $('Region_'+rId+'_Order').value = Sortable.serialize(el.id);
        }
      });

      $('Region_'+regionId+'_Order').value = Sortable.serialize(SortableRegionId);
    }
  },

  /**
   * addArticle
   */
  addArticle: function(fieldId) {

    var arrWidgets = [], n, emptyArticle, newArticle;

    $(fieldId + '_Instances').value.scan(/\[\d*\]/, function(widgets) {
      arrWidgets.push(widgets[0].gsub(/\[/, '').gsub(/\]/, ''))
    });
    n = Number(arrWidgets[arrWidgets.length - 1]) + 1;

    emptyArticle = $(fieldId + '_REPLACE_x');
    newArticle = new Element(emptyArticle.tagName);

    newArticle.update(emptyArticle.innerHTML.gsub(/REPLACE_x/, n));

    newArticle['id'] = fieldId + '_' + n;
    newArticle.addClassName(emptyArticle.className);

    arrWidgets.each(function(wId) {
      if ($('addArticle_' + fieldId + '_' + wId)) $('addArticle_' + fieldId + '_' + wId).hide();
      if ($('removeArticle_' + fieldId + '_' + wId)) $('removeArticle_' + fieldId + '_' + wId).show();
    });

    new Insertion.Before(emptyArticle, newArticle);

    $(fieldId + '_Instances').value = $(fieldId + '_Instances').value + '[' + n + ']';
  },

  /**
   * removeArticle
   */
  removeArticle: function(fieldId, n) {

    $(fieldId + '_' + n).remove();
    var regEx = "[" + n + "]", arrWidgets = [];

    $(fieldId + '_Instances').value = $(fieldId + '_Instances').value.replace(regEx, '');

    $(fieldId + '_Instances').value.scan(/\[\d*\]/, function(widgets) {
      arrWidgets.push(widgets[0].gsub(/\[/, '').gsub(/\]/, ''))
    });

    if (arrWidgets.length == 1) {
      if ($('removeArticle_' + fieldId + '_' + arrWidgets[arrWidgets.length - 1])) $('removeArticle_' + fieldId + '_' + arrWidgets[arrWidgets.length - 1]).hide();
    }

    if ($('addArticle_' + fieldId + '_' + arrWidgets[arrWidgets.length - 1])) $('addArticle_' + fieldId + '_' + arrWidgets[arrWidgets.length - 1]).show();

    //$(fieldId + '_Order').value = Sortable.serialize('divArticle_' + fieldId);
  },

  /**
   * removeTinyMCEControl
   */
  removeTinyMCEControl: function(elementId){
    var arrElementIds = elementId.split('_');
    if(arrElementIds.length == 3){
      regionId = arrElementIds[1];
      widgetId = arrElementIds[2];
      if(this.regionTexteditorObj[regionId]){
        this.regionTexteditorObj[regionId].each(function(elementId){
          tinyMCE.execCommand('mceRemoveControl', false, elementId.gsub(/REPLACE_n/, widgetId));
        }.bind(this));
      }
    }
  },

  /**
   * addTinyMCEControl
   */
  addTinyMCEControl: function(elementId){
    var arrElementIds = elementId.split('_');
    if(arrElementIds.length == 3){
      regionId = arrElementIds[1];
      widgetId = arrElementIds[2];
      if(this.regionTexteditorObj[regionId]){
        this.regionTexteditorObj[regionId].each(function(elementId){
          tinyMCE.execCommand('mceAddControl', false, elementId.gsub(/REPLACE_n/, widgetId));
        }.bind(this));
      }
    }
  },
  
  /**
   * initTexteditor
   */
  initTexteditor: function(elementId){
    if($(elementId)){
      
// ---------------------------------------- NEW ----------------------------------------      
      tinyMCE.init({
        // General options
        mode : "exact",
        elements : elementId,
        theme : "advanced",
        width : "100%",
        height : "240", 
        debug : false,
        valid_elements : '*[*]',
        custom_shortcuts : false,
        language : myCore.languageCode,
        
        plugins : "lists,pagebreak,style,layer,table,advlink,inlinepopups,insertdatetime,media,paste,fullscreen,noneditable,visualchars,xhtmlxtras,wordcount,advlist",
        
        // Theme options        
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,bullist,numlist,|,outdent,indent,blockquote,|,justifyleft,justifycenter,justifyright,|,sub,sup,|,link,unlink,anchor",
        theme_advanced_buttons2 : "formatselect,|,tablecontrols",
        theme_advanced_buttons3 : "cut,copy,paste,pasteword,|,removeformat,charmap,|,fullscreen,code",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        theme_advanced_resize_horizontal : false,
    
        // Example content CSS (should be your site CSS)
        content_css : "/website/themes/" + this.theme + "/css/screen.css",
        
        relative_urls : false,
        convert_urls : false
      });
      
// ---------------------------------------- OLD ----------------------------------------
//      tinyMCE.init({
//        // General options
//        //mode : "specific_textareas",
//        //editor_selector : "texteditor",
//        mode : "exact",
//        elements : elementId,            
//        theme : "advanced",
//        skin : "zoolu",
//        debug : false,
//        width : "100%",
//        height : "150px",
//        valid_elements : '*[*]',
//     
//        // plugins
//        plugins : "safari,table,advimage,advlink,media,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,inlinepopups",
//                    
//        // Theme options
//        
//        theme_advanced_buttons1 : "bold,italic,strikethrough,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,|,sub,sup,|,link,unlink,anchor,|,fullscreen,code",
//        theme_advanced_buttons2 : "formatselect,|,tablecontrols",
//        theme_advanced_buttons3 : "",
//        theme_advanced_buttons4 : "",                
//        theme_advanced_toolbar_location : "top",
//        theme_advanced_toolbar_align : "left",
//        theme_advanced_statusbar_location : "bottom",
//        theme_advanced_resizing : true,
//        theme_advanced_resize_horizontal : false,
//          
//        content_css: "/website/themes/" + this.theme + "/css/screen.css",
//        
//        relative_urls : false,
//        convert_urls : false
//      });
    }
  },
  
  /**
   * addTexteditor
   */
  addTexteditor: function(elementId, regionId){
    //check if regionId is assigned 
    if(typeof(regionId) != 'undefined'){    
      if(!this.regionTexteditorObj[regionId]){
        this.regionTexteditorObj[regionId] = []
      }
      if(this.regionTexteditorObj[regionId].indexOf(elementId) == -1) this.regionTexteditorObj[regionId].push(elementId);
    }else{
      if(this.texteditorArray.indexOf(elementId) == -1) this.texteditorArray.push(elementId);
    }
  },
  
  /**
   * initRegionTitleObserver
   */
  initRegionTitleObserver: function(elementId, regionId){
    if($(elementId)){  
      $(elementId).observe('keyup', function(event){
        if($('spanRegionTitle_'+regionId)) $('spanRegionTitle_'+regionId).innerHTML = Event.element(event).value;
      });
    }
  },
  
  /**
   * addRegionTitle
   */
  addRegionTitle: function(elementId, regionId){
    //check if regionId is assigned 
    if(typeof(regionId) != 'undefined'){    
      if(!this.regionTitleObj[regionId]){
        this.regionTitleObj[regionId] = []
      }
      if(this.regionTitleObj[regionId].indexOf(elementId) == -1)  this.regionTitleObj[regionId].push(elementId);      
    }else{
      if(this.titleArray.indexOf(elementId) == -1) this.titleArray.push(elementId);
    }
  },
  
  /**
   * addTag
   */
  addTag: function(elementId, regionId, autocompleteFeed){
    tagElement = new Object;
    tagElement.elementId = elementId;
    tagElement.autocompleteFeed = autocompleteFeed;
    
    //check if regionId is assigned 
    if(typeof(regionId) != 'undefined'){    
      if(!this.regionTagObj[regionId]){
        this.regionTagObj[regionId] = []
      }
      if(this.regionTagObj[regionId].indexOf(elementId) == -1) this.regionTagObj[regionId].push(tagElement);
    }else{
      if(this.tagArray.indexOf(elementId) == -1) this.tagArray.push(tagElement);
    }
  },
  
  /**
   * initTag
   */
  initTag: function(elementId, autocompleteFeed){
    if($(elementId) && $(elementId+'_autocompleter')){
      var tagList = new FacebookList(elementId, elementId+'_autocompleter',{ regexSearch: true });
      autocompleteFeed.each(function(t){tagList.autoFeed(t)});
    }
  },
  
  /**
   * initSortable
   * Adds the scriptaculous sortable functionality to a container
   */
  initSortable: function(elementId, containerId, nodeClass, nodeTag, nodeName, constraint){
    if($(containerId) && nodeClass != '' && nodeTag != '' && nodeName != '' && constraint != ''){
    	if(constraint == 'both'){
    		constraint = false;
    	}
    	
      Sortable.create(containerId,{onChange: function(){
        var itemOrder = '';
        Sortable.sequence(containerId, {tag:nodeTag, name:nodeName}).each(function(id){
          itemOrder+='['+id+']';
        });
        $(elementId).value = itemOrder; 
      }, 
      elements:$$('#'+containerId+' .'+nodeClass),
      constraint:constraint,
      only:nodeClass
      });
    }
  },  
  
  /**
   * updateMediaDisplaySize
   */
  updateMediaDisplaySize: function(elementId, size){
    if($(elementId)){
      obj = $F(elementId).evalJSON();
      obj.size = size;
      this.updateMediaDisplayOption(elementId, obj);
    }
  },
  
  /**
   * updateMediaDisplayPosition
   */
  updateMediaDisplayPosition: function(elementId, position){
    if($(elementId)){
      obj = $F(elementId).evalJSON();
      
      if($(elementId+'_'+obj.position)){
        $(elementId+'_'+obj.position).setStyle({backgroundImage: $(elementId+'_'+obj.position).getStyle('backgroundImage').replace('selected', 'active') }); 
      }
      
      obj.position = position;
      this.updateMediaDisplayOption(elementId, obj);
      
      if($(elementId+'_'+obj.position)){
        $(elementId+'_'+obj.position).setStyle({backgroundImage: $(elementId+'_'+obj.position).getStyle('backgroundImage').replace('active', 'selected') }); 
      }
    }
  },
  
  /**
   * updateMediaDisplayOption
   */
  updateMediaDisplayOption: function(elementId, obj){
    if($(elementId)) $(elementId).value = Object.toJSON(obj).gsub('"', "'");
  },
  
  /**
   * edit the page Url
   */
  editUrl: function(elementId){
  	if($(elementId) && $F(elementId) !== ''){
      $(elementId+'_UrlValue').innerHTML = '<input style="width:40%;" id="'+elementId+'_tmpUrl" type="text" value="'+$F(elementId+'_EditableUrl')+'"></input>';
      $(elementId+'_Controls').innerHTML = '&nbsp;<a href="#" onclick="myForm.addUrl(\''+elementId+'\'); return false;">' + myCore.translate.Apply + '</span>';
      $(elementId+'_tmpUrl').focus();
      this.intValidUrlObserver(elementId+'_tmpUrl', elementId);
  	}
  },
  
  /**
   * add page url
   */
  addUrl: function(elementId){
    if($(elementId) && $F(elementId+'_tmpUrl') !== ''){
      $(elementId+'_EditableUrl').value = $F(elementId+'_tmpUrl');
      
      this.stopValidUrlObserver(elementId+'_tmpUrl');
      
      $(elementId+'_UrlValue').innerHTML = $F(elementId+'_tmpUrl');
      $(elementId+'_Controls').innerHTML = '&nbsp;<a href="#" onclick="myForm.editUrl(\''+elementId+'\'); return false;">' + myCore.translate.Edit + '</span>';
      $(elementId).setValue($(elementId+'_ShownUrl').value+$(elementId+'_EditableUrl').value);
    }
  },
  
  /**
   * toggleUrlHistory
   */
  toggleUrlHistory: function(elementId){
    if($(elementId)){
    	
      $(elementId+'_ToggleUrlHistory').toggle();

      if($(elementId+'_ToggleUrlHistory').innerHTML.blank() && $(elementId+'_ToggleUrlHistory').getStyle('display') != 'none'){
        myCore.addBusyClass(elementId+'_ToggleUrlHistory');

        new Ajax.Updater(elementId+'_ToggleUrlHistory', '/zoolu/core/url/geturlhistory', {
          parameters:{
            elementId: elementId,
            id: $F('id'),
            linkId: ($('linkId') ? $F('linkId') : -1),
            moduleId: myNavigation.module,
            languageId: $F('languageId'),
            languageCode: (($('languageCode')) ? $F('languageCode') : '')
          },
          evalScripts: true,
          onComplete: function(){
            myCore.removeBusyClass(elementId+'_ToggleUrlHistory');
          }.bind(this)
        });
      }
    }
  }, 
  
  /**
   * removeUrlHistoryEntry
   */
  removeUrlHistoryEntry: function(urlId, relationId, elementId){
    if(urlId && relationId && elementId){
      var Check = confirm("Alte Url unwiederruflich löschen?");
      
      if(Check == true){
        new Ajax.Request('/zoolu/core/url/removeUrlHistoryEntry', {
          parameters:{ 
            urlId: urlId,
            relationId: relationId
          },
          evalScripts: true,
          onComplete: function(){
        	  $(urlId+'_'+elementId).remove();
          }.bind(this)
        }); 
      }
    }
  },
  
  /**
   * initValidUrlObserver
   */
  intValidUrlObserver: function(inputId, elementId){
    if($(elementId) && $(inputId)) {   
      $(inputId).observe('keypress', function(event){
      	var intCharCode = event.charCode;  
      	var intKeyCode = event.keyCode; 
         
      	if(intCharCode == 0 || intCharCode == undefined){
      	  // return	
      	  if(intKeyCode == 13){
      	    this.addUrl(elementId);
      	  }else{
            return true;
          }	
      	}else{
      	  // allow: capital letters || small letters || numbers || underscore || hyphen || slash
          if(intCharCode >= 65 && intCharCode <= 90 || intCharCode >= 97 && intCharCode <= 123 || intCharCode >= 48 && intCharCode <= 57 || intCharCode == 95 || intCharCode == 45 || intCharCode == 47){
            return true;
          }else{
            Event.stop(event);
            return false;
          }
      	}
      }.bind(this));
    }
  },
  
  /**
   * stopValidUrlObserver
   */
  stopValidUrlObserver: function(elementId){
    if($(elementId)) {   
      $(elementId).stopObserving();
    }
  },
  
   /**
   * initVideoChannelObserver
   */
  initVideoChannelObserver: function(elementId){
    if($(elementId+'TypeId')){
      $(elementId+'TypeId').observe('change', function(event){
  	    if(Event.element(event).value != '' && Event.element(event).value > 0){
  	      this.getVideoChannelSelect(elementId, Event.element(event).value);
  	    }
      }.bind(this));
	
      if($F(elementId+'TypeId') != '' && $F(elementId+'TypeId') > 0){
  	    channelUserId = ($(elementId+'User') ? $F(elementId+'User') : '');
  	    this.getVideoChannelSelect(elementId, $F(elementId+'TypeId'), channelUserId);
  	  }
    }
  },
  
  /**
   * getVideoChannelSelect
   */
  getVideoChannelSelect: function(elementId, channelId, channelUserId){
    if($('div_'+elementId)){    	
      $('div_'+elementId).show();
  	  myCore.addBusyClass('div_'+elementId);
  	  
  	  if(typeof(channelUserId) == 'undefined'){       
  	    channelUserId = null;
  	  }else{
  	    if($(elementId+'User') && $F(elementId+'User') != ''){
    		  channelUserId = $F(elementId+'User');
    		}else{
    		  channelUserId = null;  
    		}  
  	  } 
		   
  	  new Ajax.Updater('div_'+elementId, '/zoolu/core/video/getvideoselect', {
    	  parameters: { 
    	    elementId: elementId,
    	    channelId: channelId,
    	    channelUserId: channelUserId,
    	    value: $F(elementId)  
    	  },
    	  evalScripts: true,
    	  onComplete: function(){
    	    myCore.removeBusyClass('div_'+elementId);
    	  }.bind(this)
  	  });
    }
  },
  
  /**
   * initVideoChannelUserObserver
   */
  initVideoChannelUserObserver: function(elementId){
    if($(elementId+'User')){
      $(elementId+'User').observe('change', function(event){
        if(Event.element(event).value != ''){
          this.getVideoChannelSelect(elementId, $F(elementId+'TypeId'), Event.element(event).value);
        }
      }.bind(this));
    }
  },
  
  /**
   * initVideoResetSearchObserver
   */
  initVideoResetSearchObserver: function(elementId){
    if($(elementId+'SearchReset')){
      $(elementId+'SearchReset').observe('click', function(event){
      	if($F(elementId+'User')){
             this.getVideoChannelSelect(elementId, $F(elementId+'TypeId'), $F(elementId+'User'));
      	}
      }.bind(this));
    }
  },
  
  /**
   * getVideoSearchSelect
   */
  getVideoSearchSelect: function(elementId, channelId, searchString, channelUserId){
    if($('div_'+elementId)){
      myCore.addBusyClass('div_'+elementId);
      new Ajax.Updater('div_'+elementId, '/zoolu/core/video/getvideoselect',{ 
        parameters: { 
    	    elementId: elementId,
    	    channelId: channelId,
    	    channelUserId: channelUserId,
    	    searchString: searchString,
    	    value: $F(elementId)
        },
        evalScripts: true,
        onComplete: function(){
          myCore.removeBusyClass('div_'+elementId);
        }.bind(this)
      });
  	}
  },
  
  /**
   * initVideoSearch
   */
  initVideoSearch: function(elementId) {
    if($(elementId+'Search')){
      $(elementId+'SearchButton').observe('click', function(event){ 
        if($F(elementId+'Search') != ''){
          this.getVideoSearchSelect(elementId, $F(elementId+'TypeId'), $F(elementId+'Search'),($(elementId+'User')? $F(elementId+'User'): '')); 
        }
      }.bind(this)); 
    }
  },
  
  /**
   * selectVideo
   */
  selectVideo: function(elementId, videoId){
    if($(elementId)){
      if($(elementId+'SelectedService') && $(elementId+'User') && $(elementId+'TypeId')){ 	  
    	  
        var intIndexType = $(elementId+'TypeId').selectedIndex;
        var serviceName = $(elementId+'TypeId').options[intIndexType].text;
        var intIndexUser = $(elementId+'User').selectedIndex;
        var serviceUser = $(elementId+'User').options[intIndexUser].text;

        $(elementId).value = videoId;
        $(elementId+'Thumb').value = $F('thumb_'+elementId+'_'+videoId);
        $(elementId+'Title').value = $F('title_'+elementId+'_'+videoId);
        $(elementId+'TypeCur').value = $F(elementId+'TypeId');
        $(elementId+'UserCur').value = $F(elementId+'User');
			  
        $(elementId+'SelectedService').update(serviceName+'/'+serviceUser);
        $('div_selected'+elementId).update($('div_'+elementId+'_'+videoId).innerHTML);      
        $('div_selected'+elementId).down('.buttonSelectVideo').setStyle({display:'none'});
        $('div_selected'+elementId).down('.buttonUnselectVideo').setStyle({display:'inline'});
      }
    }
  },
  
  /**
   * getSelectedVideo
   */
  getSelectedVideo: function(elementId){
	  if($(elementId+'SelectedContainer') && $F(elementId+'TypeCur') != '' && $F(elementId) != '' && $F(elementId+'UserCur') != '') {
	
		  myCore.addBusyClass('div_selected'+elementId);
	  		
		  new Ajax.Updater('div_'+elementId, '/zoolu/core/video/getselectedvideo', {
  		  parameters: { 
  			  elementId: elementId,
  			  channelId: $F(elementId+'TypeCur'),
  			  channelUserId: $F(elementId+'UserCur'),
  			  value: $F(elementId)
  		  },
  		  evalScripts: true,
  		  onComplete: function(){
  			  myCore.removeBusyClass('div_selected'+elementId);
  		  }.bind(this)
		  });
	  }
  },

  /**
   * unselect a selected video
   */
  unselectVideo: function(elementId, videoId){
	  if($(elementId)){
		  if($(elementId)){
			  $(elementId).value = '';
			  $(elementId+'Thumb').value = '';
			  $(elementId+'Title').value = '';
			  $('div_selected'+elementId).update('');
			  $(elementId+'SelectedService').update('');
		  }
	  }
  },
  
  /**
   * writePublishDate
   */
  writePublishDate: function(){
    var year, monthnum, day, hour, minute;
    var monthShortName;
    
    if($('publishDate')){
	    // date
	    if($('publishYear')){
	      year = $('publishYear').getValue(); 
	    } 
	    
	    if($('publishMonth')){
	      var w = $('publishMonth').selectedIndex;
        monthShortName = $('publishMonth').options[w].text;
	      monthnum = $('publishMonth').getValue();
	    } 
	    
	    if($('publishDay')){
	      day = $('publishDay').getValue();
	      if(day.length < 2){
          day = '0' + day;
        }
	    } 
	    
	    // time
	    if($('publishHour')){
	      hour = $('publishHour').getValue();
	      if(hour.length < 2){
	        hour = '0' + hour;
	      }
	    } 
	    
	    if($('publishMinute')){
	      minute = $('publishMinute').getValue();
	      if(minute.length < 2){
          minute = '0' + minute;
        }
	    } 
	    	    
	    // write to hidden field in form
	    $('publishDate').setValue(year+'-'+monthnum+'-'+day+' '+hour+':'+minute+':00');
	    
	    // write output
	    $('divPublishDate').innerHTML = day+'. '+monthShortName+'. '+year+', '+hour+':'+minute;
	    new Effect.Highlight('divPublishDate', {startcolor: '#ffd300', endcolor: '#e4e4e4'});
	    
	    this.togglePublishDate();
	  }       
  },
  
  /**
   * togglePublishDate
   */
  togglePublishDate: function(){
    if($('divPublishDateNew') && $('divPublishDateNew').style.display == 'none'){
      Effect.SlideDown('divPublishDateNew', {duration: 0.5});
    }else{
      Effect.SlideUp('divPublishDateNew', {duration: 0.5});
    }
  },
  
  /**
   * toggleNavigationOptions
   */
  toggleNavigationOptions: function(checkBox){
    if($('divShowInNavigationOptions') && checkBox.checked){
      Effect.SlideDown('divShowInNavigationOptions', {duration: 0.5});
      $('showInNavigation').value = $F('showinnavigationoption');
    }else{
      Effect.SlideUp('divShowInNavigationOptions', {duration: 0.5});
      $('showInNavigation').value = 0;
    }
  },
  
  /**
   * toggleDestinationOptions
   */
  toggleDestinationOptions: function(checkBox){
    if($('divShownOnlyForDestinationOptions') && checkBox.checked){
      Effect.SlideDown('divShownOnlyForDestinationOptions', {duration: 0.5});
      $('destinationId').value = $F('shownonlyfordestinationoption');
    }else{
      Effect.SlideUp('divShownOnlyForDestinationOptions', {duration: 0.5});
      $('destinationId').value = 0;
    }
  },

  /**
   * toggleSegmentOptions
   */
  toggleSegmentOptions: function(checkBox){
    if($('divShownOnlyForSegmentOptions') && checkBox.checked){
      Effect.SlideDown('divShownOnlyForSegmentOptions', {duration: 0.5});
      $('segmentId').value = $F('shownonlyforsegmentoption');
    }else{
      Effect.SlideUp('divShownOnlyForSegmentOptions', {duration: 0.5});
      $('segmentId').value = 0;
    }
  },
  
  /**
   * toggleLanguageFallbackOptions
   */
  toggleLanguageFallbackOptions: function(checkBox){
    if($('divLanguageFallbackOptions') && checkBox.checked){
      Effect.SlideDown('divLanguageFallbackOptions', {duration: 0.5});
      $('languageFallback').value = $F('languagefallbackoption');
    }else{
      Effect.SlideUp('divLanguageFallbackOptions', {duration: 0.5});
      $('languageFallback').value = 0;
    }
  },
  
  /**
   * toggleTemplateChooser
   */
  toggleTemplateChooser: function(){    
    if($('divAllTemplates') && $('divAllTemplates').style.display == 'none'){
      Effect.SlideDown('divAllTemplates'); //$('divAllTemplates').show();
    }else{
      Effect.SlideUp('divAllTemplates'); //$('divAllTemplates').hide();
    }
  },
  
  /**
   * getCopyLanguageChooser
   */
  getCopyLanguageChooser: function(){
    $('overlayGenContent2').innerHTML = '';
    
    myCore.putCenter('overlayGenContentWrapper2');
    $('overlayGenContentWrapper2').show();    
  
    new Ajax.Updater('overlayGenContent2', '/zoolu/core/language/getcopylanguages', { 
      parameters: { 
        rootLevelId: myNavigation.rootLevelId,
        moduleId: myNavigation.module,
        srcLanguage: $F('languageId')
      },
      evalScripts: true,
      onComplete: function(){
        myCore.putOverlayCenter('overlayGenContentWrapper2');
        myCore.removeBusyClass('overlayGenContent2');
      } 
    });
  },
  
  /**
   * copyLanguage
   */
  copyLanguage: function(dstLanguage, userId){
    var strAjaxRequest = '';
    if(myNavigation.module == 1){ //cms
      strAjaxRequest = '/zoolu/cms/page/getpropertiescount';
    }else if(myNavigation.module == 5) { //global
      strAjaxRequest = '/zoolu/global/element/getpropertiescount';
    }
    new Ajax.Request(strAjaxRequest, {
      parameters: {
        elementId: $F('id'),
        languageId: dstLanguage
      },
      onComplete: function(response){
        var cnt = parseInt(response.responseText);
        if(cnt > 0){
          myCore.showAlertMessage(myCore.translate['Copy_language_override']);
          
          $('buttonOk').observe('click', function(){
            myCore.hideAlertMessage();
            myOverlay.close('overlayGenContentWrapper2');
            this.setCopyValues(dstLanguage, userId);
            myForm.save();
          }.bind(this));
          
          $('buttonCancel').observe('click', function(){
            myCore.hideAlertMessage();
          }.bind(this));
        }else{
          myOverlay.close('overlayGenContentWrapper2');
          this.setCopyValues(dstLanguage, userId);
          myForm.save();
        }
      }.bind(this)
    });
  },
  
  /**
   * setCopyValues
   */
  setCopyValues: function(dstLanguage, userId){
    $('languageId').value = dstLanguage;
    $('idStatus').value = 1; //Setting Status to Test
    $$('#statusSelect option').each(function(o){
      if(o.value == 1) o.selected = true;
    });
    $('creator').value = userId;
    $$('#creatorSelect option').each(function(o){
      if(o.value == userId) o.selected = true;
    });
  },  
  
  /**
   * selectTab
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
   * getFormSaveSucces
   */
  getFormSaveSucces: function(){
    $('divFormSaveSucces').show();
    $('divFormSaveLoader').hide();
    $('divFormSaveSucces').fade({duration: 2});
  },
  
  /**
   * getFormSaveError
   */
  getFormSaveError: function(){
    $('divFormSaveError').show();
    $('divFormSaveLoader').hide();
    $('divFormSaveError').fade({duration: 2});
  },
  
  /**
   * getFormDeleteSucces
   */
  getFormDeleteSucces: function(){
    $('divFormDeleteSucces').show();
    $('divFormSaveLoader').hide();
    $('divFormDeleteSucces').fade({duration: 2});
  },
  
  /**
   * getFormSaveLoader
   */
  getFormSaveLoader: function(){
    $('divFormSaveLoader').show();    
  },
  
  /**
   * getFormSaveLoader
   */
  cancleFormSaveLoader: function(){
    $('divFormSaveLoader').hide();    
  },

  countChars: function(elementId, maxChars) {
    var field = $( elementId ).getValue();
    var count = maxChars - field.length;
    var str = '';
    if( count > 0 ) {
        str = '<span class="plus">'+count+'</span>';
    } else {
        str = '<span class="minus">'+count+'</span>';
    }
    $( 'chars_count_' + elementId ).update( str );
  },

  initSnippetPreview: function() {

    this.updateSnippetPreviewTitle();
    this.updateSnippetPreviewDesc();
    this.updateSnippetPreviewUrl();

    $('articletitle').observe('keyup', function(event){
        myForm.updateSnippetPreviewTitle();
    });

    $('seo_title').observe('keyup', function(event){
        myForm.updateSnippetPreviewTitle();
    });

    $('seo_description').observe('keyup', function(event) {
        myForm.updateSnippetPreviewDesc();
    });
  },

  updateSnippetPreviewTitle: function() {

    var title = this.cleanText( $('seo_title').getValue() );
    if( title == '' ) {
        title = this.cleanText( $('articletitle').getValue() );
    }

    if( title == '' ) {
        title = this.cleanText( $('title').getValue() );
    }

    if( title.length > 70 ) {
        var space = title.lastIndexOf( " ", 67 );
        title = title.substring( 0, space ).concat( ' <strong>...</strong>' );
    }

    title = this.pickOutSeoKeywords( title );

    $('snippet_seo_title').update( title );
  },

  updateSnippetPreviewDesc: function () {
    var desc = this.cleanText( $('seo_description').getValue() );

    if( desc == '' ) {
        desc = this.cleanText( $('description').getValue() );
    }

    if( desc.length > 150 ) {
        var space = desc.lastIndexOf( " ", 147 );
        desc = desc.substring( 0, space ).concat( ' <strong>...</strong>' );
    }

    desc = this.pickOutSeoKeywords( desc );

    $('snippet_seo_desc').update( desc );
  },

  updateSnippetPreviewUrl: function () {
    var url = $('page_url').readAttribute('href');
    $('snippet_seo_url').insert(url);
  },

  cleanText: function( text ) {

      if ( text == '' || text == undefined )
          return '';

      text = text.replace(/^\s+|\s+$/g, '');
      text = text.replace(/<\/?[^>]+>/gi, '');
      text = text.replace(/\[(.+?)\](.+?\[\/\\1\])?/, '');
      return text;
  },

  pickOutSeoKeywords: function( text ) {

      var seo_keywords = $('seo_keywords').getValue();
      if( seo_keywords == '' )
          return text;

      if ( seo_keywords.search(' ') != -1 ) {
          var aKeywords = seo_keywords.split(' ');
      } else {
          var aKeywords	= new Array( seo_keywords );
      }

      for ( var i = 0; i < aKeywords.length; i++) {

          var tKeyword = aKeywords[ i ].replace(',', '');
          var tRegex = new RegExp( "(^|[ \s\n\r\t\.,'\(\"\+;!?:\-]+)(" + tKeyword + ")($|[ \s\n\r\t\.,'\)\"\+;!?:\-]+)", 'gim' );

          text 	= text.replace( tRegex, "$1<strong>$2</strong>$3" );
      }

      return text;
  }
});
