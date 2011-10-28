/**
 * default.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-26: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Default = Class.create({

  initialize: function() {
  
},
  
  /**
   * changeTestMode
   */
  changeTestMode: function(status){
    new Ajax.Request('/zoolu-website/testmode/change', {
      parameters: { TestMode: status },
      evalScripts: true,
      onComplete: function(transport) {         
        window.location.href = window.location.href;
      }.bind(this)
    });
  },
  
  /**
   * addBusyClass
   */
  addBusyClass: function(busyElement, blnDisplay) {
    if($(busyElement)){
      $(busyElement).addClassName('busy');
      if(blnDisplay) $(busyElement).show();
    }
  },

  /**
   * removeBusyClass
   */
  removeBusyClass: function(busyElement, blnDisplay) {
    if($(busyElement)){
      $(busyElement).removeClassName('busy');
      if(blnDisplay) $(busyElement).hide();
    }
  }
  
});