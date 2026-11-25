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
var mjPendingUpdates = {};
var mjWidgetUpdateTimer;

function mjApplyWidgetHTML(container, html) {
    var $container = $(container);
    var $new = $('<div>').html(html).find('.device-widget').first();
    var $old = $container.find('.device-widget').first();
    if (!$new.length || !$old.length) {
        $container.html(html);
        return;
    }
    var newClass = $new.attr('class') || '';
    if (($old.attr('class') || '') !== newClass) {
        $old.attr('class', newClass);
    }
    if (typeof $new.attr('data-state') !== 'undefined') {
        $old.attr('data-state', $new.attr('data-state'));
    }
    $('.device-header, .device-details', $new).each(function() {
        var $source = $(this);
        var selector = '.' + $source.attr('class').split(/\s+/).join('.');
        $old.find(selector).first().html($source.html());
    });
    $old.addClass('updated');
    setTimeout(function() { $old.removeClass('updated'); }, 600);
}

function mjFlushWidgetUpdates() {
    for (var deviceId in mjPendingUpdates) {
        if (!mjPendingUpdates.hasOwnProperty(deviceId)) continue;
        mjApplyWidgetHTML('#device' + deviceId, mjPendingUpdates[deviceId]);
        delete mjPendingUpdates[deviceId];
    }
}

$.subscribe('wsData', function (_, response) {
    if (response.action=='subscribed') {
        //console.log('Subscription to devices confirmed.');
    }
    if (response.action=='devices') {
        var obj=jQuery.parseJSON(response.data);
        if (typeof obj.DATA !='object') return false;
        for (var i=0; i<obj.DATA.length; i++) {
            mjPendingUpdates[obj.DATA[i].DEVICE_ID] = obj.DATA[i].DATA;
        }
        clearTimeout(mjWidgetUpdateTimer);
        mjWidgetUpdateTimer = setTimeout(mjFlushWidgetUpdates, 120);
    }
});

function refreshDevicesHTTP() {
    if (!activeDevices.length || document.visibilityState === 'hidden') return;
    var url = ROOTHTML + "ajax/devices.html?op=get_devices&ids=" + activeDevices.join(',');
    $.ajax({ url: url }).done(function(data) {
        var obj = jQuery.parseJSON(data);
        if (!obj.devices) return;
        for (var i=0; i<obj.devices.length; i++) {
            mjApplyWidgetHTML('#device' + obj.devices[i].DEVICE_ID, obj.devices[i].HTML);
        }
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

$(document).on('keydown', '.device-widget[tabindex="0"]', function(e) {
    if (e.target !== this) return;
    if (e.which === 13 || e.which === 32) {
        e.preventDefault();
        $(this).trigger('click');
    }
});