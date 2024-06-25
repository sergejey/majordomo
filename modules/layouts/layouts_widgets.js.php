<?php
header("Content-type:application/javascript");
chdir(dirname(__FILE__) . '/../../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./load_settings.php");
?>
    (function()
    {

        freeboard.loadWidgetPlugin({
            // Same stuff here as with datasource plugin.
            "type_name"   : "clock_plugin",
            "display_name": LANG_CLOCK,
            "description" : "",
            "fill_size" : false,
            "settings"    : [
                        {
                    "name"         : "show_date",
                    "display_name" : "<?php echo LANG_DATE;?>",
                    "type"         : "boolean"
                }

            ],
// Same as with datasource plugin, but there is no updateCallback parameter in this case.
            newInstance   : function(settings, newInstanceCallback)
            {
                newInstanceCallback(new myClockPlugin(settings));
            }
        });

        var clockDayNames= ["<?php echo LANG_WEEK_SUN;?>",
            "<?php echo LANG_WEEK_MON;?>",
            "<?php echo LANG_WEEK_TUE;?>",
            "<?php echo LANG_WEEK_WED;?>",
            "<?php echo LANG_WEEK_THU;?>",
            "<?php echo LANG_WEEK_FRI;?>",
            "<?php echo LANG_WEEK_SAT;?>"];


        var myClockPlugin = function(settings)
        {
            var self = this;
            var currentSettings = settings;



            var stateElement = $('<div><div class="clock dark"><div id="clock_date"></div><ul><li id="clock_hours"> </li><li id="point">:</li><li id="clock_min"> </li></ul></div></div>');
            //<li id="point">:</li><li id="clock_sec"></li>

            self.render = function(element)
            {
                setInterval( function() {
                    var hours = new Date().getHours();
                    $("#clock_hours").html(( hours < 10 ? "0" : "" ) + hours);
                    var minutes = new Date().getMinutes();
                    $("#clock_min").html(( minutes < 10 ? "0" : "" ) + minutes);
                    //var seconds = new Date().getSeconds();
                    //$("#clock_sec").html(( seconds < 10 ? "0" : "" ) + seconds);
                    if (currentSettings.show_date) {
                        var newDate = new Date();
                        newDate.setDate(newDate.getDate());
                        $('#clock_date').show();
                    } else {
                        $('#clock_date').hide();
                    }

                    $('#clock_date').html(clockDayNames[newDate.getDay()] + " " + newDate.getDate());
                }, 1000);
                $(element).append(stateElement);
            }

            self.getHeight = function()
            {
                return 4;
            }

            self.onSettingsChanged = function(newSettings)
            {
                currentSettings = newSettings;
            }

            self.onCalculatedValueChanged = function(settingName, newValue)
            {
            }

            self.onDispose = function()
            {
            }

        }




freeboard.loadWidgetPlugin({
                // Same stuff here as with datasource plugin.
                "type_name"   : "application_plugin",
                "display_name": LANG_APP,
        "description" : "MajorDoMo "+LANG_SECTION_APPLICATIONS,
                "fill_size" : true,
                "settings"    : [
                        {
                                "name"        : "app",
                                "display_name": LANG_APP,
                                "type"        : "option",
                                <?php

                                $files=scandir(DIR_MODULES,0);
                                $total = count($files);
                                $apps=array();
                                for ($i = 0; $i < $total; $i++) {
                    if ($files[$i]=='.' || $files[$i]=='..' || !is_dir(DIR_MODULES.$files[$i])) continue;
                    if (file_exists(DIR_MODULES.$files[$i].'/app')) {
                        //echo $files[$i]."!<br/>";
                        $apps[]=$files[$i];
                    } else {
                        //echo $files[$i]."<br/>";
                    }
                }

                                $project_modules=SQLSelect("SELECT * FROM project_modules");
                                $modules=array();
                                foreach($project_modules as $k=>$v) {
                    $modules[$v['NAME']]=$v;
                }

                                $res=array();
                                $total = count($apps);
                                for ($i = 0; $i < $total; $i++) {
                    $rec=array();
                    $rec['NAME']=$apps[$i];
                    if (isset($modules[$rec['NAME']])) {
                        $rec['TITLE']=$modules[$rec['NAME']]['TITLE'];
                    } else {
                        $rec['TITLE']=$rec['NAME'];
                    }
                    if (file_exists(ROOT.'img/modules/'.$rec['NAME'].'.png')) {
                        $rec['ICON']=$rec['NAME'].'.png';
                    } else {
                        $rec['ICON']='default.png';
                    }
                    $res[]=$rec;
                }

                                ?>
"options"     : [
<?php
foreach($res as $k=>$v) {
    echo '{';
    echo '"name" : "'.processTitle($v['TITLE']).'",'."\n";
    echo '"value" : "'.$v['NAME'].'"';
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
newInstanceCallback(new myApplicationPlugin(settings));
}
});
var myApplicationPlugin = function(settings)
{
var self = this;
var currentSettings = settings;
var widgetElement;
function updateFrame()
{
if(widgetElement)
{
var newHeight=parseInt(currentSettings.size)*60-20;
var myTextElement = $("<iframe style='margin-top:20px;height:"+newHeight+"px' src='<?php echo ROOTHTML;?>apps/"+currentSettings.app+".html' width='100%' height='"+newHeight+"' frameborder=0></iframe");
    $(widgetElement).append(myTextElement);
    }
    }

    self.render = function(element)
    {
    widgetElement = element;
    updateFrame();
    }

    self.getHeight = function()
    {
    return parseInt(currentSettings.size);
    }

    self.onSettingsChanged = function(newSettings)
    {
    currentSettings = newSettings;
    updateFrame();
    }

    self.onCalculatedValueChanged = function(settingName, newValue)
    {
    updateFrame();
    }

    self.onDispose = function()
    {
    }

    }

    freeboard.loadWidgetPlugin({
    // Same stuff here as with datasource plugin.
    "type_name"   : "iframe_plugin",
    "display_name": "IFrame",
    "description" : "General URL",
    // **fill_size** : If this is set to true, the widget will fill be allowed to fill the entire space given it, otherwise it will contain an automatic padding of around 10 pixels around it.
    "fill_size" : true,
    "settings"    : [
    {
    "name": "src",
    "display_name": "URL",
    "type": "calculated"
    },
    {
    "name"        : "size",
    "display_name": LANG_SIZE,
    "type"        : "option",
    "options"     : [
    {
    "name" : "1",
    "value": "1"
    },
    {
    "name" : "2",
    "value": "2"
    },
    {
    "name" : "3",
    "value": "3"
    }
    ,
    {
    "name" : "4",
    "value": "4"
    }
    ,
    {
    "name" : "5",
    "value": "5"
    }
    ,
    {
    "name" : "6",
    "value": "6"
    }
    ,
    {
    "name" : "7",
    "value": "7"
    }
    ,
    {
    "name" : "8",
    "value": "8"
    }
    ]
    }
    ],
    // Same as with datasource plugin, but there is no updateCallback parameter in this case.
    newInstance   : function(settings, newInstanceCallback)
    {
    newInstanceCallback(new myIframePlugin(settings));
    }
    });
    var myIframePlugin = function(settings)
    {
    var self = this;
    var currentSettings = settings;
    var widgetElement;
    function updateIFrame()
    {
    if(widgetElement)
    {
    var newHeight=parseInt(currentSettings.size)*100-20;
    var myTextElement = $("<iframe style='margin-top:20px;height:"+newHeight+"px' src='"+currentSettings.src+"' width='100%' height='"+newHeight+"' frameborder=0></iframe>");
    $(widgetElement).append(myTextElement);
    }
    }

    self.render = function(element)
    {
    widgetElement = element;
    updateIFrame();
    }

    self.getHeight = function()
    {
    return parseInt(currentSettings.size);
    }

    self.onSettingsChanged = function(newSettings)
    {
    currentSettings = newSettings;
    updateIFrame();
    }

    self.onCalculatedValueChanged = function(settingName, newValue)
    {
    updateIFrame();
    }

    self.onDispose = function()
    {
    }

    }

    }());
