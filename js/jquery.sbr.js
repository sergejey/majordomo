/**
 * jQuery SELECT box replacement plugin
 * Simple replacement for generic SELECT boxes, allows CSS customization etc.
 * @name jquery.sbr.js
 * @author Ondrej Hudecek - http://www.houdasovo.cz
 * @version 0.1
 * @date August 10, 2009
 * @category jQuery plugin
 * @copyright (c) 2009 Ondrej Hudecek (houdasovo.cz)
 * @license 
 * @example Visit http://www.houdasovo.cz/skripty/jquery-sbr/ for demo
 */

(function($) {

  var sbWrapObj;
  var sbWrapInnerObj;
  var sbTitleObj;
  var sbListObj;

  var sbOpened;
  var sbLastOption;
  var sbOnChange;
  var sbCurSelected;
  var sbMultiple;
  var sbrLastVal;

  $.fn.disableTextSelect = function() {
    var sbObj = $(this);
    if($.browser.mozilla){
    	$(sbObj).css('MozUserSelect','none');
    }else if($.browser.msie){
    	$(sbObj).bind('selectstart',function(){return false;});
    }else{
    	$(sbObj).mousedown(function(){return false;});
    }
  }

  function _createSBRSimple(sbObj)
  {
    var offset = $(sbObj).offset();
    var width = $(sbObj).outerWidth();
    var height = $(sbObj).outerHeight();
  
  	$('body').append('<div class="sbrWrap ' + sbWrapObj.replace('.', '') + '"><p class="sbrTitle ' + sbTitleObj.replace('.', '') + '"><span></span></p></div>');
  	$(sbTitleObj).disableTextSelect();
  	
  	$(sbWrapObj).css('left', offset.left + 'px');
  	$(sbWrapObj).css('top', offset.top + 'px');
  	$(sbWrapObj).css('width', width + 'px');
  	$(sbWrapObj).css('height', height + 'px');
  
    var sbTitleSet = false;
    $(sbWrapObj).append('<div class="sbrWrapInner ' + sbWrapInnerObj.replace('.', '') + '"><ul class="sbrList ' + sbListObj.replace('.', '') + '"></ul></div>');
    $(sbObj).children('option').each(function() {
      var val = $(this).val();
      $(sbListObj).append('<li>' + $(this).html() + '</li>');
      if(val == sbCurSelected) {
        $(sbListObj + ' li:last').addClass('act');
        sbLastOption = $(sbListObj + ' li:last');
        $(sbTitleObj + ' span').html($(this).html());
        sbTitleSet = true;
      }
  	});
  
  	if(!sbTitleSet) $(sbListObj + ' span').html($(sbListObj + ' li:first').html());
  	$(sbTitleObj + ' span').css('line-height', height + 'px');
  
    $(document).click(function(){
      $(sbWrapInnerObj).css('display','none');
      sbOpened = false;
    });
  
  	$(sbTitleObj).click(function(e){
  	 if(sbOpened) {
  	   $(sbWrapInnerObj).css('display','none');
  	 } else {
  	   $(sbWrapInnerObj).css('display','block');
     } 
  	 sbOpened = !sbOpened;
     e.preventDefault();
     e.stopPropagation();    
  	});
  	
  	$(sbListObj + ' li').mouseover(function(){
  	 $(this).addClass('hover');
  	}).mouseout(function(){
  	 $(this).removeClass('hover');
  	});
  	
  	$(sbListObj + ' li').click(function(){
  	 if(sbLastOption) sbLastOption.removeClass('act'); 
  	 $(this).addClass('act');
  	 $(sbTitleObj + ' span').html($(this).html());
  	 $(sbObj).val($(this).html());
  	 if(sbOnChange) sbOnChange();
  	 sbLastOption = $(this);
     sbOpened = false;
  	});	
  
  } 
  
  function _createSBRMultiple(sbObj, sbObjName)
  {
    var width = $(sbObj).outerWidth();
    var height = $(sbObj).outerHeight();

  	$(sbObj).before('<div class="sbrWrapMultiple ' + sbWrapObj.replace('.', '') + '"></div>');
  	$(sbWrapObj).css('width', width + 16 + 'px');
  	$(sbWrapObj).css('height', height + 'px');
  
    $(sbWrapObj).append('<ul class="sbrListMultiple ' + sbListObj.replace('.', '') + '"></ul>');
    var nr = 0;
    $(sbObj).children('option').each(function() {
      var val = $(this).val();
      $(sbListObj).append('<li val="' + val + '">' + $(this).html() + ' <span><input type="checkbox" class="sbCBox" name="' + val + '" /></span></li>');
      $(sbListObj + ' li:last').disableTextSelect();
      $(sbListObj + ' li:last').attr('nr', nr);
      nr++;
      
      if($(this).attr('selected')) {
        $(sbListObj + ' li:last').addClass('act');
        $(sbListObj + ' li:last').find('.sbCBox').attr('checked', true);
      }

  	});
  
  	$(sbListObj + ' li').mouseover(function(){
  	 $(this).addClass('hover');
  	}).mouseout(function(){
  	 $(this).removeClass('hover');
  	});
  	
  	$(sbListObj + ' li').click(function(e){
  	   
     	 var curVal = $(this).attr('val');
       var curNr = $(this).attr('nr');

  	   if(e.shiftKey) {
  	   
  	      var nrStart,nrEnd;
  	      if(curNr >= sbrLastVal) {
  	        nrStart = sbrLastVal;
  	        nrEnd = curNr;
  	      } else {
  	        nrStart = curNr;
  	        nrEnd = sbrLastVal;
  	      }

            	       
          for(nr = nrStart; nr <= nrEnd; nr++) {
            $(sbObj).find('option:eq(' + nr + ')').attr('selected', 'selected');
       	    $(this).parent().find('li:eq(' + nr + ')').find('.sbCBox').attr('checked', true);
       	    $(this).parent().find('li:eq(' + nr + ')').addClass('act');
          }
  	   
  	   } else {
      	 if($(this).hasClass('act')) {
           $(sbObj).find('option[value="' + curVal + '"]').removeAttr('selected');
      	   $(this).find('.sbCBox').attr('checked', false);
      	   $(this).removeClass('act');
      	 } else {
           $(sbObj).find('option[value="' + curVal + '"]').attr('selected', 'selected');
      	   $(this).find('.sbCBox').attr('checked', true);
        	 $(this).addClass('act');
         }
       }

    	 if(sbOnChange) sbOnChange();
  	   sbrLastVal = curNr;
       sleep(250); 

  	});	

  } 
  
  $.fn.selectBoxReplacement = function() {
  
    var sbObj;
    var sbObjName;

    sbObj = $(this);
    sbObjName = $(sbObj).attr('name');

    sbWrapObj = '.sbWrap_'+sbObjName.replace('[]', '');
	sbWrapInnerObj = '.sbWrapInner_'+sbObjName.replace('[]', '');
	sbTitleObj = '.sbTitle_'+sbObjName.replace('[]', '');
	sbListObj = '.sbList_'+sbObjName.replace('[]', '');

	sbOpened = false;
	sbLastOption = false;
	sbOnChange = $(sbObj).attr('onchange');
	sbCurSelected = $(sbObj).val();
	sbMultiple = $(sbObj).attr('multiple');
	sbrLastVal = 0;
  
	$(sbObj).css('position', 'relative');
	$(sbObj).css('visibility', 'hidden');


	if(sbMultiple) {  	
	  _createSBRMultiple(sbObj);
	} else {
	  _createSBRSimple(sbObj);
	}
  }
  
})(jQuery);
