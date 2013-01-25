/**
 * core.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2007-12-19: Thomas Schedler
 * 1.1, 2008-09-24: Cornelius Hansjakob
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Massiveart = {version:"1.0"};

Massiveart.Core = Class.create({

  initialize: function() {
    this.elementInFocus = '';
    
    this.languageId = 1;
    this.languageCode = 'de';
    this.translate = {Save: 'Speichern', Cancel: 'Abbrechen', All_files: 'Alle Files', Apply: 'Übernehmen', Edit: 'Bearbeiten'};
    
    this.deleteAlertMultiMessage = '';
    this.deleteAlertSingleMessage = '';
    this.CHECK = false;
  },
  
  /**
   * initSelectAll
   */
  initSelectAll: function(){    
    $('listSelectAll').observe('click', function(event){
      if($('listSelectAll').checked == true){
        $$('.listSelectRow').each(function(el){   
	          var strRowId = el.up('.listrow').id;
            if(!$(strRowId).hasClassName('selected')){
              $(strRowId).addClassName('selected');
            }
            var id = strRowId.replace('Row', '');
            $('listSelect'+id).checked = true;
	      }.bind(this));
      }else{
        $$('.listSelectRow').each(function(el){   
            var strRowId = el.up('.listrow').id;
            if($(strRowId).hasClassName('selected')){
              $(strRowId).removeClassName('selected');
            }
            var id = strRowId.replace('Row', '');
            $('listSelect'+id).checked = false;
        }.bind(this));
      }      
    }.bind(this));
  },
  
  /**
   * initListHover
   */
  initListHover: function(blnClickActive){
    if(typeof(blnClickActive) == 'undefined'){
      blnClickActive = true;
    }
    
    $$('.tablelist tr.listrow').each(function(elTr){ 
    
      elTr.observe('mouseover', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('listrow')){
          el.addClassName('hover');
        }else{         
          el.up('tr.listrow').addClassName('hover');          
        }
      }.bind(this));
      
      elTr.observe('mouseout', function(event){        
        el = Event.element(event);        
        if(el.hasClassName('listrow')){
          el.removeClassName('hover');
        }else{         
          el.up('tr.listrow').removeClassName('hover');         
        }
      }.bind(this));
      
      if(blnClickActive){
        elTr.observe('click', function(event){        
	        el = Event.element(event);              
	        if(el.hasClassName('listrow')){
	          this.toggleItemSelected(el.id);  
	        }else{         
	          this.toggleItemSelected(el.up('tr.listrow').id);         
	        }        
	      }.bind(this));
      }
                 
    }.bind(this));
  },
  
  /**
   * toggleItemSelected
   */
  toggleItemSelected: function(divId){    
    var id = ''; 
    
    if($(divId)){
      if(divId.indexOf('Row') > -1){
        id = divId.replace('Row', '');
      }
      
      if($(divId).hasClassName('selected')){
        $(divId).removeClassName('selected');
        if(id != '' && $('listSelect'+id)){
          $('listSelect'+id).checked = false;
        }        
      }else{
        $(divId).addClassName('selected');
        if(id != '' && $('listSelect'+id)){
          $('listSelect'+id).checked = true;
        } 
      }
    }    
  },
  
  /**
   * showAlertMessage
   */
  showAlertMessage: function(text){
    if($('overlayGenContentWrapper')){
      $('overlayGenContent').innerHTML = '';        

      if($('overlayBlack75')) $('overlayBlack75').show();
            
      $('overlayGenContent').innerHTML = text;
      $('overlayButtons').show();
            
      this.putOverlayCenter('overlayGenContentWrapper');
      $('overlayGenContentWrapper').show(); 
      myOverlay.overlayCounter++;
    }   
  },
  
  /**
   * hideAlertMessage
   */
  hideAlertMessage: function(){
    if($('buttonOk')) $('buttonOk').stopObserving();
    if($('buttonCancel')) $('buttonCancel').stopObserving();
    if($('overlayGenContentWrapper')) $('overlayGenContentWrapper').hide();
    if($('overlayGenContent')) $('overlayGenContent').innerHTML = '';
    if($('overlayButtons')) $('overlayButtons').hide();
    if(--myOverlay.overlayCounter == 0) $('overlayBlack75').hide();
  },
  
  /**
   * showDeleteAlertMessage
   */
  showDeleteAlertMessage: function(size){
    if(typeof(size != 'undefined') && size > 0){
      if($('overlayGenContentWrapper')){
        $('overlayGenContentWrapper').setStyle({height: 'auto'});
        $('overlayGenContent').setStyle({height: 'auto'});
        $('overlayGenContent').innerHTML = '';	      

      	if($('overlayBlack75')) $('overlayBlack75').show();
      	      
      	if(size == 1){
      	  $('overlayGenContent').innerHTML = this.deleteAlertSingleMessage; 
      	}else{
      	  $('overlayGenContent').innerHTML = this.deleteAlertMultiMessage.replace('%i', size);  
      	}
      	$('overlayButtons').show();
      	      
      	this.putOverlayCenter('overlayGenContentWrapper');
      	$('overlayGenContentWrapper').show();
      	myOverlay.overlayCounter++;
      }	  
    }
  },
  
  /**
   * hideDeleteAlertMessage
   */
  hideDeleteAlertMessage: function(){
    if($('buttonOk')) $('buttonOk').stopObserving();
    if($('buttonCancel')) $('buttonCancel').stopObserving();
    if($('overlayGenContentWrapper')) $('overlayGenContentWrapper').hide();
    if($('overlayGenContent')) $('overlayGenContent').innerHTML = '';
    if($('overlayButtons')) $('overlayButtons').hide();
    if(--myOverlay.overlayCounter == 0) $('overlayBlack75').hide();
  },

    /**
     * showDynamicMessage
     */
  showDynamicMessage: function() {
      $('overlayGenContent2').innerHTML = '';
      $('overlayBlack75').show();
      this.putOverlayCenter('overlayGenContentWrapper2');
      $('overlayGenContentWrapper2').show();
      myOverlay.overlayCounter++;
  },

  hideDynamicMessage: function() {
      $('overlayGenContentWrapper2').hide();
  },

  /**
   * check
   */
  check: function(value){
    if(typeof(value) != 'undefined'){
      this.CHECK = value;
    }else{
      this.CHECK = true;
    }
  },

  /**
   * addBusyClass
   */
  addBusyClass: function(busyElement) {
    if($(busyElement)){
      $(busyElement).addClassName('busy');
    }
  },

  /**
   * removeBusyClass
   */
  removeBusyClass: function(busyElement) {
    if($(busyElement)){
      $(busyElement).removeClassName('busy');
    }
  },

  /**
   * addBusyClassExtended
   */
  addBusyClassExtended: function(busyElement, cssClass) {
    if($(busyElement)){
      $(busyElement).addClassName(cssClass);
    }
  },

  /**
   * addBusyClassExtendedWithSize
   */
  addBusyClassExtendedWithSize: function(busyElement, cssClass, elementWidth, elementHeight) {
    if($(busyElement)){
      $(busyElement).setStyle({width: elementWidth+'px', height: elementHeight+'px'});
      $(busyElement).addClassName(cssClass);
    }
  },

  /**
   * addBusyClassExtendedWithHeight
   */
  addBusyClassExtendedWithHeight: function(busyElement, cssClass, elementHeight) {
    if($(busyElement)){
      $(busyElement).setStyle({height: elementHeight+'px'});
      $(busyElement).addClassName(cssClass);
    }
  },
  
  /**
   * resetTinyMCE
   */
  resetTinyMCE: function(blnAndDestroy){
    if($$('.texteditor')){
      if(typeof(blnAndDestroy) == 'undefined'){
        blnAndDestroy = false;
      }

      if(tinyMCE.editors.length > 0){
        /**
         * reset some tiny mce issues
         */
        tinyMCE.editors.each(function(e) {
          if(e.controlManager.controls){
            if(e.controlManager.get('formatselect')){
              if(e.controlManager.get('formatselect').menu){
                if($('menu_' + e.controlManager.get('formatselect').menu.id)){
                  $('menu_' + e.controlManager.get('formatselect').menu.id).remove(); 
                }
              }
            }
          }
        });  
        
        /**
         * destroy all editors
         */
        if(blnAndDestroy){
          tinyMCE.editors = [];
        }
      }
    }
  },

  /**
   * removeBusyClassExtended
   */
  removeBusyClassExtended: function(busyElement, cssClass) {
    if($(busyElement)){
      $(busyElement).setStyle({width: 'auto', height: 'auto'});
      $(busyElement).removeClassName(cssClass);
    }
  },

  /**
   * removeBusyClassExtendedWithoutHeight
   */
  removeBusyClassExtendedWithoutHeight: function(busyElement, cssClass) {
    if($(busyElement)){
      $(busyElement).setStyle({height: 'auto'});
      $(busyElement).removeClassName(cssClass);
    }
  },
  
  /**
   * putOverlayCenter
   * options: e.g. x: -10, y: 20
   */
  putOverlayCenter: function(item, options) {
    if(typeof(options) == 'undefined') options = null;
    
    item = $(item);
    var xy = item.getDimensions();
    var win = this.windowDimensions();
    
    var xOffset = 0;
    var yOffset = 0;
    if(options != null && options.x) xOffset = options.x; 
    if(options != null && options.y) yOffset = options.y;
    
    item.style.left = (((win[0] / 2) - (xy.width / 2)) + xOffset) + "px";
    item.style.top = (((win[1]/2) - (xy.height/2)) + yOffset) + "px";    
   },
  
  /**
   * calcMaxOverlayHeight
   */
  calcMaxOverlayHeight: function(item, blnSetStyle, blnSetMax){
    var height = document.viewport.getHeight();    
    var newOlHeight = height-200;
    if($(item) && blnSetStyle){
      if(blnSetMax){
        $(item).setStyle({maxHeight: newOlHeight+'px'});
      }else{
        $(item).setStyle({height: newOlHeight+'px'});
      }      
    }
    if(blnSetStyle == false) 
      return newOlHeight;
  },
  
  /**
   * calcMaxOverlayWidth
   */
  calcMaxOverlayWidth: function(item, blnSetStyle, padding){
    if(typeof(padding) == 'undefined') padding = 0;
    var width = document.viewport.getWidth();    
    var newOlWidth = width-400-padding;
    if($(item) && blnSetStyle){
      $(item).setStyle({width: newOlWidth+'px'});      
    }
    if(blnSetStyle == false) 
      return newOlWidth;
  },

  /**
   * putCenter
   */
  putCenter: function(item) {
    item = $(item);
    var xy = item.getDimensions();
    var win = this.windowDimensions();
    var scrol = this.scrollOffset();
    item.style.left = (win[0] / 2) + scrol[0] - (xy.width / 2) + "px";
    item.style.top = (win[1] / 2) + scrol[1] - (xy.height / 2) + "px";
  },

  /**
   * fullScreen
   */
  fullScreen: function(item) {
    $(item).style.height = Window.getScrollHeight()+ "px";
  },

  /**
   * windowDimensions
   */
  windowDimensions: function() {
    var x, y;
    if (self.innerHeight) {
      // all except Explorer
      x = self.innerWidth;
      y = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) {
      // Explorer 6 Strict Mode
      x = document.documentElement.clientWidth;
      y = document.documentElement.clientHeight;
    } else if (document.body) {
      // other Explorers
      x = document.body.clientWidth;
      y = document.body.clientHeight;
    }
    if (!x) x = 0;
    if (!y) y = 0;
    arrayWindowSize = new Array(x,y);
    return arrayWindowSize;
  },

  /**
   * scrollOffset
   */
  scrollOffset: function() {
    var x, y;
    if (self.pageYOffset) {
      // all except Explorer
      x = self.pageXOffset;
      y = self.pageYOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
      // Explorer 6 Strict
      x = document.documentElement.scrollLeft;
      y = document.documentElement.scrollTop;
    } else if (document.body) {
      // all other Explorers
      x = document.body.scrollLeft;
      y = document.body.scrollTop;
    }
    if (!x) x = 0;
    if (!y) y = 0;
    arrayScrollOffset = new Array(x,y);
    return arrayScrollOffset;
  },
  
  /**
   * openVersionHistory
   */
  openVersionHistory: function(){

    var width  = 640;
    var height = 480;
    var left   = (screen.width  - width)/2;
    var top    = (screen.height - height)/2;
    var params = 'width='+width+', height='+height;
    params += ', top='+top+', left='+left;
    params += ', directories=no';
    params += ', location=no';
    params += ', menubar=no';
    params += ', resizable=no';
    params += ', scrollbars=yes';
    params += ', status=no';
    params += ', toolbar=no';
    newwin = window.open('/version_history', 'VersionHistory', params);

    if (window.focus) {
      newwin.focus()
    }
  },
  
  /**
   * adjust
   */
  adjust: function(sender){
    // zeilenumbrueche harmonisieren
    var text = sender.value.replace(/(\015\012)|(\015)|(\012)/g, '\n');

    // text in array verwandeln
    var text_arr = text.split('\n');

    //zeilen zaehlen
    sender.rows = text_arr.length;
  }
  
});

