<?php

global $op;

if (!headers_sent()) {
    header("HTTP/1.0: 200 OK\n");
    header('Content-Type: text/html; charset=utf-8');
}

function evalConsole($code, $print = 0) {
	if($print == 1) {
		if(mb_substr($code, -1) == ';') {
			$code = mb_substr($code, 0, -1);
		}
		return eval('print_r('.$code.');');
	} else {
		return eval($code);
	}
}

if ($op == 'console') {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(E_ALL);

    global $command;
	$code = explode('PHP_EOL', $command);
	
	foreach($code as $value) {
		$value = trim($value);
		if (substr(mb_strtolower($value), 0, 4) == 'echo' || $value[0] == '$' || preg_match('/include/', $value)) {
			evalConsole(trim($value));
		} else {
			evalConsole(trim($value), 1);
		}
	}

}

if ($op == 'filter') {
    global $title;

    $object_title = '';
    $property_title = '';

    if (preg_match('/^(.+)\.$/', $title, $m)) {
        $object_title = $m[1];
        $object = SQLSelectOne("SELECT * FROM objects WHERE TITLE LIKE '" . DBSafe($object_title) . "'");
        if ($object['ID']) {
            // OBJECT SELECTED
            $res .= LANG_OBJECT . " <b><a href='/panel/class/" . $object['CLASS_ID'] . "/object/" . $object['ID'] . ".html'>" . $object['TITLE'] . "</a></b><br>";

            $class = SQLSelectOne("SELECT * FROM classes WHERE ID='" . $object['CLASS_ID'] . "'");
            if ($class['ID']) {
                $res .= LANG_CLASS . ' <b><a href="#" onClick="return setFilter(\'' . $class['TITLE'] . '.\');">' . $class['TITLE'] . "</a></b><br>";
            }

            //properties and methods
            $properties = SQLSelect("SELECT properties.ID, properties.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN objects ON properties.OBJECT_ID=objects.ID WHERE (properties.OBJECT_ID = '" . DBSafe($object['ID']) . "' OR properties.CLASS_ID = '" . DBSafe($object['CLASS_ID']) . "') ORDER BY properties.TITLE");
            $total = count($properties);
            $base_link = '/panel/class/' . $object['CLASS_ID'] . '/object/' . $object['ID'] . '/properties.html';
            $res .= '<a href="' . $base_link . '">' . LANG_PROPERTIES . "</a>:<br>";
            for ($i = 0; $i < $total; $i++) {
                $res .= '' . $object['TITLE'];
                $res .= '.' . $properties[$i]['TITLE'];
                $res .= ' <a href="' . $base_link . '#" title="' . htmlspecialchars(gg($object['TITLE'] . '.' . $properties[$i]['TITLE'])) . '">#</a>';
                $res .= "<br/>";
            }
            $methods = SQLSelect("SELECT methods.ID, methods.TITLE, methods.OBJECT_ID, methods.CLASS_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT, objects.CLASS_ID AS OBJECT_CLASS_ID FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.OBJECT_ID = '" . DBSafe($object['ID']) . "' OR methods.CLASS_ID = '" . DBSafe($object['CLASS_ID']) . "') ORDER BY methods.OBJECT_ID DESC, methods.TITLE");
            $total = count($methods);
            $res .= '<a href="/panel/class/' . $object['CLASS_ID'] . '/object/' . $object['ID'] . '/methods.html">' . LANG_METHODS . "</a>:<br>";
            for ($i = 0; $i < $total; $i++) {
                $key = $object['TITLE'] . '.' . $methods[$i]['TITLE'];
                if ($seen[$key]) {
                    continue;
                }
                $seen[$key] = 1;
                if ($methods[$i]['OBJECT']) {
                    $res .= '<a href="/panel/class/' . $methods[$i]['OBJECT_CLASS_ID'] . '/object/' . $methods[$i]['OBJECT_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['OBJECT'];
                } else {
                    $res .= '<a href="/panel/class/' . $methods[$i]['CLASS_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['CLASS'];
                }
                $res .= '.' . $methods[$i]['TITLE'] . '</a><br>';
            }
        }

        $class = SQLSelectOne("SELECT * FROM classes WHERE TITLE LIKE '" . DBSafe($m[1]) . "'");
        if ($class['ID']) {
            $res .= LANG_CLASS . " <b><a href='/panel/class/" . $class['ID'] . ".html'>" . $class['TITLE'] . "</a></b><br>";

            //properties and methods
            $properties = SQLSelect("SELECT properties.ID, properties.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN objects ON properties.OBJECT_ID=objects.ID WHERE (properties.CLASS_ID = '" . DBSafe($class['ID']) . "') ORDER BY properties.TITLE");
            $total = count($properties);
            $res .= '<a href="/panel/class/' . $class['ID'] . '/properties.html">' . LANG_PROPERTIES . "</a>:<br>";
            for ($i = 0; $i < $total; $i++) {
                $res .= '' . $class['TITLE'];
                $res .= '.' . $properties[$i]['TITLE'] . '<br>';
            }
            $methods = SQLSelect("SELECT methods.ID, methods.TITLE, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.CLASS_ID = '" . DBSafe($class['ID']) . "') ORDER BY methods.OBJECT_ID DESC, methods.TITLE");
            $total = count($methods);
            $res .= '<a href="/panel/class/' . $class['ID'] . '/methods.html">' . LANG_METHODS . "</a>:<br>";
            for ($i = 0; $i < $total; $i++) {
                $key = $class['TITLE'] . '.' . $methods[$i]['TITLE'];
                if ($seen[$key]) {
                    continue;
                }
                $seen[$key] = 1;
                $res .= '<a href="/panel/class/' . $class['ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $class['TITLE'];
                $res .= '.' . $methods[$i]['TITLE'] . '</a><br>';
            }

            $res .= '' . LANG_OBJECTS . ":<br>";
            $objects = SQLSelect("SELECT ID, TITLE FROM objects WHERE (CLASS_ID = '" . $class['ID'] . "') ORDER BY TITLE");
            $total = count($objects);
            for ($i = 0; $i < $total; $i++) {
                $res .= '<a href="#" onClick="return setFilter(\'' . $objects[$i]['TITLE'] . '.\');">' . $objects[$i]['TITLE'] . '</a><br>';
            }

        }

    }

    //Project Modules
    $items = SQLSelect("SELECT NAME,TITLE FROM project_modules WHERE TITLE LIKE '%".DBSafe($title)."%' AND HIDDEN=0");
    foreach($items as $item) {
        $res.= '<a href="?md=panel&action='.$item['NAME'].'">'.processTitle($item['TITLE']).'</a><br/>';
    }

    //classes
    $classes = SQLSelect("SELECT ID, TITLE FROM classes WHERE TITLE LIKE '%" . DBSafe($title) . "%' ORDER BY TITLE");
    $total = count($classes);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Class: <a href="#" onClick="return setFilter(\'' . $classes[$i]['TITLE'] . '.\');">' . $classes[$i]['TITLE'] . '</a><br>';
    }
    //objects
    $objects = SQLSelect("SELECT ID, TITLE FROM objects WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR DESCRIPTION LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($objects);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Obj: <a href="#" onClick="return setFilter(\'' . $objects[$i]['TITLE'] . '.\');">' . $objects[$i]['TITLE'] . '</a><br>';
    }

    //properties and methods
    $qry = "SELECT properties.ID, properties.CLASS_ID, properties.TITLE, objects.CLASS_ID AS OBJECT_CLASS_ID, objects.ID AS OBJECT_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN pvalues ON (properties.ID=pvalues.PROPERTY_ID AND (properties.OBJECT_ID=pvalues.OBJECT_ID OR properties.OBJECT_ID=0)) LEFT JOIN objects ON (properties.OBJECT_ID=objects.ID OR pvalues.OBJECT_ID=objects.ID)  WHERE (properties.TITLE LIKE '%" . DBSafe($title) . "%' OR pvalues.VALUE LIKE '%" . DBSafe($title) . "%') ORDER BY properties.TITLE";
    $properties = SQLSelect($qry);
    $total = count($properties);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'P: '; //<a href="/panel/object/'.'">
        if ($properties[$i]['OBJECT']) {
            $res .= '<a href="/panel/class/' . $properties[$i]['OBJECT_CLASS_ID'] . '/object/' . $properties[$i]['OBJECT_ID'] . '/properties.html">' . $methods[$i]['OBJECT'];
            $res .= $properties[$i]['OBJECT'];
        } else {
            $res .= '<a href="/panel/class/' . $properties[$i]['CLASS_ID'] . '/properties.html">' . $methods[$i]['OBJECT'];
            $res .= $properties[$i]['CLASS'];
        }
        $res .= '.' . $properties[$i]['TITLE'] . '</a><br>';
    }

    $methods = SQLSelect("SELECT methods.ID, methods.TITLE, methods.OBJECT_ID, objects.CLASS_ID AS OBJECT_CLASS_ID, methods.CLASS_ID, classes.TITLE AS CLASS, objects.TITLE AS OBJECT FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.TITLE LIKE '%" . DBSafe($title) . "%' OR methods.CODE LIKE '%" . DBSafe($title) . "%') ORDER BY methods.TITLE");
    $total = count($methods);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'M: '; //<a href="#">
        if ($methods[$i]['OBJECT']) {
            $res .= '<a href="/panel/class/' . $methods[$i]['OBJECT_CLASS_ID'] . '/object/' . $methods[$i]['OBJECT_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['OBJECT'];
        } else {
            $res .= '<a href="/panel/class/' . $methods[$i]['CLASS_ID'] . '/methods/' . $methods[$i]['ID'] . '.html">' . $methods[$i]['CLASS'];
        }
        $res .= '.' . $methods[$i]['TITLE'] . '</a><br>';
    }
    //scripts
    $scripts = SQLSelect("SELECT ID, TITLE FROM scripts WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($scripts);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Script: <a href="/panel/script/' . $scripts[$i]['ID'] . '.html">' . $scripts[$i]['TITLE'] . '</a><br>';
    }
    //patterns
    $patterns = SQLSelect("SELECT ID, TITLE FROM patterns WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR SCRIPT LIKE '%" . DBSafe($title) . "%' OR PATTERN LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($patterns);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Pattern: <a href="/panel/pattern/' . $patterns[$i]['ID'] . '.html">' . $patterns[$i]['TITLE'] . '</a><br>';
    }
    //menu elements (to-do: content)
    $commands = SQLSelect("SELECT ID, TITLE FROM commands WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR ONCHANGE_METHOD LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY TITLE");
    $total = count($commands);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Menu: <a href="/panel/command/' . $commands[$i]['ID'] . '.html">' . $commands[$i]['TITLE'] . '</a><br>';
    }

    //scene states
    $states = SQLSelect("SELECT elm_states.ID, elm_states.TITLE, ELEMENT_ID, elements.SCENE_ID, elements.TITLE AS ELEMENT_TITLE FROM elm_states LEFT JOIN elements ON elements.ID=elm_states.ELEMENT_ID WHERE (elm_states.LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR elm_states.LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR elm_states.ACTION_METHOD LIKE '%" . DBSafe($title) . "%' OR elm_states.CONDITION_ADVANCED LIKE '%" . DBSafe($title) . "%' OR elm_states.CONDITION_VALUE LIKE '%" . DBSafe($title) . "%') AND elements.ID>0 ORDER BY elm_states.TITLE");
    $total = count($states);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Scene: <a href="/panel/scene/' . $states[$i]['SCENE_ID'] . '/elements/' . $states[$i]['ELEMENT_ID'] . '/state' . $states[$i]['ID'] . '.html">' . $states[$i]['ELEMENT_TITLE'] . '.' . $states[$i]['TITLE'] . '</a><br>';
    }
    //scene elements
    $elements = SQLSelect("SELECT elements.ID, elements.SCENE_ID, elements.TITLE FROM elements WHERE (elements.LINKED_OBJECT LIKE '%" . DBSafe($title) . "%' OR elements.LINKED_PROPERTY LIKE '%" . DBSafe($title) . "%' OR elements.TITLE LIKE '%" . DBSafe($title) . "%') ORDER BY elements.TITLE");
    $total = count($elements);
    for ($i = 0; $i < $total; $i++) {
        $res .= 'Scene: <a href="/panel/scene/' . $elements[$i]['SCENE_ID'] . '/elements/' . $elements[$i]['ID'] . '.html">' . $elements[$i]['TITLE'] . '</a><br>';
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
            $res .= 'Device: <a href="/panel/devices/' . $devices[$i]['ID'] . '.html">' . $devices[$i]['TITLE'] . '</a><br>';
        }
    }

    //GPS
    if (file_exists(DIR_MODULES . 'app_gpstrack/app_gpstrack.class.php')) {
        $devices = SQLSelect("SELECT gpsactions.ID, gpslocations.TITLE, users.NAME FROM gpsactions LEFT JOIN users ON gpsactions.USER_ID=users.ID LEFT JOIN gpslocations ON gpsactions.LOCATION_ID=gpslocations.ID WHERE (TITLE LIKE '%" . DBSafe($title) . "%' OR CODE LIKE '%" . DBSafe($title) . "%') ORDER BY gpslocations.TITLE");
        $total = count($devices);
        for ($i = 0; $i < $total; $i++) {
            $res .= 'GPS: <a href="/panel/app_gpstrack/action_' . $devices[$i]['ID'] . '.html">' . $devices[$i]['TITLE'] . ' - ' . $devices[$i]['NAME'] . '</a><br>';
        }
    }

    //todo: webvars
    //todo: patterns


    echo $res;
}
exit;

?>