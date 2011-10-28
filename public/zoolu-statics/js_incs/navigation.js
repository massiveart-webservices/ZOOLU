/**
 * navigation.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation = Class.create({

  initialize: function() {
    this.module;
    
    this.genFormContainer = 'genFormContainer';
    this.genFormSaveContainer = 'genFormSaveContainer';
    this.genFormFunctions = 'genFormFunctions';
    this.genListContainer = 'genListContainer';
    this.genListFunctions = 'genListFunctions';
    this.genTableListContainer = 'genTableListContainer';
    this.genTmpContainer = 'genTmpContainer';
    
    this.constFolder = 'folder';
    this.constPage = 'page';
    this.constStartPage = 'startpage';
    this.constStartItem = 'start';
    this.constGlobal = 'global';
    
    this.constRequestRootNav = '/zoolu/cms/navigation/rootnavigation';
    this.constRequestChildNav = '/zoolu/cms/navigation/childnavigation';
    
    this.rootLevelId = 0;
    this.preSelectedPortal;
    this.rootLevelTypeId = 0;
    this.rootLevelGroupId = 0;

    this.preSelectedNaviItem;
    this.preSelectedSubNaviItem;
    
    this.preSelectedItem;
    this.preSelectedItemId = 0;
    this.currLevel = 0;
    this.parentFolderId = 0;
    
    this.levelArray = new Array();
    this.navigation = new Array();
    
    this.topNaviTitle = '';
    
    this.currItemId = 0;
    this.itemId = '';
    
    this.folderId;
    this.pageId;
    
    this.arrNavigationTreeIds = new Array();
    
    this.constListTimeOut = 1000;
    this.ListTimeOut;
    
    this.arrTreeToLoad = null;
    this.intTreeItemId = 0;
    this.strTreeItemType = ''; 
    
    this.blnLoadStartpage = false;   
  },
  
  /**
   * initPortalHover
   */
  initPortalHover: function(){
    $$('#divNaviLeftMain div.portal').each(function(elDiv){    
      
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);
        el.addClassName('hover');
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);
        el.removeClassName('hover');
      }.bind(this));
      
    }.bind(this));
  },
  
  /**
   * initFolderHover
   */
  initFolderHover: function(){
    $$('div.'+this.constFolder).each(function(elDiv){    
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);
        if(el.hasClassName(this.constFolder)){
          el.addClassName('hover');
        }else{         
          el.up('.'+this.constFolder).addClassName('hover');          
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName(this.constFolder)){
          el.removeClassName('hover');
        }else{         
          el.up('.'+this.constFolder).removeClassName('hover');        
        }        
      }.bind(this));
      
    }.bind(this));
  },
  
  /**
   * initPageHover
   */
  initPageHover: function(){
    // page hover
    $$('div.'+this.constPage).each(function(elDiv){    
      
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);
        if(el.hasClassName(this.constPage)){
          el.addClassName('hover');
        }else{         
          el.up('.'+this.constPage).addClassName('hover');          
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName(this.constPage)){
          el.removeClassName('hover');
        }else{         
          el.up('.'+this.constPage).removeClassName('hover');        
        }        
      }.bind(this));
      
    }.bind(this));
    
    // startpage hover
    $$('div.'+this.constStartPage).each(function(elDiv){    
      
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);
        if(el.hasClassName(this.constStartPage)){
          el.addClassName('hover');
        }else{         
          el.up('.'+this.constStartPage).addClassName('hover');          
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName(this.constStartPage)){
          el.removeClassName('hover');
        }else{         
          el.up('.'+this.constStartPage).removeClassName('hover');        
        }        
      }.bind(this));
    }.bind(this));
  },
  
  /**
   * initAddMenuHover
   */
  initAddMenuHover: function(){
    $$('.levelmenu').each(function(elDiv){    
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event)
        if(el.hasClassName('levelmenu')){
          el.addClassName('addmenuhover');
        }else{         
          el.up('.levelmenu').addClassName('addmenuhover');        
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event)        
        if(el.hasClassName('levelmenu')){
          el.removeClassName('addmenuhover');
        }else{         
          el.up('.levelmenu').removeClassName('addmenuhover');        
        }
      }.bind(this));
    }.bind(this));
  },
  
  /**
   * selectPortal
   * @param integer portalId
   */
  selectPortal: function(portalId, mainPortalId){
    this.currLevel = 1;
    
    this.hideCurrentFolder();
    
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();   
    
    if(typeof(mainPortalId) == 'undefined'){
      mainPortalId = portalId;
    } 
    
    this.makeSelected('portal'+mainPortalId);
    if($(this.preSelectedPortal) && ('portal'+mainPortalId) != this.preSelectedPortal){ 
      this.makeDeselected(this.preSelectedPortal);
    }  
            
    this.preSelectedPortal = 'portal'+mainPortalId;
    this.rootLevelId = portalId;
    
    if($('divNaviCenterInner')){
      $('divNaviCenterInner').innerHTML = '';
      this.levelArray = [];
      
      var levelContainer = '<div id="navlevel'+this.currLevel+'" rootlevelid="'+this.rootLevelId+'" parentid="" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
      new Insertion.Bottom('divNaviCenterInner', levelContainer);
      
      if(Prototype.Browser.IE){
        newNavHeight = $('divNaviCenter').getHeight();
        $$('.navlevel').each(function(elDiv){
          $(elDiv).setStyle({height: (newNavHeight-42) + 'px'});
        });
      }
      else if(Prototype.Browser.WebKit){
        newNavHeight = $('divNaviCenter').getHeight();
        $$('.navlevel').each(function(elDiv){
          $(elDiv).setStyle({height: (newNavHeight-40) + 'px'});
        });
      }          
          
      new Ajax.Updater('navlevel'+this.currLevel, this.constRequestRootNav, {
        parameters: { 
          rootLevelId: this.rootLevelId,
          rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
          rootLevelGroupId: this.rootLevelGroupId,
          rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
          currLevel: this.currLevel
        },      
        evalScripts: true,     
        onComplete: function() {
          myCore.removeBusyClass('navlevel'+this.currLevel);
          this.levelArray.push(this.currLevel);
          this.initFolderHover();
          this.initPageHover();
          this.initAddMenuHover();    
          //this.createSortableNavLevel(this.currLevel);
        }.bind(this)
      });
    }
  },
  
  /**
   * getRootLevelTreeStart 
   */
  getRootLevelTreeStart: function(){
    this.selectRootLevel(this.rootLevelId, this.rootLevelGroupId);
  },
  
  /**
   * selectRootLevel
   * @param integer rootLevelId
   */
  selectRootLevel: function(rootLevelId, rootLevelGroupId, url, makeRequest, viewType, rootLevelTypeId){
    if($('importList')) $('importList').hide();
    if($('exportList')) $('exportList').hide();
    
    if(typeof(viewType) == 'undefined'){
      viewType = 'tree';
    } 
    
    /**
     * select root level with layout change -> location href.
     */
    if(typeof(url) != 'undefined' && url != '' && (!location.href.endsWith(url) || viewType == 'list')){
      location.href = url;
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
      
      if(typeof(rootLevelTypeId) != 'undefined'){
        var myRootLevelTypeIdInput = document.createElement("input");
        myRootLevelTypeIdInput.setAttribute('name', 'rootLevelTypeId');
        myRootLevelTypeIdInput.setAttribute('value', rootLevelTypeId);
        myRootLevelTypeIdInput.setAttribute('type', 'hidden');
        myForm.appendChild(myRootLevelTypeIdInput);
      }
      
      document.body.appendChild(myForm);
      myForm.submit();
    }else{
      if(typeof(makeRequest) == 'undefined'){
        makeRequest = true;
      } 
      
      this.currLevel = 1;
      
      this.hideCurrentFolder();
      
      if($(this.genFormSaveContainer)) $(this.genFormContainer).hide();
      if($(this.genFormSaveContainer)) $(this.genFormSaveContainer).hide();    
      
      if($('naviitem'+rootLevelId)){
        this.makeSelected('naviitem'+rootLevelId);
        if($(this.preSelectedNaviItem) && ('naviitem'+rootLevelId) != this.preSelectedNaviItem){ 
          this.makeDeselected(this.preSelectedNaviItem);
          this.makeDeselected(this.preSelectedSubNaviItem);
        }      
        this.preSelectedNaviItem = 'naviitem'+rootLevelId;
      }else if($('subnaviitem'+rootLevelId)){
        this.makeSelected('subnaviitem'+rootLevelId);
        this.preSelectedSubNaviItem = 'subnaviitem'+rootLevelId;
      }
      
      this.rootLevelId = rootLevelId;
      this.rootLevelGroupId = rootLevelGroupId;
      if(typeof(rootLevelTypeId) != 'undefined'){
        this.rootLevelTypeId = rootLevelTypeId; 
      }
      
      if($('divNaviCenterInner')) $('divNaviCenterInner').innerHTML = '';
      this.levelArray = [];
      
      if(makeRequest == true && $('divNaviCenterInner')){
        var levelContainer = '<div id="navlevel'+this.currLevel+'" rootlevelid="'+this.rootLevelId+'" parentid="" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
        new Insertion.Bottom('divNaviCenterInner', levelContainer);
        
        if(Prototype.Browser.IE){
          newNavHeight = $('divNaviCenter').getHeight();
          $$('.navlevel').each(function(elDiv){
            if((newNavHeight-42) > 0) $(elDiv).setStyle({height: (newNavHeight-42) + 'px'});
          });
        }
        else if(Prototype.Browser.WebKit){
          newNavHeight = $('divNaviCenter').getHeight();
          $$('.navlevel').each(function(elDiv){
            if((newNavHeight-40) > 0) $(elDiv).setStyle({height: (newNavHeight-40) + 'px'});
          });
        }          
        
        new Ajax.Updater('navlevel'+this.currLevel, this.constRequestRootNav, {
          parameters: { 
            rootLevelId: this.rootLevelId,
            rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
            rootLevelGroupId: this.rootLevelGroupId,
            rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
            rootLevelTypeId: this.rootLevelTypeId,
            currLevel: this.currLevel
          },      
          evalScripts: true,     
          onComplete: function() {
            myCore.removeBusyClass('navlevel'+this.currLevel);
            this.levelArray.push(this.currLevel);
            this.initFolderHover();
            this.initPageHover();
            this.initAddMenuHover();
          }.bind(this)
        });
      }
    }
  },
    
  /**
   * getRootLevelList 
   */
  getRootLevelList: function(){
    if((typeof(myList) != 'undefined')) myList.getListPage();
  },
  
  /**
   * selectNavigationItem
   * @param integer parentLevel, string elType, integer itemId
   */
  selectNavigationItem: function(parentLevel, elType, itemId, showList){
    clearTimeout(this.listTimeOut);
    
    if(typeof(showList) == 'undefined') showList = true;
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    
    var level = parentLevel + 1;    
    var element = elType+itemId;
        
    this.currLevel = level;
    this.currItemId = itemId;
  
    if(this.navigation[parentLevel]){
      this.makeDeselected(this.navigation[parentLevel]);
    }
    
    this.navigation[parentLevel] = element;
    
    if(this.navigation.length > 0){    
      for(var i = 1; i <= this.navigation.length-1; i++){
        if(this.navigation[i] != element){
          this.makeParentSelected(this.navigation[i]);
        }else{
          this.makeSelected(this.navigation[parentLevel]);
        }   
      } 
    }
        
    this.setParentFolderId(itemId);    
    
    if(this.levelArray.indexOf(this.currLevel) == -1){
      this.levelArray.push(this.currLevel);
      
      var levelContainer = '<div id="navlevel'+this.currLevel+'" rootlevelid="'+this.rootLevelId+'" parentid="'+this.getParentFolderId()+'" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
      new Insertion.Bottom('divNaviCenterInner', levelContainer);      
    }else{      
      myCore.addBusyClass('navlevel'+this.currLevel);   
      $('navlevel'+this.currLevel).writeAttribute('parentid', this.getParentFolderId());
      
      var levelPos = this.levelArray.indexOf(this.currLevel);
      for(var i = levelPos; i < this.levelArray.length; i++){
        if($('navlevel'+this.levelArray[i])) $('navlevel'+this.levelArray[i]).innerHTML = '';
      }      
    }
    
    if(Prototype.Browser.IE){
      newNavHeight = $('divNaviCenter').getHeight();
      $$('.navlevel').each(function(elDiv){
        $(elDiv).setStyle({height: (newNavHeight-42) + 'px'});
      });
    }
    else if(Prototype.Browser.WebKit){
      newNavHeight = $('divNaviCenter').getHeight();
      $$('.navlevel').each(function(elDiv){
        $(elDiv).setStyle({height: (newNavHeight-40) + 'px'});
      });
    } 
    
    if(elType == this.constFolder){    
      new Ajax.Updater('navlevel'+this.currLevel, this.constRequestChildNav, {
        parameters: { 
          folderId: this.currItemId,
          rootLevelId: this.rootLevelId,
          rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
          rootLevelGroupId: this.rootLevelGroupId,
          rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
          currLevel: this.currLevel
        },      
        evalScripts: true,     
        onComplete: function() {        
          myCore.removeBusyClass('navlevel'+this.currLevel);
          this.initFolderHover();
          this.initPageHover();
          this.initAddMenuHover();
          //this.createSortableNavLevel(this.currLevel);
          this.scrollNavigationBar();
          this.hideCurrentPage();
          this.hideCurrentElement();
          this.updateCurrentFolder();          
          // check if tree has to load          
          if(this.arrTreeToLoad != null){
            //Clear old automatic List timeout
            clearTimeout(this.listTimeOut);
            showList = false;
            if(this.arrTreeToLoad.length > 1){
              this.arrTreeToLoad.splice(0, 1);
              if($('divNavigationTitle_folder'+this.arrTreeToLoad.first())) $('divNavigationTitle_folder'+this.arrTreeToLoad.first()).onclick();
            }else{
              if(this.strTreeItemType != 'media'){
                if($('divNavigationTitle_'+this.strTreeItemType+this.intTreeItemId)) $('divNavigationTitle_'+this.strTreeItemType+this.intTreeItemId).onclick();
                // reset tree load item id
                this.intTreeItemId = 0;
              }
              // reset tree load
              this.strTreeItemType = '';
              this.arrTreeToLoad = null;
            }
          }
          //Load startpage if wished
          if(this.blnLoadStartpage){
            $('level'+(myNavigation.currLevel)).down('.title').onclick();
            this.blnLoadStartpage = false;
          }
          //Set Timeout for automatic list display
          else if(showList) this.listTimeOut = setTimeout(function(){myFolder.getFolderContentList()}, this.constListTimeOut);
        }.bind(this)
      });
    }
  },
  
  /**
   * scrollNavigationBar
   */
  scrollNavigationBar: function(){
    if($('level'+this.currLevel)){
      var navigationBar = $('divNaviCenterInner');
      var navOffset = Element.cumulativeOffset(navigationBar);
      var navDimension = Element.getDimensions(navigationBar);
      var navPointer = [(navDimension.width + navOffset.left), (navDimension.height + navOffset.top)];
      var navScrollLeft = navigationBar.scrollLeft;
            
      var levelEl = $('level'+this.currLevel);
      var levelOffset = Element.cumulativeOffset(levelEl);
      var levelDimension = Element.getDimensions(levelEl);
      var levelPointer = [(levelDimension.width + levelOffset.left), (levelDimension.height + levelOffset.top)];
      
      
      if(levelPointer[0] > navPointer[0]){
        if((levelPointer[0] - navScrollLeft) > navPointer[0]){
          new Effect.Scroll('divNaviCenterInner', {x: ((levelPointer[0] - navScrollLeft) - navPointer[0]), y: 0, duration: 0.5})
        }  
      }else if(navScrollLeft > 0){
        new Effect.Scroll('divNaviCenterInner', {x: - navScrollLeft, y: 0, duration: 0.5})
      }
    }
  },
  
  /**
   * updateCurrentFolder
   */
  updateCurrentFolder: function() {
    this.folderId = this.currItemId;
    if($('divFolderRapper')){
      $('divFolderRapper').show();
      $('aFolderTitle').innerHTML = $('divNavigationTitle_folder'+this.folderId).innerHTML;
    } 
  },
  
  /**
   * hideCurrentFolder
   */
  hideCurrentFolder: function() {
    if($('divFolderRapper')){
      $('divFolderRapper').hide();
      $('aFolderTitle').innerHTML = '';
    } 
  },
  
  /**
   * updateCurrentPage
   */
  updateCurrentPage: function() {
    this.pageId = this.currItemId;
    if($('divPageRapper')){
      $('divPageRapper').show();
      $('divPageTitle').innerHTML = $('divNavigationTitle_page'+this.pageId).innerHTML;
    } 
  },
  
  /**
   * hideCurrentPage
   */
  hideCurrentPage: function() {
    if($('divPageRapper')){
      $('divPageRapper').hide();
      $('divPageTitle').innerHTML = '';
    } 
  },
  
  /**
   * updateCurrentElement
   */
  updateCurrentElement: function() {
    this.elementId = this.currItemId;
    if($('divElementRapper')){
      $('divElementRapper').show();
      $('divElementTitle').innerHTML = $('divNavigationTitle_element'+this.elementId).innerHTML;
    } 
  },
  
  /**
   * hideCurrentElement
   */
  hideCurrentElement: function() {
    if($('divElementRapper')){
      $('divElementRapper').hide();
      $('divElementTitle').innerHTML = '';
    } 
  },
  
  /**
   * getEditFormMainFolder
   */
  getEditFormMainFolder: function(){
    if($('divNavigationEdit_'+this.folderId)){
      $('divNavigationEdit_'+this.folderId).ondblclick();
    }
  },
  
  /**
   * createSortableNavigation
   */
  createSortableNavLevel: function(level) {
    SortableNavLevel= 'level'+level;
    Sortable.destroy(SortableNavLevel);
    if($(SortableNavLevel)){      
      Sortable.create(SortableNavLevel,{
        tag:'div',
        scroll:SortableNavLevel,
        only: ['folder', 'page'],
        handle:'icon',            
        //constraint: false,
        //ghosting: true,
        containment: SortableNavLevel,
        onUpdate: function(){ 
          Sortable.serialize(SortableNavLevel);
        }
      });
    }
  },
  
  /**
   * selectItem 
   */
  selectItem: function(blnLoadStartPage){
    if(typeof(blnLoadStartPage) == 'undefined'){
      this.blnLoadStartpage = false;
    }else{
      this.blnLoadStartpage = blnLoadStartPage;
    }
    if(this.itemId != ''){
      if($('divNavigationTitle_'+this.itemId)) $('divNavigationTitle_'+this.itemId).onclick();
    }
  },
  
  /**
   * updateNavigationLevel
   * @param integer level, integer parentItemId
   */
  updateNavigationLevel: function(level, parentItemId){
    
    var elementId;
    var currLevel;
    var parentId;
    var elementType = '';
    
    if(typeof(level) != 'undefined' && level != ''){ 
      currLevel = level;
    }else{
      if($('currLevel')) currLevel = $F('currLevel');
    }
    
    if(typeof(parentItemId) != 'undefined' && parentItemId != ''){
      parentId = parentItemId;
    }else{
      if($('parentFolderId')) parentId = $F('parentFolderId');
    }
    
    if($('elementType') && $F('elementType') != '') elementType = $F('elementType');
    if($('id') && $F('id')) elementId = $F('id');
     
    var strAjaxAction = '';
    var strParams = '';
        
    if(parentId != '' && parentId > 0){
      strAjaxAction = this.constRequestChildNav;
      strParams = 'currLevel='+currLevel+'&folderId='+parentId+'&rootLevelId='+this.rootLevelId+'&rootLevelGroupId='+this.rootLevelGroupId;     
    } else {
      strAjaxAction = this.constRequestRootNav;
      strParams = 'currLevel='+currLevel+'&rootLevelId='+this.rootLevelId+'&rootLevelGroupId='+this.rootLevelGroupId;
    }
    
    var rootLevelGroupKey = ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '';
    strParams += '&rootLevelGroupKey='+rootLevelGroupKey; 
    
    var rootLevelLanguageId = ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '';
    strParams += '&rootLevelLanguageId='+rootLevelLanguageId; 
        
    if(strParams != '' && strAjaxAction != ''){      
      new Ajax.Updater('navlevel'+currLevel, strAjaxAction, {
        parameters: strParams,      
        evalScripts: true,     
        onComplete: function() {       
          new Effect.Highlight('navlevel'+currLevel, {startcolor: '#ffd300', endcolor: '#ffffff'});
          
          if(elementType != '' && elementId != '' && $(elementType+elementId)){ 
            if(this.navigation[currLevel]){
              this.makeDeselected(this.navigation[currLevel]);
            }    
            this.navigation[currLevel] = elementType+elementId;
            
            if(this.navigation.length > 0){      
              for(var i = 1; i <= this.navigation.length-1; i++){
                if(this.navigation[i] != elementType+elementId){
                  if(currLevel < i){
                    this.makeDeselected(this.navigation[i]);
                  }else{
                    this.makeParentSelected(this.navigation[i]);
                  }
                }else{
                  this.makeSelected(this.navigation[currLevel]);
                }   
              } 
            }           
            if(this.levelArray.indexOf(currLevel) != -1 && elType == this.constPage){
              var levelPos = this.levelArray.indexOf(currLevel)+1;
              for(var i = levelPos; i < this.levelArray.length; i++){
                if($('navlevel'+this.levelArray[i])) $('navlevel'+this.levelArray[i]).innerHTML = '';
              }
            }           
            if(elementType == this.constFolder){
              this.selectItem(this.blnLoadStartpage);
            } 
          }
                             
          this.initFolderHover();
          this.initPageHover();
          this.initAddMenuHover();    
        }.bind(this)
      });       
    }  
  },
  
  /**
   * updateSortPosition
   * @param string posElement, string elType, integer level 
   */
  updateSortPosition: function(posElement, elType, level){
    
    var intPosLastUnderscore = posElement.lastIndexOf('_');
    var itemId = posElement.substring(intPosLastUnderscore + 1);
    var parentId = $('navlevel'+level).readAttribute('parentid');

    if(elType == this.constGlobal) {
      strAjaxAction = '/zoolu/global/navigation/updateposition';
    } else {
      strAjaxAction = '/zoolu/cms/navigation/updateposition';
    }
  
    new Ajax.Updater('navlevel'+level, strAjaxAction, {
      parameters: {
        id: itemId,
        elementType: elType,
        sortPosition: $(posElement).getValue(),
        rootLevelId: this.rootLevelId,
        rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
        rootLevelTypeId: this.rootLevelTypeId,
        rootLevelGroupId: this.rootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
        parentId: parentId        
      },      
      evalScripts: true,     
      onComplete: function() {      
        if(this.rootLevelId != '' && this.rootLevelId > 0){
          this.updateNavigationLevel(level, parentId);
        }
        //Also reload list if visible
        if($('genTableListContainer').visible()){
          myFolder.getFolderContentList();
        }
      }.bind(this)
    });   
  },
  
  /**
   * updateBreadcrumb
   */
  updateBreadcrumb: function(){
    var breadcrumb = this.topNaviTitle;
    
    if($('divRootLevelTitle_'+this.rootLevelId)){
      breadcrumb += " &raquo; " + $('divRootLevelTitle_'+this.rootLevelId).innerHTML;
    }
    
    if(this.navigation && this.navigation.length > 0){
      for(var i = 1; i <= this.navigation.length-1; i++){
        if($('divNavigationTitle_'+this.navigation[i])){
          breadcrumb += " &raquo; " + $('divNavigationTitle_'+this.navigation[i]).innerHTML;
        }
      }
    }
    
    $('navtopbreadcrumb').innerHTML = breadcrumb;
  },
  
  /**
   * toggleAddMenuIcon
   * @param integer levelId, string mode
   */
  toggleAddMenuIcon: function(levelId, mode){
    
    if(mode != '' && mode == 'show'){
      $('levelmenu'+levelId).show();
      $('addmenu'+levelId).hide();
      //$('addmenu'+levelId).fade({ duration: 0.5 });
      //setTimeout('$(\'addmenu'+levelId+'\').hide()', 500); 
    }else{      
      $('levelmenu'+levelId).hide();
    }    
  },
  
  /**
   * toggleSortPosBox
   * @param string element
   */
  toggleSortPosBox: function(element){
    
    if($(element).hasClassName('sortactive')){
      $(element).removeClassName('sortactive');
    }else{
      $(element).addClassName('sortactive');
    }  
      
  },
  
  /**
   * showAddMenu
   * @param integer levelId
   */
  showAddMenu: function(levelId){
    currMenuDiv = 'addmenu'+levelId;
    $(currMenuDiv).appear({ duration: 0.5 });       
  },
  
  /**
   * addFolder
   * @param integer currLevel
   */
  addFolder: function(currLevel){
    if($('divMediaContainer')) $('divMediaContainer').hide(); 
    if($('buttondelete')) $('buttondelete').hide();  
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    this.showFormContainer();
    
    $(this.genFormContainer).innerHTML = '';
    $('divWidgetMetaInfos').innerHTML = '';
    
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();

    myCore.addBusyClass(this.genFormContainer);
    myCore.addBusyClass('divWidgetMetaInfos');
    
    myCore.resetTinyMCE(true);
        
    new Ajax.Updater('genFormContainer', '/zoolu/core/folder/getaddform', {
      parameters: {
        formId: folderFormDefaultId,
        rootLevelId: this.rootLevelId,
        rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
        rootLevelTypeId: this.rootLevelTypeId,
        rootLevelGroupId: this.rootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
        parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel,
        elementType: this.constFolder,
        zoolu_module: this.module            
      },      
      evalScripts: true,     
      onComplete: function() {
        myForm.writeMetaInfos();
        
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.2});
        myCore.removeBusyClass('divWidgetMetaInfos');
        myCore.removeBusyClass(this.genFormContainer);    
      }.bind(this)
    });
    
  },
  
  /**
   * addPage
   * @param integer currLevel
   */
  addPage: function(currLevel){
    $('buttondelete').hide();
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    this.showFormContainer();    
        
    $(this.genFormContainer).innerHTML = '';
    $('divWidgetMetaInfos').innerHTML = '';
    
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();
        
    myCore.addBusyClass(this.genFormContainer);
    myCore.addBusyClass('divWidgetMetaInfos');
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater('genFormContainer', '/zoolu/cms/page/getaddform', {
      parameters: {
        templateId: pageTemplateDefaultId,
        rootLevelId: this.rootLevelId,
        rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
        rootLevelGroupId: this.rootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
        parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel,
        pageTypeId: pageTypeDefaultId,
        elementType: this.constPage,
        isStartPage: 0       
      },      
      evalScripts: true,     
      onComplete: function() {
        myForm.writeMetaInfos();
        
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass('divWidgetMetaInfos');
        myCore.removeBusyClass(this.genFormContainer);              
      }.bind(this)
    });
        
  },
  
  /**
   * addStartPage
   * @param integer currLevel
   */
  addStartPage: function(currLevel){
    $('buttondelete').hide();
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
    this.showFormContainer();
    
    $('divWidgetMetaInfos').innerHTML = '';
    $(this.genFormContainer).innerHTML = '';
    
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();
    
    myCore.addBusyClass(this.genFormContainer);
    myCore.addBusyClass('divWidgetMetaInfos');
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater('genFormContainer', '/zoolu/cms/page/getaddform', {
      parameters: {
        templateId: pageTemplateDefaultId,
        rootLevelId: this.rootLevelId,
        rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
        rootLevelGroupId: this.rootLevelGroupId,
        rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
        parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel,
        pageTypeId: pageTypeDefaultId,
        elementType: this.constStartPage,
        isStartPage: 1
      },      
      evalScripts: true,     
      onComplete: function() {
        myForm.writeMetaInfos();
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass('divWidgetMetaInfos');
        myCore.removeBusyClass(this.genFormContainer);              
      }.bind(this)
    }); 
  },
  
  /**
   * getEditForm
   * @param integer itemId, string elType, integer formId, integer version, (integer templateId), (integer linkId)
   */
  getEditForm: function(itemId, elType, formId, version, templateId, linkId, backLink){
    //Clear automatic list timeout
    clearTimeout(this.listTimeOut);
    $(this.genFormContainer).innerHTML = '';
    if($('divWidgetMetaInfos')) $('divWidgetMetaInfos').innerHTML = '';
    // hide media container
    if($('divMediaContainer')) $('divMediaContainer').hide(); 
    if($(this.genTableListContainer)) $(this.genTableListContainer).hide();
        
    //check if templateId is assigned 
    templateId = (typeof(templateId) != 'undefined' || templateId == null) ? templateId : -1;

    //check if linkId is assigned
    linkId = (typeof(linkId) != 'undefined' || templateId == null) ? linkId : -1;
    
    //check if backLink is assigned
    backLink = (typeof(backLink) != 'undefined' || backLink == null) ? backLink : false;

    var element = elType+itemId;
    this.currItemId = itemId;
    
    var currLevel = 0;
    // e.g. level1 - cut level to get currLevel number
    currLevel = parseInt($(element).up().id.substr(5)); 
        
    if(this.navigation[currLevel]){
      this.makeDeselected(this.navigation[currLevel]);
    }    
    this.navigation[currLevel] = element;
    
    if(this.navigation.length > 0){      
      for(var i = 1; i <= this.navigation.length-1; i++){
        if(this.navigation[i] != element){
          if(currLevel < i){
            this.makeDeselected(this.navigation[i]);
          }else{
            this.makeParentSelected(this.navigation[i]);
          }
        }else{
          this.makeSelected(this.navigation[currLevel]);
        }   
      } 
    }
    
    if(this.levelArray.indexOf(currLevel) != -1 && (elType == this.constPage || elType == this.constGlobal)){
      var levelPos = this.levelArray.indexOf(currLevel)+1;
      for(var i = levelPos; i < this.levelArray.length; i++){
        if($('navlevel'+this.levelArray[i])) $('navlevel'+this.levelArray[i]).innerHTML = '';
      }
      this.hideCurrentFolder();
      if(elType == this.constPage){
        if($(element).down('.icon').className.indexOf(this.constStartItem) == -1){
          this.updateCurrentPage();
        }else{
          this.hideCurrentPage();
        }
      }else if(elType == this.constGlobal){
        if($(element).down('.icon').className.indexOf(this.constStartItem) == -1){
          this.updateCurrentElement();
        }else{
          this.hideCurrentElement();
        }
      }
    }
    
    this.showFormContainer();    
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();    
    
    myCore.addBusyClass(this.genFormContainer);
    myCore.addBusyClass('divWidgetMetaInfos');
    
    strAjaxAction = '';    
    if(elType == this.constFolder){      
      strAjaxAction = '/zoolu/core/' + elType + '/geteditform';
    } else if(elType == this.constGlobal) {
      strAjaxAction = '/zoolu/global/element/geteditform';
    } else {
      strAjaxAction = '/zoolu/cms/' + elType + '/geteditform';
    }
    
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater('genFormContainer', strAjaxAction, {
       parameters: { 
         id: itemId,
         formId: formId,         
         formVersion: version,
         templateId: templateId,
         linkId: linkId,
         currLevel: currLevel,
         rootLevelId: this.rootLevelId,
         rootLevelLanguageId: ($('rootLevelLanguageId'+this.rootLevelId)) ? $F('rootLevelLanguageId'+this.rootLevelId) : '',
         rootLevelGroupId: this.rootLevelGroupId,
         rootLevelGroupKey: ($('rootLevelGroupKey'+this.rootLevelGroupId)) ? $F('rootLevelGroupKey'+this.rootLevelGroupId) : '',
         parentFolderId: $('navlevel'+currLevel).readAttribute('parentid'),
         elementType: elType,
         zoolu_module: this.module,
         rootLevelTypeId: this.rootLevelTypeId,
         backLink: backLink
       },      
       evalScripts: true,     
       onComplete: function() {
         myForm.writeMetaInfos();
         myCore.removeBusyClass('divWidgetMetaInfos');
         myCore.removeBusyClass(this.genFormContainer);
         // load medias
         myForm.loadFileFieldsContent('media');
         // load documents
         myForm.loadFileFieldsContent('document');
         // load videos
         myForm.loadFileFieldsContent('video');
         // load contacts
         myForm.loadContactFieldsContent();
         myForm.loadGroupFieldsContent();
       }.bind(this)
     });
  },  
  
  /**
   * showFormContainer
   */
  showFormContainer: function(){
    if($('divThumbContainer')) $('divThumbContainer').hide();
    if($('divListContainer')) $('divListContainer').hide();
    if($('divFormContainer')) $('divFormContainer').show();
  },

  /**
   * setRootLevelId
   * @param integer rootLevelId
   */
  setRootLevelId: function(rootLevelId){
    this.rootLevelId = rootLevelId;
  },
  
  /**
   * setRootLevelGroupId
   * @param integer rootLevelGroupId
   */
  setRootLevelGroupId: function(rootLevelGroupId){
    this.rootLevelGroupId = rootLevelGroupId;
  },
  
  /**
   * makeSelected
   */
  makeSelected: function(element){
    if(element != ''){
      if($(element)){
        $(element).addClassName('selected');
        if($(element+'top')) $(element+'top').addClassName('selected');
        if($(element+'bottom')) $(element+'bottom').addClassName('selected');
        if($(element+'menu')) $(element+'menu').show();
        if($(element)) $(element).removeClassName('hover');
      } 
    }
  },
  
  /**
   * makeDeselected
   */
  makeDeselected: function(element) {
    if(element != ''){
      if($(element)){
        $(element).removeClassName('selected');
        if($(element+'menu')) $(element+'menu').hide();
        if($(element+'top')) $(element+'top').removeClassName('selected');
        if($(element+'bottom')) $(element+'bottom').removeClassName('selected');
        if($(element).hasClassName('pselected')) $(element).removeClassName('pselected');
      }
    }
  },
  
  /**
   * makeParentSelected
   */
  makeParentSelected: function(element){
    if(element != ''){
      if($(element)) {
        $(element).addClassName('pselected');
        $(element).removeClassName('selected');
      }else{
        var parentId = $('navlevel'+(this.currLevel-1)).readAttribute('parentid');
        if($(this.constFolder+parentId)) {
          $(this.constFolder+parentId).addClassName('pselected');
          $(this.constFolder+parentId).removeClassName('selected');
        }
      }
    }
  },
  
  /**
   * resetGenContainer
   */
  resetGenContainer: function(){
    if($(this.genFormContainer)) $(this.genFormContainer).hide();
    if($(this.genFormFunctions)) $(this.genFormFunctions).hide();
    if($(this.genListContainer)) $(this.genListContainer).hide();
    if($(this.genListFunctions)) $(this.genListFunctions).hide();
  },
  
  getRootLevelActions: function(rootLeveId, rootLevelType) {
    // TODO
  },
  
  /**
   * setParentFolderId
   * @param integer parentId
   */
  setParentFolderId: function(parentId){
    this.parentFolderId = parentId;   
  },
  
  /**
   * getParentFolderId
   */
  getParentFolderId: function(){
    return this.parentFolderId;
  }
  
});