Massiveart.UI = { };

Massiveart.UI.Dialog = Class.create({
 // TODO  
});

Massiveart.UI.Menu  = Class.create({
  initialize: function(options) {
    this.options = { items: [] };
    
    Object.extend(this.options, options || { });
    
  }
});

Massiveart.UI.Menu.Overlap = Class.create(Massiveart.UI.Menu, {
  initialize: function($super, options) {
    this.options = { };

    Object.extend(this.options, options || { });
  
    $super(this.options);
  
    this.isOpen = false;
    this.isActive = false;
    
    this.element = new Element('div', { 'class': 'ui-menu-overlap' });
    
    this.menu = new Element('ul', { 'class': 'ui-menu' });
    
    this.options.items.each(function(item){
      this.menu.insert(new Element('li').update(item));
    }.bind(this));
    
    this.element.update(this.menu);
    
    this.element.observe('mouseenter', function(event){
      this.isActive = true;
    }.bind(this));
    
    this.element.observe('mouseleave', function(event){
      if(this.isOpen && this.isActive){
        this.close();
      }
    }.bind(this));

    this.observers = {
      keypress: this.keypress.bind(this)
    };
  },
  
  position: function() {
    // Find the middle of the viewport.
    var vSize = document.viewport.getDimensions();
    var dialog = this.element;
    var dimensions = {
      width: parseInt(dialog.getStyle('width'), 10),
      height: parseInt(dialog.getStyle('height'), 10)
    };

    var position = {
      left: ((vSize.width / 2) - (dimensions.width / 2)).round(),
      top: ((vSize.height / 2) - (dimensions.height / 2)).round()
    };

    var offsets = document.viewport.getScrollOffsets();

    position.left += offsets.left;
    position.top += offsets.top;

    this.element.setStyle({
      left: position.left  + 'px',
      top: position.top  + 'px'
    });
  },
  
  open: function(pos) {
    if (this.isOpen) return;

    $(document.body).insert(this.element);

    this.element.show();
    

    if(typeof(pos) != 'undefined'){
      this.element.setStyle({ 
        left: pos[0]  + 'px', 
        top: pos[1]  + 'px' 
      });
    }else{
      this.position();
    }

    Event.observe(window, 'keydown', this.observers.keypress);
    this.isOpen = true;
  },

  close: function() {
    this.element.hide();
    this.isOpen = false;
    Event.stopObserving(window, 'keydown', this.observers.keypress);
  },
  
  destroy: function(){
    this.close();
    this.element.remove();    
  },

  keypress: function(event) {
    var keyCode = event.keyCode || event.charCode;
    if(keyCode === Event.KEY_ESC && !this.locked){
      this.close(false);
      return;
    }
  }
});

