/**
 * layout.js
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-09-24: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

Massiveart.BorderLayout = Ext.extend(Ext.layout.BorderLayout, {
  onLayout : function(ct, target){
        var collapsed;
        if(!this.rendered){
            target.position();
            target.addClass('x-border-layout-ct');
            var items = ct.items.items;
            collapsed = [];
            for(var i = 0, len = items.length; i < len; i++) {
                var c = items[i];
                var pos = c.region;
                if(c.collapsed){
                    collapsed.push(c);
                }
                c.collapsed = false;
                if(!c.rendered){
                    c.cls = c.cls ? c.cls +' x-border-panel' : 'x-border-panel';
                    c.render(target, i);
                }
                this[pos] = pos != 'center' && c.split ?
                    new Massiveart.BorderLayout.SplitRegion(this, c.initialConfig, pos) :
                    new Massiveart.BorderLayout.Region(this, c.initialConfig, pos);
                this[pos].render(target, c);
            }
            this.rendered = true;
        }

        var size = target.getViewSize();
        if(size.width < 20 || size.height < 20){
          if(collapsed){
              this.restoreCollapsed = collapsed;
          }
          return;
        }else if(this.restoreCollapsed){
          collapsed = this.restoreCollapsed;
          delete this.restoreCollapsed;
        }

        var w = size.width, h = size.height;
        var centerW = w, centerH = h, centerY = 0, centerX = 0;

        var n = this.north, s = this.south, west = this.west, e = this.east, c = this.center;
        if(!c){
            throw 'No center region defined in BorderLayout ' + ct.id;
        }

        if(n && n.isVisible()){
            var b = n.getSize();
            var m = n.getMargins();
            b.width = w - (m.left+m.right);
            b.x = m.left;
            b.y = m.top;
            centerY = b.height + b.y + m.bottom;
            centerH -= centerY;
            n.applyLayout(b);
        }
        if(s && s.isVisible()){
            var b = s.getSize();
            var m = s.getMargins();
            b.width = w - (m.left+m.right);
            b.x = m.left;
            var totalHeight = (b.height + m.top + m.bottom);
            b.y = h - totalHeight + m.top;
            centerH -= totalHeight;
            s.applyLayout(b);
        }
        if(west && west.isVisible()){
            var b = west.getSize();
            var m = west.getMargins();
            b.height = centerH - (m.top+m.bottom);
            b.x = m.left;
            b.y = centerY + m.top;
            var totalWidth = (b.width + m.left + m.right);
            centerX += totalWidth;
            centerW -= totalWidth;
            west.applyLayout(b);
        }
        if(e && e.isVisible()){
            var b = e.getSize();
            var m = e.getMargins();
            b.height = centerH - (m.top+m.bottom);
            var totalWidth = (b.width + m.left + m.right);
            b.x = w - totalWidth + m.left;
            b.y = centerY + m.top;
            centerW -= totalWidth;
            e.applyLayout(b);
        }

        var m = c.getMargins();
        var centerBox = {
            x: centerX + m.left,
            y: centerY + m.top,
            width: centerW - (m.left+m.right),
            height: centerH - (m.top+m.bottom)
        };
        c.applyLayout(centerBox);

        if(collapsed){
            for(var i = 0, len = collapsed.length; i < len; i++){
                collapsed[i].collapse(false);
            }
        }

        if(Ext.isIE && Ext.isStrict){
          target.repaint();
        }
    }
});

Ext.Container.LAYOUTS['ma_border'] = Massiveart.BorderLayout;

Massiveart.BorderLayout.Region  = Ext.extend(Ext.layout.BorderLayout.Region, {

});

Massiveart.BorderLayout.SplitRegion  = Ext.extend(Ext.layout.BorderLayout.SplitRegion, {

  onSplitMove : function(split, newSize){
    var s = this.panel.getSize();
    this.lastSplitSize = newSize;
    if(this.position == 'north' || this.position == 'south'){
        this.panel.setSize(s.width, newSize);
        this.state.height = newSize;
    }else{
        this.panel.setSize(newSize, s.height);
        this.state.width = newSize;
    }
    this.layout.layout();

    if(this.panel.id == 'navi-main-panel'){
      $('divNaviLeft').setStyle({height: (newSize-20) + 'px'});
      $('divNaviCenter').setStyle({height: (newSize-20) + 'px'});
      $('divNaviRight').setStyle({height: (newSize-20) + 'px'});
      if(Prototype.Browser.IE){
        $$('.navlevel').each(function(elDiv){
          $(elDiv).setStyle({height: (newSize-42) + 'px'});
        });
      } 
      else if(Prototype.Browser.WebKit){
        $$('.navlevel').each(function(elDiv){
          $(elDiv).setStyle({height: (newSize-40) + 'px'});
        });
      }     
    }

    this.panel.saveState();
    return false;
  },

  onCollapseClick : function(e){

    this.panel.collapse();

    if(this.panel.id == 'navi-main-panel'){
      $('navtopinner').hide();
      $('navtopbreadcrumb').show();
      
      if(myNavigation){
        myNavigation.updateBreadcrumb();
      }
    }
  },

  onExpandClick : function(e){
    if(this.isSlid){
      this.afterSlideIn();
      this.panel.expand(false);
    }else{
      this.panel.expand();

      if(this.panel.id == 'navi-main-panel'){
        $('navtopinner').show();
        $('navtopbreadcrumb').hide();
      }
    }
  }

});

Ext.onReady(function(){

  // NOTE: This is an example showing simple state management. During development,
  // it is generally best to disable state management as dynamically-generated ids
  // can change across page loads, leading to unpredictable results.  The developer
  // should ensure that stable state ids are set for stateful components in real apps.
  Ext.state.Manager.setProvider(new Ext.state.CookieProvider());

  // navigation area
  var mainnavi = {
    id: 'navi-main-panel',
    region: 'north',
    contentEl:'navigation-main',
    height: 200,
    minSize: 0,
    maxSize: 400,
    split: true,
    collapsible:false,
    collapseMode:'mini',
    animCollapse:false
  };

  // content area
  var maincontent = {
    id: 'content-edit',
    title: null,
    region:'center',
    contentEl:'content',
    split: true,
    collapsible:false,
    collapseMode:'mini'
  };

  // top navi
  var topnavi = {
    id: 'navitop-panel',
    region: 'north',
    contentEl:'navigation-top',
    height: 87,
    minSize: 77,
    maxSize: 87,
    split:false,
    collapsible: false
  };

  // main
  var main = {
    layout: 'ma_border',
  	id: 'content-panel',
    region:'center',
    hideBorders:true,
    split:true,
    collapsible:false,
    collapseMode:'mini',
    margins:'0 0 0 0',
	  items: [mainnavi, maincontent]
  };

  // output body
  var viewport = new Ext.Viewport({
    layout:'ma_border',
    hideBorders:true,
    items:[topnavi, main],
    renderTo: Ext.getBody()
  });

  // calculate height for navigation area
  var naviHeight = Ext.get('navi-main-panel').getHeight();
  if($('divNaviLeft')) $('divNaviLeft').setStyle({height: (naviHeight-20) + 'px'});
  if($('divNaviCenter')) $('divNaviCenter').setStyle({height: (naviHeight-20) + 'px'});
  if($('divNaviRight')) $('divNaviRight').setStyle({height: (naviHeight-20) + 'px'});

 /* Ext.get("navi-main-panel-xsplit").split.on('beforeapply', function() {
     alert($('navi-main-panel-xsplit').getHeight());
  });*/
});