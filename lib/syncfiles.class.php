<?php
/*
 * @version 0.1 (auto-set)
 */

/**
 * Summary of preparePathTime
 * @param mixed $s String
 * @param mixed $mtime Time stamp
 * @return mixed
 */
function preparePathTime($s, $mtime)
{
    // %d #d &d $d
    $symbs = array('a', 'A', 'B', 'd', 'D', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'l', 'L',
        'm', 'M', 'n', 'O', 'r', 's', 'S', 't', 'T', 'U', 'w', 'W', 'Y', 'y', 'z', 'Z');

    foreach ($symbs as $v) {
        $s = str_replace('$' . $v, date($v), $s);
        $s = str_replace('%' . $v, date($v, $mtime), $s);
    }

    return $s;
}

/**
 * Summary of is_dir2
 * @param mixed $d Directory
 * @return int
 */
function is_dir2($d)
{

    // none directory
    if (substr($d, -2) == DIRECTORY_SEPARATOR . "." || substr($d, -2) == "/.") {
        return false;
    }

    // none directory
    if (substr($d, -3) == DIRECTORY_SEPARATOR . ".." || substr($d, -3) == "/..") {
        return false;
    }

    if (substr($d, -1) == "/") {
        $d = substr($d, 0, -1);
    }

    if (substr($d, -1) == DIRECTORY_SEPARATOR) {
        $d = substr($d, 0, -1);
    }

    if ('NET:' == substr($d, 0, 4)) {
        $d = '//' . substr($d, 4);
    }

    if (is_dir($d)) return true;

    return false;
}

/**
 * Summary of remove_old_files
 * @param mixed $path Path
 * @param mixed $days Days
 * @return void
 */
function remove_old_files($path, $days)
{
    $mtime = filemtime($path);
    $diff = round((time() - $mtime) / 60 / 60 / 24, 2);

    if ($diff > $days) {
        echo 'Removing ' . $path . ' (' . $diff . " days old)\n";
        unlink($path);
    }
}

/**
 * Summary of copyNewFile
 * @access public
 * @param mixed $path Path
 * @param mixed $days Days
 * @return int
 */
function copyNewFile($path, $days)
{
    global $dirs;
    global $current_dir;
    global $current_dest;
    global $acc;
    global $ignores;

    $mtime = filemtime($path);
    $diff = round((time() - $mtime) / 60 / 60 / 24, 2);

    if ($diff > $days) {
        return;
    }

    foreach ($ignores as $ptn) {
        if (preg_match("/" . $ptn . "/is", $path))
            return;
    }

    $tmdiff = 0;

    $dest = (!$current_dest) ? $dirs[$current_dir] : $current_dest;
    $dest = str_replace($current_dir, $dest, $path);

    $dest_path = str_replace(basename($dest), '', $dest);
    $new_dest_path = preparePathTime($dest_path, $mtime);

    $dest = str_replace($dest_path, $new_dest_path, $dest);
    $dest_path = $new_dest_path;

    if (!is_dir2($dest_path)) {
        if (!makedir($dest_path))
            return 0;
    }

    if (!file_exists($dest)) {
        echo $path . " -> " . $dest . " (new)\n";
        copyFile($path, $dest);
    }
}

/**
 * Summary of checkfile
 * @param mixed $path Path
 * @param mixed $move Move
 * @return int
 */
