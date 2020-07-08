{if $MENU_LOADED!='1'}

<script language="javascript">

 var valueChangedFlag = {};
 var latestRequest=0;
 var requestTimer=0;
 var requestDelay=3; // default delay for auto update
 var requestProcessing=0;
 var labelsCollected='';
 var valuesCollected='';
 var first_run=1;
 var initialLabels = '';

 var labelsCollected_sent='';
 var valuesCollected_sent='';

 var subscribedWebSockets=0;
 var subscribedWebSocketsTimer;


 var requestSent = Math.round(+new Date()/1000);


        function subscribeToMenu() {
         console.log('Sending menu subscription request...');
         var payload;
         payload = new Object();
         payload.action = 'Subscribe';
         payload.data = new Object();
         payload.data.TYPE='commands';
         payload.data.PARENT_ID='{$PARENT_ID}';
         wsSocket.send(JSON.stringify(payload));
         subscribedWebSocketsTimer=setTimeout('subscribeToMenu();', 3000);
         return false;
        }


        $.subscribe('wsConnected', function (_) {
         subscribeToMenu();
        });

        $.subscribe('wsData', function (_, response) {
          if (response.action=='subscribed') {
           console.log('Subscription to menu confirmed.');
           clearTimeout(subscribedWebSocketsTimer);
           subscribedWebSockets=1;
          }
          if (response.action=='commands') {
           console.log('Commands: '+response.data);
           sendRequestForUpdates_processed(0, response.data);
          }
        });


 function upateDebugField() {
  if (!$('#debugField').length) return;
  var timeUnix = Math.round(+new Date()/1000);
  var passed=timeUnix-requestSent;
  $('#debugField').html('Labels: '+labelsCollected+' (last sent: '+labelsCollected_sent+')<br/>Values: '+valuesCollected+' (last sent: '+valuesCollected_sent+')<br/>Passed: '+passed);
 }

 function sendRequestForUpdates_processed(id, data)
 {
   var obj = jQuery.parseJSON(data);
   var objLabelsCnt  = obj.LABELS.length;
   var objValuesCnt = obj.VALUES.length;


   if (objLabelsCnt > 0) {
    var labels=obj.LABELS;
    for (var i = 0; i < objLabelsCnt; i++) {
     try {
       if (labels[i].ID == '360') {
        //alert(JSON.stringify(labels[i]));
       }
       window["updateLabel"+labels[i].ID+"_Ready"](labels[i].ID, JSON.stringify(labels[i]));
     }
     catch(err) {
        // Handle error(s) here
     }
    }
  }
   if (objValuesCnt > 0) {
    var values=obj.VALUES;
    for (var i = 0; i < objValuesCnt; i++) {
     //alert("updateValue"+values[i].ID+"_Ready ("+i+" of "+values.length+")");
     try {
       window["updateValue"+values[i].ID+"_Ready"](values[i].ID, JSON.stringify(values[i]));
     }
     catch(err) {
        // Handle error(s) here
     }
    }
  }
  requestProcessing=0;
 }

 function sendRequestForUpdates() {
  clearTimeout(requestTimer);
  if (requestProcessing==1) {
   requestTimer=setTimeout('sendRequestForUpdates()', requestDelay*1000);
  }
  requestSent = Math.round(+new Date()/1000);

  if (subscribedWebSockets==1) {
   labelsCollected='';
   valuesCollected='';
   if (first_run!=1) return;
  }

  first_run=0;
  if (labelsCollected!='' || valuesCollected!='') {
   var url="{$smarty.const.ROOTHTML}ajax/commands.html?op=get_details&labels="+encodeURIComponent(labelsCollected)+"&values="+encodeURIComponent(valuesCollected);
   AJAXRequest(url, 'sendRequestForUpdates_processed', '');
  }

  labelsCollected_sent=labelsCollected;
  labelsCollected='';
  valuesCollected_sent=valuesCollected;
  valuesCollected='';
  upateDebugField();


 }

 function collectLabel(item_id) {
  var timeUnix = Math.round(+new Date()/1000);
  labelsCollected=labelsCollected+','+item_id+',';
  labelsCollected=labelsCollected.replace(',,', ',');
  upateDebugField();
  if ((timeUnix-requestSent)>requestDelay) {
   sendRequestForUpdates();
  } else {
   clearTimeout(requestTimer);
   requestTimer=setTimeout('sendRequestForUpdates()', requestDelay*1000);
  }
 }

 function collectValue(item_id) {
  var timeUnix = Math.round(+new Date()/1000);
  valuesCollected=valuesCollected+','+item_id+',';
  valuesCollected=valuesCollected.replace(',,', ',');
  upateDebugField();
  if ((timeUnix-requestSent)>requestDelay) {
   sendRequestForUpdates();
  } else {
   clearTimeout(requestTimer);
   requestTimer=setTimeout('sendRequestForUpdates()', requestDelay*1000);
  }
 }


 function menuClicked(id, parent_id, sub_list, win, winTitle, command, url, width, height) {

  if (sub_list!=id) {
   //$('sub'+parent_id).style.display='none';
   if ($('sub'+id).style.display=='none') {
    //$('sub'+id).style.display='block';
    Effect.BlindDown('sub'+id, { duration: 0.5 });
   } else {
    //Effect.SwitchOff('sub'+id');
    Effect.BlindUp('sub'+id, { duration: 0.5 });
    //$('sub'+id).style.display='none';
   }
  } else {

  if (parent.location == window.location) {
   if (url!='') {
    window.location=url;
    //openTWindow(win, winTitle, url, width, height);
   }
  } else {
   //command


   var location_string=parent.location.href;

   if ((/popup\/scenes/.test(location_string)) || (/pages\.html/.test(location_string))) {
    window.location=url;
    return false;
   }


   if (command!='') {
    //alert(command);
    parent.eval(command);
   }
   if (url!='') {
    if (width==0 && height==0) {
     parent.openNewTab(winTitle, url);
    } else {
     parent.openTWindow(win, winTitle, url, width, height);
    }
   }

  }

  }
  return false;
 }

 function itemValueChangedProcessed(data, v) {
  //alert(data);
  if ($('#processing_'+data).length) {
   $('#processing_'+data).html('<span class="opConfirm"> - OK</span>');
   setTimeout("$('#processing_"+data+"').html('')",1500);
  }
  return false;
 }

 function itemValueChanged(id, new_value) {
  //alert(id+': '+new_value);
  valuesCollected=valuesCollected.replace(','+id+',', ',');
  var url="{$smarty.const.ROOTHTML}ajax/commands.html?op=value_changed";
  if ($('#processing_'+id).length) {
   $('#processing_'+id).html(' - ...');
  }
  AJAXRequest(url+'&item_id='+id+'&new_value='+encodeURIComponent(new_value), 'itemValueChangedProcessed', id);
  return false;
 }

