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
    this.retValue = false;
  },
  
  /**
   * submitForm
   */
  submitForm: function(formId){
    if($(formId)){      
      /**
       * validation
       */
      this.retValue = true;      
      
      $$('.mandatory').each(function(element){
        this.validateInput(element.id);
      }.bind(this));
      
      $$('.val_email').each(function(element){
        this.validateInputEmail(element.id)
      }.bind(this));
      
      /**
       * check captcha
       */
      if(this.retValue && $('recaptcha_response_field') && $('recaptcha_challenge_field')){
        if($F('recaptcha_response_field') != ''){          
          new Ajax.Request('/zoolu-website/datareceiver/check-recaptcha', {
            parameters: {
              recaptcha_response_field: $F('recaptcha_response_field'),
              recaptcha_challenge_field: $F('recaptcha_challenge_field')
            },
            evalScripts: true,
            onComplete: function(transport) {              
              if(transport.responseText.isJSON()){
                var responseData = transport.responseText.evalJSON();                
                if(responseData.status == 'ok'){
                  if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').removeClassName('missing');
                  if(this.retValue == true) {
                    $(formId).submit();
                  }
                }else{
                  Recaptcha.reload();
                  if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').addClassName('missing');
                  this.retValue = false;
                }
              }
            }.bind(this)
          });
        }else{
          if($('lbl_recaptcha_response_field')) $('lbl_recaptcha_response_field').addClassName('missing');
          this.retValue = false;
        }
      }else{
        if(this.retValue == true) {
          $(formId).submit();
        }
      }
    }
  },
  
  /**
   * validateInput
   */
  validateInput: function(element, baseValue) {
    if(($(element) && $F(element).blank()) || $F(element) == baseValue){
      if($('lbl_'+element)) $('lbl_'+element).addClassName('missing');
      this.retValue = false;
    }else{
      if($('lbl_'+element)) $('lbl_'+element).removeClassName('missing');
    }
  },
  
  /**
   * validateInputEmail
   */
  validateInputEmail: function(element){
    var filter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
    if($(element)){
      if(!filter.test($F(element))){
        if($('lbl_'+element)) $('lbl_'+element).addClassName('missing');
        this.retValue = false;
      }else{
        if($('lbl_'+element)) $('lbl_'+element).removeClassName('missing');
      }
    }
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