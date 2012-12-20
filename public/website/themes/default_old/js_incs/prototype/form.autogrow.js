/**
 * form.autogrow.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-06-10: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

Element.addMethods({
  autogrow: function(element, opt) {    
    var autogrow = new Autogrow();
    autogrow.init(element, opt);  
  }
});

var Autogrow = Class.create({
  init: function(element, opt) {      
    this.options = opt || {};
    
    this.dummy          = null;
    this.interval       = null;
    this.line_height    = this.options.lineHeight || parseInt(element.getStyle('line-height'));
    this.min_height     = this.options.minHeight || parseInt(element.getStyle('min-height'));
    this.max_height     = this.options.maxHeight || parseInt(element.getStyle('max-height'));
    this.textarea       = element;
    
    if(isNaN(this.line_height))
      this.line_height = 0;
         
    this.textarea.setStyle({overflow: 'hidden'}); //, display: 'block'
    this.textarea.observe('focus', function(event){
      this.startExpand();
    }.bind(this));
    this.textarea.observe('blur', function(event){
      this.stopExpand()
    }.bind(this));
    
    this.checkExpand(); 
  },
  
  startExpand: function() {
    this.interval = window.setInterval(function() {this.checkExpand()}.bind(this), 500);
  },
  
  stopExpand: function() {
    clearInterval(this.interval); 
  },
  
  checkExpand: function() {
    
    if(this.dummy == null){
      this.dummy = new Element('div');      
      this.dummy.setStyle({
                      'font-size'  : this.textarea.getStyle('font-size'),
                      'font-family': this.textarea.getStyle('font-family'),
                      'width'      : this.textarea.getStyle('width'),
                      'padding'    : this.textarea.getStyle('padding'),
                      'line-height': this.line_height + 'px',
                      'overflow-x' : 'hidden',
                      'position'   : 'absolute',
                      'top'        : 0,
                      'left'       : -9999 + 'px'
                      });
    }
        
    document.body.insert(this.dummy, {position: 'Top'});
      
    // Strip HTML tags
    var html = $F(this.textarea).replace(/(<|>)/g, '');
    
    // IE is different, as per usual
    if (Prototype.Browser.IE){
      html = html.replace(/\n/g, '<BR>new');
    }else{
      html = html.replace(/\n/g, '<br>new');
    }
    
    if (this.dummy.innerHTML != html){
      this.dummy.innerHTML = html;  
      var textareaHeight = this.textarea.getDimensions().height;
      var dummyHeight = this.dummy.getDimensions().height;
        
      if(this.max_height > 0 && (dummyHeight + this.line_height > this.max_height)){
        this.textarea.setStyle({overflow: 'auto'}); //, display: 'block'
        this.textarea.morph({ height: (this.max_height) + 'px' }, { duration: 0.1 });        
      }else{
        this.textarea.setStyle('overflow-y', 'hidden');
        if (this.min_height > 0 && (dummyHeight + this.line_height < this.min_height)){
          this.textarea.morph({ height: (this.min_height) + 'px' }, { duration: 0.1 }); 
        }else if(textareaHeight < dummyHeight + this.line_height || (dummyHeight < textareaHeight)){          
          this.textarea.morph({ height: (dummyHeight + this.line_height) + 'px' }, { duration: 0.1 }); 
        }
      }
    }
  }
});