</script>
{/if}


        <div id="home" class="current">
{if $ONE_ITEM_MODE!='1'}
{if $PARENT_TITLE!=''}
            <div data-role="header"{if $smarty.const.SETTINGS_THEME=="dark"} data-theme="b"{/if}>
{if $IFRAME_MODE==''}
                <h1 id="label_{$PARENT_ID}">{if $PARENT_TITLE!=''}{$PARENT_TITLE}{else}MajorDoMo{/if}</h1>
                {if $PARENT_ID!=''}
                <a class="back" href="{if $PARENT_PARENT_ID!='0'}{$smarty.const.ROOTHTML}menu/{$PARENT_PARENT_ID}{else}{$smarty.const.ROOTHTML}menu{/if}.html">{$smarty.const.LANG_BACK}</a>
                {/if}
                {*
                <a class="button slideup" id="infoButton" href="#about">info</a>
                *}
{/if}
            </div>
{/if}


{if $PARENT_ID!=''}
{if $PARENT_AUTO_UPDATE!='0'}
<script language="javascript">
 var label{$PARENT_ID}_timer;
 function updateLabel{$PARENT_ID}_Ready(id, data) {
  var elem=document.getElementById('label_{$PARENT_ID}');
  var obj=jQuery.parseJSON(data);
  if (obj.DATA!='') {
   elem.innerHTML=obj.DATA;
  }
  return false;
 }
 function updateLabel{$PARENT_ID}() {
  clearTimeout(label{$PARENT_ID}_timer);
  var url="{$smarty.const.ROOTHTML}ajax/commands.html?op=get_label";
  AJAXRequest(url+'&item_id={$PARENT_ID}', 'updateLabel{$PARENT_ID}_Ready', '');
  label{$PARENT_ID}_timer=setTimeout('updateLabel{$PARENT_ID}()', ({$PARENT_AUTO_UPDATE}*1000));
  return false;
 }
 label{$PARENT_ID}_timer=setTimeout('updateLabel{$PARENT_ID}()', ({$PARENT_AUTO_UPDATE}*1000));
