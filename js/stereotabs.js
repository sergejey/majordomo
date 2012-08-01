// stereotabs.js v1.0, Mon Jul 23 2007
//
// Copyright (c) 2007 Stereo :: Interactive & Design (http://blog.stereodevelopment.com)
// Author: Scott Meves 
//
// Inspired by:
//  accordion.js by Kevin P Miller | http://www.stickmanlabs.com
// 
// tabs.js freely distributable under the terms of an MIT-style license.

if (typeof Effect == 'undefined')
        throw("tabs.js requires including script.aculo.us' effects.js library!");
        
var tabset = Class.create();

tabset.prototype = {
  tabs:           [], 
  panels:         [],
  showPanel:     null,
  currentPanel:  null,
  
  initialize: function(container, options) {
    this.options = Object.extend({
      classNames: {
        tab:        'tab',
        panel:      'panel',
        tabActive:  'selected'
      },
      ids: {
        tab:        'tab_',
        panel:      'panel_'
      },
      onEvent:      'click',
      effects:      true
    }, options || {} );
    
    container = $(container);
    
    this.tabs = container.select('.'+this.options.classNames.tab);
    this.tabs.each(function(tab) {
      Event.observe(tab, this.options.onEvent, this.activate.bind(this, tab), false);
    }.bind(this));
    
    this.panels = container.select('.'+this.options.classNames.panel);
    this.panels.invoke('hide');
  },
  
  activate: function(tab) {
    var tabName = tab.id.replace(this.options.ids.tab,'');
    this.currentPanel = this.options.ids.panel+tabName;
    if (this.showPanel == this.currentPanel) {
      return false;
    }
    
    if (this.showPanel) {
      if (this.options.effects) {
        new Effect.Fade(this.showPanel, {queue: 'front'});
      } else {
        $(this.showPanel).hide();
      }
    }
    
    if (this.options.effects) {
      new Effect.Appear(this.currentPanel, {queue: 'end'});
    } else {
      $(this.currentPanel).show();
    }

    this.tabs.invoke('removeClassName', this.options.classNames.tabActive);
    tab.addClassName(this.options.classNames.tabActive);
    this.showPanel = this.currentPanel;
  },
  
  getHash: function() {
    var hash = window.location.hash;
    return hash.substring(1); // remove #
  },
  
  autoActivate: function(tab) {
    var canDoAuto = false;
    var hash = this.getHash();
    if (hash) {
      var autoTab = $(this.options.ids.tab+hash);
      if (autoTab) {
        this.activate(autoTab);
        canDoAuto = true;
      }
    }
    if (!canDoAuto && tab) {
      this.activate(tab);
    }
  }
}