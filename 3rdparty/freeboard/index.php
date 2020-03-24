<?php
chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");


$ctl = new control_modules();

if ($_GET['theme']) {
    $theme=$_GET['theme'];
} else {
    $theme=SETTINGS_THEME;
}
?>
<!DOCTYPE html>
<!-- Dev Version links to full versions (non-minified) of javascript and css files -->
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
        <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="viewport" content = "width = device-width, initial-scale = 1, user-scalable = no" />
    <link href="lib/css/thirdparty/jquery.gridster.min.css" rel="stylesheet" />
        <link href="lib/css/thirdparty/codemirror.css" rel="stylesheet" />
        <link href="lib/css/thirdparty/codemirror-ambiance.css" rel="stylesheet" />
    <link href="lib/css/freeboard/styles.css" rel="stylesheet" />
    <?php if ($theme=='light') {?>
    <style>
        body {
            background-color:white;
            color:black;
        }
        .gridster .gs_w {
            background: #eeeeee;
        }
        .gridster header {
            background: #bbbbbb;
            color: #000000;
        }
        .modal {
            color:white;
        }
        #toggle-header {
            background-color:#bbbbbb;
        }
    </style>
    <?php }?>
    <?php if ($_GET['background_image']) {?>
        <style>
            body {
                <?php echo 'background-image:url('.$_GET['background_image'].')';?>
            }
        </style>
    <?php }?>
    <link href="/css/devices.css?v=19-10-15" rel="stylesheet" type="text/css"/>

    <?php

    $modules=SQLSelect("SELECT ID,NAME FROM project_modules");
    $total = count($modules);
    for ($i = 0; $i < $total; $i++) {
        $path=DIR_TEMPLATES.$modules[$i]['NAME'].'/'.$modules[$i]['NAME'].'_widgets.css';
        if (file_exists($path)) {
            echo '<link href="'.ROOTHTML."templates/".$modules[$i]['NAME'].'/'.$modules[$i]['NAME'].'_widgets.css" rel="stylesheet" />'."\n";
        }
    }
    ?>



    <script src="<?php echo ROOTHTML;?>3rdparty/freeboard/lib/js/thirdparty/jquery.js"></script>
    <!--#
    <script src="<?php echo ROOTHTML;?>3rdparty/freeboard/lib/js/thirdparty/head.js"></script>
    <script type="text/javascript" src="<?php echo ROOTHTML;?>3rdparty/jquery/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="<?php echo ROOTHTML;?>3rdparty/jquery/jquery-migrate-3.0.0.min.js"></script>

    #-->

    <script src="<?php echo ROOTHTML;?>3rdparty/freeboard/lib/js/thirdparty/head.min.js"></script>





    <link rel="stylesheet" type="text/css" href="<?php echo ROOTHTML;?>3rdparty/fancybox/jquery.fancybox.min.css?v=3.3.5" media="screen" />
    <script type="text/javascript" src="<?php echo ROOTHTML;?>3rdparty/fancybox/jquery.fancybox.min.js?v=3.3.5"></script>


    <?php
    $out['REQUEST_URI']=$_SERVER['REQUEST_URI'];
    $template_file=DIR_TEMPLATES.'websockets.html';
    $p=new parser($template_file, $out);
    echo $p->result;
    ?>
    <script type="text/javascript">

        ROOTHTML = '<?php echo ROOTHTML;?>';

        <?php
            $constants=get_defined_constants();
            foreach($constants as $k=>$v) {
                if (preg_match('/^LANG_/',$k) && !is_array($v)) {
                    echo "const $k = '".addcslashes($v,"'")."';\n";
                }
            }
        ?>
        <?php if($_GET['layout_id']!='') {?>
        var layoutId='<?php
            echo $_GET['layout_id'];
            ?>';
        <?php }?>

        head.js("lib/js/thirdparty/knockout.js",
                "lib/js/thirdparty/jquery-ui.js",
                "lib/js/thirdparty/underscore.js",
                "lib/js/thirdparty/jquery.gridster.js",
                "lib/js/thirdparty/jquery.caret.js",
                                "lib/js/thirdparty/codemirror.js",
                                "lib/js/thirdparty/jquery.xdomainrequest.js",

                "lib/js/freeboard/FreeboardModel.js",
                "lib/js/freeboard/DatasourceModel.js",
                "lib/js/freeboard/PaneModel.js",
                "lib/js/freeboard/WidgetModel.js",
                "lib/js/freeboard/FreeboardUI.js",
                "lib/js/freeboard/DialogBox.js",
                "lib/js/freeboard/PluginEditor.js",
                "lib/js/freeboard/ValueEditor.js",
                "lib/js/freeboard/JSEditor.js",
                "lib/js/freeboard/DeveloperConsole.js",
                "lib/js/freeboard/freeboard.js",

                "plugins/freeboard/freeboard.datasources.js",

                "plugins/freeboard/freeboard.widgets.js",
                "plugins/thirdparty/handlebars.js",
                "plugins/thirdparty/actuator.js",

            "../../js/scripts.js",

            <?php

            $modules=SQLSelect("SELECT ID,NAME FROM project_modules");
            $total = count($modules);
            for ($i = 0; $i < $total; $i++) {
                $path=DIR_MODULES.$modules[$i]['NAME'].'/'.$modules[$i]['NAME'].'_widgets.js.php';
                if (file_exists($path)) {
                    echo '"'.ROOTHTML."modules/".$modules[$i]['NAME'].'/'.$modules[$i]['NAME'].'_widgets.js.php?theme='.$_GET['theme'].'",';
                }
            }

            ?>

                // *** Load more plugins here ***
                function(){
                    $(function()
                    { //DOM Ready
                        freeboard.setAssetRoot("/freeboard-ui/");
                        freeboard.initialize(true);
                        if (layoutId) {
                            loadBoardLayout();
                        }
                    });
                });


                  function loadBoardLayout() {
                   var url='<?php echo ROOTHTML;?>ajax/layouts.html?op=loaddashboard&id='+layoutId;
                      $.ajax({
                          url: url
                      }).done(function(data) {
                          if (data!='') {
                              //alert(data)
                              freeboard.loadDashboard(JSON.parse(data));
                          }
                      });
                  }

                 function saveBoardLayout() {
                  var url='<?php echo ROOTHTML;?>ajax/layouts.html';
                  var data=JSON.stringify(freeboard.serialize());

                     var jqxhr = $.post(url,{
                         op: 'savedashboard',
                         id: layoutId,
                         data: data
                     })
                         .success(function() {
                             freeboard.setEditing(false);
                             window.location.reload();
                         })
                         .error(function() {  })
                         .complete(function() {  });
                  return false;
                 }
    </script>
