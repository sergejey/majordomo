<?php
header("Content-type:application/javascript");
chdir(dirname(__FILE__) . '/../../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./load_settings.php");
?>

var activeDevices = Array();
var devicesWidgetWSTimer;
var devicesWidgetWSUpdatedTimer;

$.subscribe('wsData', function (_, response) {
    if (response.action=='subscribed') {
        //console.log('Subscription to devices confirmed.');
    }
    if (response.action=='devices') {
        var obj=jQuery.parseJSON(response.data);
        if (typeof obj.DATA !='object') return false;
        var objCnt = obj.DATA.length;
        if (objCnt) {
            for(var i=0;i<objCnt;i++) {
                var device_id=obj.DATA[i].DEVICE_ID;
                var html=obj.DATA[i].DATA;
                $('#device'+device_id).html(html);
            }
        }
    }
});

function refreshDevicesHTTP() {
    var baseURL = ROOTHTML + "ajax/devices.html?op=get_device&id=";
    activeDevices.forEach(function(item, index) {
        var url = baseURL + item;
        $.ajax({
            url: url
        }).done(function(data) {
            var obj=jQuery.parseJSON(data);
            $('#device'+item).html(obj.HTML);
        });
    });
}

function refreshWSSubscription() {
    clearTimeout(devicesWidgetWSTimer);
    //console.log('refresh subscription');
    if (startedWebSockets) {
        //for(var i=0;i<activeDevices.length;i++) {
            console.log('subscribing ws to device '+activeDevices.join());
            var payload;
            payload = new Object();
            payload.action = 'Subscribe';
            payload.data = new Object();
            payload.data.TYPE='devices';
            payload.data.DEVICE_ID=activeDevices.join();
            wsSocket.send(JSON.stringify(payload));
        //}
        devicesWidgetWSTimer=setTimeout('refreshWSSubscription();',10*60000);
    } else {
        refreshDevicesHTTP();
        devicesWidgetWSTimer=setTimeout('refreshWSSubscription();',5000);
    }
}

function activeDevicesUpdated() {
    clearTimeout(devicesWidgetWSUpdatedTimer);
    devicesWidgetWSUpdatedTimer=setTimeout('refreshWSSubscription();',2000);
}



function requestDeviceHTML(device_id,widgetElement) {
    //alert('requested html for '+device_id+' ');

    if (activeDevices.indexOf(device_id)<0) {
        activeDevices.push(device_id);
        activeDevicesUpdated();
    }
    var url='<?php echo ROOTHTML;?>ajax/devices.html?op=get_device&id='+device_id;
    $.ajax({
        url: url
    }).done(function(data) {
        var res=JSON.parse(data);
        if (typeof res.HTML !== 'undefined') {
            var myTextElement = $("<div id='device"+device_id+"'>"+res.HTML+"</div>");
            $(widgetElement).html(myTextElement);
        }
        /*
        if (typeof res.HEIGHT !== 'undefined') {
            newSettings.size=res.HEIGHT;
            alert(newSettings);
        }
        */
    });
}

(function()
{

    freeboard.loadWidgetPlugin({
        // Same stuff here as with datasource plugin.
        "type_name"   : "devices_plugin",
        "display_name": LANG_DEVICE,
        "description" : "MajorDoMo "+LANG_DEVICES,
        "fill_size" : false,
        "settings"    : [
            {
                "name"        : "device_id",
                "display_name": LANG_DEVICE,
                "required" : true,
                "type"        : "option",
                <?php
                $scripts=SQLSelect("SELECT ID,TITLE FROM devices ORDER BY TITLE");
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
            newInstanceCallback(new myDevicesPlugin(settings));
        }
    });

    var myDevicesPlugin = function(settings)
    {
        var self = this;
        var currentSettings = settings;
        var widgetElement;

        function updateDeviceHTML()
        {
            if(widgetElement)
            {
                requestDeviceHTML(currentSettings.device_id,widgetElement);
            }
        }

        self.render = function(element)
        {
            widgetElement = element;
            updateDeviceHTML();
        }

        self.getHeight = function()
        {
            if (typeof currentSettings.size == 'undefined') currentSettings.size=1;
            return parseInt(currentSettings.size);
        }

        self.onSettingsChanged = function(newSettings)
        {
            currentSettings = newSettings;
            updateDeviceHTML();
        }

        self.onCalculatedValueChanged = function(settingName, newValue)
        {
            updateDeviceHTML();
        }

        self.onDispose = function()
        {
        }

    }


}());