function checkfile($path, $move)
{
    global $dirs;
    global $current_dir;
    global $current_dest;
    global $acc;
    global $ignores;
    global $files_copied;

    foreach ($ignores as $ptn) {
        if (preg_match("/" . $ptn . "/is", $path))
            return;
    }

    $tmdiff = 0;
    $dest = (!$current_dest) ? $dirs[$current_dir] : $current_dest;
    $path = str_replace('NET:', '//', $path);

    $current_dir = str_replace('NET:', '//', $current_dir);

    $mtime = filemtime($path);

    $dest = str_replace('NET:', '//', $dest);
    $dest = str_replace($current_dir, $dest, $path);
    $dest_path = str_replace(basename($dest), '', $dest);

    $new_dest_path = preparePathTime($dest_path, $mtime);
    $dest = str_replace($dest_path, $new_dest_path, $dest);
    $dest_path = $new_dest_path;

    if (!is_dir2($dest_path)) {
        if (!makedir($dest_path))
            return 0;
    }

    if (!file_exists($dest)) {
        echo $path . " -> " . $dest . " (new)\n";
        copyFile($path, $dest);
    } else {
        $dest_size = filesize($dest);
        $src_size = filesize($path);
        $tmdiff = filemtime($path) - filemtime($dest);

        if ($tmdiff > $acc || ($dest_size == 0 && $src_size != 0)) {
            $status = "updated $tmdiff";
            echo $path . " -> " . $dest . " (updated " . round($tmdiff / 60 / 60, 1) . " h)\n";
            copyFile($path, $dest);
        } else {
            //echo $path." -> ".$dest." (OK ".round($tmdiff/60/60, 1)." h)\n";
            $fs = filesize($path);
            //if ($fs>(2*1024*1024)) {
            $k = basename($path) . '_' . $fs;
            //$files_copied[$k]=$dest;
            //}
        }
    }

    if ($move)
        unlink($path);
}

/**
 * Summary of copyFile
 * @param mixed $src Source
 * @param mixed $dst Destination
 * @return void
 */
