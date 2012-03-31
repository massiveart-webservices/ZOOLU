/**
 * media.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.Media = Class.create({
  
  initialize: function() { 
    this.formId = 'uploadForm';
    this.editFormId = 'editForm';
    this.updateOverlayContainer = 'overlayGenContent';
        
    this.constThumbContainer = 'divThumbViewContainer';
    this.constListContainer = 'divListViewContainer';
    this.constOverlayGenContent = 'overlayGenContent';
    this.constOverlayMediaWrapper = 'overlayMediaWrapper';
    this.constList = 'list';
    this.constThumb = 'thumb';

    this.lastFileId = 0;
    this.lastFileIds = '';
    this.fileCounter = 0;

    this.intFolderId = 0;
    this.currViewType = 0;
    this.sliderValue = 100;    
    this.constSWFUploadUI = '<div id="buttonplaceholder"></div>' +                            
                            '<div id="divStatus" class="gray666">0 Files Uploaded</div>' +
                            '<input id="btnCancel" type="button" value="Alle abbrechen" disabled="disabled" />' +
                            '<div class="clear"></div>' +
                            '<div id="overlayMediaWrapperUpload" class="mediawrapper"></div>' +
                            '<input type="hidden" id="UploadedFileIds" name="FileIds" value=""/>' +
                            '<div class="clear"></div>' +
                            '<div class="buttoncancel" onclick="myOverlay.close(); return false;">' + myCore.translate.Cancel + '</div>' +  
                            '<div onclick="myMedia.updateUploadedFiles(); return false;" id="buttoneditsave">' +
                            '  <div class="button25leftOn"></div>' +
                            '  <div class="button25centerOn">' + 
                            '    <img width="13" height="13" src="/zoolu-statics/images/icons/icon_save_black.png" class="iconsave"/>' +
                            '    <div>' + myCore.translate.Save + '</div>' +
                            '  </div>' +
                            '  <div class="button25rightOn"></div>' +
                            '  <div class="clear"></div>' +
                            '</div>' +
                            '<div class="clear"></div>';
  },
  
  /**
   * initThumbHover
   */
  initThumbHover: function(){
    $$('#divThumbViewContainer .tdthumbcontainer').each(function(elDiv){ 
    
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('tdthumbcontainer')){
          el.addClassName('hover');
        }else{         
          el.up('.tdthumbcontainer').addClassName('hover');          
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('tdthumbcontainer')){
          el.removeClassName('hover');
        }else{         
          el.up('.tdthumbcontainer').removeClassName('hover');         
        }
      }.bind(this));
      
      elDiv.observe('click', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('tdthumbcontainer')){
          myCore.toggleItemSelected(el.id);
        }else{         
          myCore.toggleItemSelected(el.up('.tdfthumbcontainer').id);
        }              
      }.bind(this));                 
    }.bind(this));    
  },
  
  /**
   * scaleThumbs
   */
  scaleThumbs: function(scaleValue){
    //console.debug(scaleValue);

    var scaleThumbs = document.getElementsByClassName('thumb');
    currSliderValue = scaleValue;
    
    for(i=0; i < scaleThumbs.length; i++){
      newWidth = scaleThumbs[i].readAttribute('startWidth') * scaleValue / 100;
      scaleThumbs[i].style.width = newWidth+'px';
      
      $('divThumbPos'+scaleThumbs[i].id).style.width = newWidth+'px';                                                  
      
      $('divThumbContainer'+scaleThumbs[i].id).setStyle({width: ((100 * scaleValue / 100))+'px',
                                                         height: ((100 * scaleValue / 100))+'px'});
      
      $('tdThumb'+scaleThumbs[i].id).setStyle({width: (100 * scaleValue / 100)+'px',
                                               height: (100 * scaleValue / 100)+'px'});
    } 
  },
  
  /**
   * getMediaFolderContent
   */
  getMediaFolderContent: function(folderId, viewType){ 
    var view;
    var strAjaxAction;
    
    // check if function call is for dashboard edit
    if(myNavigation.arrTreeToLoad == null || myNavigation.arrTreeToLoad.length <= 1){
    
      this.sliderValue = Math.round(currSliderValue);
        
      if(typeof(viewType) == 'undefined' || viewType == ''){
        view = this.currViewType;
      }else{
        view = viewType;
        this.currViewType = viewType;
      }
          
      if(view == this.constList){
        updateDiv = this.constListContainer;
        strAjaxAction = '/zoolu/media/view/list';
        this.toggleMediaViewIcons(this.constList);    	  
  	  }else{
  	    updateDiv = this.constThumbContainer;
  	    strAjaxAction = '/zoolu/media/view/thumb';
  	    this.toggleMediaViewIcons(this.constThumb); 
  	  }
  	  
  	  $(updateDiv).show();
  	  $(this.constThumbContainer).innerHTML = '';
  	  $(this.constListContainer).innerHTML = '';
      
      if($('divMediaContainer')) $('divMediaContainer').show();
      if($('divFormContainer')) $('divFormContainer').hide();
      
      myCore.addBusyClass(updateDiv);
      
      new Ajax.Updater(updateDiv, strAjaxAction, {
        parameters: { 
          folderId: folderId,
          rootLevelId: myNavigation.rootLevelId,
          sliderValue: Math.round(currSliderValue) 
        },
        evalScripts: true,     
        onComplete: function() {        
          this.intFolderId = folderId;
          this.initThumbHover();        
          myCore.removeBusyClass(updateDiv);
          
          // dashboard call for edit (single edit)
          if(typeof(myNavigation.intTreeItemId) != 'undefined' && myNavigation.intTreeItemId > 0){
            // check view types (list, thumb)
            if(view == this.constList){
              if($('Row'+myNavigation.intTreeItemId)){
                $('Row'+myNavigation.intTreeItemId).down('.rowicon img').ondblclick();
              }
            }else{
              if($('divThumbPosImg'+myNavigation.intTreeItemId)){
                $('divThumbPosImg'+myNavigation.intTreeItemId).ondblclick();
              }else if($('divThumbPosDoc'+myNavigation.intTreeItemId)){
                $('divThumbPosDoc'+myNavigation.intTreeItemId).ondblclick();
              }
            }
            // reset tree load item id
            myNavigation.intTreeItemId = 0;
          }
          myCore.initListHover();
          myCore.initSelectAll();
        }.bind(this)
      });
    }
  },
  
  /**
   * isAuthorizedToAdd
   */
  isAuthorizedToAdd: function(authorized){
    if(authorized == true){
      $('buttonmedianew').show();
      $('divMediaEditMenu').setStyle({left:'122px'});
    }else{
      $('buttonmedianew').hide();
      $('divMediaEditMenu').setStyle({left:'10px'});
    }
  },
  
  /**
   * isAuthorizedToDelete
   */
  isAuthorizedToDelete: function(authorized){
    if(authorized == true){
      $('buttonmediadelete').show();
    }else{
      $('buttonmediadelete').hide();
    }
  },
  
  /**
   * isAuthorizedToUpdate
   */
  isAuthorizedToUpdate: function(authorized){
    if(authorized == true){
      $('buttonmediamove').show();
    }else{
      $('buttonmediamove').hide();
    }
  },
  
  /**
   * moveFiles
   */
  moveFiles: function(){    
    if(this.getStringFileIds() != ''){
      this.toggleMediaEditMenu('buttonmediaedittitle', 'hide');
      myFolder.getCurrentFolderParentChooser('MOVE_MEDIA');
    }
  },
  
  /**
   * selectParentFolder
   */
  selectParentFolder: function(parentFolderId){
    myCore.addBusyClass('overlayGenContent');
  
    new Ajax.Request('/zoolu/media/file/changeparentfolder', {
      parameters: { 
       files: this.getStringFileIds(),
       parentFolderId: parentFolderId
      },      
      evalScripts: true,     
      onComplete: function() {  
        $('overlayGenContentWrapper').hide(); 
        $('overlayBlack75').hide();
        
        this.getMediaFolderContent(myNavigation.folderId);
        
        /* to jump to the new folder
        if($('folder'+parentFolderId)){
          myNavigation.itemId = 'folder'+parentFolderId;            
          myNavigation.selectItem();
        }*/
        
        myCore.removeBusyClass('overlayGenContent');
      }.bind(this)
    });
  },
  
  
  /**
   * getMediaListView
   */
  getMediaListView: function(){
    $(this.constThumbContainer).hide();
    $(this.constListContainer).show();
    if(!($('divListView').readAttribute('class').indexOf('_on') == -1)){
      this.getMediaFolderContent(this.intFolderId, this.constList);
    }
    this.toggleMediaViewIcons(this.constList);
    myCore.initListHover();    
  },
  
  /**
   * getMediaThumbView
   */
  getMediaThumbView: function(){
    $(this.constThumbContainer).show();
    $(this.constListContainer).hide();
    if(!($('divThumbView').readAttribute('class').indexOf('_on') == -1)){
      this.getMediaFolderContent(this.intFolderId, this.constThumb);
    }
    this.toggleMediaViewIcons(this.constThumb);
    this.initThumbHover();    
  },
  
  /**
   * toggleMediaViewIcons
   */
  toggleMediaViewIcons: function(viewType){    
    if(viewType != this.constList){
      $('divThumbView').removeClassName('iconthumbview_on');
	    $('divThumbView').addClassName('iconthumbview');	    
	    $('divListView').removeClassName('iconlistview');
	    $('divListView').addClassName('iconlistview_on');
	    $('mediaslider').show();
	    $('mediaSearchContainer').hide();
    }else{
      $('divThumbView').removeClassName('iconthumbview');
	    $('divThumbView').addClassName('iconthumbview_on');	    
	    $('divListView').removeClassName('iconlistview_on');
	    $('divListView').addClassName('iconlistview');
	    $('mediaslider').hide();
	    $('mediaSearchContainer').show();
    }    
  },
  
  /**
   * toggleMediaEditMenu
   */
  toggleMediaEditMenu: function(elementId, forceHide){    
    if($('divMediaEditMenu')){
      if(typeof(forceHide) == 'undefined') forceHide = false;
      if(!forceHide && $('divMediaEditMenu').style.display == 'none'){
        $('divMediaEditMenu').appear({ delay: 0, duration: 0.3 });
        if($(elementId)) $(elementId).removeClassName('white');      
      }else{
        $('divMediaEditMenu').fade({ duration: 0.3 });
        if($(elementId)) $(elementId).addClassName('white');
      }
    }
  },
  
  /**
   * toggleDestinationOptions
   */
  toggleDestinationOptions: function(checkBox, elId){
    if($('shownDestinationOptions'+elId) && checkBox.checked){
      Effect.SlideDown('shownDestinationOptions'+elId, {duration: 0.5});
      $('FileDestinationId'+elId).value = $F('selectFileDestinationId'+elId);
    }else{
      Effect.SlideUp('shownDestinationOptions'+elId, {duration: 0.5});
      $('FileDestinationId'+elId).value = 0;
    }
  },
  
  /**
   * toggleGroupOptions
   */
  toggleGroupOptions: function(checkBox, elId){
    if($('shownGroupOptions'+elId) && checkBox.checked){
      Effect.SlideDown('shownGroupOptions'+elId, {duration: 0.5});
      $('FileGroupId'+elId).value = $F('selectFileGroupId'+elId);
    }else{
      Effect.SlideUp('shownGroupOptions'+elId, {duration: 0.5});
      $('FileGroupId'+elId).value = 0;
    }
  },
  
  /**
   * getUploadWidget
   */
  getUploadWidget: function(){
    this.initSWFUpload();
  },
  
  /**
   * initSWFUpload
   */
  initSWFUpload: function(){
    
    $('divSWFUploadUI').update(this.constSWFUploadUI);
        
    var settings = {
      flash_url : "/zoolu-statics/flash/swfupload/swfupload.swf",
      upload_url: "/zoolu/media/upload",
      post_params: {
        PHPSESSID: sessionId, 
        folderId: myNavigation.parentFolderId
      }, 
      file_size_limit : swf_file_size_limit,
      file_types : "*.*",
      file_types_description : myCore.translate.All_files,
      file_upload_limit : 100,
      file_queue_limit : 0,
      custom_settings : {
        progressTarget : "overlayMediaWrapperUpload",
        cancelButtonId : "btnCancel"
      },
      debug: false,
  
      // Button Settings
      button_image_url : "/zoolu-statics/images/buttons/button_selectfiles_" + myCore.languageCode + ".png",
      button_placeholder_id : "buttonplaceholder",
      button_width: 113,
      button_height: 25,
      button_cursor: -2,
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
  
      // The event handler functions are defined in handlers.js
      swfupload_loaded_handler : swfUploadLoaded,
      file_queued_handler : fileQueued,
      file_queue_error_handler : fileQueueError,
      file_dialog_complete_handler : fileDialogComplete,
      upload_start_handler : uploadStart,
      upload_progress_handler : uploadProgress,
      upload_error_handler : uploadError,
      upload_success_handler : uploadSuccess,
      upload_complete_handler : uploadComplete,
      queue_complete_handler : queueComplete, // Queue plugin event
      
      // SWFObject settings
      minimum_flash_version : "9.0.28",
      swfupload_pre_load_handler : swfUploadPreLoad,
      swfupload_load_failed_handler : swfUploadLoadFailed
    };

    swfu = new SWFUpload(settings);
    
    myCore.calcMaxOverlayHeight('overlayMediaWrapperUpload', true);
    myCore.putOverlayCenter('overlayUpload');
    $('overlayUpload').show();
    $('overlayBlack75').show();
  },
  
  /**
   * initSingleSWFUpload
   */
  initSingleSWFUpload: function(fileId){
            
    var settings = {
      flash_url: "/zoolu-statics/flash/swfupload/swfupload.swf",
      upload_url: "/zoolu/media/upload/version",
      post_params: {
        PHPSESSID: sessionId,
        fileId: fileId
      }, 
      file_size_limit: swf_file_size_limit,
      file_types: "*.*",
      file_types_description: myCore.translate.All_files,
      file_upload_limit: "0",
      file_queue_limit: "1",
      custom_settings: {
        progress_target: "fsUploadProgress",
        upload_successful: false
      },
      debug: false,
  
      // Button Settings
      button_image_url: "/zoolu-statics/images/buttons/button_selectfiles_" + myCore.languageCode + ".png",
      button_placeholder_id: "spanButtonPlaceholder",
      button_width: 113,
      button_height: 25,
      button_cursor: -2,
      button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
  
      // Event handler settings
      swfupload_loaded_handler: singleSWFUploadLoaded,
      
      file_dialog_start_handler: singleFileDialogStart,
      file_queued_handler: singleFileQueued,
      file_queue_error_handler: singleFileQueueError,
      file_dialog_complete_handler: singleFileDialogComplete,
      
      //upload_start_handler: singleUploadStart, // I could do some client/JavaScript validation here, but I don't need to.
      upload_progress_handler: singleUploadProgress,
      upload_error_handler: singleUploadError,
      upload_success_handler: singleUploadSuccess,
      upload_complete_handler: singleUploadComplete,
      
      // SWFObject settings
      minimum_flash_version : "9.0.28"
    };

    swfu = new SWFUpload(settings);
  },
  
  /**
   * addUploadedFileId
   */
  addUploadedFileId: function(fileId){  
    if($('UploadedFileIds')){ 
      $('UploadedFileIds').value = $('UploadedFileIds').value + '[' + fileId + ']';
    }    
    if($('btnSave')){
      $('btnSave').disabled = false;
    }
  },
  
  /**
   * updateUploadedFiles
   */
  updateUploadedFiles: function(){
    
    if($(this.formId)){
      
      var arrFields = $(this.formId).getElements();
      arrFields.each(function(el){
        if($F(el.id) == fileDefaultDescription){
          $(el.id).value = '';
        }
      }.bind(this));
         
      /**
       * serialize upload form
       */
      var serializedForm = $(this.formId).serialize();
      var strAjaxAction = $(this.formId).readAttribute('action') + '/save';
      myCore.addBusyClass('overlayMediaWrapperUpload');
      new Ajax.Updater(this.constThumbContainer, strAjaxAction, {
        parameters: serializedForm,
        evalScripts: true,
        onComplete: function() {       
          this.getMediaFolderContent(this.intFolderId);
          myCore.removeBusyClass('overlayMediaWrapperUpload');
          //$('overlayUpload').hide();
          //$('overlayBlack75').hide();  
        }.bind(this)
      });
    }
  },
  
  /**
   * getFilesEditForm
   */
  getFilesEditForm: function(){
        
    var intLanguageId = -1;
    if($('mediaFormLanguageId')) {
      intLanguageId = $F('mediaFormLanguageId');
    }
    
    $(this.constOverlayGenContent).innerHTML = '';
    
    var strFileIds = this.getStringFileIds();
    
    if(strFileIds == '' && this.lastFileIds != ''){
      strFileIds = this.lastFileIds;
    }
   
    if(strFileIds != ''){
      this.lastFileId = 0;
      this.lastFileIds = strFileIds;
      myCore.addBusyClass(this.constOverlayGenContent);
      
      myCore.putCenter('overlayGenContentWrapper');
      
      $('overlayBlack75').show();
      $('overlayGenContentWrapper').show();
            
      new Ajax.Updater(this.constOverlayGenContent, '/zoolu/media/file/geteditform', {
        parameters: { fileIds: strFileIds, rootLevelId: myNavigation.rootLevelId, languageId: intLanguageId },
        evalScripts: true,
        onComplete: function() {
          myCore.calcMaxOverlayHeight(this.constOverlayMediaWrapper, true);
          myCore.putOverlayCenter('overlayGenContentWrapper');          
          myCore.removeBusyClass(this.constOverlayGenContent);                    
          this.toggleMediaEditMenu('buttonmediaedittitle', true);                   
        }.bind(this)
      });
    }   
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
   * getSingleFileEditForm
   */
  getSingleFileEditForm: function(fileId, languageId){
    
    var intLanguageId = -1;
    
    if(typeof(languageId) != 'undefined'){
      intLanguageId = languageId;
    }
    
    if($('mediaFormLanguageId')) {
      intLanguageId = $F('mediaFormLanguageId');
    }
    
    $(this.constOverlayGenContent).innerHTML = '';
   
    if(typeof(fileId) == 'undefined' && this.lastFileId > 0){
      fileId = this.lastFileId;
    }
    
    if(fileId != ''){
      this.lastFileId = fileId;
      this.lastFileIds = '';
      
      var blnShow = true;
      if($('overlaySingleEdit').visible() == true) {
        blnShow = false;
      }
      myCore.addBusyClass('overlaySingleEditContent');
      myCore.putCenter('overlaySingleEdit');
      
      $('overlayBlack75').show();
      $('overlaySingleEdit').show();
                  
      new Ajax.Updater('overlaySingleEditContent', '/zoolu/media/file/getsingleeditform', {
        parameters: { fileId: fileId, rootLevelId: myNavigation.rootLevelId, languageId: intLanguageId },
        evalScripts: true,
        onComplete: function() {
          if($('spanButtonPlaceholder')) this.initSingleSWFUpload(fileId);
          myCore.calcMaxOverlayHeight(this.constOverlayMediaWrapper, true);
          myCore.putOverlayCenter('overlaySingleEdit');          
          myCore.removeBusyClass('overlaySingleEditContent');       
          if(blnShow) myOverlay.overlayCounter++;
          this.toggleMediaEditMenu('buttonmediaedittitle', true);
          this.iniZeroClipboard();
          // load medias
          this.loadFileFieldsContent('media');
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
      
      $$('#editForm .'+strType).each(function(elDiv){    
        if($(elDiv.id)){          
          var fileFieldId = elDiv.id.substring(elDiv.id.indexOf('_')+1);
          if($(fileFieldId).value != ''){
            myCore.addBusyClass(elDiv.id);     
            new Ajax.Updater(elDiv.id, '/zoolu/cms/page/getfiles', {
              parameters: { 
                fileIds: $(fileFieldId).value,
                fileFieldId: fileFieldId,
                viewtype: strViewType,
                languageId: languageId
              },
              evalScripts: true,
              onComplete: function(){
                // add the scriptaculous sortable functionality to the parent container
                //alert('complete');
                switch(strViewType){
                  case 1:
                    myForm.initSortable(fileFieldId, elDiv.id, 'mediaitem', 'div', 'fileid', 'both');  
                    break;
                  case 2:
                    myForm.initSortable(fileFieldId, elDiv.id, 'docitem', 'div', 'fileid', 'vertical');  
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
   * iniZeroClipboard
   */
  iniZeroClipboard: function(){
    clip = new ZeroClipboard.Client();
    
    clip.setText(''); // will be set later on mouseDown
    clip.setHandCursor(true);
    clip.setCSSEffects(false);
    
    clip.addEventListener('load', function(client){
      //alert("movie is loaded");      
    });
    
    clip.addEventListener('mouseDown', function(client){ 
      //set text to copy here
      clip.setText($F('singleMediaUrl'));
    });
    
    clip.glue('d_clip_button', 'd_clip_container');    
  },
  
  /**
   * editFiles
   */
  editFiles: function(isSingleEdit){
    
    if($(this.editFormId)){      
      
      if(typeof(isSingleEdit) == 'undefined'){
        isSingleEdit = false;
      }
      
      var arrFields = $(this.editFormId).getElements();
      arrFields.each(function(el){
        if($F(el.id) == fileDefaultDescription){
          $(el.id).value = '';
        }
      }.bind(this));
      
      /**
       * serialize generic form
       */
      var serializedForm = $(this.editFormId).serialize();
      myCore.addBusyClass('overlayMediaWrapper');
      
      new Ajax.Request($(this.editFormId).readAttribute('action'), {
        parameters: serializedForm,
        onComplete: function(transport) {  
          if(isSingleEdit == true && transport.responseText != ''){
            this.overlayCounter--;
            this.getSingleFileEditForm(transport.responseText);
          }else{
            myCore.removeBusyClass('overlayMediaWrapper');
          }
          this.getMediaFolderContent(this.intFolderId);          
        }.bind(this)
      });          
    }    
  },
  
  /**
   * changeAddFormLanguage
   */
  changeAddFormLanguage: function(newLanguageId){
    $('addMediaFormLanguageId').value = newLanguageId;
    this.getFilesAddEditForm();
  },
  
  /**
   * getFilesAddEditForm
   */
  getFilesAddEditForm: function(){
    
    var intLanguageId = -1;
    if($('addMediaFormLanguageId')) {
      intLanguageId = $F('addMediaFormLanguageId');
    }
    
    var strFileIds = $F('UploadedFileIds');
       
    if(strFileIds != ''){
      
      myCore.addBusyClass('overlayMediaWrapperUpload');
      $('overlayUpload').show();
      $('overlayBlack75').show();
                  
      new Ajax.Updater('overlayMediaWrapperUpload', '/zoolu/media/file/getaddeditform', {
        parameters: { fileIds: strFileIds, languageId: intLanguageId },
        evalScripts: true,
        onComplete: function() {
          myCore.calcMaxOverlayHeight('overlayMediaWrapperUpload', true);
          myCore.putOverlayCenter('overlayUpload');          
          myCore.removeBusyClass('overlayMediaWrapperUpload');
        }.bind(this)
      });
    }   
  },
  
  /**
   * changeEditFormLanguage
   */
  changeEditFormLanguage: function(newLanguageId){
    $('mediaFormLanguageId').value = newLanguageId;
    if(this.lastFileIds != '' && this.lastFileId == 0){
      this.getFilesEditForm();
    }else{
      this.getSingleFileEditForm();
    }
  },
  
  /**
   * deleteFiles
   */
  deleteFiles: function(){    
    
    var strFileIds = this.getStringFileIds();
    
    if(strFileIds != ''){

      if($('rootLevelType' + myNavigation.rootLevelId)){
        tmpKey = 'Delete_' + $('rootLevelType' + myNavigation.rootLevelId).getValue();
        var key = (myCore.translate[tmpKey]) ? tmpKey : 'Delete_';
        var keyMulti = (myCore.translate[tmpKey + 's']) ? tmpKey + 's' : 'Delete_';
      }else{
        var key = 'Delete_';
        var keyMulti = 'Delete_';
      }

      myCore.deleteAlertSingleMessage = myCore.translate[key];
      myCore.deleteAlertMultiMessage = myCore.translate[keyMulti];
      myCore.showDeleteAlertMessage(this.fileCounter);

      $('buttonOk').observe('click', function(event){
        myCore.hideDeleteAlertMessage();

        new Ajax.Updater(this.constThumbContainer, '/zoolu/media/file/delete', {
          parameters: { fileIds: strFileIds, rootLevelId: myNavigation.rootLevelId },
          evalScripts: true,
          onComplete: function() {
            this.toggleMediaEditMenu('buttonmediaedittitle', true);
            this.getMediaFolderContent(this.intFolderId);
          }.bind(this)
        });
      }.bind(this));

      $('buttonCancel').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
        this.toggleMediaEditMenu('buttonmediaedittitle', true);
      }.bind(this));
    }   
  },
  
  /**
   * getStringFileIds
   */
  getStringFileIds: function(){
    
    var strFileIds = '';
    this.fileCounter = 0;
    $$('.contentview .selected').each(function(element){ 
      strFileIds = strFileIds + '[' + element.readAttribute('fileid') + ']';
      this.fileCounter++;
    }.bind(this));
    
    return strFileIds;    
  },
  
  /**
   * setFocusTextarea
   * @param string elementId
   */
  setFocusTextarea: function(elementId){
    if($(elementId)){    
      if($(elementId).hasClassName('textarea') == false){
        $(elementId).innerHTML = '';
        $(elementId).addClassName('textarea');   
      }    
    }
  }
  
});