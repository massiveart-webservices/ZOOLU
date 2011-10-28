/**
 * dashboard.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-21: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Dashboard = Class.create({

  initialize: function() {
    this.retValue = false;
    this.formId = 'dbrdForm';
    this.formContainer = 'divNaviLeftMain';
    this.formFieldsContainer = 'fieldsbox';    
        
    this.updateOverlayContainer = 'overlayGenContent';
    this.contentContainer = 'dbrdEntries';
    
    this.recipientsContainer = 'recipients_';
    this.recipientsMore = 'recipientsMore_';
    
    this.attachContainer = 'attachments_';
    
    this.commentFormId = 'dbrdCommentForm';
    this.commentsContainer = 'commentsContainer_';
    this.commentsTextarea = 'comments_';
    this.commentsMore = 'commentsMore_';
    this.commentBox = 'commentBox_';
    this.commentTmp = 'commentTmp_';
    this.commentSave = 'buttonsave_';
    
    this.activity = 'activity_';
    this.entry = 'activityEntry_';
    this.linkToggle = 'activityToggle_';
    this.linkHide = 'activityHide_';
    this.linkShow = 'activityShow_';
    this.propsContent = 'activityPropsContent_';
 
    this.areaId = '';
    
    this.olCurrContainerId = '';
    this.olNewContainerId = '';
    
    this.offsetX = 430;
    
    this.defTab = 'ALL';
    this.activeTab = 'ALL';
    
    this.blnNoEntries = false;
    this.loadStatus = false;
    this.loadOffset = 0;
    this.loadLimit = 5;
    
    this.folderUpdateContainer = 'olFolderContent';
    this.languageField = 'languageId';
    this.languageId;
    this.fieldId;
    this.areaViewType = new Object();
    this.viewtype = null;
    this.lastFolderId = null;
    
    this.mode = null;
  },
  
  /**
   * init
   */
  init: function(){
    this.initForm();
    this.loadDashboard();
    
    $(this.contentContainer).observe('scroll', function(event){        
      this.scrollLaoder(this.contentContainer);
    }.bind(this));
  },
  
  /**
   * initForm 
   */
  initForm: function(){
    if($(this.formFieldsContainer)){
      $(this.formFieldsContainer).update('');
      
      myCore.addBusyClass(this.formFieldsContainer);
      new Ajax.Updater(this.formFieldsContainer, '/zoolu/core/dashboard/form', {
        evalScripts: false,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();
          myCore.removeBusyClass(this.formFieldsContainer);
        }.bind(this)
      });
    }
  },
  
  /**
   * selectTab
   * @param string tabSuffix
   */
  selectTab: function(tabSuffix){
    this.blnNoEntries = false;
    this.loadOffset = 0;
    this.loadDashboard(tabSuffix);
  },
  
  /**
   * selectItem
   * @param integer rootLevelId
   * @param integer relationId
   * @param integer parentId
   * @param integer parentTypeId
   * @param string url
   */
  selectItem: function(rootLevelId, rootLevelGroupId, relationId, parentId, parentTypeId, url){
    if(typeof(url) != 'undefined' && url != ''){
      
      var myForm = document.createElement('form');
      myForm.method = 'post';
      myForm.action = url;
     
      var myRootLevelIdInput = document.createElement("input");
      myRootLevelIdInput.setAttribute('name', 'rootLevelId');
      myRootLevelIdInput.setAttribute('value', rootLevelId);
      myRootLevelIdInput.setAttribute('type', 'hidden');
      myForm.appendChild(myRootLevelIdInput);
      
      var myRootLevelGroupIdInput = document.createElement("input");
      myRootLevelGroupIdInput.setAttribute('name', 'rootLevelGroupId');
      myRootLevelGroupIdInput.setAttribute('value', rootLevelGroupId);
      myRootLevelGroupIdInput.setAttribute('type', 'hidden');
      myForm.appendChild(myRootLevelGroupIdInput);
      
      var myRelationIdInput = document.createElement("input");
      myRelationIdInput.setAttribute('name', 'relationId');
      myRelationIdInput.setAttribute('value', relationId);
      myRelationIdInput.setAttribute('type', 'hidden');
      myForm.appendChild(myRelationIdInput);
      
      var myParentIdInput = document.createElement("input");
      myParentIdInput.setAttribute('name', 'parentId');
      myParentIdInput.setAttribute('value', parentId);
      myParentIdInput.setAttribute('type', 'hidden');
      myForm.appendChild(myParentIdInput);
      
      var myParentTypeIdInput = document.createElement("input");
      myParentTypeIdInput.setAttribute('name', 'parentTypeId');
      myParentTypeIdInput.setAttribute('value', parentTypeId);
      myParentTypeIdInput.setAttribute('type', 'hidden');
      myForm.appendChild(myParentTypeIdInput);
      
      document.body.appendChild(myForm);
      myForm.submit();
    }
  },
  
  /**
   * loadDashboard 
   * @param string tabSuffix 
   * @param boolean isReload
   */
  loadDashboard: function(tabSuffix, isReload){
    if($(this.contentContainer) && this.loadStatus == false){
      this.loadStatus = true;
      
      if(typeof(tabSuffix) == 'undefined') tabSuffix = this.defTab; 
      if(typeof(isReload) == 'undefined') isReload = false;
      var ajaxAction = '/zoolu/core/dashboard/entries'; 
      
      if(isReload){        
        $(this.contentContainer).insert({bottom: new Element('div', {'id': 'is_loading', 'class': 'busy'})});
        
        new Ajax.Updater(this.contentContainer, ajaxAction, {
          parameters: { 
            filter: tabSuffix,
            offset: this.loadOffset,
            limit: this.loadLimit 
          },
          insertion: 'bottom',
          evalScripts: false,
          onComplete: function(transport) {
            //problem: ajax.updater evalScripts = true was too late
            transport.responseText.evalScripts();          
            
            this.initCommentBoxes();
            if($('is_loading')) $('is_loading').remove();
            
            this.loadOffset = this.loadOffset + this.loadLimit;
            this.loadStatus = false;
          }.bind(this)
        });
      }else{
        this.loadOffset = 0;
        
        $(this.contentContainer).update('');
        myCore.addBusyClass(this.contentContainer);
        
        new Ajax.Updater(this.contentContainer, ajaxAction, {
          parameters: { 
            filter: tabSuffix,
            offset: this.loadOffset,
            limit: this.loadLimit 
          },
          evalScripts: false,
          onComplete: function(transport) {
            //problem: ajax.updater evalScripts = true was too late
            transport.responseText.evalScripts();          

            $('tabNavItem_'+this.activeTab).removeClassName('selected');
            $('tabNavItem_'+tabSuffix).addClassName('selected');          
            this.activeTab = tabSuffix;
            myCore.removeBusyClass(this.contentContainer);
            
            this.initCommentBoxes();
            
            this.loadOffset = this.loadOffset + this.loadLimit;
            this.loadStatus = false;
          }.bind(this)
        });
      }
    }
  },

  /**
   * save
   */
  save: function(){
    if($(this.formId)){
      
      /**
       * validation
       */
      this.retValue = true;
      $$('.mandatory').each(function(element){
        this.validateInput(element.id);
      }.bind(this));
      
      /**
       * serialize generic form
       */
      var serializedForm = $(this.formId).serialize();
      
      if(this.retValue){
        new Ajax.Request($(this.formId).readAttribute('action'), {
          parameters: serializedForm,
          evalScripts: false,
          onComplete: function(transport) {
            //problem: ajax.updater evalScripts = true was too late
            transport.responseText.evalScripts();
            
            this.initForm();
            this.loadDashboard(this.activeTab);
            //this.resetForm();
          }.bind(this)
        });
      }
    }
  },
  
  /**
   * toggleEntry 
   * @param integer activityId
   */
  deleteEntry: function(activityId){
      
    if($(this.activity+activityId)){
      var tmpKey = 'Delete_Activity';
      if(myCore.translate[tmpKey]){
        var key = tmpKey;
      }else{
        var key = 'Delete';
      }
      
      myCore.deleteAlertSingleMessage = myCore.translate[key];
      myCore.showDeleteAlertMessage(1);

      $('buttonOk').observe('click', function(event){
        new Ajax.Request('/zoolu/core/dashboard/delete', {
          parameters: { id: activityId },
          evalScripts: true,
          onComplete: function() {
            $(this.activity+activityId).fade({duration: 0.5});
            myCore.hideDeleteAlertMessage();
          }.bind(this)
        });      
      }.bind(this));
      
      $('buttonCancel').observe('click', function(event){
        myCore.hideDeleteAlertMessage();
      }.bind(this));
    }
  },
  
  /**
   * toggleEntry 
   * @param integer activityId
   */
  toggleEntry: function(activityId){
    if($(this.entry+activityId)){
      if($(this.entry+activityId).style.height == 'auto'){
        $(this.entry+activityId).setStyle({height: '85px'});
        if($(this.linkHide+activityId)) $(this.linkHide+activityId).hide();
        if($(this.linkShow+activityId)) $(this.linkShow+activityId).show();
      }else{
        $(this.entry+activityId).setStyle({height: 'auto'});
        if($(this.linkHide+activityId)) $(this.linkHide+activityId).show();
        if($(this.linkShow+activityId)) $(this.linkShow+activityId).hide();
      }
    }
  },
  
  /**
   * toggleProps 
   * @param integer activityId
   */
  toggleProps: function(activityId){
    if($(this.propsContent+activityId)){
      if($(this.propsContent+activityId).style.display == 'none'){
        $(this.propsContent+activityId).appear({duration: 0.2});
        $(this.propsContent+activityId).observe('mouseout', function(event){
          var target = this.propsContent+activityId;
          var mouse_over_element; 
          if( event.toElement ) {
             mouse_over_element = event.toElement;
          }
          else if(event.relatedTarget) {
            mouse_over_element = event.relatedTarget;
          }
          //In the event that the mouse is over something outside the DOM (like an alert window)...
          if(mouse_over_element == null) {
             return;
          }
          if(!mouse_over_element.descendantOf(target) && target != mouse_over_element) {
            this.toggleProps(activityId);
          }
        }.bind(this));
      }else{
        $(this.propsContent+activityId).fade({duration: 0.2});
        $(this.propsContent+activityId).stopObserving();
      }
    }
  },
  
  /**
   * saveComment
   * @param integer activityId
   */
  saveComment: function(activityId){
    if($(this.commentsTextarea+activityId) && $F(this.commentsTextarea+activityId) != ''){
      new Ajax.Request('/zoolu/core/dashboard/add-comment', {
        parameters: { 
          id: activityId, 
          comment: $F(this.commentsTextarea+activityId) 
        },
        evalScripts: false,
        onComplete: function(transport) {
          //problem: ajax.updater evalScripts = true was too late
          transport.responseText.evalScripts();
          this.getComments(activityId);
          
          $(this.commentsTextarea+activityId).remove();
          if($(this.commentTmp+activityId)) $(this.commentTmp+activityId).show();
          if($(this.commentSave+activityId)) $(this.commentSave+activityId).hide();
        }.bind(this)
      });
    }
  },
  
  /**
   * getRecipients
   * @param integer activityId
   */
  getRecipients: function(activityId){
    if($(this.recipientsContainer+activityId)){
      $(this.recipientsContainer+activityId).update('');
      
      //myCore.addBusyClass(this.recipientsContainer+activityId);
      new Ajax.Updater(this.recipientsContainer+activityId, '/zoolu/core/dashboard/get-recipients', {
        parameters: { id: activityId },
        evalScripts: true,
        onComplete: function(transport) {
          //myCore.removeBusyClass(this.recipientsContainer+activityId);
        }.bind(this)
      });
    }
  },
  
  /**
   * showRecipients
   * @param integer activityId
   */
  showRecipients: function(activityId){
    if($(this.recipientsContainer+'hidden_'+activityId)){
      $(this.recipientsContainer+'hidden_'+activityId).insert({
        before: $(this.recipientsContainer+'hidden_'+activityId).innerHTML
      });
      $(this.recipientsContainer+'hidden_'+activityId).remove();
      if($(this.recipientsMore+activityId)) $(this.recipientsMore+activityId).remove();
    }
  },
  
  /**
   * getContentLinks
   * @param integer activityId
   */
  getContentLinks: function(activityId){
    if($(this.attachContainer+activityId)){
      $(this.attachContainer+activityId).update('');
      
      myCore.addBusyClass(this.attachContainer+activityId);
      new Ajax.Updater(this.attachContainer+activityId, '/zoolu/core/dashboard/get-content-links', {
        parameters: { id: activityId },
        evalScripts: true,
        onComplete: function(transport) {
          myCore.removeBusyClass(this.attachContainer+activityId);
        }.bind(this)
      });
    }
  },
  
  /**
   * getComments
   * @param integer activityId
   */
  getComments: function(activityId){
    if($(this.commentsContainer+activityId)){
      $(this.commentsContainer+activityId).update('');
      
      //myCore.addBusyClass(this.commentsContainer+activityId);
      new Ajax.Updater(this.commentsContainer+activityId, '/zoolu/core/dashboard/get-comments', {
        parameters: { id: activityId },
        evalScripts: true,
        onComplete: function(transport) {
          //myCore.removeBusyClass(this.commentsContainer+activityId);
        }.bind(this)
      });
    }
  },
  
  /**
   * showComments
   * @param integer activityId
   */
  showComments: function(activityId){
    if($(this.commentsTextarea+'hidden_'+activityId)){
      $(this.commentsTextarea+'hidden_'+activityId).insert({
        after: $(this.commentsTextarea+'hidden_'+activityId).innerHTML
      });
      $(this.commentsTextarea+'hidden_'+activityId).remove();
      if($(this.commentsMore+activityId)) $(this.commentsMore+activityId).remove();
    }
  },
  
  /**
   * initCommentBoxes
   */
  initCommentBoxes: function(){
    $$('input.commentTmp').each(function(element){
      element.stopObserving('focus');
      element.observe('focus', function(event){        
        el = Event.element(event);
        this.initCommentBox(el);
      }.bind(this));
    }.bind(this));
  },
  
  /**
   * initCommentBox
   * @param integer activityId
   */
  initCommentBox: function(element){
    if(element){
      var elementId = element.id;
      var activityId = elementId.substring(elementId.indexOf('_')+1);
      
      var textarea = new Element('textarea', {'id': this.commentsTextarea+activityId, 'name': this.commentsTextarea+activityId});
      element.insert({ after: textarea });
      element.hide();      
      textarea.focus();
      
      $(this.commentSave+activityId).show();
      
      textarea.observe('blur', function(event){        
        el = Event.element(event);
        if(el.getValue() == ''){
          el.remove();
          element.show();
          if($(this.commentSave+activityId)) $(this.commentSave+activityId).hide();
        }
      }.bind(this));
    }
  },
  
  /**
   * changeActivityStatus
   * @param changeActivityStatus
   */
  changeActivityStatus: function(activityId){
    if($('activity_'+activityId)){      
      myCore.addBusyClass('activityStatus_'+activityId);
      
      var isChecked = false;
      if($('checked_'+activityId) && $('checked_'+activityId).checked){
        isChecked = true;
      }
      
      new Ajax.Request('/zoolu/core/dashboard/change-status', {
        parameters: { 
          id: activityId, 
          checked: isChecked 
        },
        evalScripts: false,
        onComplete: function(transport) {
          if($('activity_'+activityId).hasClassName('checked')){
            $('activity_'+activityId).removeClassName('checked');
            if($(this.linkToggle+activityId)) $(this.linkToggle+activityId).hide();
          }else{
            $('activity_'+activityId).addClassName('checked');
            if($(this.linkToggle+activityId)) $(this.linkToggle+activityId).show();
            if($(this.linkHide+activityId)) $(this.linkHide+activityId).hide();
            if($(this.linkShow+activityId)) $(this.linkShow+activityId).show();
            if($(this.entry+activityId)) $(this.entry+activityId).setStyle({height: ''});
          }
          myCore.removeBusyClass('activityStatus_'+activityId);
        }.bind(this)
      });
    }
  },
  
  /**
   * getAddUsersOverlay
   * @param string areaId
   */
  getAddUsersOverlay: function(areaId){    
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    myCore.addBusyClass(this.updateOverlayContainer);
    $('overlayGenContentWrapper').show();    
    myOverlay.overlayCounter++;
        
    if($(areaId)){
      this.areaId = areaId;
      var fieldname = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/core/dashboard/overlay-users', { 
        parameters: { userIds: $F(fieldname) },
        evalScripts: true,
        onComplete: function(){
          if(this.mode != null){
            myCore.putOverlayCenter('overlayGenContentWrapper', { x: 100, y: 40 });
          }else{
            myCore.putOverlayCenter('overlayGenContentWrapper');
          }
          myCore.removeBusyClass(this.updateOverlayContainer);
        }.bind(this) 
      });
    }    
  },
  
  /**
   * getModuleOverlay
   * @param string areaId
   */
  getModuleOverlay: function(areaId){
    $(this.updateOverlayContainer).innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    myCore.addBusyClass(this.updateOverlayContainer);
    $('overlayGenContentWrapper').show();    
        
    if($(areaId)){
      this.areaId = areaId;
      var fieldname = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
      new Ajax.Updater(this.updateOverlayContainer, '/zoolu/core/dashboard/overlay-modules', { 
        parameters: { relationIds: $F(fieldname) },
        evalScripts: true,
        onComplete: function(){
          myCore.putOverlayCenter('overlayGenContentWrapper');
          myCore.removeBusyClass(this.updateOverlayContainer);
        }.bind(this) 
      });
    }
  },
  
  /**
   * addUserItemToListArea
   * @param string itemId, integer id
   */
  addUserItemToListArea: function(itemId, id){        
    if($(this.areaId) && $(itemId)){
      
      // get the hidden field id
      var fieldId = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+id;
      
      // create new media item container
      var mediaItemContainer = '<div id="'+fieldId+'_useritem_'+id+'" fileid="'+id+'" class="contactitem" style="display:none;">' + $(itemId).innerHTML + '</div>'; 
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, mediaItemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('User'+id)) $('User'+id).removeAttribute('onclick');
      if($('Remove'+id)) $('Remove'+id).writeAttribute('id', iconRemoveId);
           
      // insert file id to hidden field - only 1 insert is possible
      if($(fieldId).value.indexOf('[' + id + ']') == -1){
        $(fieldId).value = $(fieldId).value + '[' + id + ']';
      }
            
      $(fieldId+'_useritem_'+id).appear({duration: 0.5});
      $(itemId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myForm.removeItem(fieldId, fieldId+'_useritem_'+id, id);
        }
      }
    }    
  },
  
  /**
   * addItemToListArea 
   */
  addItemToListArea: function(itemId, linkId){
    if(typeof(linkId) == 'undefined') linkId = null;
    if($(this.areaId) && $('olItem'+itemId)){
      var moduleId = 0;
      if($('olModuleId') && $F('olModuleId') != '') moduleId = $F('olModuleId');
      var rootLevelId = 0;
      if($('olRootLevelId') && $F('olRootLevelId') != '') rootLevelId = $F('olRootLevelId');
      
      var fieldId = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
      var iconRemoveId = fieldId+'_remove'+itemId;
      
      // create new item container
      var itemContainer = '<div id="'+fieldId+'_item'+itemId+'" moduleid="'+moduleId+'" rootlevelid="'+rootLevelId+'" relationid="'+itemId+'" class="elementitem" style="display:none;">' + $('olItem'+itemId).innerHTML + '</div>'; 
      if($('divClear_'+fieldId)) $('divClear_'+fieldId).remove();
      new Insertion.Bottom(this.areaId, itemContainer + '<div id="divClear_'+fieldId+'" class="clear"></div>');
      
      if($('Remove'+itemId)) $('Remove'+itemId).writeAttribute('id', iconRemoveId);
           
      // insert file id to hidden field - only 1 insert is possible
      var addToField = '{"moduleId":'+moduleId+',"rootLevelId":'+rootLevelId+',"relationId":'+itemId;
      if(linkId != null && linkId != itemId){
        addToField += ',"linkId":'+linkId+'}';
      }else{
        addToField += '}';
      }
      
      if(addToField.isJSON()){
        if($(fieldId).value.indexOf(addToField) == -1){
          if($F(fieldId) == ''){
            $(fieldId).setValue('[' + addToField + ']');
          }else if($(fieldId).value.indexOf('}]') != -1){
            $(fieldId).setValue($F(fieldId).replace('}]', '},'+addToField+']'));
          }
        }
      }
                  
      $(fieldId+'_item'+itemId).appear({duration: 0.5});
      $('olItem'+itemId).fade({duration: 0.5});
      
      // add remove method to remove icon
      if($(iconRemoveId)){
        $(iconRemoveId).show();
        $(iconRemoveId).onclick = function(){
          myDashboard.removeRelationItem(fieldId, fieldId+'_item'+itemId, itemId, linkId);
        }
      }
    }
  },
  
  /**
   * removeRelationItem
   */
  removeRelationItem: function(fieldId, elementId, id, linkId){
    if($(fieldId) && $(elementId)){     
      var moduleId = $(elementId).readAttribute('moduleid');
      var rootLevelId = $(elementId).readAttribute('rootlevelid');
      var itemId = $(elementId).readAttribute('relationid');
      
      var removeField = '{"moduleId":'+moduleId+',"rootLevelId":'+rootLevelId+',"relationId":'+itemId; 
      if(linkId != null){
        removeField += ',"linkId":'+linkId+'}';
      }else{
        removeField += '}';
      }
      
      if($(fieldId).value.indexOf(removeField) > -1){
        if($(fieldId).value == '['+removeField+']'){
          $(fieldId).value = $(fieldId).value.replace('['+removeField+']', '');
        }else if($(fieldId).value.indexOf(','+removeField) > -1){
          $(fieldId).value = $(fieldId).value.replace(','+removeField, '');
        }else if($(fieldId).value.indexOf(removeField+',') > -1){
          $(fieldId).value = $(fieldId).value.replace(removeField+',', '');
        }
        
        // delete element out of field area (media, doc)
        $(elementId).fade({duration: 0.5});
        setTimeout('$(\''+elementId+'\').remove()', 500);
                
        // display deleted element in overlay (media, doc)
        if($('olItem'+id)) $('olItem'+id).appear({duration: 0.5});
      }    
    }    
  },
  
  /**
   * getModule
   */
  getModule: function(moduleId){
    if($('olModules')){      
      this.olCurrContainerId = 'olModules';
      this.olNewContainerId = 'olRootLevels';
      this.createContainer();
      
      myCore.addBusyClass(this.olNewContainerId);
      this.moveContainers(this.olCurrContainerId, this.olNewContainerId);
      
      if($('olModuleId')) $('olModuleId').setValue(moduleId);
      new Ajax.Updater(this.olNewContainerId, '/zoolu/core/dashboard/overlay-rootlevels', { 
        parameters: { moduleId: moduleId },
        evalScripts: true,
        onComplete: function(){
          if($('olBack')) $('olBack').show();
          if($(this.olNewContainerId+'_title')){
            if($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId+'_title').innerHTML);
          }          
          myCore.removeBusyClass(this.olNewContainerId);
          
          $(this.olCurrContainerId).removeClassName('active');
          $(this.olNewContainerId).addClassName('active');
          
          this.olCurrContainerId = this.olNewContainerId;
        }.bind(this) 
      });
    }
  },
  
  /**
   * getRootLevel
   */
  getRootLevel: function(rootLevelId, rootLevelTypeId, rootLevelGroupId, rootLevelLanguageId){
    if($(this.olCurrContainerId)){   
      if(typeof(rootLevelTypeId) == 'undefined') rootLevelTypeId = '';
      if(typeof(rootLevelGroupId) == 'undefined') rootLevelGroupId = '';
      if(typeof(rootLevelLanguageId) == 'undefined') rootLevelLanguageId = '';
      
      this.olNewContainerId = 'olContentItems'; 
      this.createContainer();
      
      myCore.addBusyClass(this.olNewContainerId);
      this.moveContainers(this.olCurrContainerId, this.olNewContainerId);
      this.toggleContainerStatus('active');
      
      if($('olRootLevelId')) $('olRootLevelId').setValue(rootLevelId);
      new Ajax.Updater(this.olNewContainerId, '/zoolu/core/dashboard/overlay-content', { 
        parameters: { 
          rootLevelId: rootLevelId,
          rootLevelTypeId: rootLevelTypeId,
          rootLevelGroupId: rootLevelGroupId,
          rootLevelLanguageId: rootLevelLanguageId,
          moduleId: $F('olModuleId')
        },
        evalScripts: true,
        onComplete: function(){
          if($('olBack')) $('olBack').show();
          if($(this.olNewContainerId+'_title')){
            if($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId+'_title').innerHTML);
          }          
          myCore.removeBusyClass(this.olNewContainerId);
          
          $(this.olCurrContainerId).removeClassName('active');
          $(this.olNewContainerId).addClassName('active');
          
          if(rootLevelLanguageId != ''){
            if($(this.languageField)) $(this.languageField).setValue(rootLevelLanguageId);
          }
          
          this.olCurrContainerId = this.olNewContainerId;
        }.bind(this) 
      });
    }
  },
  
  /**
   * getNavItem
   * @param integer folderId, integer viewtype
   */
  getNavItem: function(folderId, rootLevelTypeId, rootLevelGroupId, viewtype, contenttype){
    this.resetNavItems();
    
    $('olnavitemtitle'+folderId).addClassName('selected');
    
    if($('olsubnav'+folderId)){
      this.toggleSubNavItem(folderId);      
      // if mediaFilter is active
      /*if($('mediaFilter_Folders')){
          $('mediaFilter_Folders').value = folderId;
          this.loadFileFilterContent(viewtype, contenttype);
      }else{*/
      if(typeof(contenttype) != 'undefined'){
        this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
      }
      //}
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
        
        new Ajax.Updater('olsubnav'+folderId, '/zoolu/core/dashboard/overlay-childnavigation', {
          parameters: { 
            folderId: folderId, 
            viewtype: viewtype,
            languageId: languageId,
            contenttype: contenttype,
            rootLevelTypeId: rootLevelTypeId,
            rootLevelGroupId: rootLevelGroupId
          },      
          evalScripts: true,     
          onComplete: function() {
            // if mediaFilter is active
            /*if($('mediaFilter_Folders')){
              $('mediaFilter_Folders').value = folderId;
              this.loadFileFilterContent(viewtype, contenttype);
            }else{*/
            if(typeof(contenttype) != 'undefined'){
              this.getFolderContent(folderId, rootLevelTypeId, rootLevelGroupId, contenttype);
            }
            //}
            myCore.removeBusyClass('olsubnav'+folderId);
          }.bind(this)
        });
      } 
    }
  },
  
  /**
   * getFolderContent
   */
  getFolderContent: function(folderId, rootLevelTypeId, rootLevelGroupId, contenttype){
    $(this.folderUpdateContainer).innerHTML = '';
    myCore.addBusyClass(this.folderUpdateContainer);
    
    var languageId = null;
    if($('languageId')){
      languageId = $F('languageId');
    }
    
    var fieldname = 'dbrd-'+this.areaId.substring(this.areaId.indexOf('_')+1);
    new Ajax.Updater(this.folderUpdateContainer, '/zoolu/core/dashboard/overlay-list', {
      parameters: {
        folderId: folderId,
        relation: $(fieldname).value,
        languageId: languageId,
        contenttype: contenttype,
        rootLevelTypeId: rootLevelTypeId,
        rootLevelGroupId: rootLevelGroupId
      },
      evalScripts: true,
      onComplete: function(){
        myCore.removeBusyClass(this.folderUpdateContainer);
      }.bind(this)
    });
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
   * stepBack 
   */
  stepBack: function(){
    $$('#olContent .active').each(function(element){
      if($(element.id)){
        this.olCurrContainerId = element.id;
        var prevElement = $(element.id).previous(); 
        this.olNewContainerId = prevElement.id;
        
        this.moveContainers(this.olCurrContainerId, this.olNewContainerId, 'PREV');        
        this.toggleContainerStatus('active');
        
        if($(this.olNewContainerId+'_title')){
          if($('dbrdOverlayTitle')) $('dbrdOverlayTitle').update($(this.olNewContainerId+'_title').innerHTML);
        }
        
        if($(prevElement.id).previous() == null){
          if($('olBack')) $('olBack').hide();
        }
        
        this.olCurrContainerId = this.olNewContainerId; 
      }
    }.bind(this));
  },
  
  /**
   * toggleContainerStatus 
   */
  toggleContainerStatus: function(cssClassName){
    if($(this.olCurrContainerId)) $(this.olCurrContainerId).removeClassName(cssClassName);
    if($(this.olNewContainerId)) $(this.olNewContainerId).addClassName(cssClassName);
  },
  
  /**
   * createContainer 
   */
  createContainer: function(){
    if(!$(this.olNewContainerId)){
      $(this.olCurrContainerId).insert({ 
        after: '<div id="'+this.olNewContainerId+'" style="left: '+this.offsetX+'px;"></div>' 
      });
    }else{
      $(this.olNewContainerId).update('');
    }
  },
  
  /**
   * moveContainers 
   */
  moveContainers: function(currContainer, newContainer, direction){
    if(typeof(direction) == 'undefined') direction = 'NEXT';
    
    var offsetX = this.offsetX;
    if(direction == 'NEXT'){
      offsetX = -430; 
    }
    if($(currContainer)) new Effect.Move(currContainer, { x: offsetX, y: 0, mode: 'absolute', duration: 0.3, transition: Effect.Transitions.linear });
    if($(newContainer)) new Effect.Move(newContainer, { x: 0, y: 0, mode: 'absolute', duration: 0.3, transition: Effect.Transitions.linear });
  },
  
  /**
   * resetForm  
   */
  resetForm: function(){
    if($(this.formId)){
      // clear input fields
      if($('dbrd-title')) $('dbrd-title').setValue('');
      if($('dbrd-users')) $('dbrd-users').setValue('');
      if($('dbrd-links')) $('dbrd-links').setValue('');
      // clear textarea
      if($('dbrd-description')) $('dbrd-description').setValue('');;
      // clear checkboxes (categories)
      $$('.fieldoverflowcontainer input[type=checkbox]').each(function(element){
        if(element) element.checked = 0;
      });
      // clear contacts
      if($('divContactContainer_users')) $('divContactContainer_users').update('');
      // clear content box
      if($('divLinksContainer_links')) $('divLinksContainer_links').update('');
    }
  },
  
  /**
   * validateInput
   */
  validateInput: function(element, baseValue) {
    if(($(element) && $F(element).blank()) || $F(element) == baseValue){
      if($('lbl_'+element)) $('lbl_'+element).addClassName('missing');
      if($(element)) $(element).addClassName('missing');
      this.retValue = false;
    }else{
      if($('lbl_'+element)) $('lbl_'+element).removeClassName('missing');
      if($(element)) $(element).removeClassName('missing');
    }
  },
  
  /**
   * scrollLaoder
   */
  scrollLaoder: function(elementId){
    if($(elementId) && this.blnNoEntries == false){
      var divScrollOffset = $(elementId).cumulativeScrollOffset();      
      if((divScrollOffset.top + $(elementId).getHeight() + 40) >= $(elementId).scrollHeight){
        this.loadDashboard(this.activeTab, true);        
      }
    }
  },
  
  /*************************************************
   * DASHBOARD METHODS for cms, globals, ...
   *************************************************/
  
  /**
   * getSendToDashboard
   */
  getSendToDashboard: function(){
    if($('overlayBlack75')) $('overlayBlack75').show();
    $('overlaySendToDashboardContent').innerHTML = '';
    if($('overlaySingleEdit')) $('overlaySingleEdit').setStyle({zIndex: '802'});
    
    this.mode = 'OVERLAY';
    
    myCore.putCenter('overlaySendToDashbaordWrapper');
    $('overlaySendToDashbaordWrapper').show(); 
    myOverlay.overlayCounter++;
  
    new Ajax.Updater('overlaySendToDashboardContent', '/zoolu/core/dashboard/form', { 
      parameters: { mode: this.mode },
      evalScripts: false,
      onComplete: function(transport){
        //problem: ajax.updater evalScripts = true was too late
        transport.responseText.evalScripts();
         
        // insert current element to links field
        this.addElementToField('dbrd-links');
        
        // center wrapper and remove busy class
        myCore.putOverlayCenter('overlaySendToDashbaordWrapper');
        myCore.removeBusyClass('overlaySendToDashboardContent');
      }.bind(this) 
    });
  },
  
  /**
   * addElementToField
   */
  addElementToField: function(fieldId){
    var rootLevelId;
    var itemId = null;
    
    if($('rootLevelId')){
      rootLevelId = $F('rootLevelId');
    }else if(typeof(myNavigation.rootLevelId) != 'undefined'){
      rootLevelId = myNavigation.rootLevelId;
    } 

    if($('EditFileIds') && ($('EditIsSingleEdit') && $F('EditIsSingleEdit') == 'true')){
      itemId = $F('EditFileIds');
    }else if($('id')){
      itemId = $F('id');
    }
    
    if(itemId != null){
      var addToField = '{"moduleId":'+myNavigation.module+',"rootLevelId":'+rootLevelId+',"relationId":'+itemId;
      if($('linkId') && $F('linkId') != '' && $F('linkId') != itemId){
        addToField += ',"linkId":'+$F('linkId')+'}';
      }else{
        addToField += '}';
      }
      
      console.log(addToField);
      
      if(addToField.isJSON()){
        if($(fieldId).value.indexOf(addToField) == -1){
          if($F(fieldId) == ''){
            $(fieldId).setValue('[' + addToField + ']');
          }else if($(fieldId).value.indexOf('}]') != -1){
            $(fieldId).setValue($F(fieldId).replace('}]', '},'+addToField+']'));
          }
        }
      }
    }
  },
  
  /**
   * saveDashboardEntry
   */
  saveDashboardEntry: function(){
    if($(this.formId)){
      
      /**
       * validation
       */
      this.retValue = true;
      $$('#overlaySendToDashboardContent .mandatory').each(function(element){
        this.validateInput(element.id);
      }.bind(this));
      
      /**
       * serialize generic form
       */
      var serializedForm = $(this.formId).serialize();
      
      if(this.retValue){
        myCore.addBusyClass('overlaySendToDashboardContent');
        if($('editbox')) $('editbox').hide('');
        new Ajax.Request($(this.formId).readAttribute('action'), {
          parameters: serializedForm,
          evalScripts: false,
          onComplete: function(transport) {
            //problem: ajax.updater evalScripts = true was too late
            transport.responseText.evalScripts();
            
            myCore.removeBusyClass('overlaySendToDashboardContent');            
            // close overlays
            if($('overlaySingleEdit')) myOverlay.close('overlaySingleEdit');            
            this.closeSendToDashboardOverlay();
          }.bind(this)
        });
      }
    }
  },
  
  /**
   * closeSendToDashboardOverlay
   */
  closeSendToDashboardOverlay: function(){
    if($('overlaySendToDashbaordWrapper')) $('overlaySendToDashbaordWrapper').hide();
    if(myOverlay.overlayCounter > 1 && id != undefined) {
      myOverlay.overlayCounter--;
    }else{
      if($('overlayBlack75')) $('overlayBlack75').hide();
    }
  }
});