{if isset($DRAGGABLE)}
    <link rel="stylesheet" href="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.contextMenu.min.css">
    <script type="text/javascript" src="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.contextMenu.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.ui.position.min.js"></script>
    <style>
        .draggable {
            border:1px solid blue !important;
        }
        div.draggable span {
            pointer-events:none;
        }
    </style>
{/if}

<style>
@keyframes lefttoright  {
  0% { transform: translateX(-500px); }
  45% { transform: translateX(40px); }
  65% { transform: translateX(-10px); }
  100% { transform: translateX(0px); }
}

@keyframes righttoleft {
  0% { transform: translateX(500px); }
  45% { transform: translateX(-40px); }
  65% { transform: translateX(10px); }
  100% { transform: translateX(0px); }
}

@keyframes bottomtotop {
  0% { transform: translateY(500px); }
  45% { transform: translateY(-40px); }
  65% { transform: translateY(10px); }
 100% { transform: translateY(0px); }
}
@keyframes toptobottom {
  0% { transform: translateY(-500px); }
  45% { transform: translateY(40px); }
  65% { transform: translateY(-10px); }
 100% { transform: translateY(0px); }
}
@keyframes blink {
  0%   { opacity: 0; }
  100% { opacity: 1; }
}
@keyframes scale {
  0%   { transform: scale(0.5); }
  25%  { transform: scale(1.1); }
  45%  { transform: scale(0.9); }
  100% { transform: scale(1); }
}


{foreach $RESULT as $SCENE}
{foreach $SCENE.ALL_ELEMENTS as $ELEMENT}
{if $ELEMENT.TYPE!='slider'}
{if $ELEMENT.APPEAR_ANIMATION=='1'}
.element_{$ELEMENT.ID} { animation: lefttoright 1s ease-out; }
{/if}
{if $ELEMENT.APPEAR_ANIMATION=='2'}
.element_{$ELEMENT.ID} { animation: righttoleft 1s ease-out; }
{/if}
{if $ELEMENT.APPEAR_ANIMATION=='3'}
.element_{$ELEMENT.ID} { animation: toptobottom 1s ease-out; }
{/if}
{if $ELEMENT.APPEAR_ANIMATION=='4'}
.element_{$ELEMENT.ID} { animation: bottomtotop 1s ease-out; }
{/if}
{if $ELEMENT.APPEAR_ANIMATION=='5'}
.element_{$ELEMENT.ID} { animation: blink 0.5s ease-out; }
{/if}
{if $ELEMENT.APPEAR_ANIMATION=='6'}
.element_{$ELEMENT.ID} { animation: scale 0.5s ease-out; }
{/if}
{/if}
{/foreach}
{/foreach}


.container_background {
 border:1px solid rgba(0,0,0,0.2);
 background-color:rgba(0,0,0,0.5);
 padding:0px;
}

.html_background {
 border:1px solid rgba(0,0,0,0.2);
 background-color:rgba(0,0,0,0.5);
 background-size:100%;
 padding:0px;
}

{foreach $ALL_TYPES as $TYPE}
 {if isset($TYPE.HAS_STYLE) && $TYPE.HAS_STYLE!=""}{include file="../../cms/scenes/styles/{$TYPE.TITLE}/style.css.tpl"}{/if}
{/foreach}
</style>

{if $smarty.const.SETTINGS_SCENES_CLICKSOUND!=""}
<div style="display:none">
<audio id="click_sound" src="{$smarty.const.SETTINGS_SCENES_CLICKSOUND}" controls preload="auto" autobuffer>
</audio>
<script type="text/javascript">
    var thissound=document.getElementById('click_sound');
    thissound.play();
    setTimeout(thissound.pause.bind(thissound), 10);