</head>
<body>
<div id="board-content">
    <img id="dash-logo" data-bind="attr:{src: header_image}, visible:header_image()">
    <div class="gridster responsive-column-width">
        <ul data-bind="grid: true">
        </ul>
    </div>
</div>
<header id="main-header" data-bind="if:allow_edit">
    <div id="admin-bar">
        <div id="admin-menu">
            <div id="board-tools">
                <div id="board-actions">
                    <ul class="board-toolbar vertical">
                    <!--
                        <li data-bind="click: loadDashboardFromLocalFile"><i id="full-screen-icon" class="icon-folder-open icon-white"></i><label id="full-screen">Load Freeboard</label></li>
                        -->
                        <li><i class="icon-download-alt icon-white"></i>
                            <label onclick="saveBoardLayout();"><?php echo LANG_SAVE_CHANGES;?></label>
                            <label style="display: none;" data-bind="click: saveDashboard" data-pretty="true">[Pretty]</label>
                            <label style="display: none;" data-bind="click: saveDashboard" data-pretty="false">[Minified]</label>
                        </li>
                        <li id="add-pane" data-bind="click: createPane"><i class="icon-plus icon-white"></i><label><?php echo LANG_ADD_PANE;?></label></li>
                    </ul>
                </div>
            </div>
            <div id="datasources">
                <h2 class="title"><?php echo LANG_DATA;?></h2>

                <div class="datasource-list-container">
                    <table class="table table-condensed sub-table" id="datasources-list" data-bind="if: datasources().length">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Last Updated</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: datasources">
                        <tr>
                            <td>
                                <span class="text-button datasource-name" data-bind="text: name, pluginEditor: {operation: 'edit', type: 'datasource'}"></span>
                            </td>
                            <td data-bind="text: last_updated"></td>
                            <td>
                                <ul class="board-toolbar">
                                    <li data-bind="click: updateNow"><i class="icon-refresh icon-white"></i></li>
                                    <li data-bind="pluginEditor: {operation: 'delete', type: 'datasource'}">
                                        <i class="icon-trash icon-white"></i></li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <span class="text-button table-operation" data-bind="pluginEditor: {operation: 'add', type: 'datasource'}"><?php echo LANG_ADD;?></span>
            </div>
        </div>
    </div>
        <div id="column-tools" class="responsive-column-width">
                <ul class="board-toolbar left-columns">
                        <li class="column-tool add" data-bind="click: addGridColumnLeft"><span class="column-icon right"></span><i class="icon-arrow-left icon-white"></i></li>
                        <li class="column-tool sub" data-bind="click: subGridColumnLeft"><span class="column-icon left"></span><i class="icon-arrow-right icon-white"></i></li>
                </ul>
                <ul class="board-toolbar right-columns">
                        <li class="column-tool sub" data-bind="click: subGridColumnRight"><span class="column-icon right"></span><i class="icon-arrow-left icon-white"></i></li>
                        <li class="column-tool add" data-bind="click: addGridColumnRight"><span class="column-icon left"></span><i class="icon-arrow-right icon-white"></i></li>
                </ul>
        </div>
    <div id="toggle-header" data-bind="click: toggleEditing">
        <i id="toggle-header-icon" class="icon-wrench icon-white"></i>
        </div>
