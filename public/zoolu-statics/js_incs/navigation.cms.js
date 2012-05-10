/**
 * navigation.cms.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-03-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Cms = Class.create(Massiveart.Navigation, {

  initialize: function($super) {
    // initialize superclass
    $super();
    this.constBasePath = '/zoolu/core';
    this.rootLevelType = 'folder';
  },
  
  /**
   * initModuleCMS
   */
  initModuleCMS: function(rootLevelId){
    if(typeof(rootLevelId) != 'undefined' && rootLevelId != ''){
      if($('portal'+rootLevelId)) $('portal'+rootLevelId).onclick();
    }else{
      var blnFirst = true;
      $$('#divNaviLeftMain div.portal').each(function(elDiv){
  	    if($(elDiv.id) && blnFirst){
  	      $(elDiv.id).onclick();
  	      blnFirst = false;
  	    }
  	  }.bind(this));
    }
  },
  
  /**
   * loadDashboard
   */
  loadDashboard: function(){
    $(this.genFormContainer).show();
    $(this.genTableListContainer).hide();
    myCore.addBusyClass(this.genFormContainer);
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, '/zoolu/cms/page/dashboard', {
      parameters: { 
        rootLevelId: this.rootLevelId
      },      
      evalScripts: true,     
      onComplete: function() {
        myCore.removeBusyClass(this.genFormContainer);
        myCore.initListHover(false);
      }.bind(this)
    });
  },
  
  /**
   * loadMaintenanceOverlay
   */
  loadMaintenanceOverlay: function(rootLevelId){
    if($('overlayBlack75')) $('overlayBlack75').show();    
    if('overlayMaintenanceWrapper'){
      myCore.putCenter('overlayMaintenanceWrapper');
      $('overlayMaintenanceWrapper').show();
      
      this.rootLevelId = rootLevelId;      
      if($('overlayMaintenanceContent')){
        myCore.addBusyClass('overlayMaintenanceContent');
        new Ajax.Updater('overlayMaintenanceContent', '/zoolu/cms/overlay/maintenance', {
          parameters: { 
            rootLevelId: this.rootLevelId,
            operation: 'load'
          },      
          evalScripts: true,     
          onComplete: function() {
            myCore.removeBusyClass('overlayMaintenanceContent');          
          }.bind(this)
        });  
      }
    }
  },
  
  /**
   * getEdit
   */
  getEdit: function(itemId, parentId){
    if(parentId == 'undefined') parentId = null;
    if(itemId != 'undefined'){
      
      this.intTreeItemId = itemId;
      this.strTreeItemType = 'page';
      
      new Ajax.Request('/zoolu/cms/navigation/parent-folders', {
        parameters: { 
          id: itemId, 
          parentId: parentId 
        },      
        evalScripts: true,     
        onComplete: function(transport) {
          var response = transport.responseText.evalJSON();  
          if(typeof(response.folders) != 'undefined' && response.folders.length > 0){
            // load all folders
            this.arrTreeToLoad = response.folders;
            if($('divNavigationTitle_folder'+this.arrTreeToLoad.first())) $('divNavigationTitle_folder'+this.arrTreeToLoad.first()).onclick();
          }else{
            this.arrTreeToLoad = [];
            if($('divNavigationTitle_folder'+parentId)) $('divNavigationTitle_folder'+parentId).onclick();
          }
        }.bind(this)
      });
    }
  }, 
  
  /**
   * selectSubscribers
   */
  selectLandingPages: function(rootLevelId, rootLevelGroupId, url, viewType, rootLevelType){
    if(!this.actionMenu || this.actionMenu.isOpen == false) {
        
      if(typeof(viewType) == 'undefined'){
        viewType = 'list';
      }
      
      if(typeof(rootLevelFilter) == 'undefined'){
        rootLevelFilter = null;
      }
      
      if(typeof(url) != 'undefined' && url != '' && (!location.href.replace(/#/,'').endsWith(url) || viewType != 'list')){
        location.href = url;
      }else{
        this.rootLevelId = rootLevelId;
        this.rootLevelGroupId = rootLevelGroupId;
        this.rootLevelType = rootLevelType;
        
        $(this.genFormContainer).hide();
        $(this.genFormFunctions).hide();
        
        if($('naviitem'+rootLevelId)){
          this.makeSelected('naviitem'+rootLevelId);
          if($(this.preSelectedNaviItem) && ('naviitem'+rootLevelId) != this.preSelectedNaviItem){ 
            this.makeDeselected(this.preSelectedNaviItem);
            this.makeDeselected(this.preSelectedSubNaviItem);
          }      
          this.preSelectedNaviItem = 'naviitem'+rootLevelId;
        }else if($('subnaviitem'+rootLevelId)){
          this.makeSelected('subnaviitem'+rootLevelId);
          if($(this.preSelectedSubNaviItem) && ('subnaviitem'+rootLevelId) != this.preSelectedSubNaviItem){ 
            this.makeDeselected(this.preSelectedSubNaviItem);
          }
          this.preSelectedSubNaviItem = 'subnaviitem'+rootLevelId;
        }
        
        myList.sortColumn = '';
        myList.sortOrder = '';
        myList.getListPage();
      }
    }
  },
  
  /**
   * getAddForm
   */
  getAddForm: function(){
    
    $(this.genListContainer).hide();
    $(this.genListFunctions).hide();
    
    if($('buttondelete')) $('buttondelete').hide();
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/addform', {
      parameters: { rootLevelId: this.rootLevelId },      
      evalScripts: true,     
      onComplete: function() {        
        $(this.genFormContainer).show();
        $(this.genFormFunctions).show();
        $(this.genFormContainer).scrollTo($('widgetfunctions'));        
      }.bind(this)
    });
  }
});