</script>
</div>
{/if}
        {if $TOTAL_SCENES!="1"}
            <style>{include './slider.css'}</style>
            <script type="text/javascript" src="{$smarty.const.ROOTHTML}js/easySlider1.7.js?v=2019-02-27"></script>
        {/if}

        <script type="text/javascript" language="javascript">

        var codeHash=new Object();
        var firstRun=1;
        var refreshRun=0;
        var checkTimer;
        var refreshTimer;
        var noUpdatesTimer;
        var usingWebsockets=0;
        var ignoreClick=0;
        var contextTimeout=0;
        var contextTop='';
        var contextLeft='';
        var subscribedWebSockets=0;
        var subscribedWebSocketsTimer;

        function EvalSound(soundobj) {
         var thissound=document.getElementById(soundobj);
         thissound.play();
        }

        function switchScene(scene_id) {
         {foreach $RESULT as $SCENE}
         if (scene_id=='{$SCENE.ID}') {
           {if $smarty.const.SETTINGS_SCENES_VERTICAL_NAV=="1"}
            $('#controls_vertical{$SCENE.NUMP} a').click();
           {else}
            $('#controls{$SCENE.NUMP} a').click();
           {/if}
         }
         {/foreach}
         return false;
        }

        function controlWindowPositionChanged(id) {
         return false;
        }


        {foreach $RESULT as $SCENE}
        {foreach $SCENE.ALL_ELEMENTS as $ELEMENT}
        {foreach $ELEMENT.STATES as $STATE}
        {if $STATE.MENU_ITEM_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!=""}
         var window{$STATE.ID}_width={$STATE.WINDOW_WIDTH};
         var window{$STATE.ID}_height={$STATE.WINDOW_HEIGHT};
         var window{$STATE.ID}_posx={$STATE.WINDOW_POSX};
         var window{$STATE.ID}_posy={$STATE.WINDOW_POSY};
        {/if}
        {/foreach}
        {/foreach}
        {/foreach}



        function stateClickedEdit(id) {
          if (ignoreClick==1) return false;
          var window_url = '{$smarty.const.ROOTHTML}panel/scene/{$SCENE_ID}.html?open='+id+'&print=1'
          if (id=='new') {
           window_url=window_url+'&top='+contextTop+'&left='+contextLeft;
          }
          if ( window.location !== window.parent.location ) {
              parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.parent.location.reload(); }});
          } else {
              parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.location.reload();}});
          }
          return false;
        }

            function addWidgetClicked(id) {
                var window_url = '{$smarty.const.ROOTHTML}panel/scene/{$SCENE_ID}.html?tab=widgets&print=1'
                window_url=window_url+'&top='+contextTop+'&left='+contextLeft;
                parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.location.reload(); }});
                return false;
            }

            function addDeviceClicked(id) {
                var window_url = '{$smarty.const.ROOTHTML}panel/scene/{$SCENE_ID}.html?tab=devices&open='+id+'&print=1'
                window_url=window_url+'&top='+contextTop+'&left='+contextLeft;
                parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.location.reload(); }});
                return false;
            }



        function stateClicked(id) {

         var window_url;

            {if $smarty.const.SETTINGS_SCENES_CLICKSOUND!=""}
            setTimeout("EvalSound('click_sound')",10);
            {/if}

         $('#state_'+id).animate({ opacity: .5 }, 100).animate({ opacity: 1 }, 100);

        {foreach $RESULT as $SCENE}
        {foreach $SCENE.ALL_ELEMENTS as $ELEMENT}
        {foreach $ELEMENT.STATES as $STATE}


            {if $ELEMENT.TYPE=="img"}
            if (id=='{$STATE.ID}') {
                $('#state_{$STATE.ID}').hide();
                setTimeout("$('#state_{$STATE.ID}').show();", 150);
            }
            {/if}

         {if $ELEMENT.TYPE=="button"}
         if (id=='{$STATE.ID}') {
          $('#state_{$STATE.ID}').addClass('clicked');
          setTimeout("$('#state_{$STATE.ID}').removeClass('clicked');", 150);
         }
         {/if}


         {if $STATE.MENU_ITEM_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!=""}
          {if $STATE.MENU_ITEM_ID!="0"}
          window_url='/menu.html?parent={$STATE.MENU_ITEM_ID}';
          {/if}
          {if $STATE.HOMEPAGE_ID!="0"}
          window_url='{$smarty.const.ROOTHTML}page/{$STATE.HOMEPAGE_ID}.html';
          {/if}
          {if $STATE.OPEN_SCENE_ID!="0"}
          window_url='{$smarty.const.ROOTHTML}popup/scenes/{$STATE.OPEN_SCENE_ID}.html';
          {/if}
          {if $STATE.EXT_URL!=""}
          window_url='{$STATE.EXT_URL}';
          {/if}
         {/if}

         {if $ELEMENT.TYPE=="navgo"}
         if (id=='{$STATE.ID}') {
          window.location.href=window_url;
          return;
         }
         {/if}



         {if $STATE.MENU_ITEM_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!=""}
         if (id=='{$STATE.ID}') {

          var top=$('#scene_background_{$SCENE.ID}').offset().top;
          var left=$('#scene_background_{$SCENE.ID}').offset().left;

          var wdth=window{$STATE.ID}_width;
          var hdth=window{$STATE.ID}_height;
          var x=window{$STATE.ID}_posx+left;
          var y=window{$STATE.ID}_posy+top;


          if (!wdth) wdth=500;
          if (!hdth) hdth=500;
          if (!x) x=200;
          if (!y) y=200;



          var jWindowObj{$STATE.ID} = $.jWindow({ 
           id: 'state{$STATE.ID}', 
           title: '{$ELEMENT.TITLE}', 
           posx: x, 
           posy: y, 
           width: wdth, 
           height: hdth, 
           type: 'iframe', 
           marginTop:0, 
           marginBottom:0, 
           marginLeft:0, 
           marginRight:0, 
           url: window_url,
           onResizeEnd:function () {
            //Size changed
            var top=$('#scene_background_{$SCENE.ID}').offset().top;
            var left=$('#scene_background_{$SCENE.ID}').offset().left;
            var url1="{$smarty.const.ROOTHTML}ajax/scenes.html?op=position";
            window{$STATE.ID}_posx=(jWindowObj{$STATE.ID}.get('posx'))-left;
            window{$STATE.ID}_posy=(jWindowObj{$STATE.ID}.get('posy'))-top;
            window{$STATE.ID}_width=(jWindowObj{$STATE.ID}.get('width'));
            window{$STATE.ID}_height=(jWindowObj{$STATE.ID}.get('height'));
            url1+='&id={$STATE.ID}&posx='+window{$STATE.ID}_posx+'&posy='+window{$STATE.ID}_posy+'&width='+window{$STATE.ID}_width+'&height='+window{$STATE.ID}_height;
            {literal}
            $.ajax({url: url1});
            {/literal}
           }, 
           onDragEnd:function () {
            //Position changed
            ignoreClick=0;
            var url1="{$smarty.const.ROOTHTML}ajax/scenes.html?op=position";
            var top=$('#scene_background_{$SCENE.ID}').offset().top;
            var left=$('#scene_background_{$SCENE.ID}').offset().left;
            window{$STATE.ID}_posx=(jWindowObj{$STATE.ID}.get('posx'))-left;
            window{$STATE.ID}_posy=(jWindowObj{$STATE.ID}.get('posy'))-top;
            window{$STATE.ID}_width=(jWindowObj{$STATE.ID}.get('width'));
            window{$STATE.ID}_height=(jWindowObj{$STATE.ID}.get('height'));
            url1+='&id={$STATE.ID}&posx='+window{$STATE.ID}_posx+'&posy='+window{$STATE.ID}_posy+'&width='+window{$STATE.ID}_width+'&height='+window{$STATE.ID}_height;
            {literal}
            $.ajax({url: url1});
            {/literal}
           },
           modal: false });
          jWindowObj{$STATE.ID}.show();
          jWindowObj{$STATE.ID}.update();
          {if ($STATE.SCRIPT_ID=="0") && ($STATE.ACTION_METHOD=="") && ($STATE.CODE=="")}return;{/if}
         }
         {/if}
        {/foreach} // state
        {/foreach} // element
        {/foreach} // scene



         var url="{$smarty.const.ROOTHTML}ajax/scenes.html?op=click";
         url+='&id='+id;
         $.ajax({
          url: url,
          }).done(function(data) { 
           processCheckStates(data);
          });


            return false;
        }



        function processCheckStates(data) {

           var obj=jQuery.parseJSON(data);
           if (typeof obj !='object') return false;
           

           clearTimeout(noUpdatesTimer);
           noUpdatesTimer=setTimeout("$.publish('scenesNoUpdates');", 30*60*1000);

           var objCnt = obj.length;
           if (objCnt) {
             for(var i=0;i<objCnt;i++) {
              var elem=$('#state_'+obj[i].ID);
              if ((typeof obj[i].HTML!= 'undefined') && (obj[i].TYPE!='container') && (obj[i].HTML!=null) && (!codeHash.hasOwnProperty('code'+obj[i].ID) || codeHash['code'+obj[i].ID]!=obj[i].HTML)) {
               var subElement = $('#state_'+obj[i].ID+' > span');
               subElement.html(obj[i].HTML);
               codeHash['code'+obj[i].ID]=obj[i].HTML;
              }
              if (obj[i].STATE=='1' && !elem.is(':visible')) {
               if (elem.hasClass('inlineblock')) {
                elem.css('display', 'inline-block');
               } else {
                elem.show();
               }
               {if $TOTAL_SCENES!="1"}
               if (firstRun!=1 && obj[i].SWITCH_SCENE=='1') {
                switchScene(obj[i].SCENE_ID);
               }
               {/if}
              }
              if (obj[i].STATE!='1' && elem.is(':visible')) {
               elem.hide();
              }





             }
           }
        }


        function checkAllStates() {
         clearTimeout(checkTimer);

            if (firstRun==1) {
                {if $TOTAL_SCENES!="1"}
                 $("#slider").easySlider({
                 auto: false,
                 numeric: true,
                {if $smarty.const.SETTINGS_SCENES_VERTICAL_NAV=="1"}numericId: 'controls_vertical',{/if}
                 continuous: false
                 });
                {/if}
            }

         if (subscribedWebSockets==1) {
          firstRun=0;
          checkTimer=setTimeout('checkAllStates();', 3000);
          if (firstRun!=1 && refreshRun!=1) {
           return;
          }
         }


         var url="{$smarty.const.ROOTHTML}ajax/scenes.html?op=checkAllStates{if $SCENE_ID!=""}&scene_id={$SCENE_ID}{/if}{$PARAMS}";

         $.ajax({
          url: url,
          }).done(function(data) { 
           processCheckStates(data);
           firstRun=0;
           refreshRun=0;
           refreshTimer=setTimeout('refreshRun=1;', 5*60*1000);
           checkTimer=setTimeout('checkAllStates();', 3000);
          });
         return false;
        }

        $.subscribe('scenesNoUpdates', function (_) {
         window.location.reload();
        });


        function subscribeToScene() {
         console.log('Sending scene subscription request...');
         var payload;
         payload = new Object();
         payload.action = 'Subscribe';
         payload.data = new Object();
         payload.data.TYPE='scenes';
         payload.data.SCENE_ID='{$SCENE_ID}';
         wsSocket.send(JSON.stringify(payload));
         subscribedWebSocketsTimer=setTimeout('subscribeToScene();', 3000);
         return false;
        }


        $.subscribe('wsConnected', function (_) {
         subscribeToScene();
        });

        $.subscribe('wsData', function (_, response) {
          if (response.action=='subscribed') {
           console.log('Subscription confirmed.');
           clearTimeout(subscribedWebSocketsTimer);
           subscribedWebSockets=1;
          }
          if (response.action=='states') {
           processCheckStates(response.data);
          }
        });


                $(document).ready(function(){
                {if $TOTAL_SCENES=="1"}

                 {if isset($DRAGGABLE)}

                    $.contextMenu({
                        selector: '.context-menu-one',
                        zIndex: 1000,
                        events: {
                          show: function(options) {
                              contextLeft=event.pageX;
                              contextTop=event.pageY;
                          }
                        },
                        callback: function(key, options) {
                            if (key == 'add') {
                                stateClickedEdit('new');
                            }
                            if (key == 'adddevice') {
                                addDeviceClicked();
                            }
                            if (key == 'addwidget') {
                                addWidgetClicked();
                            }
                        },
                        items: {
                            {literal}"add": {name:{/literal}"{$smarty.const.LANG_ADD_NEW_ELEMENT}", icon: "add"},
                            {literal}"adddevice": {name:{/literal}"{$smarty.const.LANG_DEVICES_ADD_SCENE}", icon: "add"},
                            {literal}"addwidget": {name:{/literal}"{$smarty.const.LANG_ADD_WIDGET}", icon: "add"},
                        }
                });



$(".draggable" ).draggable({ cursor: "move", snap: true , snapTolerance: 5, grid: [5,5],
                        start: function(e, ui) {
                            var pos = ui.helper.offset();
                            this.originalLeft=pos.left;
                            this.originalTop=pos.top;
                        },
                        stop: function(e, ui) {
                            var pos = ui.helper.offset();
                            var dLeft=pos.left-this.originalLeft;
                            var dTop=pos.top-this.originalTop;
                            var url="{$smarty.const.ROOTHTML}ajax/scenes.html?op=dragged&element="+$(this).attr("id");
                            url+='&dleft='+encodeURIComponent(dLeft);
                            url+='&dtop='+encodeURIComponent(dTop);
                         {literal}
                         $.ajax({
                          url: url,
                          }).done(function(data) { 
                           //alert(data);
                          });
                          {/literal}
                        }
                   }).click(function(){
            if ( $(this).is('.ui-draggable-dragging')) {
                  return;
            }
            return stateClickedEdit($(this).attr("id"));
      });

     $(".resizable" ).resizable({literal}{grid: 5, {/literal}
                        start: function(e, ui) {
                            ignoreClick=1;
                        },
                        stop: function(e, ui) {

                            setTimeout('ignoreClick=0;',500);

                            var dwidth=ui.size.width;
                            var dheight=ui.size.height;

                            var url="{$smarty.const.ROOTHTML}ajax/scenes.html?op=resized&element="+$(this).attr("id");
                            url+='&dwidth='+encodeURIComponent(dwidth);
                            url+='&dheight='+encodeURIComponent(dheight);

                            {literal}
                            $.ajax({
                                url: url,
                            }).done(function(data) {
                                //alert(data);
                            });
                            {/literal}

                        }});


                 {/if}

                 {if $SCENE_AUTO_REFRESH!="0" && $SCENE_AUTO_REFRESH!=""}
                   setTimeout('window.location.reload();',{$SCENE_AUTO_REFRESH}*60*1000);
                 {/if}

                 {if $SCENE_WALLPAPER!=""}
                 if (inIframe()) {
                  if (typeof window.parent.setBackgroundStyle!=='undefined') {
                    if ($('#scene_wallpaper_{$SCENE_ID}').css('background-image')!='') {
                     $('body').css('background-color', 'transparent');
                     window.parent.$('body').css('background-image', $('#scene_wallpaper_{$SCENE_ID}').css('background-image'));
                     window.parent.$('body').css('background-attachment', $('#scene_wallpaper_{$SCENE_ID}').css('background-attachment'));
                     window.parent.$('body').css('background-repeat', $('#scene_wallpaper_{$SCENE_ID}').css('background-repeat'));
                     $('#scene_wallpaper_{$SCENE_ID}').css('background-image', '');
                    }
                  }
                 }
                 {/if}
                    {if $SCENE_AUTO_SCALE!="0" && !isset($DRAGGABLE)}
                    setTimeout('sceneZoom();',2000);
                    $(window).on('resize', function(){
                        sceneZoom();
                    });
                    {/if}
                {/if}
                 
                 checkAllStates();




                });


            function sceneZoom() {
                let zoomMode = parseInt('{$SCENE_AUTO_SCALE}');
                let zoomw = $(window).width();
                if(window.innerWidth > 0 && window.innerWidth < zoomw) zoomw = window.innerWidth;

                let maxElementX = 0;
                let maxElementXTitle = '';

                let maxElementY = 0;
                let maxElementYTitle = '';


                $('.scene_element, .element_state, .scene_wallpaper').each(function() {
                    let x = $( this ).get(0).getBoundingClientRect().right+20;
                    let y = $( this ).get(0).getBoundingClientRect().bottom+20;
                    if (x>maxElementX) {
                        maxElementX = x;
                        maxElementXTitle = this.id;
                    }
                    if (y>maxElementY) {
                        maxElementY = y;
                        maxElementYTitle = this.id;
                    }
                });


                zoomw = Math.round(zoomw/maxElementX*100);
                let zoomh = $(window).height();
                if(window.innerHeight > 0 && window.innerHeight < zoomh) zoomh = window.innerHeight;
                zoomh = Math.round(zoomh/maxElementY*100);
                let newZoom = '';
                if (zoomMode == 3) { // height
                    newZoom = zoomh+"%";
                }
                if (zoomMode == 2) { // width
                    newZoom = zoomw+"%";
                }
                if (zoomMode == 1) { // both
                    if(zoomh < zoomw) {
                        newZoom = zoomh+"%";
                    } else {
                        newZoom = zoomw+"%";
                    }
                }
                if (newZoom!='') {
                    console.log('Max element X: ' + maxElementX + ' (' + maxElementXTitle + ')');
                    console.log('Max element Y: ' + maxElementY + ' (' + maxElementYTitle + ')');
                    console.log('Zoom: '+newZoom);
                    document.body.style.zoom = newZoom;
                }

            }


        </script>



