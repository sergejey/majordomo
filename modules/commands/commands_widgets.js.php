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
        "type_name"   : "commands_plugin",
        "display_name": LANG_CONTROL_MENU,
        "description" : "MajorDoMo "+LANG_CONTROL_MENU,
        "fill_size" : true,
        "settings"    : [
            {
                "name"        : "menu",
                "display_name": LANG_CONTROL_MENU,
                "required" : true,
                "type"        : "option",
                <?php
                $scripts=SQLSelect("SELECT ID,TITLE FROM commands ORDER BY PARENT_ID, PRIORITY DESC, TITLE");
                ?>
                "options"     : [
                    <?php
                    foreach($scripts as $k=>$v) {
                        echo '{';
                        echo '"name" : "'.addcslashes($v['TITLE'],'"').'",'."\n";
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
            newInstanceCallback(new myMenuPlugin(settings));
        }
    });

    var myMenuPlugin = function(settings)
    {
        var self = this;
        var currentSettings = settings;
        var widgetElement;
        function updateMenuFrame()
        {
            if(widgetElement)
            {
                var newHeight=parseInt(currentSettings.size)*80-20;
                var myTextElement = $("<iframe style='margin-top:20px;height:"+newHeight+"px' src='<?php echo ROOTHTML;?>menu.html?parent="+currentSettings.menu+"' width='100%' height='"+newHeight+"' frameborder=0></iframe>");
                $(widgetElement).append(myTextElement);
            }
        }

        self.render = function(element)
        {
            widgetElement = element;
            updateMenuFrame();
        }

        self.getHeight = function()
        {
            return parseInt(currentSettings.size);
        }

        self.onSettingsChanged = function(newSettings)
        {
            currentSettings = newSettings;
            updateMenuFrame();
        }

        self.onCalculatedValueChanged = function(settingName, newValue)
        {
            updateMenuFrame();
        }

        self.onDispose = function()
        {
        }

    }


}());