</script>
{/if}
{/if}

{/if}

<div data-role="content"{if $FROM_SCENE==1} style='margin:0px;padding:0px;'{/if}>


<!--
<li>
<div id="debugField" style="white-space:normal;">...</div>
</li>
-->


{if $RESULT}

<!-- search results (list) -->
{if $ONE_ITEM_MODE!='1'}
<ul data-role="listview" data-inset="true">
{/if}


{if $RESULT_HTML!=''}{$RESULT_HTML}{/if}

{function name=menu}

{foreach $items as $item}
{if $item.SUB_PRELOAD=='1'}
 <div data-role="collapsible" data-iconpos="right">
  <h2>{if $item.ICON!=''}<img src="{$smarty.const.ROOTHTML}cms/icons/{$item.ICON}" alt="" class="item_icon">{/if}<span id="label_{$item.ID}">{$item.TITLE}</span></h2>
  <!--  -->
  <ul data-role="listview" data-inset="true">
  {if $item.RESULT}
  {menu items=$item.RESULT}
  {/if}
  </ul>
 </div>
{else}
{if $item.TYPE=='' || $item.TYPE=='command' || $item.TYPE=='window' || $item.TYPE=='url'}
<li{if $item.VISIBLE_DELAY!='0'} class='visible_delay'{/if} id='item{$item.ID}'>
 <a
         {if !$item.RESULT_TOTAL}
          href="#"
          onClick="return menuClicked('{$item.ID}', '{$item.PARENT_ID}', '{$item.SUB_LIST}', '{$item.WINDOW}', '{$item.TITLE_SAFE}', '{$item.COMMAND}', '{$item.URL}'{if $item.TYPE=='window'}, '{$item.WIDTH}', '{$item.HEIGHT}'{else},0,0{/if});"
         {else}
          href="{$smarty.const.ROOTHTML}menu/{$item.ID}.html"
         {/if}
         {if $item.SUB_PRELOAD=='1'} onClick="$('#sublist{$item.ID}').toggle();return false;"{/if}
 >
<h2>{if $item.ICON!=''}<img src="{$smarty.const.ROOTHTML}cms/icons/{$item.ICON}" alt="" class="ui-li-icon item_icon" style="margin-bottom:0px;margin-top:0px;">{/if}
 <span id="label_{$item.ID}">{$item.TITLE}</span></h2>{*{if $item.RESULT_TOTAL} <span class="ui-li-count">{$item.RESULT_TOTAL}</span>{/if}*}</a>
</li>
{/if}

{if $item.TYPE=='urlblank'}
<li{if $item.VISIBLE_DELAY!='0'} class='visible_delay'{/if} id='item{$item.ID}'>
<a
 {if !$item.RESULT_TOTAL}
 href="{$item.URL}" target=_blank
 {else}
 href="{$smarty.const.ROOTHTML}menu/{$item.ID}.html"
 {/if}
>
{if $item.ICON!=''}<span><img src="{$smarty.const.ROOTHTML}cms/icons/{$item.ICON}" alt="" class="ui-li-icon item_icon"></span>{/if}
<span id="label_{$item.ID}">{$item.TITLE}</span></a>
</li>

{/if}

{/if}




{if $item.TYPE=='switch'}
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>

<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}() {
  if (valueChangedFlag['item{$item.ID}']==1) {
   valueChangedFlag['item{$item.ID}']=0;
   return;
  }
  var elem=document.getElementById('menu{$item.ID}_v');
  itemValueChanged("{$item.ID}", elem.value);
  return false;
 }