function copyFile($src, $dst)
{

    $path = pathinfo($dst);
    if (!is_dir($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }

    global $files_copied;
    $size_limit = 2000 * 1024 * 1024;

    $fs = filesize($src);

    if ($fs == 0)
        return;

    $fs_mb = round($fs / 1024 / 1024, 2);

    if ($fs > $size_limit) {
        $k = basename($src) . '_' . $fs;

        if ($files_copied[$k] == '') {
            echo "Size: " . $fs_mb . "Mb\n";

            $src = str_replace('/', '\\', $src);
            $dst = str_replace('/', '\\', $dst);

            system('copy "' . $src . '" "' . $dst . '"'); // long copy
            $files_copied[$k] = $dst;
        } else {
            echo " already copied to (" . $files_copied[$k] . ")\n";
        }
    } else {
        copy($src, $dst);
    }

    touch($dst, filemtime($src));
}

// function copy files from sourse directory to destination 
function copyFiles($source, $destination, $over = 0, $patterns = 0)
{
    $res = 1;

    //Remove last slash '/' in source and destination - slash was added when copy
    if (substr($source, -1) == "/") {
        $source = substr($source, 0, -1);
    } else if (substr($source, -1) == DIRECTORY_SEPARATOR) {
        $source = substr($source, 0, -1);
    }

    if (substr($destination, -1) == "/") {
        $destination = substr($destination, 0, -1);
    } else if (substr($destination, -1) == DIRECTORY_SEPARATOR) {
        $destination = substr($destination, 0, -1);
    }

    if (!Is_Dir2($source)) {
        return false; // cannot create destination path
    }

    if (!Is_Dir2($destination)) {
        if (!mkdir($destination, 0777, true)) {
            // cannot create destination path
            return false;
        }
    }

    if ($dir = @opendir($source)) {
        while (($file = readdir($dir)) !== false) {

            if (is_dir($source . DIRECTORY_SEPARATOR . $file) && ($file != '.') && ($file != '..')) {
                //$res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
                continue;
            } elseif (is_file($source . DIRECTORY_SEPARATOR . $file) && (!file_exists($destination . DIRECTORY_SEPARATOR . $file) || $over)) {
                if (!is_array($patterns)) {
                    $ok_to_copy = 1;
                } else {
                    $ok_to_copy = 0;
                    $total = count($patterns);
                    for ($i = 0; $i < $total; $i++) {
                        if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                            $ok_to_copy = 1;
                        }
                    }
                }
                if ($ok_to_copy) {
                    $res = copy($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }
    return $res;
}

/**
 * walking directory
 * @param mixed $dir Directory
 * @param mixed $callback CallBack
 * @param mixed $move Move (default 0)
 * @return void
 */
function walk_dir($dir, $callback, $move = 0)
{
    global $ignores;

    $dir = str_replace('NET:', '//', $dir);
    $dir .= '/';

    foreach ($ignores as $ptn) {
        if (preg_match("/" . $ptn . "/is", $dir))
            return;
    }

    //if (!preg_match('/mail.ru Blogs/is', $dir)) {
    // return;
    //}
    echo "processing $dir\n";

    if (!is_dir2($dir))
        return;

    $handle = opendir($dir);

    while (false !== $thing = readdir($handle)) {
        if ($thing == '.' || $thing == '..') continue;

        $thing = $dir . $thing;

        if (is_dir2($thing))
            walk_dir($thing, $callback, $move);
        elseif (is_file($thing))
            call_user_func($callback, $thing, $move);
    }

    closedir($handle);
}

/**
 * walking directory 2 (removing destination if neccessary)
 * @param mixed $dir Directory
 * @param mixed $callback CallBack
 * @param mixed $move Move (default 0)
 * @return void
 */
function walk_dir2($dir, $callback, $move = 0)
{
    global $ignores;
    global $dirs;
    global $acc;
    global $current_dir;
    global $current_dest;

    $dir = str_replace('NET:', '//', $dir);
    $dir .= '/';

    foreach ($ignores as $ptn) {
        if (preg_match("/" . $ptn . "/is", $dir))
            return;
    }

    $tmpdir = $current_dir;
    $tmpdir = str_replace('NET:', '//', $tmpdir);
    $dest = $dirs[$dir];
    $dest = str_replace($tmpdir, $current_dest, $dir);

    // ADDING NEW/UPDATED FILES
    $processed = array();

    //$dir=str_replace('/', '\\', $dir);
    if (!is_dir2($dir)) {
        echo "Dir not found: $dir\n";
        return;
    }

    $handle = opendir($dir);

    while (false !== $thing = readdir($handle)) {
        if ($thing == '.' || $thing == '..')
            continue;

        $processed[$thing] = 1;

        $thing = $dir . $thing;

        if (is_dir2($thing))
            walk_dir2($thing, $callback, $move);
        elseif (is_file($thing))
            call_user_func($callback, $thing, $move);
    }

    closedir($handle);

    // print_r($processed);

    // REMOVING FILES
    $handle = opendir($dest);

    while (false !== $thing = readdir($handle)) {
        if ($thing == '.' || $thing == '..')
            continue;

        if (!$processed[$thing]) {
            if (is_file($dest . $thing)) {
                echo "Removing file: " . $dest . $thing . " \n";
                unlink($dest . $thing);
            } elseif (is_dir2($dest . $thing)) {
                echo "Removing dir: " . $dest . $thing . " \n";
                removeTree($dest . $thing);
            }
        }
    }

    closedir($handle);
    // exit;
}

/**
 * creating new directory
 * @param mixed $dir Directory
 * @param mixed $sep Separator (default '/')
 * @return void
 */
function makeDir($dir, $sep = '/')
{
    $tmp = explode($sep, $dir);
    $tmpCnt = count($tmp);
    $cr = "";

    for ($i = 0; $i < $tmpCnt; $i++) {
        $cr .= $tmp[$i] . "$sep";

        if (!Is_Dir2($cr)) {
            echo "Making folder [$cr]\n";
            mkDir($cr);
        }
    }
}

/**
 * removeTree
 * remove directory tree
 * @access public
 */
function removeTree($destination, $iframe = 0)
{
    $res = 1;
    if (!Is_Dir2($destination)) {
        return false; // cannot create destination path
    }
    if ($dir = @opendir($destination)) {
        if ($iframe) {
            echonow("Removing dir $destination ... ");
        }
        while (($file = readdir($dir)) !== false) {
            if (Is_Dir2($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                $res = removeTree($destination . "/" . $file);
            } elseif (Is_File($destination . "/" . $file)) {
                $res = @unlink($destination . "/" . $file);
            }
        }
        closedir($dir);
        $res = @rmdir($destination);
        if ($iframe) {
            echonow("OK<br/>", "green");
        }
    }
    return $res;
}


/**
 * Title
 * Description
 * @access public
 */
function getLocalFilesTree($dir, $pattern, $ex_pattern, &$log, $verbose)
{
    $res = array();
    $destination = $dir;
    if (!Is_Dir2($destination)) {
        return $res; // cannot create destination path
    }

    if ($dir = @opendir($destination)) {
        while (($file = readdir($dir)) !== false) {
            if (Is_Dir2($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                $sub_ar = getLocalFilesTree($destination . "/" . $file, $pattern, $ex_pattern, $log, $verbose);
                $res = array_merge($res, $sub_ar);
            } elseif (Is_File($destination . "/" . $file)) {
                $fl = array();
                $fl['FILENAME'] = str_replace('//', '/', $destination . "/" . $file);
                $fl['FILENAME_SHORT'] = str_replace('//', '/', $file);
                $fl['MTIME'] = filemtime($fl['FILENAME']);
                $fl['SIZE'] = filesize($fl['FILENAME']);
                if (preg_match('/' . $pattern . '/is', $fl['FILENAME_SHORT']) && ($ex_pattern == '' || !preg_match('/' . $ex_pattern . '/is', $fl['FILENAME_SHORT']))) {
                    $res[] = $fl;
                }
            }
        }
        closedir($dir);
    }
    return $res;
}

// loading file

/**
 * ProcessLines
 * @param mixed $data Data
 * @return int
 */
function processLines($data)
{
    global $ignores;

    $hash = array();
    $data = str_replace("\r", '', $data);
    $lines = explode("\n", $data);
    $total = count($lines);

    for ($i = 0; $i < $total; $i++)
        processLine($lines[$i], $hash);

    return $total;
}

/**
 * Process Line
 * @param mixed $line Line
 * @param mixed $hash Hash (default empty)
 * @return void
 */
function processLine($line, $hash = '')
{
    global $current_dest;
    global $current_dir;
    global $ignores;

    if (!is_array($ignores))
        $ignores = array();

    if (!is_array($hash))
        $hash = array();

    $line = trim($line);

    foreach ($hash as $k => $v)
        $line = str_replace($k, $v, $line);

    echo $line . "\n";

    if (preg_match('/^\/\//', $line)) {
        return;
    } elseif (preg_match('/^IGNORE (.+?)$/i', $line, $matches)) {
        $ignores[] = trim($matches[1]);
    } elseif (preg_match('/^SET (.+?)=(.+?)$/i', $line, $matches)) {
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $hash[$key] = $value;
    } elseif (preg_match('/^CLEAR (.+?) (\d+) DAYS OLD$/is', $line, $matches)) {
        $from = trim($matches[1]);

        $current_dir = $from;

        $days = (int)($matches[2]);

        if ($days > 0)
            walk_dir($from, "remove_old_files", $days);
    } elseif (preg_match('/^(.+?)\+>(.+?) (\d+) DAYS OLD$/is', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);

        $current_dir = $from;
        $current_dest = $to;

        $days = (int)($matches[3]);

        walk_dir($from, "copyNewFile", $days);
    } elseif (preg_match('/^(.+?)<\+(.+?) (\d+) DAYS OLD$/is', $line, $matches)) {
        $to = trim($matches[1]);
        $from = trim($matches[2]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        echo "\n Cannot make destination dir ($to)\n";
        return;
        }
         */

        $days = (int)($matches[3]);

        walk_dir($from, "copyNewFile", $days);
    } elseif (preg_match('/^(.+?)=>(.+?)$/is', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        echo "\n Cannot make destination dir ($to)\n";
        return;
        }
         */
        //echo "walking $from\n";

        walk_dir($from, "checkfile");
    } elseif (preg_match('/^(.+?)<=(.+?)$/is', $line, $matches)) {
        $from = trim($matches[2]);
        $to = trim($matches[1]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        return;
        }
         */

        walk_dir($from, "checkfile");
    } elseif (preg_match('/^(.+?)\!>(.+?)$/is', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        return;
        }
         */

        walk_dir2($from, "checkfile");
    } elseif (preg_match('/^(.+?)<\!(.+?)$/is', $line, $matches)) {
        $from = trim($matches[2]);
        $to = trim($matches[1]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        return;
        }
         */

        walk_dir2($from, "checkfile");
    } elseif (preg_match('/^(.+?)->(.+?)$/is', $line, $matches)) {
        $from = trim($matches[1]);
        $to = trim($matches[2]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        return;
        }
         */

        walk_dir($from, "checkfile", 1);
    } elseif (preg_match('/^(.+?)<-(.+?)$/is', $line, $matches)) {
        $from = trim($matches[2]);
        $to = trim($matches[1]);

        $current_dir = $from;
        $current_dest = $to;

        /*
        if (!is_dir2($to) && !@mkdir($to)) {
        return;
        }
         */

        walk_dir($from, "checkfile", 1);
    }
    // echo $line."\n";
}

/**
 * Summary of UTF_Encode
 * @param mixed $str String
 * @param mixed $type Type ('w' - encodes from UTF to win, 'u' - encodes from win to UTF)
 * @return mixed
 */
function UTF_Encode($str, $type)
{
    static $conv = '';

    if (!is_array($conv)) {
        $conv = array();

        for ($x = 128; $x <= 143; $x++) {
            $conv['utf'][] = chr(209) . chr($x);
            $conv['win'][] = chr($x + 112);
        }

        for ($x = 144; $x <= 191; $x++) {
            $conv['utf'][] = chr(208) . chr($x);
            $conv['win'][] = chr($x + 48);
        }

        $conv['utf'][] = chr(208) . chr(129);
        $conv['win'][] = chr(168);
        $conv['utf'][] = chr(209) . chr(145);
        $conv['win'][] = chr(184);
    }

    if ($type == 'w')
        return str_replace($conv['utf'], $conv['win'], $str);
    elseif ($type == 'u')
        return str_replace($conv['win'], $conv['utf'], $str);
    else
        return $str;
}

/**
 * copyTree
 * Copy source directory tree to destination directory
 * @access public
 */
function copyTree($source, $destination, $over = 0, $patterns = 0)
{
    $source = preg_replace('/\\/\\.$/', '', $source);
    //Remove last slash '/' in source and destination - slash was added when copy
    if (substr($source, -1) == "/") {
        $source = substr($source, 0, -1);
    } else if (substr($source, -1) == DIRECTORY_SEPARATOR) {
        $source = substr($source, 0, -1);
    }


    if (substr($destination, -1) == "/") {
        $destination = substr($destination, 0, -1);
    } else if (substr($destination, -1) == DIRECTORY_SEPARATOR) {
        $destination = substr($destination, 0, -1);
    }

    if (!Is_Dir2($source)) {
        // cannot find source path
        return false;
    }

    if (!Is_Dir2($destination)) {
        if (!mkdir($destination, 0777, true)) {
            // cannot create destination path
            return false;
        }
    }

    if ($dir = @opendir($source)) {
        while (($file = readdir($dir)) !== false) {
            if (Is_Dir2($source . DIRECTORY_SEPARATOR . $file)) {
                copyTree($source . DIRECTORY_SEPARATOR . $file, $destination . DIRECTORY_SEPARATOR . $file, $over, $patterns);
            }
        }
        copyFiles($source, $destination, $over, $patterns);
        closedir($dir);
    }
    return true;
}

function removeEmptySubFolders($path)
{
    $empty = true;
    foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
        $empty &= is_dir($file) && removeEmptySubFolders($file);
    }

    if (is_dir($path)) {
        $empty &= rmdir($path);
    }

    return $empty;
}

function getDirTree($dir, &$results = array())
{
    $isdir = is_dir($dir);
    if ($isdir) {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = array('FILENAME' => $path, 'DT' => date('Y-m-d H:i:s', filemtime($path)), 'TM' => filemtime($path), 'SIZE' => filesize($path));
            } else if ($value != "." && $value != "..") {
                getDirTree($path, $results);
            }
        }
    }

    return $results;
}

function keepLatestLimitedBySize($path, $max_size, $removeEmptyFolders = true)
{
    $files = array();
    getDirTree($path, $files);
    $total = count($files);
    if ($total > 0) {
        if (!function_exists('sort_files_by_date')) {
            function sort_files_by_date($a, $b)
            {
                if ($a['TM'] == $b['TM']) {
                    return 0;
                }
                return ($a['TM'] > $b['TM']) ? -1 : 1;
            }
        }
        usort($files, 'sort_files_by_date');
        $size = 0;
        for ($i = 0; $i < $total; $i++) {
            $size += $files[$i]['SIZE'];
            if ($size > $max_size) {
                @unlink($files[$i]['FILENAME']);
            }
        }
    }
    if ($removeEmptyFolders) {
        removeEmptySubFolders($path);
    }
}

/**
 * Summary of getRandomLine
 * @param mixed $filename File name
 * @return mixed
 */
function getRandomLine($filename)
{
    if (file_exists(ROOT . 'cms/texts/' . $filename . '.txt')) {
        $filename = ROOT . 'cms/texts/' . $filename . '.txt';
    }

    if (file_exists($filename)) {
        $data = LoadFile($filename);
        $data = str_replace("\r", '', $data);
        $data = str_replace("\n\n", "\n", $data);
        $lines = mb_split("\n", $data);
        $total = count($lines);
        $line = $lines[round(rand(0, $total - 1))];

        if ($line != '') {
            return $line;
        }
    }
}

function remote_file_exists($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        return true;
    }
    return false;
}

function getMediaDurationSeconds($file)
{
    if (!defined('PATH_TO_FFMPEG')) {
        if (IsWindowsOS()) {
            define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
        } else {
            define("PATH_TO_FFMPEG", 'ffmpeg');
        }
    }
    $dur = shell_exec(PATH_TO_FFMPEG . " -i " . $file . " 2>&1");
    if (preg_match("/: Invalid /", $dur)) {
        return false;
    }
    preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $dur, $duration);
    if (!isset($duration[1])) {
        return false;
    }
    $hours = $duration[1];
    $minutes = $duration[2];
    $seconds = $duration[3];
    return $seconds + ($minutes * 60) + ($hours * 60 * 60);
}

function get_media_info($file)
{
    if (!defined('PATH_TO_FFMPEG')) {
        if (IsWindowsOS()) {
            define("PATH_TO_FFMPEG", SERVER_ROOT . '/apps/ffmpeg/ffmpeg.exe');
        } else {
            define("PATH_TO_FFMPEG", 'ffmpeg');
        }
    }
    $data = shell_exec(PATH_TO_FFMPEG . " -i " . $file . " 2>&1");

    if (preg_match("/: Invalid /", $data)) {
        return false;
    }
    //get duration
    preg_match("/Duration: (.{2}):(.{2}):(.{2})/", $data, $duration);

    if (!isset($duration[1])) {
        return false;
    }
    $hours = $duration[1];
    $minutes = $duration[2];
    $seconds = $duration[3] + 1;
    $out['duration'] = $seconds + ($minutes * 60) + ($hours * 60 * 60);
    // get all info about codec
    preg_match("/Audio: (.+), (.\d+) Hz, (.\w+), (.+), (.\d+) kb/", $data, $format);

    if ($format) {
        $out['Audio_format'] = $format[1];
        $out['Audio_sample_rate'] = $format[2];
        $out['Audio_type'] = $format[3];
        $out['Audio_codec'] = $format[4];
        $out['Audio_bitrate'] = $format[5];
        if ($out['Audio_type'] == 'mono') {
            $out['Audio_chanel'] = 1;
        } else {
            $out['Audio_chanel'] = 2;
        }
    }
    preg_match("/Video: (.+),\s(.\d+x.\d+) (.+), (.+), (.+), (.+), (.+), (.+) /", $data, $formatv);
    if ($formatv) {
        $out['Video_format'] = $formatv[1];
        $out['Video_size'] = $formatv[2];
        $out['Video_bitrate'] = str_ireplace("kb/s", "", $formatv[4]);
        $out['Video_fps'] = $formatv[5];
    }
    return $out;
}

function get_remote_filesize($url)
{
    $head = array_change_key_case(get_headers($url, 1));
    // content-length of download (in bytes), read from Content-Length: field
    $clen = isset($head['content-length']) ? $head['content-length'] : 0;

    // cannot retrieve file size, return "-1"
    if (!$clen) {
        return '0';
    }
    return $clen; // return size in bytes
}

/**
 * Summary of LoadFile
 *
 * @access public
 *
 * @param mixed $filename File name
 * @return string
 */
function LoadFile($filename)
{
    // loading file
    $f = fopen($filename, "r");
    $data = "";
    if ($f) {
        $fsize = filesize($filename);
        if ($fsize > 0) {
            $data = fread($f, $fsize);
        }
        fclose($f);
    }
    return $data;
}

/**
 * Summary of SaveFile
 * @access public
 *
 * @param mixed $filename File name
 * @param mixed $data Content
 * @return int
 */
function SaveFile($filename, $data)
{
    // saving file
    $f = fopen("$filename", "w+");

    if ($f) {
        flock($f, 2);
        fwrite($f, $data);
        flock($f, 3);
        fclose($f);
        @chmod($filename, 0666);

        return 1;
    }

    return 0;
}

/**
 * Summary of clearCache
 * @param mixed $verbose Verbode (default 0)
 * @return void
 */
function clearCache($verbose = 0)
{
    if ($handle = opendir(DOC_ROOT . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'cached')) {
        while (false !== ($file = readdir($handle))) {
            if (is_file(DOC_ROOT . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'cached' . DIRECTORY_SEPARATOR . $file)) {
                @unlink(DOC_ROOT . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR . 'cached' . DIRECTORY_SEPARATOR . $file);

                if ($verbose) {
                    echo "File : " . $file . " <b>removed</b><br>\n";
                }
            }
        }

        closedir($handle);
    }
}

/**
 * Summary of getFilesTree
 * @param mixed $destination Destination
 * @param mixed $sort Sort (default 'name')
 * @return array
 */
function getFilesTree($destination, $sort = 'name')
{
    if (substr($destination, -1) == '/' || substr($destination, -1) == '\\') {
        $destination = substr($destination, 0, strlen($destination) - 1);
    }

    $res = array();

    if (!is_dir($destination))
        return $res;

    if ($dir = @opendir($destination)) {
        while (($file = readdir($dir)) !== false) {
            if (is_dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                $tmp = getFilesTree($destination . "/" . $file);
                if (is_array($tmp)) {
                    foreach ($tmp as $elem) {
                        $res[] = $elem;
                    }
                }
            } elseif (is_file($destination . "/" . $file)) {
                $res[] = ($destination . "/" . $file);
            }
        }
        closedir($dir);
    }

    if ($sort == 'name') {
        sort($res, SORT_STRING);
    }

    return $res;
}

function get_mime_type($filename)
{
    $idx = explode('.', $filename);
    $count_explode = count($idx);
    $idx = strtolower($idx[$count_explode - 1]);

    $mimet = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',


        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    if (isset($mimet[$idx])) {
        return $mimet[$idx];
    } else {
        return 'application/octet-stream';
    }
}

function getDirFiles($dir, &$results = array())
{
    if (is_dir2($dir)) {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . "/" . $value);
            if (!is_dir($path) && $value != ".htaccess" && $value != "." && $value != "..") {
                $results[] = array('NAME' => $value, 'FILENAME' => $path, 'DT' => date('Y-m-d H:i:s', filemtime($path)), 'TM' => filemtime($path), 'SIZE' => filesize($path));
            }
        }
    }
    return $results;
}
