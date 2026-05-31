<?php
header("Content-type:application/javascript");
chdir(dirname(__FILE__) . '/../../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./load_settings.php");
?>

var activeObjects = Array();
var objectsWidgetWSTimer;
var objectsWidgetWSUpdatedTimer;

$.subscribe('wsData', function (_, response) {
    if (response.action=='subscribed') {
        //console.log('Subscription to objects confirmed.');
    }
    if (response.action=='objects') {
        var obj=jQuery.parseJSON(response.data);
        if (typeof obj.DATA !='object') return false;
        var objCnt = obj.DATA.length;
        if (objCnt) {
            for(var i=0;i<objCnt;i++) {
                var object_id=obj.DATA[i].OBJECT_ID;
                var html=obj.DATA[i].DATA;
                $('#object'+object_id).html(html);
            }
        }
    }
});

function refreshWSObjectsSubscription() {
    clearTimeout(objectsWidgetWSTimer);
    //console.log('refresh subscription');
    if (startedWebSockets) {
        //for(var i=0;i<activeObjects.length;i++) {
            console.log('subscribing ws to objects '+activeObjects.join());
            var payload;
            payload = new Object();
            payload.action = 'Subscribe';
            payload.data = new Object();
            payload.data.TYPE='objects';
            payload.data.OBJECT_ID=activeObjects.join();
            wsSocket.send(JSON.stringify(payload));
        //}
        objectsWidgetWSTimer=setTimeout('refreshWSObjectsSubscription();',10*60000);
    } else {
        objectsWidgetWSTimer=setTimeout('refreshWSObjectsSubscription();',5000);
    }
}

function activeObjectsUpdated() {
    clearTimeout(objectsWidgetWSUpdatedTimer);
    objectsWidgetWSUpdatedTimer=setTimeout('refreshWSObjectsSubscription();',2000);
}



function requestObjectHTML(object_id,widgetElement) {
    //alert('requested html for '+object_id+' ');

    if (activeObjects.indexOf(object_id)<0) {
        activeObjects.push(object_id);
        activeObjectsUpdated();
    }

    var url='<?php echo ROOTHTML;?>ajax/objects.html?op=get_object&id='+object_id;
    $.ajax({
        url: url
    }).done(function(data) {
        var res=JSON.parse(data);
        if (typeof res.HTML !== 'undefined') {
            //alert(res.HTML);
            var myTextElement = $("<div id='object"+object_id+"'>"+res.HTML+"</div>");
            $(widgetElement).html(myTextElement);
            //subscribe to changes
        }
    });


}

(function()
{

    freeboard.loadWidgetPlugin({
        // Same stuff here as with datasource plugin.
        "type_name"   : "objects_plugin",
        "display_name": LANG_OBJECT,
        "description" : "MajorDoMo "+LANG_SECTION_OBJECTS,
        "fill_size" : false,
        "settings"    : [
            {
                "name"        : "object_id",
                "display_name": "<?php echo LANG_LINKED_OBJECT;?>",
                "required" : true,
                "type"        : "option",
                <?php
                $scripts=SQLSelect("SELECT ID,TITLE FROM objects ORDER BY TITLE");
                ?>
                "options"     : [
                    <?php
                    foreach($scripts as $k=>$v) {
                        echo '{';
                        echo '"name" : "'.($v['TITLE']).'",'."\n";
                        echo '"value" : "'.$v['ID'].'"';
                        echo '},';
                    }
                    ?>
                ]
            },
            {
                "name"        : "size",
                "display_name": LANG_SIZE,
                "type"        : "option",
                "options"     : [
                    {"name" : "1","value": "1"},
                    {"name" : "2","value": "2"},
                    {"name" : "3","value": "3"},
                    {"name" : "4","value": "4"},
                    {"name" : "5","value": "5"},
                    {"name" : "6","value": "6"},
                    {"name" : "7","value": "7"},
                    {"name" : "8","value": "8"}
                ]
            }

        ],
// Same as with datasource plugin, but there is no updateCallback parameter in this case.
        newInstance   : function(settings, newInstanceCallback)
        {
            newInstanceCallback(new myObjectsPlugin(settings));
        }
    });

    var myObjectsPlugin = function(settings)
    {
        var self = this;
        var currentSettings = settings;
        var widgetElement;
        function updateObjectHTML()
        {
            if(widgetElement)
            {
                requestObjectHTML(currentSettings.object_id,widgetElement);
            }
        }

        self.render = function(element)
        {
            widgetElement = element;
            updateObjectHTML();
        }

        self.getHeight = function()
        {
            if (typeof currentSettings.size == 'undefined') currentSettings.size=1;
            return parseInt(currentSettings.size);
        }

        self.onSettingsChanged = function(newSettings)
        {
            currentSettings = newSettings;
            updateObjectHTML();
        }

        self.onCalculatedValueChanged = function(settingName, newValue)
        {
            updateObjectHTML();
        }

        self.onDispose = function()
        {
        }

    }


}());