<div id="scenes_body">
<div style="{if $TOTAL_SCENES!="1"}width:{$smarty.const.SETTINGS_SCENES_WIDTH}px;{/if};position:relative;" class="context-menu-one">
<div id="slider">
{if $TOTAL_SCENES!="1"}<ul class='scenes_ul'>{/if}
{foreach $RESULT as $SCENE}
    <style>
        {if $SCENE.DEVICES_BACKGROUND=="dark"}
        #scene_background_{$SCENE.ID} > .type_device {
            background-color:#222222;
        }
        {/if}
        {if $SCENE.DEVICES_BACKGROUND=="light"}
        #scene_background_{$SCENE.ID} > .type_device {
            background-color:#dddddd;
        }
        {/if}
    </style>
{if $TOTAL_SCENES!="1"}<li class='scenes_li' id='scene_{$SCENE.ID}' style="width:{$smarty.const.SETTINGS_SCENES_WIDTH}px;">{/if}
 {if isset($SCENE.VIDEO_WALLPAPER)}
     <video autoplay muted loop id="myVideo" style="position: fixed;
  right: 0;
  bottom: 0;
  min-width: 100%;
  min-height: 100%;">
         <source src="{$SCENE.VIDEO_WALLPAPER}" type="video/mp4">
     </video>
 {/if}
 <div class="scene_wallpaper" id="scene_wallpaper_{$SCENE.ID}" style="{if $SCENE.WALLPAPER!=""}background-image:url({$SCENE.WALLPAPER});{if $SCENE.WALLPAPER_FIXED=="1"}background-attachment: fixed;{/if}{if $SCENE.WALLPAPER_NOREPEAT=="1"}background-repeat: no-repeat;{/if}width:100%;height:100%;{/if};">
 <div id="scene_background_{$SCENE.ID}" style="position:relative;">
 {function name=elements}
 {foreach $items as $ELEMENT}
 <!-- element {$ELEMENT.ID} -->
 {if isset($ELEMENT.ELEMENTS)}
 <div
   class="scene_element element_{$ELEMENT.ID} type_{$ELEMENT.TYPE}{if isset($ELEMENT.CSS_STYLE) && $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if}{if isset($ELEMENT.BACKGROUND) && $ELEMENT.BACKGROUND=="1"} container_background{/if}{if isset($DRAGGABLE)} draggable{/if}"
   style="{if isset($ELEMENT.POSITION_TYPE) && $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
   {if isset($ELEMENT.ZINDEX) && $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
   {if isset($ELEMENT.WIDTH) && $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if isset($ELEMENT.HEIGHT) && $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
   {if isset($ELEMENT.STATE) && $ELEMENT.STATE!="1"}display:none;{/if}
   "
   id="state_{$ELEMENT.STATE_ID}"
   >
  {elements items=$ELEMENT.ELEMENTS}
 </div>
 {else}

     {if $ELEMENT.TYPE=='slider'}
     {if $sliderLoaded!='1'}
        <link rel="stylesheet" href="{$smarty.const.ROOTHTML}3rdparty/tinyslider/tiny-slider.css">
        <script src="{$smarty.const.ROOTHTML}3rdparty/tinyslider/tiny-slider.js"></script>
        {assign var="sliderLoaded" value="1"}
     {/if}
     <div
          class="element_{$ELEMENT.ID}
          element_state
          type_{$ELEMENT.TYPE}
          {if $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if}
          state_{$STATE.TITLE}
          {if $ELEMENT.BACKGROUND=="1"} html_background{/if}
          {if $ELEMENT.POSITION_TYPE=="1"} inlineblock{/if}
          {if isset($DRAGGABLE) && $ELEMENT.POSITION_TYPE=="0"} draggable
           {if $ELEMENT.RESIZABLE=="1"} resizable {$ELEMENT.RESIZABLE}{/if}
          {/if}"
          id = "state_element_{$ELEMENT.ID}"
             style="
             {if $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
             {if isset($ELEMENT.ZINDEX) && $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
             {if $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
             ">
         <div id="slider_body_{$ELEMENT.ID}" style="width:100%">
         {foreach $ELEMENT.STATES as $STATE}
             <div
                     class="element_{$ELEMENT.ID}
                     state_{$STATE.TITLE}"
                     id="state_{$STATE.ID}"
                     {if $STATE.SCRIPT_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!="" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.CODE!=""}
                         {if !isset($DRAGGABLE)}
                             onClick="stateClicked('{$STATE.ID}');"
                         {/if}
                     {/if}
                     style="{if $STATE.SCRIPT_ID!="0" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.EXT_URL!="" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.CODE!=""}cursor:pointer;{/if}"
             >{$STATE.HTML}</div>
         {/foreach}
         </div>
     </div>
     <script type="text/javascript">
         $(document).ready(function() {
             // http://ganlanyuan.github.io/tiny-slider/
             var slider_{$ELEMENT.ID} = tns({
                 container: '#slider_body_{$ELEMENT.ID}',
                 {if $ELEMENT.APPEAR_ANIMATION=='4'} // bottom-to-top
                 axis: 'vertical',
                 {/if}
                 {if $ELEMENT.APPEAR_ANIMATION=='5'} // blink
                 mode: 'gallery',
                 {/if}
                 controls: false,
                 nav: false,
                 autoplay: true,
                 autoplayButtonOutput: false,
                 {if $ELEMENT.JAVASCRIPT!=""}
                 {$ELEMENT.JAVASCRIPT}
                 {/if}
             });
         });
     </script>
     {else}

 {foreach $ELEMENT.STATES as $STATE}
     <div
          class="element_{$ELEMENT.ID}
          element_state
          type_{$ELEMENT.TYPE}
          {if $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if}
          state_{$STATE.TITLE}
          {if $ELEMENT.BACKGROUND=="1"} html_background{/if}
          {if $ELEMENT.POSITION_TYPE=="1"} inlineblock{/if}
          {if isset($DRAGGABLE) && $ELEMENT.POSITION_TYPE=="0"} draggable
           {if $ELEMENT.RESIZABLE=="1"} resizable {$ELEMENT.RESIZABLE}{/if}
          {/if}"
             id="state_{$STATE.ID}"
             {if $STATE.SCRIPT_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!="" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.CODE!=""}
                 {if !isset($DRAGGABLE)}
                     onClick="stateClicked('{$STATE.ID}');"
                 {/if}
             {/if}
             style="
             {if $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
             {if isset($ELEMENT.ZINDEX) && $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
             {if $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
             {if $STATE.SCRIPT_ID!="0" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.EXT_URL!="" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.CODE!=""}cursor:pointer;{/if}
                     {if $STATE.STATE!="1"}display:none;{else}display:inline-block;{/if}">{if $ELEMENT.TYPE=="img"}<img src="{$STATE.IMAGE}" border="0">{/if}<span>{$STATE.HTML}</span></div>
 {/foreach}

 {if $ELEMENT.CSS!=""}
     <style>
         {$ELEMENT.CSS}
     </style>
 {/if}
 {if $ELEMENT.JAVASCRIPT!=""}
     <script language="javascript">
         {$ELEMENT.JAVASCRIPT}
     </script>
 {/if}

     {/if}


 {/if}


 <!-- /element {$ELEMENT.ID} -->
 {/foreach}
 {/function}

 {elements items=$SCENE.ELEMENTS}

 {if $SCENE.BACKGROUND!=""}
     <div class="scene_background">
         <img src="{$SCENE.BACKGROUND}" border="0">
     </div>
 {/if}
 </div>
 </div>
 {if $TOTAL_SCENES!="1"}</li>{/if}
{/foreach}

{if $TOTAL_SCENES!="1"}</ul>{/if}
</div>
</div> <!-- /slider -->
</div>

