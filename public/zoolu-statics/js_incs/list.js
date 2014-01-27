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

    initialize: function () {
        this.ItemsPerPage = 20;
        this.page = 1;

        this.sortColumn = '';
        this.sortOrder = '';
        this.searchValue = '';

        if ($('search')) {
            $('search').observe('keypress', function (event) {
                if (event.keyCode == Event.KEY_RETURN) {
                    this.search();
                }
            }.bind(this));
        }
    },

    /**
     * getListPage
     */
    getListPage: function(page, rootLevelFilter, bounced){
    if(myNavigation){
      
      if(typeof(page) != 'undefined' && page > 0){ 
        this.page = page;
      }
      
      if(typeof(bounced) == 'undefined') {
        bounced = '';
      }
      this.bounced = bounced;
      
      var languageId = null;
      var rootLevelId = myNavigation.rootLevelId;
      if($('rootLevelLanguageId'+rootLevelId) && $F('rootLevelLanguageId'+rootLevelId) != ''){
    	  languageId = $F('rootLevelLanguageId'+rootLevelId);
      }
      
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
      	  bounced: this.bounced,
      	  languageId: languageId
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
//    	    myCore.removeBusyClass(myNavigation.genListContainer);
        }.bind(this)
      });
    }
  },

    /**
     * backFilter
     */
    backFilter: function () {
        this.getListPage();
    },

    /**
     * backReset
     */
    backReset: function () {
        this.resetSearch();
    },

    /**
     * sort
     */
    sort: function (sortColumn, sortOrder) {
        if (typeof(sortColumn != 'undefined')) this.sortColumn = sortColumn;
        if (typeof(sortOrder != 'undefined')) this.sortOrder = sortOrder;
        this.getListPage();
    },

    /**
     * search
     */
    search: function () {
        if ($('search')) {
            if ($F('search') != '') {
                this.searchValue = $F('search');
                this.getListPage();
            }
        }
    },

    /**
     * resetSearch
     */
    resetSearch: function () {
        if ($('search')) $('search').value = '';
        this.searchValue = '';
        this.getListPage();
    },

    /**
     * deleteListItem
     */
    deleteListItem: function () {
        var arrEntries = [];
        var strEntries = '';
        var index = 0;
        $$('#listEntries input').each(function (e) {
            if (e.type == 'checkbox') {
                if (e.checked) {
                    arrEntries[index] = e.value;
                    strEntries += '[' + e.value + ']';
                    index++;
                }
            }
        });
        if (arrEntries.size() > 0) {
            myCore.showDeleteAlertMessage(arrEntries.size());
            $('buttonOk').observe('click', function (event) {
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
                    onComplete: function () {
                        myCore.hideDeleteAlertMessage();
                    }.bind(this)
                });
            }.bind(this));
            $('buttonCancel').observe('click', function (event) {
                myCore.hideDeleteAlertMessage();
            }.bind(this));
        }
    },

    /**
     * toggleEditMenu
     */
    toggleEditMenu: function (elementId) {
        if ($('buttonEditMenu')) {
            Effect.toggle('buttonEditMenu', 'appear', { delay: 0, duration: 0.3 });
            if ($(elementId) && $(elementId).hasClassName('white')) {
                $(elementId).removeClassName('white');
            } else {
                $(elementId).addClassName('white');
            }
        }
    },

    /**
     * toggle
     */
    toggle: function (id, elementId) {
        if ($(id)) {
            Effect.toggle(id, 'appear', { delay: 0, duration: 0.3 });
            if ($(elementId) && $(elementId).hasClassName('white')) {
                $(elementId).removeClassName('white');
            } else {
                $(elementId).addClassName('white');
            }
        }
    },

    /**
     * importListUpload
     */
    importListUpload: function() {
        if ($('overlayBlack75'))
            $('overlayBlack75').show();
        if ($('overlayGenContentWrapper')) {
            myCore.putCenter('overlayGenContentWrapper');
            $('overlayGenContentWrapper').show();

            if ($('overlayGenContent')) {
                myCore.addBusyClass('overlayGenContent');
                new Ajax.Updater('overlayGenContent', myNavigation.constBasePath + '/' + myNavigation.rootLevelType + '/importupload', {
                    parameters: {
                        rootLevelId: $('rootLevelId').getValue()
                    },
                    evalScripts: true,
                    onComplete: function() {
                        $('buttonOk').observe('click', function() {
                            $('importForm').submit();
                        });
                        $('buttonCancel').observe('click', function() {
                            myOverlay.close();
                        });
                        $('overlayButtons').show();
                        myCore.putCenter('overlayGenContentWrapper');
                        myCore.removeBusyClass('overlayGenContent');
                    }.bind(this)
                });
            }
        }
    }
  
});