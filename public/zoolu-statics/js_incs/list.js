/**
 * list.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-07: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart.List = Class.create({
  
  initialize: function() {
    this.ItemsPerPage = 20;
    this.page = 1;
    
    this.sortColumn = '';
    this.sortOrder = '';
    this.searchValue = '';
    
    this.hardbounced = false;
    
    if($('search')){
      $('search').observe('keypress', function(event){
        if(event.keyCode == Event.KEY_RETURN) {
          this.search();
        }
      }.bind(this));  
    }
  },
  
  /**
   * getListPage
   */
  getListPage: function(page, rootLevelFilter, hardbounced){ //FIXME hardbounce variable only as quick & dirty solution for subscriber hard bounce list!
    if(myNavigation){      
      
      if(typeof(page) != 'undefined' && page > 0){ 
        this.page = page;
      }
      
      if(typeof(hardbounced) == 'undefined'){
        hardbounced = false;
      }
      this.hardbounced = hardbounced;
      
      // wait
      setTimeout(function(){ return true; }, 10);
      
      var ajaxAction = myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/list';
      
      new Ajax.Updater(myNavigation.genListContainer, ajaxAction, {
        parameters: { 
      	  rootLevelId: myNavigation.rootLevelId,
      	  folderId: myNavigation.currItemId,
      	  page: this.page, 
      	  itemsPerPage: this.ItemsPerPage,
      	  order: this.sortColumn,
      	  sort: this.sortOrder,
      	  search: this.searchValue,
      	  currLevel: myNavigation.currLevel,
      	  rootLevelFilter: rootLevelFilter,
      	  hardbounced: this.hardbounced
      	},      
        evalScripts: true,     
        onComplete: function(transport) {
      	  if($(myNavigation.genFormContainer)) $(myNavigation.genFormContainer).hide();
          if($(myNavigation.genFormFunctions)) $(myNavigation.genFormFunctions).hide();
          if($(myNavigation.genFormSaveContainer)) $(myNavigation.genFormSaveContainer).hide();
          if($(myNavigation.genListContainer)) $(myNavigation.genListContainer).show();
          if($(myNavigation.genListFunctions)) $(myNavigation.genListFunctions).show();
    	    myCore.initSelectAll();
    	    myCore.initListHover();
        }.bind(this)
      });
    }
  },
  
  /**
   * getListPage
   */
  getListDynFormEntries: function(id, page, url){
    if(myNavigation){      
      
      if(typeof(page) != 'undefined' && page > 0){ 
        this.page = page;
      }
      
      // wait
      setTimeout(function(){ return true; }, 10);
      
      new Ajax.Updater(id, url, {
        parameters: { 
          id: $F('id'),
          page: this.page,
          itemsPerPage: this.ItemsPerPage
        },      
        evalScripts: true,     
        onComplete: function(transport) {
          
        }.bind(this)
      });
    }
  },
  
  /**
   * backFilter
   */
  backFilter: function(){
    var rootLevelFilterId = null;
    if($('rootLevelFilterId')){
      rootLevelFilterId = $('rootLevelFilterId').value;
    }
    this.getListPage(0, rootLevelFilterId);
  },
  
  /**
   * backReset
   */
  backReset: function(){
    this.resetSearch();
  },
  
  /**
   * sort
   */
  sort: function(sortColumn, sortOrder){
    var rootLevelFilterId = null;
    if(typeof(sortColumn != 'undefined')) this.sortColumn = sortColumn;
    if(typeof(sortOrder != 'undefined')) this.sortOrder = sortOrder;
    if($('rootLevelFilterListId'))  rootLevelFilterId = $('rootLevelFilterListId').getValue();
    this.getListPage(0, rootLevelFilterId);
  },
  
  /**
   * search
   */
  search: function(rootLevelFilterId){
    var rootLevelFilterId = null;
    if($('search')){
      if($F('search') != ''){
        if($('rootLevelFilterListId'))  rootLevelFilterId = $('rootLevelFilterListId').getValue();
        this.searchValue = $F('search');
        this.getListPage(0, rootLevelFilterId);
      }
    }
  },
  
  /**
   * resetSearch
   */
  resetSearch: function(){
    var rootLevelFilterId = null;
    if($('search')) $('search').value = '';
    this.searchValue = '';
    if($('rootLevelFilterListId')) rootLevelFilterId = $('rootLevelFilterListId').getValue();
    this.getListPage(0, rootLevelFilterId);
  },
  
  /**
   * deleteListItem
   */
  deleteListItem: function(){
    var arrEntries = [];
    var strEntries = '';
    var index = 0;
  	$$('#listEntries input').each(function(e){ 
      if(e.type == 'checkbox'){
        if(e.checked){
          arrEntries[index] = e.value;
          strEntries += '[' + e.value + ']';
          index++;
        }
      }      
  	});    
  	if(arrEntries.size() > 0){
  	  if (typeof(myCore.deleteAlertSingleMessage) == 'undefined' || myCore.deleteAlertSingleMessage == '' &&
          typeof(myCore.deleteAlertMultiMessage) == 'undefined' || myCore.deleteAlertMultiMessage == '' ) {
        myCore.deleteAlertSingleMessage = myCore.translate['Delete_'];
        myCore.deleteAlertMultiMessage = myCore.translate['Delete_'];
      }
      myCore.showDeleteAlertMessage(arrEntries.size());
      $('buttonOk').observe('click', function(event){
        new Ajax.Updater(myNavigation.genListContainer, myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/listdelete', {
      	  parameters: { 
      	    values: strEntries,
      	    rootLevelId: myNavigation.rootLevelId,
            folderId: myNavigation.currItemId,
            page: this.page, 
            itemsPerPage: this.ItemsPerPage,
            order: this.sortColumn,
            sort: this.sortOrder,
            search: this.searchValue
          },      
      	  evalScripts: true,     
      	  onComplete: function() {
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
   * unsubscribeListItem
   */
  unsubscribeListItem: function(){
    var arrEntries = [];
    var strEntries = '';
    var index = 0;
    $$('#listEntries input').each(function(e){ 
      if(e.type == 'checkbox'){
        if(e.checked){
          arrEntries[index] = e.value;
          strEntries += '[' + e.value + ']';
          index++;
        }
      }      
    });    
    if(arrEntries.size() > 0){
      myCore.deleteAlertSingleMessage = myCore.translate['Unsubscribe_subscriber'];
      myCore.deleteAlertMultiMessage = myCore.translate['Unsubscribe_subscriber'];
      myCore.showDeleteAlertMessage(arrEntries.size());
      $('buttonOk').observe('click', function(event){
        new Ajax.Updater(myNavigation.genListContainer, myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/listunsubscribe', {
          parameters: { 
            values: strEntries,
            rootLevelId: myNavigation.rootLevelId,
            folderId: myNavigation.currItemId,
            page: this.page, 
            itemsPerPage: this.ItemsPerPage,
            order: this.sortColumn,
            sort: this.sortOrder,
            search: this.searchValue
          },      
          evalScripts: true,     
          onComplete: function() {
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
   * toggleEditMenu
   */
  toggleEditMenu: function(elementId, menuId){
    if(typeof(menuId) == 'undefined'){
      menuId = 'buttonEditMenu';  //Backwards compatibility
    }
    if($(menuId)){
      Effect.toggle(menuId, 'appear', { delay: 0, duration: 0.3 });
      if($(elementId) && $(elementId).hasClassName('white')){
        $(elementId).removeClassName('white');
      }else{
        $(elementId).addClassName('white');
      }
    }    
  },
  
  /**
   * exportList
   */
  exportList: function(){
    var rootLevelId = null;
    var rootLevelFilterId = null;
    if($('rootLevelId')) rootLevelId = $('rootLevelId').getValue();
    if($('rootLevelFilterListId')) rootLevelFilterId = $F('rootLevelFilterListId');
    
    var url = myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/exportlist?rootLevelId='+rootLevelId;
    if(rootLevelFilterId = '') url = url + '&rootLevelFilterId='+rootLevelFilterId;
    else if(this.hardbounced) url = url + '&hardbounced=true';
    location.href = url;
  },
  
  /**
   * importListUpload
   */
  importListUpload: function(){
    if($('overlayBlack75')) $('overlayBlack75').show();
    if($('overlayGenContentWrapper')){
      myCore.putCenter('overlayGenContentWrapper');
      $('overlayGenContentWrapper').show();
      
      if($('overlayGenContent')){
        myCore.addBusyClass('overlayGenContent');
        new Ajax.Updater('overlayGenContent', myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/importupload', {
          parameters: { 
            rootLevelId: $('rootLevelId').getValue()
          },
          evalScripts: true,
          onComplete: function() {
            $('buttonOk').observe('click', function(){
              $('importForm').submit();
            });
            $('buttonCancel').observe('click', function(){
              myOverlay.close();
            });
            $('overlayButtons').hide();
            myCore.putCenter('overlayGenContentWrapper');
            myCore.removeBusyClass('overlayGenContent');          
          }.bind(this)
        });
      }
    }
  },
  
  /**
   * importListOverlay
   */
  importListOverlay: function(rootLevelId, fileId){
    if($('overlayBlack75')) $('overlayBlack75').show();
    if($('overlayImportWrapper')){
      myCore.putCenter('overlayImportWrapper');
      myCore.calcMaxOverlayHeight('overlayImportContent', true);
      $('overlayImportWrapper').show();
      
      if($('overlayImportContent')){
        myCore.addBusyClass('overlayImportContent');
        new Ajax.Updater('overlayImportContent', myNavigation.constBasePath + '/subscriber/importform', { //FIXME: subscribers should not be hardcoded!
          parameters: { 
            rootLevelId: rootLevelId,
            fileId: fileId
          },
          evalScripts: true,
          onComplete: function() {
            myCore.putCenter('overlayImportWrapper');
            myCore.removeBusyClass('overlayImportContent');          
          }.bind(this)
        });
      }
    }
  },
  
  importList: function(){
    $('buttonimportpreview').hide();
    $('buttonimportsave').hide();
    myCore.addBusyClass('importloader');    
    $('importloader').show();
    myCore.addBusyClass('buttonimportsave');
    new Ajax.Request(myNavigation.constBasePath + '/subscriber/import', { //FIXME: subscribers should not be hardcoded!
      parameters: $('importForm').serialize(),
      onComplete: function(transport){
        myCore.removeBusyClass('importloader');
        myCore.showAlertMessage(transport.responseText);
        $('buttonOk').observe('click', function(){
          myOverlay.close('overlayImportWrapper');
        });
        $('buttonCancel').hide();
      }
    });
  },
  
  previewImport: function(){
    $('overlayGenContent').innerHTML = '';
    myCore.putCenter('overlayGenContentWrapper');
    $('overlayGenContentWrapper').show();
    myCore.addBusyClass('overlayGenContent');
    new Ajax.Updater('overlayGenContent', myNavigation.constBasePath + '/subscriber/previewimport', { //FIXME: subscribers should not be hardcoded
      parameters: {
        fileId: $F('fileId'),
        encoding: $F('encoding'),
        importHeader: $F('import_header')
      },
      onComplete: function(){
        myCore.removeBusyClass('overlayGenContent');
        myCore.calcMaxOverlayHeight('overlayGenContent', true, true);
        myCore.calcMaxOverlayWidth('overlayGenContentWrapper', true);
        myCore.calcMaxOverlayHeight('importPreview', true, true);
        myCore.calcMaxOverlayWidth('importPreview', true, 30);
        myOverlay.overlayCounter++;
        myCore.putOverlayCenter('overlayGenContentWrapper');
      }
    })
  }
});