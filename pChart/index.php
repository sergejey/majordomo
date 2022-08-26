<?php
chdir('../');
set_time_limit(0);

/*
       Документация по построению графиков: http://wiki.pchart.net/
       Параметры в адресной строке:
        &p=sensor1.temp - Объект.свойство
        &op=log - лог исходных данных
        &op=debug - лог данных сформированных для построения графика
        &op=value - последнее значение свойства объекта
        &op=timed - лог исходных данных в формате JSON
        &op=json - лог данных графика в формате JSON
        &px=6 - расстояние между точками графика
        &approx=avg - аппроксимация (sum - по сумме, max - по максимуму, count - по разнице между макимальним и минимальным значением в периуде)
        &fil01=0 - сглаживание, по умолчанию = 0 (отсутствует)
        &gcolor=brown - цвет графика (brown, blue, green, orange), безцветный по умолчанию
        &bg=light - фон графика (может быть light, dark), по умолчанию dark
        &title=Title - заголовок
        &scale=zero - показывать ось нулевого значения
        &gtype=curve - тип графика (может быть: curve, bar, line -- плавная линия, столбцы, ступенчатый)
        &type=8h - Период (8h = 8 часов, 8d = 8 дней, 8m = 8 месяцев)
        &start=2014/09/25 - дата с которой берется начало графика в формате (гггг/мм/дд)
        &interval= секунд в интервале
        &width=610 - ширина графика в пикселях
        &height=210 - высота графика в пикселях
        &unit=°C - единицы измерения
*/

$w = 610; //width
$h = 210; //heigh
$right_border = 25;
$bottom_border = 30;
$left_border = 50;
$top_border = 20;
$title_top_offset = $top_border - 3;
$font = dirname(__FILE__) . "/fonts/tahoma.ttf";
$title_fontsize = 10;
$scale_fontsize = 8;
$threshold_fontsize = 6;
$w_delta = 80;
$px_per_point = 6;
$unit = "°C";
$end_time = time();
$approx = 'avg';
$fil01 = 0;

include_once("./config.php");
include_once("./lib/loader.php");
include_once(DIR_MODULES . "application.class.php");
include_once("./load_settings.php");
include(dirname(__FILE__) . "/class/pDraw.class.php");
include(dirname(__FILE__) . "/class/pImage.class.php");
include(dirname(__FILE__) . "/class/pData.class.php");
if ($width) $w = (int)$width;
if ($height) $h = (int)$height;
if ($_GET['px']) $px_per_point = (int)$_GET['px'];

// Dataset definition   
$DataSet = new pData;

if (is_array($p)) {
    $p = $p[0];
}

if ($p != '') {
    if (preg_match('/(.+)\.(.+)/is', $p, $m)) {
        $obj = getObject($m[1]);
        $prop_id = $obj->getPropertyByName($m[2], $obj->class_id, $obj->id);
    }
}

$property = SQLSelectOne("SELECT * FROM properties WHERE ID=" . (int)$prop_id);
$pvalue = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='" . $prop_id . "' AND OBJECT_ID='" . $obj->id . "'");

if (!$pvalue['ID']) {
    echo LANG_NO_RECORDS_FOUND;
    exit;
}

if ($_GET['op'] == 'value') {
    echo $pvalue['VALUE'];
    exit;
}

if (!$type) {
    $type = '7d';
}

if ($_GET['group']) {
    $group = $_GET['group'];
}

if (preg_match('/(\d+)d/', $type, $m)) {
    $total = (int)$m[1];
    $period = round(($total * 24 * 60 * 60) / (($w - $w_delta) / $px_per_point)); // seconds
    $start_time = $end_time - $total * 24 * 60 * 60;

} elseif (preg_match('/(\d+)h/', $type, $m)) {
    $total = (int)$m[1];
    $period = round(($total * 60 * 60) / (($w - $w_delta) / $px_per_point)); // seconds
    $start_time = $end_time - $total * 60 * 60;

} elseif (preg_match('/(\d+)m/', $type, $m)) {
    $total = (int)$m[1];
    $period = round(($total * 31 * 24 * 60 * 60) / (($w - $w_delta) / $px_per_point)); // seconds
    $start_time = $end_time - $total * 31 * 24 * 60 * 60;

} elseif (preg_match('/(\d+)\/(\d+)\/(\d+)/', $_GET['start'], $m) && $_GET['interval']) {
    $period = (int)$_GET['interval']; //seconds
    $start_time = mktime(0, 0, 0, $m[2], $m[3], $m[1]);
    $total = 1;
}

