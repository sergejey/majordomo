{if $DRAGGABLE=="1"}
    <link rel="stylesheet" href="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.contextMenu.min.css">
    <script type="text/javascript" src="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.contextMenu.min.js"></script>
    <script type="text/javascript" src="{$smarty.const.ROOTHTML}3rdparty/jquery.contextmenu/jquery.ui.position.min.js"></script>
    <!--
    <div id='contextMenuDiv' style="display:none;width:100px;height:20px;background-color:white;position:absolute;border: 1px solid black;z-index:10000;top:200px;left:300px;padding:10px;text-align:center"><a href="#" onClick="stateClickedEdit('new');return false;">{$smarty.const.LANG_ADD}</a></div>
    -->
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
 {if $TYPE.HAS_STYLE!=""}{include file="../../cms/scenes/styles/{$TYPE.TITLE}/style.css.tpl"}{/if}
{/foreach}
</style>

{if $smarty.const.SETTINGS_SCENES_CLICKSOUND!=""}
<div style="display:none">
<audio id="click_sound" src="{$smarty.const.SETTINGS_SCENES_CLICKSOUND}" controls preload="auto" autobuffer>
</audio>
</div>
{/if}
        {if $TOTAL_SCENES!="1"}
        <style>{include './slider.css'}</style>
        <script type="text/javascript" src="{$smarty.const.ROOTHTML}js/easySlider1.7.js?v=2019-02-27"></script>
        {/if}

        <script type="text/javascript" language="javascript">

            /*
$.fn.customContextMenu = function(callBack){
    $(this).each(function(){
        $(this).bind("contextmenu",function(e){
             e.preventDefault();
             callBack(e);
        });
    }); 
}
*/



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
          var window_url=window.parent.location.href;
          window_url=window_url.replace('tab=preview', 'tab=elements')+'&open='+id+'&print=1';
          if (id=='new') {
           window_url=window_url+'&top='+contextTop+'&left='+contextLeft;
          }
          parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.location.reload(); }});
          return false;
        }

            function addDeviceClicked(id) {
                var window_url=window.parent.location.href;
                window_url=window_url.replace('tab=preview', 'tab=devices')+'&open='+id+'&print=1';
                window_url=window_url+'&top='+contextTop+'&left='+contextLeft;
                parent.$.fancybox.open({ src: window_url, type: 'iframe','beforeClose': function() { window.location.reload(); }});
                return false;
            }



        function stateClicked(id) {

         var window_url;

            {if $smarty.const.SETTINGS_SCENES_CLICKSOUND!=""}
            setTimeout("EvalSound('click_sound')",100);
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
        {/foreach}
        {/foreach}
        {/foreach}



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
               elem.html('<span>'+obj[i].HTML+'</span>');
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


              if (elem.hasClass('s3d_state')) {


               if (elem.data('s3d_object')) {
                var object3d = scene.getObjectByName( elem.data('s3d_object'), true );
                if (obj[i].STATE=='1') {
                 object3d.visible=true;
                } else {
                 object3d.visible=false;
                }
               }


               if (elem.data('s3d_camera')) {
                if (obj[i].STATE=='1') {
                  new_camera = scene.getObjectByName( elem.data('s3d_camera'), true );;
                 }else {
                  new_camera = default_camera;
                 }

                        var new_position = new_camera.position.clone();
                        var new_rotation = new_camera.rotation.clone();
                        var new_quaternion = new_camera.quaternion.clone();

                        //newlookAtVector = new THREE.Vector3(new_camera.matrix[8], new_camera.matrix[9], new_camera.matrix[10]);

                        camera.rotation.clone(new_rotation);
                        camera.quaternion.clone(new_quaternion);

                        newlookAtVector = new THREE.Vector3(0, 0, -1);
                        newlookAtVector.applyEuler(new_camera.rotation, new_camera.eulerOrder);


                        new TWEEN.Tween( camera.position ).to( {
                                x: new_position.x,
                                y: new_position.y,
                                z: new_position.z}, 600 ).onUpdate(function () {

                         camera.lookAt(newlookAtVector);

                        }).onComplete(function () {

                         camera.lookAt(newlookAtVector);
        
                        }).easing( TWEEN.Easing.Sinusoidal.Out).start();
               }
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
           //tryWebSockets();
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

                 {if $DRAGGABLE=="1"}


                    $.contextMenu({
                        selector: '.context-menu-one',
                        zIndex: 1000,
                        callback: function(key, options) {
                            contextLeft=event.pageX;
                            contextTop=event.pageY;
                            if (key == 'add') {
                                stateClickedEdit('new');
                            }
                            if (key == 'adddevice') {
                                addDeviceClicked();
                            }
                            //var m = "clicked: " + key;
                            //window.console && console.log(m) || alert(m);
                        },
                        items: {
                            {literal}"add": {name:{/literal}"{$smarty.const.LANG_ADD_NEW_ELEMENT}", icon: "add"},
                            {literal}"adddevice": {name:{/literal}"{$smarty.const.LANG_DEVICES_ADD_SCENE}", icon: "add"},
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
                   }).resizable({literal}{grid: 5, {/literal}
                           stop: function(e, ui) {
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
                           }}).click(function(){
            if ( $(this).is('.ui-draggable-dragging') ) {
                  return;
            }
            // click action here
            stateClickedEdit($(this).attr("id"));
      });

      {foreach $RESULT as $SCENE}
                    /*
      $("#scene_wallpaper_{$SCENE.ID}").customContextMenu(function(e){
       contextTop=e.pageY;
       contextLeft=e.pageX;
       $("#contextMenuDiv").css({ "top": e.pageY+"px", "left": e.pageX+"px" });
       $('#contextMenuDiv').show();
       contextTimeout=setTimeout("$('#contextMenuDiv').hide();", 3*1000);
       return false;
      });
      */
      {/foreach}
 
                 {/if}
                 {if $SCENE_WALLPAPER!=""}
                 if (inIframe) {
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
                    {if $SCENE_AUTO_SCALE!="0" && $DRAGGABLE!="1"}
                    setTimeout('sceneZoom();',2000);
                    $(window).on('resize', function(){
                        sceneZoom();
                    });
                    {/if}
                {/if}
                 
                 checkAllStates();




                });


            function sceneZoom() {
                var zoomMode = parseInt('{$SCENE_AUTO_SCALE}');
                var zoomw = $(window).width();
                if(window.innerWidth > 0 && window.innerWidth < zoomw) zoomw = window.innerWidth;
                zoomw = zoomw/$("#slider").width()*100;
                var zoomh = $(window).height();
                if(window.innerHeight > 0 && window.innerHeight < zoomh) zoomh = window.innerHeight;
                zoomh = zoomh/$("#slider").height()*100;
                if (zoomMode == 3) { // height
                    document.body.style.zoom = zoomh+"%"
                }
                if (zoomMode == 2) { // width
                    document.body.style.zoom = zoomw+"%"
                }
                if (zoomMode == 1) { // both
                    if(zoomh < zoomw) {
                        document.body.style.zoom = zoomh+"%"
                    } else {
                        document.body.style.zoom = zoomw+"%"
                    }
                }

            }


        </script>



<div id="scenes_body">
<table  border="0" cellpadding="0" cellspacing="0">
 <tr>
  <td valign="top">
<div style="{if $TOTAL_SCENES!="1"}width:{$smarty.const.SETTINGS_SCENES_WIDTH}px;{/if};position:relative;" class="context-menu-one">
<div id="slider">
{if $TOTAL_SCENES!="1"}<ul>{/if}
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
{if $TOTAL_SCENES!="1"}<li id='scene_{$ID}' style="width:{$smarty.const.SETTINGS_SCENES_WIDTH}px;">{/if}
 <div id="scene_wallpaper_{$SCENE.ID}" style="{if $SCENE.WALLPAPER!=""}background-image:url({$SCENE.WALLPAPER});{if $SCENE.WALLPAPER_FIXED=="1"}background-attachment: fixed;{/if}{if $SCENE.WALLPAPER_NOREPEAT=="1"}background-repeat: no-repeat;{/if}{/if};">
 <div id="scene_background_{$SCENE.ID}" style="position:relative;">
 {function name=elements}

 {foreach $items as $ELEMENT}
 <!-- element {$ID} -->
 {if $ELEMENT.ELEMENTS}
 <div 
   class="element_{$ELEMENT.ID} type_{$ELEMENT.TYPE}{if $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if}{if $ELEMENT.BACKGROUND=="1"} container_background{/if}{if $DRAGGABLE=="1"} draggable{/if}"
   style="{if $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
   {if $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
   {if $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
   {if $ELEMENT.STATE!="1"}display:none;{/if}
   "
   id="state_{$ELEMENT.STATE_ID}"
   >
  {elements items=$ELEMENT.ELEMENTS}
 </div>
 {else}

 {if $ELEMENT.TYPE=="s3d"}
  <div 
   class="element_{$ELEMENT.ID} type_{$ELEMENT.TYPE}{if $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if} state_{$TITLE}{if $ELEMENT.BACKGROUND=="1"} html_background{/if}{if $ELEMENT.POSITION_TYPE=="1"} inlineblock{/if}{if $DRAGGABLE=="1" && $ELEMENT.POSITION_TYPE=="0"} draggable{/if}" 
   id='canvas_{$ELEMENT.ID}'
   style="
   background-color:red;
   {if $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
   {if $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
   {if $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
   display:inline-block;"></div>

<script language="javascript" src="{$smarty.const.ROOTHTML}3rdparty/threejs/libs/tween.min.js"></script>
<script language="javascript" src="{$smarty.const.ROOTHTML}3rdparty/threejs/three.min.js"></script>
<script src="{$smarty.const.ROOTHTML}3rdparty/threejs/loaders/SceneLoader.js" language="javascript"></script>
   <script language="javascript">

                        var container;
                        var camera, scene, loaded;
                        var renderer;
                        var mixers = [];
                        var rotatingObjects = [];
                        var clock = new THREE.Clock();
                        var objects = [];

    var container = document.getElementById('canvas_{$ELEMENT.ID}');
    var camera = new THREE.PerspectiveCamera( 75, {$ELEMENT.WIDTH}/{$ELEMENT.HEIGHT}, 0.1, 1000 );
    var default_camera = new THREE.PerspectiveCamera( 75, {$ELEMENT.WIDTH}/{$ELEMENT.HEIGHT}, 0.1, 1000 );

    var renderer = new THREE.WebGLRenderer();
    renderer.setSize( {$ELEMENT.WIDTH}, {$ELEMENT.HEIGHT} );
    renderer.domElement.style.position = "relative";
    container.appendChild( renderer.domElement );
    renderer.gammaInput = true;
    renderer.gammaOutput = true;

    var scene = new THREE.Scene();


var loader = new THREE.SceneLoader();
var sceneURL='{$ELEMENT.S3D_SCENE}'; //

// load a resource
loader.load(
        sceneURL,
        function ( result ) {
           loaded = result;
           scene = loaded.scene;
           for (var obj in loaded.objects ) {
            objects.push(loaded.objects[obj]);
           }
           if (loaded.currentCamera) {

            loaded.currentCamera.aspect = {$ELEMENT.WIDTH}/{$ELEMENT.HEIGHT};
            loaded.currentCamera.updateProjectionMatrix();
            default_camera=loaded.currentCamera;
            camera = default_camera.clone();
            /*
            var old_position = new THREE.Vector3();
            old_position.setFromMatrixPosition( camera.matrix );
            camera.matrixAutoUpdate = true;
            camera.position.setX(old_position.x);
            camera.position.setY(old_position.y);
            camera.position.setZ(old_position.z);
            */

           }

        }
);


                        function render() {
                                requestAnimationFrame( render );
                                renderer.render( scene, camera );
                                TWEEN.update();
                        }


     render();


// projector
raycaster = new THREE.Raycaster();

// listeners
document.addEventListener( 'mousedown', onDocumentMouseDown, false)

function onDocumentMouseDown( event ) {
    event.preventDefault();
                var mouse = new THREE.Vector2();
                mouse.x = ( (event.clientX-{$ELEMENT.LEFT}) / renderer.domElement.width ) * 2 - 1;
                mouse.y = - ( (event.clientY-{$ELEMENT.TOP}) / renderer.domElement.height ) * 2 + 1;
                raycaster.setFromCamera( mouse, camera );

        var intersects = raycaster.intersectObjects( objects ); 

    if ( intersects.length > 0 ) {
     console.log('Clicked on '+intersects[0].object.name);
     {foreach $STATES as $STATE}
      {if $STATE.S3D_OBJECT!=""}
      if (intersects[0].object.name=='{$STATE.S3D_OBJECT}') {
       stateClicked('{$STATE.ID}');
      }{/if}
     {/foreach}
    }
}
 

   </script>
   <div style="display:none">
    {foreach $ELEMENT.STATES as $STATE}
    <div class="element_{$ELEMENT.ID} type_{$ELEMENT.TYPE} state_{$STATE.TITLE} s3d_state" id="state_{$STATE.ID}"
    {if $STATE.S3D_OBJECT!=""} data-s3d_object='{$STATE.S3D_OBJECT}'{/if}
    {if $STATE.S3D_CAMERA!=""} data-s3d_camera='{$STATE.S3D_CAMERA}'{/if}
    {if $DRAGGABLE!="1"}onClick="stateClicked('{$STATE.ID}');"{/if}
    ></div>{/foreach}
   </div>

 {else}

 {foreach $ELEMENT.STATES as $STATE}
  <div 
   class="element_{$ELEMENT.ID} type_{$ELEMENT.TYPE}{if $ELEMENT.CSS_STYLE!=""} style_{$ELEMENT.CSS_STYLE}{/if} state_{$STATE.TITLE}{if $ELEMENT.BACKGROUND=="1"} html_background{/if}{if $ELEMENT.POSITION_TYPE=="1"} inlineblock{/if}{if $DRAGGABLE=="1" && $ELEMENT.POSITION_TYPE=="0"} draggable{/if}" 
   id="state_{$STATE.ID}"
   {if $STATE.SCRIPT_ID!="0" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.EXT_URL!="" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.CODE!=""} 
   {if $DRAGGABLE!="1"}
    onClick="stateClicked('{$STATE.ID}');"
   {/if}
   {/if} 
   style="
   {if $DRAGGABLE=="1"}border:1px solid blue;{/if}
   {if $ELEMENT.POSITION_TYPE=="0"}position:absolute;left:{$ELEMENT.LEFT}px;top:{$ELEMENT.TOP}px;{/if}
   {if $ELEMENT.ZINDEX!=""}z-index:{$ELEMENT.ZINDEX};{/if}
   {if $ELEMENT.WIDTH!="0"}width:{$ELEMENT.WIDTH}px;{/if}{if $ELEMENT.HEIGHT!="0"}height:{$ELEMENT.HEIGHT}px;{/if}
   {if $STATE.SCRIPT_ID!="0" || $STATE.MENU_ITEM_ID!="0" || $STATE.ACTION_METHOD!="" || $STATE.EXT_URL!="" || $STATE.HOMEPAGE_ID!="0" || $STATE.OPEN_SCENE_ID!="0" || $STATE.CODE!=""}cursor:pointer;{/if}
   {if $STATE.STATE!="1"}display:none;{else}display:inline-block;{/if}">{if $ELEMENT.TYPE=="img"}<img src="{$STATE.IMAGE}" border="0">{/if}<span>{$STATE.HTML}</span></div>
 {/foreach}
 {/if}

 {/if}

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
 <!-- /element {$ELEMENT.ID} -->
 {/foreach}
 {/function}

 {elements items=$SCENE.ELEMENTS}

 {if $SCENE.BACKGROUND!=""}<div class="scene_background"><img src="{$SCENE.BACKGROUND}" border="0"></div>{/if}
 </div>
 </div>
 {if $TOTAL_SCENES!="1"}</li>{/if}
{/foreach}

{if $TOTAL_SCENES!="1"}</ul>{/if}
</div>
</div> <!-- /slider -->
</td>
 </tr>
</table>
</div>