/*** Copy Right Information ***
  * Please do not remove following information.
  * Window Size v1.0
  * Author: John J Kim
  * Email: john@frontendframework.com
  * URL: www.FrontEndFramework.com
  *
  * You are welcome to modify the codes as long as you include this copyright information.
 *****************************/

Window = {
  //Returns an integer representing the width of the browser window (without the scrollbar).
  getWindowWidth : function() {
  return (document.layers||(document.getElementById&&!document.all)) ? window.outerWidth : (document.all ? document.body.clientWidth : 0);
  },

  //Returns an integer representing the height of the browser window (without the scrollbar).
  getWindowHeight : function() {
  return window.innerHeight ? window.innerHeight :(document.getBoxObjectFor ? Math.min(document.documentElement.clientHeight, document.body.clientHeight) : ((document.documentElement.clientHeight != 0) ? document.documentElement.clientHeight : (document.body ? document.body.clientHeight : 0)));
  },

  //Returns an integer representing the scrollWidth of the window.
  getScrollWidth : function() {
  return document.all ? Math.max(Math.max(document.documentElement.offsetWidth, document.documentElement.scrollWidth), document.body.scrollWidth) : (document.body ? document.body.scrollWidth : ((document.documentElement.scrollWidth != 0) ? document.documentElement.scrollWidth : 0));
  },

  //Returns an integer representing the scrollHeight of the window.
  getScrollHeight : function(){
    return document.all ? Math.max(Math.max(document.documentElement.offsetHeight, document.documentElement.scrollHeight), Math.max(document.body.offsetHeight, document.body.scrollHeight)) : (document.body ? document.body.scrollHeight : ((document.documentElement.scrollHeight != 0) ? document.documentElement.scrollHeight : 0));
  },

  //Returns an integer representing the scrollLeft of the window (the number of pixels the window has scrolled from the left).
  getScrollLeft : function() {
    return document.all ? (!document.documentElement.scrollLeft ? document.body.scrollLeft : document.documentElement.scrollLeft) : ((window.pageXOffset != 0) ? window.pageXOffset : 0);
  },

  //Returns an integer representing the scrollTop of the window (the number of pixels the window has scrolled from the top).
  getScrollTop : function() {
    return document.all ? (!document.documentElement.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop) : ((window.pageYOffset != 0) ? window.pageYOffset : 0);
  }
}