</script>


        <label for="menu{$item.ID}_v"><span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span></label>
        <select name="menu{$item.ID}_v" id="menu{$item.ID}_v" data-role="slider"  onChange="changedValue{$item.ID}();">
                <option value="{$item.OFF_VALUE}"{if $item.CUR_VALUE!=$item.ON_VALUE} selected{/if}>{$smarty.const.LANG_OFF}</option>
                <option value="{$item.ON_VALUE}"{if $item.CUR_VALUE==$item.ON_VALUE} selected{/if}>{$smarty.const.LANG_ON}</option>
        </select> 
</li>
{/if}

{if $item.TYPE=='custom'}
<li {if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<div id="label_{$item.ID}" style="white-space:normal">{$item.DATA}</div>
</li>
{/if}

{if $item.TYPE=='object'}
 <li {if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
  <div id="label_{$item.ID}" style="white-space:normal">{$item.DATA}</div>
 </li>
{/if}

{if $item.TYPE=='selectbox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}() {
  if (valueChangedFlag['item{$item.ID}']==1) {
   valueChangedFlag['item{$item.ID}']=0;
   return;
  }
  var elem=document.getElementById('menu{$item.ID}_v');
  itemValueChanged("{$item.ID}", elem.value);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<label for="menu{$item.ID}_v" class="select"><span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span></label>

<select name="menu{$item.ID}_v" id="menu{$item.ID}_v" onChange="changedValue{$item.ID}();">
 {foreach $item.OPTIONS as $i}
 <option value="{$i.VALUE}"{if $i.SELECTED} selected{/if}>{$i.TITLE}
 {/foreach}
</select>
</li>
{/if}

{if $item.TYPE=='radiobox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}(new_value) {
  if (valueChangedFlag['item{$item.ID}']==1) {
   valueChangedFlag['item{$item.ID}']=0;
   return;
  }
  //var elem=document.getElementById('menu{$item.ID}_v');
  itemValueChanged("{$item.ID}", new_value);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<label for="menu{$item.ID}_v" class="select"><span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span></label>


    <fieldset data-role="controlgroup" data-type="horizontal">
     {foreach $item.OPTIONS as $i}
     <input type="radio" name="menu{$item.ID}_v" class="radiobox{$item.ID}" id="menu{$item.ID}_v_{$i.NUM}" value="{$i.VALUE}" {if $i.SELECTED}checked="checked"{/if}  onClick="changedValue{$item.ID}('{$i.VALUE}');"/>
     <label for="menu{$item.ID}_v_{$i.NUM}">{$i.TITLE}</label>
      {/foreach}
    </fieldset>

{*
<select name="menu{$item.ID}_v" id="menu{$item.ID}_v" onChange="changedValue{$item.ID}();">
 {foreach $item.OPTIONS as $i}
 <option value="{$i.VALUE}"{if $i.SELECTED} selected{/if}>{$i.TITLE}
 {/foreach}
</select>
*}
</li>
{/if}



{if $item.TYPE=='timebox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}() {
  if (valueChangedFlag['item{$item.ID}']==1) {
   valueChangedFlag['item{$item.ID}']=0;
   return;
  }
  clearTimeout(item{$item.ID}_timer);
  var elem1=document.getElementById('menu{$item.ID}_v1');
  var elem2=document.getElementById('menu{$item.ID}_v2');
  item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem1.value+':'+elem2.value+'")', 500);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<label for="menu{$item.ID}_v" class="select"><span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span></label>
<fieldset data-role="controlgroup" data-type="horizontal"> 
<select name="menu{$item.ID}_v1" id="menu{$item.ID}_v1" onChange="changedValue{$item.ID}();">
 {foreach $item.OPTIONS1 as $i}
  <option value="{$i.VALUE}"{if $i.SELECTED=='1'} selected{/if}>{$i.VALUE}
 {/foreach}
</select>
<select name="menu{$item.ID}_v2" id="menu{$item.ID}_v2" onChange="changedValue{$item.ID}();">
 {foreach $item.OPTIONS2 as $i}
 <option value="{$i.VALUE}"{if $i.SELECTED=='1'} selected{/if}>{$i.VALUE}
 {/foreach}
</select>
</fieldset>
</li>
{/if}


{if $item.TYPE=='datebox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}_delay() {
  clearTimeout(item{$item.ID}_timer);
  var elem=document.getElementById('menu{$item.ID}_v');
  item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 500);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span>

<div data-inline="true" data-role="fieldcontain">
 <input type="date" id="menu{$item.ID}_v" name="menu{$item.ID}_value" value="{$item.CUR_VALUE}" data-inline="true" onChange="changedValue{$item.ID}_delay()" onKeyUp="changedValue{$item.ID}_delay();">
</div>
</li>
{/if}


{if $item.TYPE=='plusminus'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function increaseValue{$item.ID}() {
  var elem=document.getElementById('menu{$item.ID}_v');
  var elem2=document.getElementById('menu{$item.ID}_vv');
  var v=parseFloat(elem.value);
  if ((v+{$item.STEP_VALUE})<={$item.MAX_VALUE}) {
   var resultV = v+{$item.STEP_VALUE};
   elem.value = parseFloat(resultV.toFixed(4));
   elem2.innerHTML=elem.value;
   clearTimeout(item{$item.ID}_timer);
   item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 500);
  }
  return false;
 }
 function decreaseValue{$item.ID}() {
  var elem=document.getElementById('menu{$item.ID}_v');
  var elem2=document.getElementById('menu{$item.ID}_vv');
  var v=parseFloat(elem.value);
  if ((v-{$item.STEP_VALUE})>={$item.MIN_VALUE}) {
   var resultV = v-{$item.STEP_VALUE};
   elem.value = parseFloat(resultV.toFixed(4));
   elem2.innerHTML=elem.value;
   clearTimeout(item{$item.ID}_timer);
   item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 500);
  }
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span>

<div data-inline="true" data-role="fieldcontain">
 <a href="#" data-role="button" onClick="return decreaseValue{$item.ID}();" data-inline="true">-</a>
 <span style="margin-left:10px;margin-right:10px" id="menu{$item.ID}_vv">{$item.CUR_VALUE}</span>
 <a href="#" data-role="button" onClick="return increaseValue{$item.ID}();" data-inline="true">+</a>
 <div style="display:none">
 <input type="text" id="menu{$item.ID}_v" name="menu{$item.ID}_value" value="{$item.CUR_VALUE}" size="5">
 </div>
</div>
</li>
{/if}

{if $item.TYPE=='sliderbox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}() {
  if (valueChangedFlag['item{$item.ID}']==1) {
   valueChangedFlag['item{$item.ID}']=0;
   return;
  }
  clearTimeout(item{$item.ID}_timer);
  var elem=document.getElementById('menu{$item.ID}_v');
  item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 500);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span>

<div data-inline="true" data-role="fieldcontain">
 <input type="range" id="menu{$item.ID}_v" data-inline="true" name="menu{$item.ID}_value" value="{$item.CUR_VALUE}" min="{$item.MIN_VALUE}" max="{$item.MAX_VALUE}" step="{$item.STEP_VALUE}"  onChange="changedValue{$item.ID}();"/>
</div>
</li>
{/if}


{if $item.TYPE=='textbox'}
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}_delay() {
  clearTimeout(item{$item.ID}_timer);
  var elem=document.getElementById('menu{$item.ID}_v');
  item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 5000);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span>

<div data-inline="true" data-role="fieldcontain">
 <input type="text" id="menu{$item.ID}_v" name="menu{$item.ID}_value" value="{$item.CUR_VALUE}" data-inline="true" onChange="changedValue{$item.ID}_delay()" onKeyUp="changedValue{$item.ID}_delay();">
</div>
</li>
{/if}

{if $item.TYPE=='color'}
<script src='{$smarty.const.ROOTHTML}3rdparty/spectrum/spectrum.min.js'></script>
<link rel='stylesheet' href='{$smarty.const.ROOTHTML}3rdparty/spectrum/spectrum.min.css' />
<script language="javascript">
 var item{$item.ID}_timer=0;
 function changedValue{$item.ID}_delay() {
  clearTimeout(item{$item.ID}_timer);
  var elem=document.getElementById('menu{$item.ID}_v');
  item{$item.ID}_timer=setTimeout('itemValueChanged("{$item.ID}", "'+elem.value+'")', 500);
  return false;
 }
</script>
<li data-role="fieldcontain"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
<span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span>

<div data-inline="true" data-role="fieldcontain">
 <input type="text" id="menu{$item.ID}_v" name="menu{$item.ID}_value" value="{$item.CUR_VALUE}" data-inline="true" onChange="changedValue{$item.ID}_delay()" onKeyUp="changedValue{$item.ID}_delay();">
</div>
</li>
<script>
 $("#menu{$item.ID}_v").spectrum({
  preferredFormat: "hex",
  showInput: true,
  chooseText: "OK",
  cancelText: "{$smarty.const.LANG_CANCEL}"
 });
</script>
{/if}


{if $item.TYPE=='label'}
<li data-role="list-divider"{if $item.VISIBLE_DELAY!='0'}  class='visible_delay'{/if} id='item{$item.ID}'>
{if $item.ICON!=''}<span><img src="{$smarty.const.ROOTHTML}cms/icons/{$item.ICON}" border="0" class="ui-icon item_icon"></span>{/if}
<span id="label_{$item.ID}">{$item.TITLE}</span>
</li>
{/if}

{if $item.TYPE=='button'}
<a href="#" onClick="return itemValueChanged('{$item.ID}', 'clicked');" data-role="button" {if $item.INLINE=='1'}data-inline="true"{/if}><span id="label_{$item.ID}">{$item.TITLE}</span><span id="processing_{$item.ID}"></span></a>
{*
<table border="0" cellspacing="0" cellpadding="0" width="100%">
 <tr>
  <td><img src="{$smarty.const.ROOTHTML}img/blackBtn_left.png" border="0"></td>
  {if $item.ICON!=''}
  <td background="{$smarty.const.ROOTHTML}img/blackBtn_center.png"><img src="{$smarty.const.ROOTHTML}cms/icons/{$item.ICON}" border="0" style="float:left;margin-right:10px;padding:0px"></td>
  {/if}
  <td  width="100%" background="{$smarty.const.ROOTHTML}img/blackBtn_center.png">
  <a href="#" onClick="return itemValueChanged('{$item.ID}', 'clicked');" style="font-weight:bold" id="label_{$item.ID}">{$item.TITLE}</a></td>
  <td><img src="{$smarty.const.ROOTHTML}img/blackBtn_right.png" border="0"></td>
 </tr>
</table>
*}
{/if}


<script language="javascript">
  valueChangedFlag['item{$item.ID}']=0;
</script>

<script language="javascript">
 var label{$item.ID}_timer;
 function updateLabel{$item.ID}_Ready(id, data) {
  var obj=jQuery.parseJSON(data);
  if (obj.DATA!='') {
   $('#label_{$item.ID}').html(obj.DATA);
   $('#label_{$item.ID}').trigger( "create" );
  }
  return false;
 }

 function updateValue{$item.ID}_Ready(id, data) {

 var obj=jQuery.parseJSON(data);
 if (typeof obj.DATA != 'undefined') {
  data=obj.DATA;
  {if $item.TYPE=='textbox'}
   if ($('#menu{$item.ID}_v').val()!=data) {
    $('#menu{$item.ID}_v').val(data);
   }
  {/if}
  {if $item.TYPE=='color'}
   if ($('#menu{$item.ID}_v').val()!=data) {
    $("#menu{$item.ID}_v").spectrum("set", data);
   }
  {/if}

  {if $item.TYPE=='selectbox'}
   if ($('#menu{$item.ID}_v').val()!=data) {
    $('#menu{$item.ID}_v').val(data);
    $('#menu{$item.ID}_v').selectmenu("refresh");
   }
  {/if}

  {if $item.TYPE=='timebox'}
  var dataList = data.split(':');
  var data1=dataList[0];
  var data2=dataList[1];
  if ($('#menu{$item.ID}_v1').val()!=data1) {
   $('#menu{$item.ID}_v1').val(data1);
   $('#menu{$item.ID}_v1').selectmenu("refresh");
  }
  if ($('#menu{$item.ID}_v2').val()!=data2) {
   $('#menu{$item.ID}_v2').val(data2);
   $('#menu{$item.ID}_v2').selectmenu("refresh");
  }
  {/if}


  {if $item.TYPE=='plusminus'}
  $('#menu{$item.ID}_vv').html(data);
  {/if}

  {if $item.TYPE=='radiobox'}
   var $selected = $('.radiobox{$item.ID}:checked');
   if (!$selected.length || $selected.val()!=data) {
    $( ".radiobox{$item.ID}" ).each(function( index ) {
     if ($( this ).val()!=data) {
      //alert('not found: '+$( this ).val()+' != '+data)
      $( this ).prop('checked', false).checkboxradio("refresh");
     } else {
      //alert('found: '+$( this ).val())
      $( this ).prop('checked', true).checkboxradio("refresh");
     }
    });
   }
  {/if}


  {if $item.TYPE=='switch'}
   //alert('{$item.TITLE}'+"\nValue:"+$('#menu{$item.ID}_v').val()+"\nData:"+data);
   if ($('#menu{$item.ID}_v').val()!=data) {
    if (data=='{$item.ON_VALUE}') {
     $('#menu{$item.ID}_v').val('{$item.ON_VALUE}');
    } else {
     $('#menu{$item.ID}_v').val('{$item.OFF_VALUE}');
    }
    $('#menu{$item.ID}_v').slider('refresh');
   }
  {/if}

  {if $item.TYPE=='sliderbox'}
   if ($('#menu{$item.ID}_v').val()!=data) {
    $('#menu{$item.ID}_v').val(data);
    valueChangedFlag['item{$item.ID}']=1;
    $('#menu{$item.ID}_v').slider('refresh');
   }
  {/if}
  }
  return false;
 }


 function updateLabel{$item.ID}() {
  clearTimeout(label{$item.ID}_timer);
  collectLabel('{$item.ID}');
  {if $item.TYPE=='switch' || $item.TYPE=='textbox' || $item.TYPE=='sliderbox' || $item.TYPE=='selectbox' || $item.TYPE=='radiobox'}
  collectValue('{$item.ID}');
  {/if}
  {if $item.AUTO_UPDATE!='0'}
  label{$item.ID}_timer=setTimeout('updateLabel{$item.ID}()', ({$item.AUTO_UPDATE}*1000));
  return false;
  {/if}
 }

 {if $item.AUTO_UPDATE!='0'}
 label{$item.ID}_timer=setTimeout('updateLabel{$item.ID}()', (1000));
 initialLabels = initialLabels + ',{$item.ID}';
 {/if}

</script>

{/foreach}
{/function}

{menu items=$RESULT}

{if $ONE_ITEM_MODE!='1'}</ul>{/if}
<!-- / search results (list) -->
{else}
<p>
<font color="red">{$smarty.const.LANG_NO_RECORDS_FOUND}</font>
</p>
{/if}

</div>

<script type="text/javascript">
 /*
 var url="{$smarty.const.ROOTHTML}ajax/commands.html";
 $.ajax({
  url: url,
  type: "POST",
  data: {
   op: 'get_details',
   labels: initialLabels,
   values: initialLabels
  }
 }).done(function(data) {
  sendRequestForUpdates_processed(0,data);
 });
 */
</script>

{if $VISIBLE_DELAYS}
<script language="javascript">
 var vd_timer;
 var currentItem=0;
 var currentNum=0;
 function visible_delay_carusel() {
  clearTimeout(vd_timer);
  var delay=10000;
  var i=0;

  if (currentItem!=0) {
   $('#item'+currentItem).hide();
  }
  {foreach $RESULT as $i}
   {if $i.VISIBLE_DELAY!='0'}
    if (currentNum==i) {
     currentItem={$i.ID};
     delay={$i.VISIBLE_DELAY}*1000;
    }
    i++;
   {/if}
  {/foreach}

  currentNum++;
  if (currentNum>={$item.VISIBLE_DELAYS}) {
   currentNum=0;
  }
  if (currentItem!=0) {
   $('#item'+currentItem).show();
  }
  vd_timer=setTimeout('visible_delay_carusel();', delay)
  return false;
 }

 $(document ).bind("pageinit", function( event, data ){
    $('.visible_delay').hide();
    visible_delay_carusel();
});
</script>
{/if}

<script type="text/javascript">
 //update all labels and values
</script>