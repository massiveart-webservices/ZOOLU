/**
 * navigation.properties.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-19: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.Navigation.Properties = Class.create(Massiveart.Navigation, {

  initialize: function($super) {
    $super();
    
    this.constBasePath = '/zoolu/properties';
    
    this.navigationItemType = 'category';
    this.navigationPath = '';
    this.categoryTypeId = 1;
    
    this.constContact = 'contact';
    
    this.currLevel = 0;
  },
  
  /**
   * initItemHover
   */
  initItemHover: function(){
    $$('div.hoveritem').each(function(elDiv){    
      elDiv.observe('mouseover', function(event){        
        el = Event.element(event);
        if(el.hasClassName('hoveritem')){
          el.addClassName('hover');
        }else{         
          el.up('.hoveritem').addClassName('hover');          
        }
      }.bind(this));
      
      elDiv.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('hoveritem')){
          el.removeClassName('hover');
        }else{         
          el.up('.hoveritem').removeClassName('hover');        
        }        
      }.bind(this));      
    }.bind(this));
  },
  
  /**
   * selectUsers
   */
  selectUsers: function(){
    alert('In Arbeit!');
  },
  
  /**
   * selectContacts
   */
  selectContacts: function(portalId){
    this.resetGenContainer();
    this.currLevel = 1;
    this.navigationItemType = 'unit';
   
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();
    
    // add css classes to mark an item as 'selected'
    this.makeSelected('portal'+portalId);
    
    // remove css classes to deselect an item
    if($(this.preSelectedPortal) && ('portal'+portalId) != this.preSelectedPortal){ 
      this.makeDeselected(this.preSelectedPortal);
    }  
            
    this.preSelectedPortal = 'portal'+portalId;
    this.rootLevelId = portalId;
    this.rootLevelType = 'contact';
    
    $('divNaviCenterInner').innerHTML = '';
    this.levelArray = [];
    
    var levelContainer = '<div id="navlevel'+this.currLevel+'" parentid="" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
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
    
    this.navigationPath = '/navigation/contactnavigation';
    new Ajax.Updater('navlevel'+this.currLevel, this.constBasePath+this.navigationPath, {
      parameters: { 
        rootLevelId: this.rootLevelId,
        currLevel: this.currLevel
      },      
      evalScripts: true,     
      onComplete: function() {
        myCore.removeBusyClass('navlevel'+this.currLevel);
        this.initItemHover();
        this.initAddMenuHover();
        this.levelArray.push(this.currLevel);   
      }.bind(this)
    });
    
  },
  
  /**
   * selectLocations
   */
  selectLocations: function(rootLevelId){
    this.resetGenContainer();
    this.currLevel = 1;
    this.navigationItemType = 'unit';
   
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();
    
    // add css classes to mark an item as 'selected'
    this.makeSelected('portal'+rootLevelId);
    
    // remove css classes to deselect an item
    if($(this.preSelectedPortal) && ('portal'+rootLevelId) != this.preSelectedPortal){ 
      this.makeDeselected(this.preSelectedPortal);
    }  
            
    this.preSelectedPortal = 'portal'+rootLevelId;
    this.rootLevelId = rootLevelId;
    this.rootLevelType = 'location';
    
    $('divNaviCenterInner').innerHTML = '';
    this.levelArray = [];
    
    var levelContainer = '<div id="navlevel'+this.currLevel+'" parentid="" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
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
    
    this.navigationPath = '/navigation/locationnavigation';
    new Ajax.Updater('navlevel' + this.currLevel, this.constBasePath + this.navigationPath, {
      parameters: { 
        rootLevelId: this.rootLevelId,
        currLevel: this.currLevel
      },      
      evalScripts: true,     
      onComplete: function() {
        myCore.removeBusyClass('navlevel'+this.currLevel);
        this.initItemHover();
        this.initAddMenuHover();
        this.levelArray.push(this.currLevel);   
      }.bind(this)
    });
    
  },
    
  /**
   * selectCategories
   */
  selectCategories: function(portalId, categoryTypeId){
    this.resetGenContainer();
    this.currLevel = 1;
    this.categoryTypeId = categoryTypeId;
    this.navigationItemType = 'category';
     
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();
    
    // add css classes to mark an item as 'selected'
    this.makeSelected('portal'+portalId);
    
    // remove css classes to deselect an item
    if($(this.preSelectedPortal) && ('portal'+portalId) != this.preSelectedPortal){ 
      this.makeDeselected(this.preSelectedPortal);
    }  
            
    this.preSelectedPortal = 'portal'+portalId;
    this.rootLevelId = portalId;
    this.rootLevelType = 'category';
    
    $('divNaviCenterInner').innerHTML = '';
    this.levelArray = [];
    
    var levelContainer = '<div id="navlevel'+this.currLevel+'" parentid="" class="navlevel busy" style="left: '+(201*this.currLevel-201)+'px"></div>'; 
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
    
    this.navigationPath = '/navigation/catnavigation';
    new Ajax.Updater('navlevel'+this.currLevel, this.constBasePath+this.navigationPath, {
      parameters: { 
        currLevel: this.currLevel, 
        categoryTypeId: categoryTypeId 
      },      
      evalScripts: true,     
      onComplete: function() {
        myCore.removeBusyClass('navlevel'+this.currLevel);
        this.initItemHover();
        this.initAddMenuHover();
        this.levelArray.push(this.currLevel);   
      }.bind(this)
    });
    
  },
  
  /**
   * selectCatNavItem
   */
  selectNavigationItem: function(parentLevel, elType, itemId, categoryTypeId){
    $(this.genFormContainer).hide();
    $(this.genFormSaveContainer).hide();
    
    this.categoryTypeId = (typeof(categoryTypeId) != 'undefined') ? categoryTypeId : -1;
    
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
    
    new Ajax.Updater('navlevel'+this.currLevel, this.constBasePath+this.navigationPath, {
      parameters: { 
        itemId: itemId,
        rootLevelId: this.rootLevelId,
        currLevel: this.currLevel,
        categoryTypeId: categoryTypeId
      },      
      evalScripts: true,     
      onComplete: function() {        
        this.initItemHover();
        this.initAddMenuHover();
        myCore.removeBusyClass('navlevel'+this.currLevel);
        this.scrollNavigationBar();
      }.bind(this)
    });
  },
  
  /**
   * updateNavigationLevel
   * @param integer level, integer rootLevelId, integer parentItemId
   */
  updateNavigationLevel: function(level, rootLevelId, parentItemId){
    
    var elementId;
    var currLevel;
    var rootId;
    var parentId;
    var elementType = '';
    
    if($('currLevel') && $F('currLevel') != ''){ 
      currLevel = $F('currLevel');
    }else{
      currLevel = level;
    }
    
    this.currLevel = currLevel;
    
    if($('rootLevelId') && $F('rootLevelId') != ''){ 
      rootId = $F('rootLevelId');
    }else{
      rootId = rootLevelId; 
    }

    if($('parentId') && $F('parentId') != ''){
      parentId = $F('parentId');
    }else{
      parentId = parentItemId;
    }
    
    if($('elementType') && $F('elementType') != '') elementType = $F('elementType');
    if($('id') && $F('id') != '') elementId = $F('id');
     
    var strAjaxAction = '';
    var strParams = '';
    
    strAjaxAction = this.constBasePath+this.navigationPath;    
		if(parentId != '' && parentId > 0){
		  strParams = 'currLevel='+currLevel+'&itemId='+parentId+'&rootLevelId='+rootId+'&categoryTypeId='+this.categoryTypeId;
		}else{
      strParams = 'currLevel='+currLevel+'&rootLevelId='+rootId+'&categoryTypeId='+this.categoryTypeId;
		} 
		    
    if(strParams != '' && strAjaxAction != ''){      
      new Ajax.Updater('navlevel'+currLevel, strAjaxAction, {
	      parameters: strParams,      
	      evalScripts: true,     
	      onComplete: function() {       
	        new Effect.Highlight('navlevel'+currLevel, {startcolor: '#ffd300', endcolor: '#ffffff'});
          
          if(elementId != '' && $('formType')){
            $($F('formType')+elementId).addClassName('selected');
          }                    
	        this.initItemHover();	        
	        this.initAddMenuHover();    
	      }.bind(this)
	    });       
    }  
  },
  
  /**
   * addCategory
   * @param integer currLevel
   */
  addCategory: function(currLevel, categoryTypeId){
    if($('buttondelete')) $('buttondelete').hide();   
    myNavigation.showFormContainer();
    
    $(this.genFormContainer).innerHTML = '';
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();

    myCore.addBusyClass(this.genFormContainer);    
    myCore.resetTinyMCE(true);
        
    new Ajax.Updater(this.genFormContainer, '/zoolu/properties/category/getaddform', {
      parameters: {
        formId: categoryFormDefaultId,
        rootLevelId: this.rootLevelId,
        parentId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel,
        categoryTypeId: categoryTypeId            
      },      
      evalScripts: true,     
      onComplete: function() {       
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass(this.genFormContainer);             
      }.bind(this)
    });
    
  },
  
  /**
   * addUnit
   * @param integer currLevel
   */
  addUnit: function(currLevel){
    if($('buttondelete')) $('buttondelete').hide();   
    myNavigation.showFormContainer();
    
    $(this.genFormContainer).innerHTML = '';
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();

    myCore.addBusyClass(this.genFormContainer);    
    myCore.resetTinyMCE(true);
        
    new Ajax.Updater(this.genFormContainer, '/zoolu/properties/contact/getunitaddform', {
      parameters: {
        formId: unitFormDefaultId,
        rootLevelId: this.rootLevelId,
        parentId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel         
      },      
      evalScripts: true,     
      onComplete: function() {       
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass(this.genFormContainer);             
      }.bind(this)
    });
  },
  
  /**
   * addContact
   * @param integer currLevel
   */
  addContact: function(currLevel){
    if($('buttondelete')) $('buttondelete').hide();   
    myNavigation.showFormContainer();
    
    $(this.genFormContainer).innerHTML = '';
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();

    myCore.addBusyClass(this.genFormContainer);
    myCore.resetTinyMCE(true);
        
    new Ajax.Updater(this.genFormContainer, '/zoolu/properties/contact/getaddform', {
      parameters: {
        formId: contactFormDefaultId,
        rootLevelId: this.rootLevelId,
        parentId: $('navlevel'+currLevel).readAttribute('parentid'),
        currLevel: currLevel         
      },      
      evalScripts: true,     
      onComplete: function() {       
        $('levelmenu'+currLevel).hide();
        $('addmenu'+currLevel).fade({duration: 0.5});
        myCore.removeBusyClass(this.genFormContainer);             
      }.bind(this)
    });
  },
  
  /**
   * getAddForm
   */
  getAddForm: function(){
    
    this.resetGenContainer();
    
    if($('buttondelete')) $('buttondelete').hide();
    
    myCore.addBusyClass(this.genFormContainer);
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath + '/' + this.rootLevelType + '/getaddform', {
      parameters: { 
        rootLevelId: this.rootLevelId, 
        parentId: $('navlevel'+this.currLevel).readAttribute('parentid'),
        currLevel: this.currLevel  
      },      
      evalScripts: true,     
      onComplete: function() {        
        if($(this.genFormContainer)) $(this.genFormContainer).show();
        if($(this.genFormFunctions)) $(this.genFormFunctions).show();
        if($(this.genFormSaveContainer)) $(this.genFormSaveContainer).show();
        if($('widgetfunctions')) $(this.genFormContainer).scrollTo($('widgetfunctions'));   
        myCore.removeBusyClass(this.genFormContainer);
      }.bind(this)
    });
  },
  
  /**
   * getEditForm
   * @param integer itemId
   */
  getEditForm: function(itemId, elType, formId, version, categoryTypeId){
    $(this.genFormContainer).innerHTML = '';
    
    this.resetGenContainer();
    
    var element = elType+itemId;
    if($(element)) this.currItemId = itemId;
    
    this.categoryTypeId = (typeof(categoryTypeId) != 'undefined') ? categoryTypeId : -1;
    
    switch (elType) {
      case 'unit':
        formDefaultId = unitFormDefaultId;
        typeEditPath = '/contact/getuniteditform';
        break;
      case 'contact':
        formDefaultId = contactFormDefaultId;
        typeEditPath = '/contact/geteditform';
        break;
      case 'location':
        formDefaultId = contactFormDefaultId;
        typeEditPath = '/location/geteditform';
        break;      
      default:
        formDefaultId = categoryFormDefaultId;
        typeEditPath = '/category/geteditform';
        break;
    }
    
    formId = (formId == null) ? formDefaultId : formId;
    version = (version == null) ? 1 : version;
    
    var currLevel = 0;
    // e.g. level1 - cut level to get currLevel number
    currLevel = ($(element)) ? parseInt($(element).up().id.substr(5)) : this.currLevel; 
        
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
    
    if(this.levelArray.indexOf(currLevel) != -1 && elType == this.constContact){
      var levelPos = this.levelArray.indexOf(currLevel)+1;
      for(var i = levelPos; i < this.levelArray.length; i++){
        if($('navlevel'+this.levelArray[i])) $('navlevel'+this.levelArray[i]).innerHTML = '';
      }
    }
    
    this.showFormContainer();
    
    $('buttondelete').show();
    $(this.genFormContainer).show();
    $(this.genFormSaveContainer).show();    
    
    myCore.addBusyClass(this.genFormContainer);
    myCore.resetTinyMCE(true);
    
    new Ajax.Updater(this.genFormContainer, this.constBasePath+typeEditPath, {
       parameters: { 
         id: itemId,
         formId: formId,    
         formVersion: version,
         currLevel: currLevel,
         rootLevelId: this.rootLevelId,
         parentId: $('navlevel'+currLevel).readAttribute('parentid'),
         categoryTypeId: this.categoryTypeId 
       },      
       evalScripts: true,     
       onComplete: function() {
         myCore.removeBusyClass(this.genFormContainer);
         // load medias
         myForm.loadFileFieldsContent('media');
         // load documents
         myForm.loadFileFieldsContent('document');
         // load videos
         myForm.loadFileFieldsContent('video');
         // load filter documents
         myForm.loadFileFilterFieldsContent('documentFilter');
       }.bind(this)
     });
  }
  
});