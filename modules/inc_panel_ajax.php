<?php

global $op;

if (!headers_sent()) {
    header("HTTP/1.0: 200 OK\n");
    header('Content-Type: text/html; charset=utf-8');
}

function evalConsole($code, $print = 0)
{
    if ($print == 1) {
        if (mb_substr($code, -1) == ';') {
            $code = mb_substr($code, 0, -1);
        }
        return eval('print_r(' . $code . ');');
    } else {
        setEvalCode($code);
        $eval_result = eval($code);
        setEvalCode();
        return $eval_result;
    }
}

if ($op == 'dismiss_notification') {
    $id = gr('id', 'int');
    SQLExec("UPDATE module_notifications SET IS_READ=1 WHERE ID=" . $id);
    echo "OK";
    exit;
}

if ($op == 'console') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    global $command;
    $code = explode('PHP_EOL', $command);

    foreach ($code as $value) {
        $value = trim($value);
        if (substr(mb_strtolower($value), 0, 4) == 'echo' || $value[0] == '$' || preg_match('/include/', $value)) {
            evalConsole(trim($value));
        } else {
            evalConsole(trim($value), 1);
        }
    }

}

if ($op == 'filter') {
    $title = gr('title', 'trim');

    $object_title = '';
    $property_title = '';

    if (preg_match('/^(.+)\.$/', $title, $m)) {
        $object_title = $m[1];
        $object = SQLSelectOne("SELECT * FROM objects WHERE TITLE LIKE '" . DBSafe($object_title) . "'");
        if ($object['ID']) {
            // OBJECT SELECTED
            $res .= '<div style="border-bottom: 1px solid #eeeeee;padding: 3px 5px;">';
            $resObj = '<span class="label label-warning">Объект</span> <a href="/panel/class/' . $object['CLASS_ID'] . '/object/' . $object['ID'] . '.html">' . $object['TITLE'] . '</a>';

            $class = SQLSelectOne("SELECT * FROM classes WHERE ID='" . $object['CLASS_ID'] . "'");
            if ($class['ID']) {
                $res .= '<div><span class="label label-default">' . LANG_CLASS . '</span> <a href="#" onClick="return setFilter(\'' . $class['TITLE'] . '.\');">' . $class['TITLE'] . "</a></div>";
            }
            $res .= $resObj;
            //properties and methods
            $properties = SQLSelect("SELECT properties.ID, properties.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN objects ON properties.OBJECT_ID=objects.ID WHERE (properties.OBJECT_ID = '" . DBSafe($object['ID']) . "' OR properties.CLASS_ID = '" . DBSafe($object['CLASS_ID']) . "') ORDER BY properties.TITLE");
            $total = count($properties);
            $base_link = '/panel/class/' . $object['CLASS_ID'] . '/object/' . $object['ID'] . '/properties.html';

            $res .= '<div style="padding-left: 10px">
				<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <span class="label label-success"><a style="color: white;" target="_blank" href="' . $base_link . '">' . LANG_PROPERTIES . '</a></span></p>
			</div>';

            for ($i = 0; $i < $total; $i++) {
                $res .= '<div style="padding-left: 20px">
					<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <a target="_blank" href="' . $base_link . '">' . $class['TITLE'] . '.' . $properties[$i]['TITLE'] . '</a></p>
				</div>';
            }
            $methods = SQLSelect("SELECT methods.ID, methods.TITLE, methods.OBJECT_ID, methods.CLASS_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT, objects.CLASS_ID AS OBJECT_CLASS_ID FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.OBJECT_ID = '" . DBSafe($object['ID']) . "' OR methods.CLASS_ID = '" . DBSafe($object['CLASS_ID']) . "') ORDER BY methods.OBJECT_ID DESC, methods.TITLE");
            $total = count($methods);
            $res .= '<div style="padding-left: 10px">
				<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <span class="label label-primary"><a style="color: white;" target="_blank" href="/panel/class/' . $class['ID'] . '/methods.html">' . LANG_METHODS . '</a></span></p>
			</div>';

            for ($i = 0; $i < $total; $i++) {
                $key = $object['TITLE'] . '.' . $methods[$i]['TITLE'];
                if ($seen[$key]) {
                    continue;
                }
                $seen[$key] = 1;

                if ($methods[$i]['OBJECT']) {
                    $res .= '<div style="padding-left: 20px">
						<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <a target="_blank" href="/panel/class/' . $methods[$i]['OBJECT_CLASS_ID'] . '/object/' . $methods[$i]['OBJECT_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['OBJECT'] . '.' . $methods[$i]['TITLE'] . '</a></p>
					</div>';
                } else {
                    $res .= '<div style="padding-left: 20px">
						<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <a target="_blank" href="/panel/class/' . $methods[$i]['CLASS_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['CLASS'] . '.' . $methods[$i]['TITLE'] . '</a></p>
					</div>';
                }
            }
            $res .= '</div>';
        }

        $class = SQLSelectOne("SELECT * FROM classes WHERE TITLE LIKE '" . DBSafe($m[1]) . "'");
        if ($class['ID']) {
            $res .= '<div style="border-bottom: 1px solid #eeeeee;padding: 3px 5px;"><span class="label label-default">' . LANG_CLASS . '</span> <a target="_blank" href="/panel/class/' . $class['ID'] . '.html">' . $class['TITLE'] . '</a>';

            //properties and methods
            $properties = SQLSelect("SELECT properties.ID, properties.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN objects ON properties.OBJECT_ID=objects.ID WHERE (properties.CLASS_ID = '" . DBSafe($class['ID']) . "') ORDER BY properties.TITLE");
            $total = count($properties);
            $res .= '<div style="padding-left: 10px">
				<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <span class="label label-success"><a style="color: white;" target="_blank" href="/panel/class/' . $class['ID'] . '/properties.html">' . LANG_PROPERTIES . '</a></span></p>
			</div>';

            for ($i = 0; $i < $total; $i++) {
                $res .= '<div style="padding-left: 20px">
					<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <a target="_blank" href="?action=properties&md=properties&view_mode=edit_properties&id=' . $properties[$i]['ID'] . '">' . $class['TITLE'] . '.' . $properties[$i]['TITLE'] . '</a></p>
				</div>';
            }
            $methods = SQLSelect("SELECT methods.ID, methods.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.CLASS_ID = '" . DBSafe($class['ID']) . "') ORDER BY methods.OBJECT_ID DESC, methods.TITLE");
            $total = count($methods);

            $res .= '<div style="padding-left: 10px">
				<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <span class="label label-primary"><a style="color: white;" target="_blank" href="/panel/class/' . $class['ID'] . '/methods.html">' . LANG_METHODS . '</a></span></p>
			</div>';

            for ($i = 0; $i < $total; $i++) {
                $key = $class['TITLE'] . '.' . $methods[$i]['TITLE'];
                if ($seen[$key]) {
                    continue;
                }
                $seen[$key] = 1;

                $res .= '<div style="padding-left: 20px">
					<p style="color: gray;font-size: 1.2rem;margin: 0px;">↳ <a target="_blank" href="/panel/class/' . $class['ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $class['TITLE'] . '.' . $methods[$i]['TITLE'] . '</a></p>
				</div>';
            }

            //$res .= '<span class="label label-warning">'.LANG_OBJECTS.'</span>';
            $objects = SQLSelect("SELECT ID, TITLE FROM objects WHERE (CLASS_ID = '" . $class['ID'] . "') ORDER BY TITLE");
            $total = count($objects);
            for ($i = 0; $i < $total; $i++) {
                $res .= '<div><span class="label label-warning">' . LANG_OBJECTS . '</span> <a href="#" onClick="return setFilter(\'' . $objects[$i]['TITLE'] . '.\');"><span style="color: gray;">' . $class['TITLE'] . '.</span>' . $objects[$i]['TITLE'] . '</a></div>';
            }
            $res .= '</div>';
        }

    }

    //Project Modules

    $items = SQLSelect("SELECT NAME,TITLE FROM project_modules WHERE TITLE LIKE '%" . DBSafe($title) . "%' AND HIDDEN=0");
    $iter = 0;
    $totalMod = count($items);
    foreach ($items as $item) {
        if ($iter == 0) $res .= '<div style="white-space: pre-wrap;">';
        $res .= '<div class="searchHoverBtn"><a class="btn" style="color: #333;background-color: #ffffff;border: 2px solid #5cb85c;" href="?md=panel&action=' . $item['NAME'] . '"><div style="font-size: .9rem;">Модуль</div>' . processTitle($item['TITLE']) . '</a></div>';
        $iter++;
        if ($iter == $totalMod) $res .= '</div>';
    }

    //classes
    $classes = SQLSelect("SELECT ID, TITLE, DESCRIPTION FROM classes WHERE TITLE LIKE '%" . DBSafe($title) . "%' OR DESCRIPTION LIKE '%" . DBSafe($title) . "%' ORDER BY TITLE");
    $total = count($classes);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-default">' . LANG_CLASS . '</span> <a href="#" onClick="return setFilter(\'' . $classes[$i]['TITLE'] . '.\');">' . $classes[$i]['TITLE'] . (($classes[$i]['DESCRIPTION']) ? '<small style="color: gray;padding-left: 5px;"><i class="glyphicon glyphicon-arrow-right" style="font-size: .8rem;vertical-align: text-top;color: lightgray;"></i> ' . $classes[$i]['DESCRIPTION'] . '</small>' : '') . '</a></div>';
    }
    //objects
    $objects = SQLSelect("SELECT ID, TITLE, DESCRIPTION FROM objects WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR DESCRIPTION LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($objects);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-warning">Объект</span> <a href="#" onClick="return setFilter(\'' . $objects[$i]['TITLE'] . '.\');">' . $objects[$i]['TITLE'] . (($objects[$i]['DESCRIPTION']) ? '<small style="color: gray;padding-left: 5px;"><i class="glyphicon glyphicon-arrow-right" style="font-size: .8rem;vertical-align: text-top;color: lightgray;"></i> ' . $objects[$i]['DESCRIPTION'] . '</small>' : '') . '</a></div>';
    }

    //properties and methods
    $qry = "SELECT properties.ID, properties.CLASS_ID, properties.TITLE, properties.DESCRIPTION, objects.CLASS_ID AS OBJECT_CLASS_ID, objects.ID AS OBJECT_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT, objects.DESCRIPTION AS OBJECT_DESCRIPTION FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN pvalues ON (properties.ID=pvalues.PROPERTY_ID AND (properties.OBJECT_ID=pvalues.OBJECT_ID OR properties.OBJECT_ID=0)) LEFT JOIN objects ON (properties.OBJECT_ID=objects.ID OR pvalues.OBJECT_ID=objects.ID)  WHERE (properties.TITLE LIKE '%" . DBSafe($title) . "%' OR properties.DESCRIPTION LIKE '%" . DBSafe($title) . "%' OR pvalues.VALUE LIKE '%" . DBSafe($title) . "%') ORDER BY properties.TITLE";
    $properties = SQLSelect($qry);
    $total = count($properties);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-success">Свойство</span> '; //<a href="/panel/object/'.'">
        if ($properties[$i]['OBJECT']) {
            $res .= '<a target="_blank" href="/panel/class/' . $properties[$i]['OBJECT_CLASS_ID'] . '/object/' . $properties[$i]['OBJECT_ID'] . '/properties.html">' . $methods[$i]['OBJECT'];
            $res .= $properties[$i]['OBJECT'];
        } else {
            $res .= '<a target="_blank" href="/panel/class/' . $properties[$i]['CLASS_ID'] . '/properties.html">' . $methods[$i]['OBJECT'];
            $res .= $properties[$i]['CLASS'];
        }
        $res .= '.' . $properties[$i]['TITLE'] . (($properties[$i]['DESCRIPTION']) ? '<small style="color: gray;padding-left: 5px;"><i class="glyphicon glyphicon-arrow-right" style="font-size: .8rem;vertical-align: text-top;color: lightgray;"></i> ' . $properties[$i]['DESCRIPTION'] . '</small>' : '') . '</a></div>';
    }

    $methods = SQLSelect("SELECT methods.ID, methods.TITLE, methods.OBJECT_ID, methods.DESCRIPTION, objects.CLASS_ID AS OBJECT_CLASS_ID, methods.CLASS_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.TITLE LIKE '%" . DBSafe($title) . "%' OR methods.CODE LIKE '%" . DBSafe($title) . "%' OR methods.DESCRIPTION LIKE '%" . DBSafe($title) . "%') ORDER BY methods.TITLE");
    $total = count($methods);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-primary">Метод</span> '; //<a href="#">
        if ($methods[$i]['OBJECT']) {
            $res .= '<a target="_blank" href="/panel/class/' . $methods[$i]['OBJECT_CLASS_ID'] . '/object/' . $methods[$i]['OBJECT_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['OBJECT'];
        } else {
            $res .= '<a target="_blank" href="/panel/class/' . $methods[$i]['CLASS_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['CLASS'];
        }
        $res .= '.' . $methods[$i]['TITLE'] . (($methods[$i]['DESCRIPTION']) ? '<small style="color: gray;padding-left: 5px;"><i class="glyphicon glyphicon-arrow-right" style="font-size: .8rem;vertical-align: text-top;color: lightgray;"></i> ' . $methods[$i]['DESCRIPTION'] . '</small>' : '') . '</a></div>';
    }
    //scripts
    $scripts = SQLSelect("SELECT ID, TITLE FROM scripts WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($scripts);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-info">Скрипты</span> <a target="_blank" href="/panel/script/' . $scripts[$i]['ID'] . '.html">' . $scripts[$i]['TITLE'] . '</a></div>';
    }
    //patterns
    $patterns = SQLSelect("SELECT ID, TITLE FROM patterns WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR SCRIPT LIKE '%" . DBSafe($title) . "%' OR PATTERN LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($patterns);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label" style="background-color: #ff81d5;">Шаб. пов.</span> <a target="_blank" href="/panel/pattern/' . $patterns[$i]['ID'] . '.html">' . $patterns[$i]['TITLE'] . '</a></div>';
    }
    //menu elements (to-do: content)
    $commands = SQLSelect("SELECT ID, TITLE FROM commands WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR ONCHANGE_METHOD LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($commands);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-danger">Меню</span> <a href="/panel/command/' . $commands[$i]['ID'] . '.html">' . $commands[$i]['TITLE'] . '</a></div>';
    }

    //scene states
    $states = SQLSelect("SELECT elm_states.ID, elm_states.TITLE, ELEMENT_ID, elements.SCENE_ID, elements.TITLE AS ELEMENT_TITLE FROM elm_states LEFT JOIN elements ON elements.ID=elm_states.ELEMENT_ID WHERE (elm_states.LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR elm_states.LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR elm_states.ACTION_METHOD LIKE '%" . DBSafe($title) . "%' OR elm_states.CONDITION_ADVANCED LIKE '%" . DBSafe($title) . "%' OR elm_states.CONDITION_VALUE LIKE '%" . DBSafe($title) . "%') AND elements.ID>0 ORDER BY elm_states.TITLE");
    $total = count($states);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-warning">Сцены</span> <a href="/panel/scene/' . $states[$i]['SCENE_ID'] . '/elements/' . $states[$i]['ELEMENT_ID'] . '/state' . $states[$i]['ID'] . '.html">' . $states[$i]['ELEMENT_TITLE'] . '.' . $states[$i]['TITLE'] . '</a></div>';
    }
    //scene elements
    $elements = SQLSelect("SELECT elements.ID, elements.SCENE_ID, elements.TITLE FROM elements WHERE (elements.LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR elements.LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR elements.TITLE LIKE '%" . DBSafe($title) . "%') ORDER BY elements.TITLE");
    $total = count($elements);
    for ($i = 0; $i < $total; $i++) {
        $res .= '<div class="searchHover"><span class="label label-warning">Сцены</span> <a href="/panel/scene/' . $elements[$i]['SCENE_ID'] . '/elements/' . $elements[$i]['ID'] . '.html">' . $elements[$i]['TITLE'] . '</a></div>';
    }

    //zwave devices
    if (file_exists(DIR_MODULES . 'zwave/zwave.class.php')) {
        $devices = SQLSelect("SELECT ID, DEVICE_ID, TITLE FROM zwave_properties WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
        $total = count($devices);
        for ($i = 0; $i < $total; $i++) {
            $res .= 'ZWave: <a href="/panel/zwave/' . $devices[$i]['DEVICE_ID'] . '.html">' . $devices[$i]['TITLE'] . '</a><br>';
        }
    }

    //Simple Devices
    if (file_exists(DIR_MODULES . 'devices/devices.class.php')) {
        $devices = SQLSelect("SELECT ID, TITLE FROM devices WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR LINKED_OBJECT LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
        $total = count($devices);
        for ($i = 0; $i < $total; $i++) {
            $res .= '<div class="searchHover"><span class="label" style="background-color: #1500f4;">ПУ</span> <a href="/panel/devices/' . $devices[$i]['ID'] . '.html">' . $devices[$i]['TITLE'] . '</a></div>';
        }
    }

    //GPS #1500f4
    if (file_exists(DIR_MODULES . 'app_gpstrack/app_gpstrack.class.php')) {
        $devices = SQLSelect("SELECT gpsactions.ID, gpslocations.TITLE, users.NAME FROM gpsactions LEFT JOIN users ON gpsactions.USER_ID=users.ID LEFT JOIN gpslocations ON gpsactions.LOCATION_ID=gpslocations.ID WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY gpslocations.TITLE");
        $total = count($devices);
        for ($i = 0; $i < $total; $i++) {
            $res .= '<div class="searchHover"><span class="label" style="background-color: #af81ff;">GPS</span> <a href="/panel/app_gpstrack/action_' . $devices[$i]['ID'] . '.html">' . $devices[$i]['TITLE'] . ' - ' . $devices[$i]['NAME'] . '</a></div>';
        }
    }

    // find in modules
    $items = SQLSelect("SELECT * FROM project_modules");
    foreach ($items as $item) {
        $module_name = $item['NAME'];
        $module_file = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';
        if (file_exists($module_file)) {
            include_once($module_file);
            $module = new $module_name;
            if (method_exists($module, 'findData')) {
                $result = $module->findData($title);
                foreach ($result as $data) {
                    $res .= '<div class="searchHover"><span class="label" style="background-color: #5cb85c;">&nbsp;' . $item['TITLE'] . '</span>' . $data . '</div>';
                }
            }
        }
    }

    //todo: webvars
    //todo: patterns

    //$arrayResult = explode('<br>', $res);
    //$arraySlice = array_slice($arrayResult, 0, 20);

    //echo '<pre>';
    //var_dump($arraySlice);

    if ($res) {
        echo '
		<style>
		a {
			vertical-align: middle;
			text-decoration: none;
		}
		a:hover {
			font-weight: bold;
			text-decoration: none;
		}
		.searchHover:hover {
			background: #e8e8e8;
		}
		.searchHover {
			border-bottom: 1px solid #eeeeee;
			padding: 3px 5px;
		}
		.searchHoverBtn {
			border-bottom: 1px solid #eeeeee;
			padding: 3px 5px;
			display: inline-block;
		}
		</style>
		';
    }

    echo $res;
}
exit;