if ($total > 0) {
    $px = 0;
    $px_passed = 0;
    $dt = date('Y-m-d', $start_time);

    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $history_table = createHistoryTable($pvalue['ID']);
    } else {
        $history_table = 'phistory';
    }

    if ($end_time == $start_time) {
        $history = array();
    } else {
        $history = SQLSelect("SELECT ID, VALUE, ADDED, SOURCE FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' AND ADDED>=('" . date('Y-m-d H:i:s', $start_time) . "') AND ADDED<=('" . date('Y-m-d H:i:s', $end_time) . "')"); // ORDER BY ADDED
        if (!$history[0]['ID'] && $op == 'log') {
            $history = SQLSelect("SELECT ID, VALUE, ADDED, SOURCE FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' ORDER BY ADDED DESC LIMIT 20");
            $history = array_reverse($history);
        }

        if ($history[0]['ID']) {
            $total = count($history);
            for ($i = 0; $i < $total; $i++) {
                $history[$i]['UNX'] = strtotime($history[$i]['ADDED']);
            }
            usort($history, function ($a, $b) {
                if ($a['UNX'] == $b['UNX']) {
                    return 0;
                }
                return ($a['UNX'] < $b['UNX']) ? -1 : 1;
            });
        }
    }

    //echo "test";exit;

    $total_values = count($history);
    $start_time = $history[0]['UNX'];

    if ($_GET['op'] == 'timed') {
        //header("Content-type: text/json");
        $tret = array();
        $t_times = array();
        $t_values = array();
        for ($i = 0; $i < $total_values; $i++) {
            $t_times[] = $history[$i]['UNX'];
            $t_values[] = $history[$i]['VALUE'];
        }
        $ret['TIMES'] = $t_times;
        $ret['VALUES'] = $t_values;
        echo json_encode($ret);
        exit;
    }

    if ($_GET['op'] == 'log') {
        if ($total_values > 0) {
            if ($_GET['subop'] == 'clear') {
                if (!$_GET['id']) {
                    $values = SQLSelect("SELECT * FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "'");
                    $total = count($values);
                    for ($i = 0; $i < $total; $i++) {
                        if ($property['DATA_TYPE'] == 5) {
                            @unlink(ROOT . 'cms/images/' . $values[$i]['VALUE']);
                        }
                    }
                    SQLExec("DELETE FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "'");
                } else {
                    $value = SQLSelectOne("SELECT * FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' AND ID='" . (int)$_GET['id'] . "'");
                    if ($property['DATA_TYPE'] == 5) {
                        @unlink(ROOT . 'cms/images/' . $value['VALUE']);
                    }
                    SQLExec("DELETE FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' AND ID='" . (int)$_GET['id'] . "'");
                }
                header('Location:' . str_replace('&subop=clear', '', $_SERVER['REQUEST_URI']));
                exit;
            }
            //OPTIMIZE_LOG
            if ($_GET['subop'] == 'optimize') {
                $data = SQLSelect("SELECT * FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' ORDER BY ADDED DESC");
                $total = count($data);
                $old_value = $data[0]['VALUE'];
                for ($i = 1; $i < $total; $i++) {
                    if ($data[$i]['VALUE'] == $old_value) {
                        SQLExec("DELETE FROM $history_table WHERE ID='" . $data[$i]['ID'] . "'");
                    } else {
                        $old_value = $data[$i]['VALUE'];
                    }
                }
                header('Location:' . str_replace('&subop=optimize', '', $_SERVER['REQUEST_URI']));
                exit;
            }

            if ($_GET['export']) {
                $data = SQLSelect("SELECT * FROM $history_table WHERE VALUE_ID='" . $pvalue['ID'] . "' ORDER BY ADDED");
                //dprint($data);

                $csv = implode("\t", array('ADDED','VALUE')) . PHP_EOL;
                foreach ($data as $row) {
                    $csv .= $row['ADDED']."\t".$row['VALUE'];
                    $csv .= PHP_EOL;
                }

                $filename = 'data_'.date('Y-m-d-H_i_s').'.txt';
                $now = gmdate("D, d M Y H:i:s");
                //header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
                //header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
                //header("Last-Modified: {$now} GMT");

                // force download
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");

                // disposition / encoding on response body
                header("Content-Disposition: attachment;filename={$filename}");
                header("Content-Transfer-Encoding: binary");

                echo $csv;


                exit;

                // ID, VALUE, SOURCE, ADDED
            }

            echo "<html><head>";
            $roothtml = ROOTHTML;
            echo <<<FF
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
<link href="{$roothtml}css/devices.css?v=19-10-15"  rel="stylesheet" type="text/css"/>
<script type="text/javascript"  src="{$roothtml}3rdparty/jquery/jquery-3.3.1.min.js"></script>
<script type="text/javascript"  src="{$roothtml}3rdparty/jquery/jquery-migrate-3.0.0.min.js"></script>
<link rel="stylesheet" href="{$roothtml}3rdparty/bootstrap/css/bootstrap.min.css" type="text/css">
<script type="text/javascript" src="{$roothtml}3rdparty/bootstrap/js/bootstrap.min.js"></script>
FF;
            echo "</head><body><div>";
            //echo "<table width=100%><tr><td width='99%'>";

            $_SERVER['REQUEST_URI'] = preg_replace('/&subop=(\w+)/', '', $_SERVER['REQUEST_URI']);
            echo "<ul class='nav nav-tabs'>";
            echo '<li'.($_GET['subop']==''?' class="active"':'').'><a href="' . $_SERVER['REQUEST_URI'] . '&subop=">H</a></li>';
            echo '<li'.($_GET['subop']=='1h'?' class="active"':'').'><a href="' . $_SERVER['REQUEST_URI'] . '&subop=1h">1h</a></li>';
            echo '<li'.($_GET['subop']=='24h'?' class="active"':'').'><a href="' . $_SERVER['REQUEST_URI'] . '&subop=24h">24h</a></li>';
            echo '<li'.($_GET['subop']=='7d'?' class="active"':'').'><a href="' . $_SERVER['REQUEST_URI'] . '&subop=7d">7d</a></li>';
            echo '<li'.($_GET['subop']=='31d'?' class="active"':'').'><a href="' . $_SERVER['REQUEST_URI'] . '&subop=31d">31d</a></li>';
            if (!$_GET['minimal']) {
                echo '<li><a href="' . $_SERVER['REQUEST_URI'] . '&subop=clear" onClick="return confirm(\'' . LANG_ARE_YOU_SURE . '\')">' . LANG_CLEAR_ALL . '</a></li>';
                echo '<li><a href="' . $_SERVER['REQUEST_URI'] . '&subop=optimize" onClick="return confirm(\'' . LANG_ARE_YOU_SURE . '\')">' . LANG_OPTIMIZE_LOG . '</a></li>';
            }
            echo "</ul>";
            //echo "</td>";
            /*
            if (!$_GET['minimal']) {
                echo "<td>";
                echo '<a href="javascript:window.close();">X</a>';
                echo "</td>";
            }
            */
            //echo "</tr></table>";
            //echo '<br/>';

            if (preg_match('/^\d+\w$/', $_GET['subop'])) {

                if (file_exists(DIR_MODULES . 'charts/charts.class.php')) {

                    $height = 400;
                    if ($_GET['minimal']) {
                        $height = 500;
                    }

                    if (!is_array($_GET['p'])) {
                        $properties=array($_GET['p']);
                    } else {
                        $properties = $_GET['p'];
                        $p_url .= '&height=' . $height;
                    }
                    $p_url = '';
                    foreach ($properties as $p) {
                        $p_url .= '&properties[]=' . urlencode($p);
                        if (preg_match('/^(\w+)\.(\w+)$/',$p,$m)) {
                            $object = getObject($m[1]);
                            if ($object->description) {
                                $p_url .= '&legend[]=' . urlencode($object->description.' ('.$m[2].')');
                            } else {
                                $p_url .= '&legend[]=' . urlencode($p);
                            }
                        }
                    }
                    $code = '&nbsp;<iframe allowfullscreen="true" border="0" frameborder="0" src="' . ROOTHTML . 'module/charts.html?id=config&enable_fullscreen=1&period=' . $_GET['subop'] . '&chart_type=' . urlencode($_GET['chart_type']) . '&group=' . $group . $p_url . '&theme=grid-light&frameBorder=0" style="height:80% !important" width=100% ></iframe>';//height=' . $height . '
                } else {
                    $code = '&nbsp;<img src="' . ROOTHTML . '3rdparty/jpgraph/?p=' . $p . '&type=' . $_GET['subop'] . '&width=500&"/>';
                }
                echo $code;
                /*
                if (!$_GET['minimal']) {
                    echo "<br/>" . htmlspecialchars($code);
                }
                */
                exit;
            }
        }


        $history = array_reverse($history);

        /*
        if (!$_GET['full']) {
            $history = array_slice($history, 0, 25);
            $total_values = count($history);
        }
        */
        require(ROOT.'3rdparty/Paginator/Paginator.php');
        $page=(int)$_GET['page'];
        if (!$page) $page=1;

        $on_page=20;
        //$limit=(($page-1)*$on_page).','.$on_page;
        $start_offset = (($page-1)*$on_page);
        //$urlPattern='?page=(:num)';
        $url = $_SERVER['REQUEST_URI'];
        $url = preg_replace('/&page=\d+/','',$url);
        $urlPattern=$url.'&page=(:num)';
        $paginator = new JasonGrimes\Paginator($total_values, $on_page, $page, $urlPattern);

        $history = array_slice($history,$start_offset,$on_page);
        $total_values = count($history);

        echo "<div class='row'><div class='col-md-1'>&nbsp;</div>";
        echo "<div class='col-md-5'>";
        echo $paginator;
        echo "</div>";
        echo "<div class='col-md-5 text-right pagination'>";
        echo "<a href=\"".$_SERVER['REQUEST_URI'] . "&type=1&export=1\" class='btn btn-default'><i class='glyphicon glyphicon-export'></i> ".LANG_EXPORT."</a>";
        echo "</div>";
        echo "<div class='col-md-1'>&nbsp;</div></div>";

        echo "<table class='table table-striped'>";
        echo "<thead><tr><th>".LANG_ADDED."</th><th>".LANG_VALUE."</th><th>Src</th><th>&nbsp;</th></tr></thead>";
        for ($i = 0; $i < $total_values; $i++) {
            //echo date('Y-m-d H:i:s', $history[$i]['UNX']);
            echo "<tr><td>";
            echo $history[$i]['ADDED'];
            echo "</td>";
            echo "<td>";
            if ($property['DATA_TYPE'] == 5) {
                echo "<a href='" . ROOTHTML . "cms/images/" . $history[$i]['VALUE'] . "' target='_blank'><img src='" . ROOTHTML . "cms/images/" . $history[$i]['VALUE'] . "' height=100></a>";
            } else {
                echo "<b>".htmlspecialchars($history[$i]['VALUE']) . "</b>";
            }
            echo "</td>";
            echo "<td>";
            if ($history[$i]['SOURCE']) {
                if (strlen($history[$i]['SOURCE']) > 30) {
                    $src = substr($history[$i]['SOURCE'], 0, 30) . ' ...';
                } else {
                    $src = $history[$i]['SOURCE'];
                }
                echo '<input class="form-control" type=text value="' . $history[$i]['SOURCE'] . '"/>';
            }
            echo "</td>";
            echo '<td><a href="' . $_SERVER['REQUEST_URI'] . '&subop=clear&id=' . $history[$i]['ID'] . '" onClick="return confirm(\'' . LANG_ARE_YOU_SURE . '\')" class="btn btn-default btn-sm btn-danger"><i class="glyphicon glyphicon-trash"></i></a></td>';
            echo "</tr>";
        }
        echo "</table>";
        /*
        if (!$_GET['full']) {
            echo ' <br/><a href="' . $_SERVER['REQUEST_URI'] . '&type=1&full=1" class="btn btn-default btn-warning">Load all values</a> ';
        }
        */

        echo "</div></body></html>";
        exit;
    }

    $next_index = 0;
    /*if ($approx=='count' && $total_values>1) {
            $temp_array=array(round($history[1]['VALUE']-$history[0]['VALUE'],2));
    } else */
    //$temp_array=array($history[0]['VALUE']);
    $last_value = $history[0]['VALUE'];

    if ($_GET['approx']) {
        $approx = $_GET['approx'];
    }

    $index = 0;
    while ($start_time <= $end_time) {
        if ($next_index < $total_values) {        //���������� ��������
            for ($i = $next_index; $i < $total_values; $i++) {
                $next_index = $i + 1;
                if ($history[$i]['UNX'] >= $start_time || $next_index >= $total_values) {
                    if ($temp_array) {
                        if ($history[$i]['UNX'] + $period > $start_time && $next_index == $total_values) $temp_array[] = $history[$i]['VALUE']; //��������� ��������
                        if ($approx == 'sum') {
                            $value = array_sum($temp_array);
                        } elseif ($approx == 'max') {
                            $value = max($temp_array);
                        } elseif ($approx == 'count') {
                            if ($i > 0) $value = round($temp_array[count($temp_array) - 1] - $last_value, 2);
                            else            $value = round($history[1]['VALUE'] - $history[0]['VALUE'], 2);
                        } else {
                            $value = round(array_sum($temp_array) / count($temp_array), 2);
                        }
                    } elseif ($approx == 'count') {
                        $value = NULL;
                    } else {
                        $value = $last_value;
                    }
                    if ($_GET['op'] == 'debug') {
                        echo "<tt>Take value = </tt><b>" . $value . "</b><tt> from ";
                        //print_r($temp_array);         //������ �������������� �� �����, ������� ���������������
                        //var_dump($temp_array);        //������ �������������� �� �����, ������� ���������������
                        var_export($temp_array);        //������ �������������� �� �����, ������� ���������������
                        echo "<tt><br>Period time: " . date('Y-m-d H:i:s', $start_time - $period) . " - " . date('Y-m-d H:i:s', $start_time) . " (" . $period . " sec)</tt><br>";
                        echo "<hr></tt>";
                    }

                    //����� ���������� ������
                    if ($i >= 1 && $i < $total_values - 1) $last_value = $history[$i - 1]['VALUE'];
                    elseif ($i == $total_values - 1) $last_value = $history[$total_values - 1]['VALUE'];
                    else                                                            $last_value = $history[0]['VALUE'];
                    if (($start_time + $period) < $history[$i]['UNX']) { //��� ��������� � ��������
                        $temp_array = array($last_value); //����� �������� ���������� �������
                        $next_index = $i;
                    } elseif ($start_time > $history[$i]['UNX']) { //��� ��������� � �����
                        $temp_array = array($history[$total_values - 1]['VALUE']); //����� �������� ��������� �������
                        $next_index = $i;
                    } else
                        $temp_array = array($history[$i]['VALUE']); //�������� ����������� ����� ������
                    //}
                    //if ($_GET['op']=='debug') echo "<tt>".$history[$i]['UNX'].">".$start_time.": ".$history[$i]['VALUE']."</tt><br>";
                    break;
                } else {
                    if ($history[$i]['UNX'] < $start_time)
                        $temp_array[] = $history[$i]['VALUE']; //���������� ����������� ������, ��������� ��������� ��������
                    //if ($_GET['op']=='debug') echo "<tt>".$history[$i]['UNX']."<".$start_time.": ".$history[$i]['VALUE']."</tt><br>";
                }
            }
        } else {
            // ��������� ��������� ��������
            if ($approx == 'count') {
                $value = round($history[$total_values - 1]['VALUE'] - $last_value, 2);
            } else {
                $value = $history[$total_values - 1]['VALUE'];
            }
            //$values[]=$value;
        }
        if (isset($value)) $values[] = $value;
        if ($_GET['op'] == 'debug') {
            $hours[] = date('Y-m-d H:i', $start_time);
        } else {
            if ($px_passed > 30) {
                if (date('Y-m-d', $start_time) != $dt) {
                    $hours[] = date('d/m', $start_time);
                    $dt = date('Y-m-d', $start_time);
                    $thresholds[] = $index;
                } else {
                    $hours[] = date('H:i', $start_time);
                }
                $px_passed = 0;
            } else {
                $hours[] = '';
            }
        }
        $start_time += $period;
        $px += $px_per_point;
        $px_passed += $px_per_point;
        ++$index;
    }

    if ($_GET['fil01']) $fil01 = $_GET['fil01'];
    if ($fil01 != 0) {
        $all = count($values);
        for ($z = 0; $z < $fil01; $z++) {
            for ($i = 0; $i < $all - 1; $i++) {
                if ($values[$i] != 0 && $values[$i + 1] != 0) {
                    $values[$i] = ($values[$i] + $values[$i + 1]) / 2;
                }
            }
            for ($i = $all - 1; $i >= 0; $i--) {
                if ($values[$i] != 0 && $values[$i - 1] != 0) {
                    $values[$i] = ($values[$i] + $values[$i - 1]) / 2;
                }
            }
        }
    }

    if ($_GET['op'] == 'debug') {
        //$history=array_reverse($history);
        echo "This array in pChart:<br>";
        $all = count($values);
        for ($i = 0; $i < $all; $i++) {
            echo $hours[$i];
            echo " (+";
            echo $period;
            echo " sec): <b>";
            echo htmlspecialchars($values[$i]) . "</b><br>";
        }
        exit;
    }

    $DataSet->AddPoints($values, "Serie1");
    //$DataSet->AddPoints($hours,"Serie3");

} else {
    $DataSet->AddPoints(0, "Serie1");
    //$DataSet->AddPoints(0,"Serie3");
}

if ($_GET['op'] == 'values') {
    echo json_encode($values);
    exit;
}

if ($_GET['op'] == 'json') {
    //header("Content-type: text/json");
    $ret = array();
    $ret['VALUES'] = $values;
    $ret['TIME'] = $hours;
    echo json_encode($ret);
    exit;
}

$DataSet->setAxisUnit(0, $unit);
$DataSet->addPoints($hours, "Labels");
$DataSet->setSerieDescription("Labels", "�����");
$DataSet->setAbscissa("Labels");

//���� �������
if ($_GET['gcolor'] == 'red') {
    $ColorPalete = array("R" => 220, "G" => 50, "B" => 50);
} elseif ($_GET['gcolor'] == 'brown') {
    $ColorPalete = array("R" => 220, "G" => 140, "B" => 100);
} elseif ($_GET['gcolor'] == 'blue') {
    $ColorPalete = array("R" => 100, "G" => 140, "B" => 220);
} elseif ($_GET['gcolor'] == 'green') {
    $ColorPalete = array("R" => 69, "G" => 139, "B" => 16);
} elseif ($_GET['gcolor'] == 'orange') {
    $ColorPalete = array("R" => 255, "G" => 140, "B" => 0);
} else {
    if (SETTINGS_THEME == 'light' || $_GET['bg'] == 'light') {
        $ColorPalete = array("R" => 150, "G" => 150, "B" => 150);
    } else {
        $ColorPalete = array("R" => 250, "G" => 250, "B" => 250);
    }
}
//$ColorPalete["Alpha"] = 100; //������������
$DataSet->setPalette("Serie1", $ColorPalete);

// Initialise the graph  
/* Create a pChart object and associate your dataset */
$Test = new pImage($w, $h, $DataSet);
/* Define the boundaries of the graph area */
$Test->setGraphArea($left_border, $top_border, $w - $right_border, $h - $bottom_border);

//����������� ������� ����
if (SETTINGS_THEME == 'light' || $_GET['bg'] == 'light') {
    $Settings = array(
        "StartR" => 240,
        "StartG" => 240,
        "StartB" => 240,
        "EndR" => 160,
        "EndG" => 160,
        "EndB" => 160,
        "Alpha" => 100);
} else {
    $Settings = array(
        "StartR" => 132,
        "StartG" => 153,
        "StartB" => 172,
        "EndR" => 82,
        "EndG" => 103,
        "EndB" => 122,
        "Alpha" => 100);
}
$Test->drawGradientArea(0, 0, $w, $h, DIRECTION_VERTICAL, $Settings);
//����������� ������� ���� �������
$Settings["StartR"] = $Settings["StartR"] + 40;
$Settings["StartG"] = $Settings["StartG"] + 40;
$Settings["StartB"] = $Settings["StartB"] + 40;
$Settings["EndR"] = $Settings["EndR"] + 40;
$Settings["EndG"] = $Settings["EndG"] + 40;
$Settings["EndB"] = $Settings["EndB"] + 40;
$Test->drawGradientArea($left_border, $top_border, $w - $right_border, $h - $bottom_border, DIRECTION_VERTICAL, $Settings);

/* Draw the scale, keep everything automatic */
if ($_GET['scale'] == 'zero') {
    if (min($values) >= 0) $AxisBoundaries = array(0 => array("Min" => 0, "Max" => max($values)));
    elseif (max($values) <= 0) $AxisBoundaries = array(0 => array("Min" => min($values), "Max" => 0));
    else                                            $AxisBoundaries = array(0 => array("Min" => min($values), "Max" => max($values)));
    $scale = SCALE_MODE_MANUAL;
    //$scale=SCALE_MODE_START0;
} else {
    $scale = SCALE_MODE_FLOATING;
}

if (SETTINGS_THEME == 'light' || $_GET['bg'] == 'light') {
    $scaleSettings = array(
        "Mode" => $scale,
        "ManualScale" => $AxisBoundaries,
        "DrawXLines" => FALSE,
        "DrawYLines" => FALSE,
        //���� ������������ ���
        "AxisR" => 100,
        "AxisG" => 100,
        "AxisB" => 100,
        //���� ����� �� ������������ ���
        "TickR" => 100,
        "TickG" => 100,
        "TickB" => 100,
        "InnerTickWidth" => 0,
        "OuterTickWidth" => 5,
        "LabelSkip" => 0, //���������� ����
        "GridTicks" => 1,
        "ScaleSpacing" => 100,
        "XMargin" => 0,
        "YMargin" => 0,
        "Floating" => TRUE,
        "GridR" => 200,
        "GridG" => 200,
        "GridB" => 200,
        "DrawSubTicks" => FALSE,
        "CycleBackground" => TRUE);
    $ThresholdSettings = array(
        "Alpha" => 50,
        "Ticks" => 0,
        "R" => 150,
        "G" => 150,
        "B" => 150,
        "CaptionR" => 90,
        "CaptionG" => 90,
        "CaptionB" => 90,
        "WriteCaption" => TRUE,
        "DrawBox" => FALSE,
        "CaptionOffset" => -18,
        "NoMargin" => TRUE,
        "Border" => FALSE,                                //������� ������� ������������ �������� ��� drawXThresholdArea
        "CaptionAlign" => CAPTION_RIGHT_BOTTOM);
    /* Choose a nice font */
    $Test->setFontProperties(array("FontName" => $font, "R" => 100, "G" => 100, "B" => 100, "FontSize" => $scale_fontsize));
} else {
    $scaleSettings = array(
        "Mode" => $scale,
        "ManualScale" => $AxisBoundaries,
        "DrawXLines" => FALSE,
        "DrawYLines" => FALSE,
        //���� ������������ ���
        "AxisR" => 240,
        "AxisG" => 240,
        "AxisB" => 240,
        //���� ����� �� ������������ ���
        "TickR" => 240,
        "TickG" => 240,
        "TickB" => 240,
        "InnerTickWidth" => 0,
        "OuterTickWidth" => 5,
        "LabelSkip" => 0, //���������� ����
        "GridTicks" => 1,
        "ScaleSpacing" => 100,
        "XMargin" => 0,
        "YMargin" => 0,
        "Floating" => TRUE,
        "GridR" => 200,
        "GridG" => 200,
        "GridB" => 200,
        "DrawSubTicks" => FALSE,
        "CycleBackground" => TRUE);
    $ThresholdSettings = array(
        "Alpha" => 50,
        "Ticks" => 0,
        "R" => 230,
        "G" => 230,
        "B" => 230,
        "CaptionR" => 230,
        "CaptionG" => 230,
        "CaptionB" => 230,
        "WriteCaption" => TRUE,
        "DrawBox" => FALSE,
        "CaptionOffset" => -18,
        "NoMargin" => TRUE,
        "Border" => FALSE,                                //������� ������� ������������ �������� ��� drawXThresholdArea
        "CaptionAlign" => CAPTION_RIGHT_BOTTOM);
    /* Choose a nice font */
    $Test->setFontProperties(array("FontName" => $font, "R" => 240, "G" => 240, "B" => 240, "FontSize" => $scale_fontsize));
}

$Test->drawScale($scaleSettings);
//$Test->drawGraphAreaGradient(162,183,202,50);
$Test->setFontProperties(array("FontName" => $font, "FontSize" => $threshold_fontsize));
$drawThreshold = $ThresholdSettings;
$drawThreshold["Alpha"] = 10;  //������������ ������������ ��������
$index = 0;
$Alpha = $drawThreshold["Alpha"];
while ($index < sizeof($thresholds)) {
    if ($index == 0) {
        $Test->drawXThresholdArea(0, $thresholds[$index], $drawThreshold);
    } elseif ($index % 2 == 1) {
        if ($index + 1 >= sizeof($thresholds)) {
            $Test->drawXThresholdArea($thresholds[$index], sizeof($hours), $drawThreshold);
        } else {
            $Test->drawXThresholdArea($thresholds[$index], $thresholds[$index + 1], $drawThreshold);
        }
    }
    ++$index;
}
$drawThreshold["Alpha"] = $Alpha;
$Test->drawThreshold(round(max($values), 1), $ThresholdSettings); //�������������� ����� �� ������������� ��������
$Test->drawThreshold(round(min($values), 1), $ThresholdSettings); //�������������� ����� �� ������������ ��������
$Test->drawThreshold(round(array_sum($values) / sizeof($values), 1), $ThresholdSettings); //�������������� ����� �� �������� ��������
//$Test->drawGrid(1,TRUE,230,230,230,10);
if ($_GET['scale'] == 'zero') {
    $temp = $ThresholdSettings["WriteCaption"];
    $ThresholdSettings["WriteCaption"] = FALSE;
    $Test->drawThreshold(0, $ThresholdSettings);
    $ThresholdSettings["WriteCaption"] = $temp;
}

//������� ��������� �������

if (IsSet($_GET['title'])) {
    $_GET['title'] = strip_tags($_GET['title']);
}

if (SETTINGS_THEME == 'light' || $_GET['bg'] == 'light') {
    if ($_GET['title']) {
        $Test->drawText($left_border, $title_top_offset, $_GET['title'], array("FontSize" => $title_fontsize, "R" => 55, "G" => 55, "B" => 55, "Align" => TEXT_ALIGN_BOTTOMLEFT));
    } else {
        $Test->drawText($left_border, $title_top_offset, $p, array("FontSize" => $title_fontsize, "R" => 55, "G" => 55, "B" => 55, "Align" => TEXT_ALIGN_BOTTOMLEFT));
    }
} else {
    if ($_GET['title']) {
        $Test->drawText($left_border, $title_top_offset, $_GET['title'], array("FontSize" => $title_fontsize, "R" => 250, "G" => 250, "B" => 250, "Align" => TEXT_ALIGN_BOTTOMLEFT));
    } else {
        $Test->drawText($left_border, $title_top_offset, $p, array("FontSize" => $title_fontsize, "R" => 250, "G" => 250, "B" => 250, "Align" => TEXT_ALIGN_BOTTOMLEFT));
    }
}

// ������ ������
if ($_GET['gtype'] == 'curve') { //������ ��������� ������
    //$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
    //$Test->clearShadow();
    //$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),.1,30, FALSE);
    $Test->drawSplineChart(array(
        "DisplayValues" => FALSE,
        "BreakVoid" => FALSE,
        "VoidTicks" => 0,
        "DisplayColor" => DISPLAY_AUTO));
    $Test->drawAreaChart(array("AroundZero" => FALSE)); // ���������� ����� �� ������ �������
    // $Test->drawAreaChart(array("AroundZero"=>TRUE)); // ���������� �� ���� �� ������ �������
} elseif ($_GET['gtype'] == 'bar') { //������ ������
    //$Test->drawFilledRectangle(60,60,450,190,array("R"=>255,"G"=>255,"B"=>255,"Surrounding"=>-200,"Alpha"=>10));
    //$Test->drawScale(array("DrawSubTicks"=>TRUE));
    $Test->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
    //$Test->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));
    $Test->drawBarChart(array(
        "DisplayValues" => FALSE,
        "Interleave" => 0.2,
        "Gradient" => TRUE,
        "DisplayColor" => DISPLAY_AUTO,
        "Rounded" => FALSE,
        "AroundZero" => TRUE,
        "Surrounding" => 0));
} else { //������ ������������� ������
    //$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
    //$Test->clearShadow();
    //$Test->drawFilledLineGraph($DataSet->GetData(),$DataSet->GetDataDescription(), 30);
    $Test->drawLineChart(array(
        "DisplayValues" => FALSE,
        "BreakVoid" => FALSE,
        "VoidTicks" => 0,
        "DisplayColor" => DISPLAY_AUTO));
    //$Test->drawAreaChart(array("AroundZero"=>FALSE)); // ���������� ����� �� ������ �������
    $Test->drawAreaChart(array("AroundZero" => TRUE)); // ���������� �� ���� �� ������ �������
}

/* Render the picture (choose the best way) */
$path_to_file = './cms/cached/' . md5($_SERVER['REQUEST_URI']) . '.png';
imagepng($Test->autoOutput($path_to_file));

if (file_exists($path_to_file)) {
    Header("Content-type:image/png");
    $fsize = filesize($path_to_file);
    header("Content-Length:" . (string)$fsize);
    $buff_length = 200 * 1024;
    if ($buff_length > $fsize) {
        $buff_length = $fsize;
    }
    if ($buff_length > 0) {
        $fd = fopen($path_to_file, 'rb');
        if ($fd) {
            while (!feof($fd)) {
                print fread($fd, $buff_length);
            }
            fclose($fd);
        }
    }
}
