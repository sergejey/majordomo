<?php
header("Content-type:application/javascript");
chdir(dirname(__FILE__) . '/../../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./load_settings.php");
?>

function runScriptClicked(script) {
    if (freeboard.isEditing()) return;
    runScript(script);
    $('#script_light'+script).addClass('on');
    setTimeout("$('#script_light"+script+"').removeClass('on');",1000);
}

(function()
{

    freeboard.loadWidgetPlugin({
        // Same stuff here as with datasource plugin.
        "type_name"   : "scripts_plugin",
        "display_name": LANG_SCRIPTS,
        "description" : "MajorDoMo "+LANG_SCRIPTS,
        "fill_size" : false,
        "settings"    : [
            {
                "name"        : "script",
                "display_name": LANG_SCRIPT,
                "required" : true,
                "type"        : "option",
                <?php
                $scripts=SQLSelect("SELECT ID,TITLE FROM scripts ORDER BY TITLE");
                ?>
                "options"     : [
                    <?php
                    foreach($scripts as $k=>$v) {
                        echo '{';
                        echo '"name" : "'.processTitle($v['TITLE']).'",'."\n";
                        echo '"value" : "'.$v['ID'].'"';
                        echo '},';
                    }
                    ?>
                ]
            },
            {
                "name"         : "title",
                "display_name" : LANG_TITLE,
                "type"         : "calculated",
                "required" : true
            }

        ],
// Same as with datasource plugin, but there is no updateCallback parameter in this case.
        newInstance   : function(settings, newInstanceCallback)
        {
            newInstanceCallback(new myScriptsPlugin(settings));
        }
    });

    freeboard.addStyle('.indicator-light', "border-radius:50%;width:22px;height:22px;border:2px solid #3d3d3d;margin-top:5px;float:left;margin-right:10px;");
    freeboard.addStyle('.indicator-light.on', "background-color:#FFC773;box-shadow: 0px 0px 15px #FF9900;border-color:#FDF1DF;");
    freeboard.addStyle('.indicator-text', "margin-top:10px;cursor:pointer;vertical-align:middle;");
    var myScriptsPlugin = function(settings)
    {
        var self = this;
        var currentSettings = settings;

        var stateElement = $('<div class="indicator-text" onclick="runScriptClicked('+currentSettings.script+');">'+settings.title+'</div>');
        var indicatorElement = $('<div class="indicator-light" id="script_light'+currentSettings.script+'"></div>');

        self.render = function(element)
        {
            $(element).append(indicatorElement).append(stateElement);
        }

        self.getHeight = function()
        {
            return 1;
        }

        self.onSettingsChanged = function(newSettings)
        {
            currentSettings = newSettings;
            stateElement.text((_.isUndefined(newSettings.title) ? "" : newSettings.title));
        }

        self.onCalculatedValueChanged = function(settingName, newValue)
        {
        }

        self.onDispose = function()
        {
        }

    }


}());