</header>

<div style="display:hidden">
    <ul data-bind="template: { name: 'pane-template', foreach: panes}">
    </ul>
</div>

<script type="text/html" id="pane-template">
    <li data-bind="pane: true">
        <header>
            <h1 data-bind="text: title"></h1>
            <ul class="board-toolbar pane-tools">
                <li data-bind="pluginEditor: {operation: 'add', type: 'widget'}">
                    <i class="icon-plus icon-white"></i>
                </li>
                <li data-bind="pluginEditor: {operation: 'edit', type: 'pane'}">
                    <i class="icon-wrench icon-white"></i>
                </li>
                <li data-bind="pluginEditor: {operation: 'delete', type: 'pane'}">
                    <i class="icon-trash icon-white"></i>
                </li>
            </ul>
        </header>
        <section data-bind="foreach: widgets">
            <div class="sub-section" data-bind="css: 'sub-section-height-' + height()">
                <div class="widget" data-bind="widget: true, css:{fillsize:fillSize}"></div>
                <div class="sub-section-tools">
                    <ul class="board-toolbar">
                        <!-- ko if:$parent.widgetCanMoveUp($data) -->
                        <li data-bind="click:$parent.moveWidgetUp"><i class="icon-chevron-up icon-white"></i></li>
                        <!-- /ko -->
                        <!-- ko if:$parent.widgetCanMoveDown($data) -->
                        <li data-bind="click:$parent.moveWidgetDown"><i class="icon-chevron-down icon-white"></i></li>
                        <!-- /ko -->
                        <li data-bind="pluginEditor: {operation: 'edit', type: 'widget'}"><i class="icon-wrench icon-white"></i></li>
                        <li data-bind="pluginEditor: {operation: 'delete', type: 'widget'}"><i class="icon-trash icon-white"></i></li>
                    </ul>
                </div>
            </div>
        </section>
    </li>
</script>
</body>
</html>