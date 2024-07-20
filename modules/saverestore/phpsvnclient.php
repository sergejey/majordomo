<?php

/*
 * **************************************************************************
 *   Copyright (C) 2007-2008 by Sixdegrees                                 *
 *   cesar@sixdegrees.com.br                                               *
 *   "Working with freedom"                                                *
 *   http://www.sixdegrees.com.br                                          *
 *                                                                         *  
 *   Permission is hereby granted, free of charge, to any person obtaining *
 *   a copy of this software and associated documentation files (the       *
 *   "Software"), to deal in the Software without restriction, including   *
 *   without limitation the rights to use, copy, modify, merge, publish,   *
 *   distribute, sublicense, and/or sell copies of the Software, and to    *
 *   permit persons to whom the Software is furnished to do so, subject to *
 *   the following conditions:                                             *
 *                                                                         *
 *   The above copyright notice and this permission notice shall be        *
 *   included in all copies or substantial portions of the Software.       *
 *                                                                         *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,       *
 *   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF    *
 *   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.*
 *   IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR     *
 *   OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, *
 *   ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR *
 *   OTHER DEALINGS IN THE SOFTWARE.                                       *
 * **************************************************************************
 */
define("PHPSVN_DIR", dirname(__FILE__));
define("LOG_FILE", ROOT . 'cms/saverestore/'. time() . ".log.html");

require_once PHPSVN_DIR . "/http.php";
require_once PHPSVN_DIR . "/xml_parser.php"; // to be dropped?
require_once PHPSVN_DIR . "/definitions.php";
require_once PHPSVN_DIR .  "/xml2Array.php";

/**
 *  PHP SVN CLIENT
 *
 *  This class is a SVN client. It can perform read operations
 *  to a SVN server (over Web-DAV). 
 *  It can get directory files, file contents, logs. All the operaration
 *  could be done for a specific version or for the last version.
 *
 *  @author Cesar D. Rodas <cesar@sixdegrees.com.br>
 *  @license BSD License
 */
class phpsvnclient {

    /**
     *  SVN Repository URL
     *
     *  @var string
     *  @access private
     */
    private $_url;

    /**
     *  Cache, for don't request the same thing in a
     *  short period of time.
     *
     *  @var string
     *  @access private
     */
    private $_cache;

    /**
     *  HTTP Client object
     *
     *  @var object
     *  @access private
     */
    private $_http;

    /**
     *  Respository Version.
     *
     *  @access private
     *  @var interger
     */
    private $_repVersion;

    /**
     *  Password
     *
     *  @access private
     *  @var string
     */
    private $pass;

    /**
     *  Password
     *
     *  @access private
     *  @var string
     */
    private $user;

    /**
     *  Last error number
     *
     *  Possible values are NOT_ERROR, NOT_FOUND, AUTH_REQUIRED, UNKOWN_ERROR
     *
     *  @access public
     *  @var integer
     */
    public $errNro;

    /**
     * Number of actual revision local repository.
     * @var Integer, Long
     */
    private $actVersion;
    private $storeDirectoryFiles = array();
    private $lastDirectoryFiles;
    private $file_size;
    private $file_size_founded = false;

    /**
     * The path to the file to perform after update procedure 
     * or checkout of a local repository.
     * @var String
     */
    private $path_exec_after_completition = '';

    /**
     * Array with MIME types.
     * @var Array
     */
    private $mime_array;

    public function phpsvnclient($url = 'http://phpsvnclient.googlecode.com/svn/', $user = false, $pass = false) {
        $this->__construct($url, $user, $pass);
        register_shutdown_function(array(&$this, '__destruct'));
    }

    public function __construct($url = 'http://phpsvnclient.googlecode.com/svn/', $user = false, $pass = false) {
        $http = & $this->_http;
        $http = new http_class;
        $http->user_agent = "phpsvnclient (http://phpsvnclient.googlecode.com/)";

        $this->_url = $url;
        $this->user = $user;
        $this->pass = $pass;

        $this->actVersion = $this->getVersion();
    }

    /**
     * Function for creating directories.
     * @param type $path The path to the directory that will be created.
     */
    function createDirs($path) {
        $dirs = explode("/", $path);

        foreach ($dirs as $dir) {
            if ($dir != "") {
                $createDir = substr($path, 0, strpos($path, $dir) + strlen($dir));
                @mkdir($createDir);
            }
        }
    }

    /**
     * Function for the recursive removal of directories.
     * @param type $path The path to the directory to be deleted.
     * @return type Returns the status of a function or function rmdir unlink.
     */
    function removeDirs($path) {
        if (is_dir($path)) {
            $entries = scandir($path);
            if ($entries === false) {
                $entries = array();
            }
            foreach ($entries as $entry) {
                if ($entry != '.' && $entry != '..') {
                    $this->removeDirs($path . '/' . $entry);
                }
            }
            return rmdir($path);
        } else {
            return unlink($path);
        }
    }

    /**
     * Function for logging.
     * @param type $contents The line for entry in the log file.
     */
    function logging($contents) {
        $hOut = fopen(LOG_FILE, 'a+');
        fwrite($hOut, $contents);
        fclose($hOut);
    }

    /**
     *  Public Functions
     */

    /**
     * Performs a checkout and creates files and folders.
     * 
     * @param string $folder Defaults to disk root
     * @param string $outPath Defaults to current folder (.)
     * @param boolean $checkFiles Whether it is necessary to check the received 
     * files in the sizes. Can be useful in case often files are accepted 
     * with an error.
     */
    public function checkOut($folder = '/', $outPath = '.', $checkFiles = false) {
        while ($outPath[strlen($outPath) - 1] == '/' && strlen($outPath) > 1) {
            $outPath = substr($outPath, 0, -1);
        }
        $tree = $this->getDirectoryTree($folder);
        if (!file_exists($outPath)) {
            mkdir($outPath, 0777, TRUE);
        }
        foreach ($tree as $file) {
            $path = $file['path'];
            $tmp = strstr(trim($path, '/'), trim($folder, '/'));
            $createPath = $outPath . '/' . ($tmp ? substr($tmp, strlen(trim($folder, '/'))) : "");
            if (trim($path, '/') == trim($folder, '/'))
                continue;
            if ($file['type'] == 'directory' && !is_dir($createPath)) {
                //echo "Current status: <font color='blue'>Directory: " . $createPath . "</font><br /> \r\n";
                $this->logging("Current status: <font color='blue'>Directory: " . $createPath . "</font><br /> \r\n");
                flush();
                mkdir($createPath);
            } elseif ($file['type'] == 'file') {

                for ($x = 0; $x < 2; $x++) {
                    $contents = $this->getFile($path);
                    $outText .= "<font color='blue'>Getting file: </font> " . $path;
                    $outText .= " <br />\r\n";
                    if ($checkFiles) {
                        $fileSize = $this->getFileSize($path);
                        $outText.= " The size of the received file: " . strlen($contents) .
                                " File size in a repository: " . $fileSize;
                        $outText.= " <br />\r\n";

                        if (strlen($contents) != $fileSize) {
                            $outText.= "<font color='red'> Error receiving file: " . $createPath . "</font> --- " . $x;
                        } else {
                            break;
                        }
                        $outText.= " <br />\r\n";
                    } else {
                        break;
                    }
                }
                //echo $outText;
                $this->logging($outText);
                flush();

                $hOut = fopen($createPath, 'w');
                fwrite($hOut, $contents);
                fclose($hOut);
            }
        }
        if ($this->path_exec_after_completition != '') {
            $this->exec_after_completition();
        }
    }

    /**
     * Function to easily create and update a working copy of the repository.
     * @param type $folder Folder in remote repository
     * @param type $outPath Folder for storing files
     * @param boolean $checkFiles Whether it is necessary to check the received 
     * files in the sizes. Can be useful in case often files are accepted 
     * with an error.
     */
    public function createOrUpdateWorkingCopy($folder = '/', $outPath = '.', $checkFiles = false) {

        if (!file_exists($outPath . '/.svn/entries')) {
            //Create a directory for storing system information for further updates.
            $this->createDirs($outPath . '/.svn');
            //Keeping the current version of the copy.
            $hOut = fopen($outPath . '/.svn/entries', 'w');
            fwrite($hOut, $this->actVersion);
            fclose($hOut);
            //echo "Current status: <font color='blue'>Starting checkout...</font><br /> \r\n";
            $this->logging("Current status: <font color='blue'>Starting checkout...</font><br /> \r\n");
            flush();
            $this->checkOut($folder, $outPath, $checkFiles);
        } else {
            //Obtain the number of current version number of the local copy.
            $hOut = fopen($outPath . '/.svn/entries', 'r');
            while (!feof($hOut)) {
                $copy_version = fgets($hOut);
            }
            fclose($hOut);

            //echo "Repository exist with version: " . $copy_version . "<br /> \r\n";
            $this->logging("Repository exist with version: " . $copy_version . "<br /> \r\n");
            flush();

            //Get a list of objects to be updated.
            $objects_list = $this->getLogsForUpdate($folder, $copy_version + 1);
            if (!is_null($objects_list)) {
                ////Lets update dirs
                // Add dirs
                foreach ($objects_list['dirs'] as $file) {
                    if ($file != '') {
                        $file = str_replace($folder, "", $file);
                        $file = $outPath . '/' . $file;
                        $file = str_replace("///", "/", $file);
                        //echo "<font color='blue'>Added or modified directory: </font>" . $file . "<br />\r\n";
                        $this->logging("<font color='blue'>Added or modified directory: </font>" . $file . "<br />\r\n");
                        $this->createDirs($file);
                    }
                }
                // Remove dirs
                // TEST IT!
                foreach ($objects_list['dirsDelete'] as $file) {
                    if ($file != '') {
                        $file = str_replace($folder, "", $file);
                        $file = $outPath . '/' . $file;
                        $file = str_replace("///", "/", $file);
                        $this->removeDirs($file);
                        //echo "<font color='red'>Removed directory: </font>" . $file . "<br />\r\n";
                    }
                }

                //echo "<font color='green'>************************</font><br />\r\n";

                ////Lets update files
                // Add files
                foreach ($objects_list['files'] as $file) {
                    if ($file != '') {
                        $createPath = str_replace($folder, "", $file);
                        $createPath = $outPath . '/' . $createPath;
                        $createPath = str_replace("///", "/", $createPath);

                        $contents = $this->getFile($file);
                        $hOut = fopen($createPath, 'w');
                        fwrite($hOut, $contents);
                        fclose($hOut);
                        $out = "<font color='blue'>Added or modified file: </font> ";
                        if (strlen($contents) < 1) {
                            $out.= "<font color='red'> " . $file . " with 0 size </font> ";
                        } else {
                            $out.= $file;
                        }
                        $out.= " <br />\r\n";
                        //echo $out;
                    }
                }
                //Remove files
                foreach ($objects_list['filesDelete'] as $file) {
                    if ($file != '') {
                        $file = str_replace($folder, "", $file);
                        $file = $outPath . '/' . $file;
                        $file = str_replace("///", "/", $file);
                        unlink($file);
                        //echo "<font color='red'>Removed file: </font>" . $file . "<br />\r\n";
                    }
                }
                $hOut = fopen($outPath . '/.svn/entries', 'w');
                fwrite($hOut, $this->actVersion);
                fclose($hOut);
            }
        }

        if ($this->path_exec_after_completition != '') {
            $this->exec_after_completition();
        }
    }

    /**
     * Function to view the changes between revisions of the specified object.
     * @param type $path The path to the object (file or directory).
     * @param type $revFrom Initial revision.
     * @param type $revTo The final revision.
     */
    public function diffVersions($path = '', $revFrom = 0, $revTo = 0) {

        require_once 'ext/Diff/Diff.php';
        require_once 'ext/Diff/Renderer.php';
        require_once 'ext/Diff/Renderer/unified.php';

        $this->mime_array = $this->get_mime_array();

        //Get a list of objects to be updated.
        $objects_list = $this->getLogsForUpdate($path, $revFrom, $revTo, false);
        if (!is_null($objects_list)) {
//            print_r($objects_list);
            foreach ($objects_list['files'] as $file) {
                if ($file != '') {

                    $path_info = pathinfo($file);
                    $mime_type = $this->mime_array[$path_info['extension']];

                    if (strpos($mime_type, "text") !== false) {

                        $file_revFrom = $this->getFile($file, $revFrom);
                        $file_revFrom =
                                $this->explodeX(array("\r\n", "\r", "\n"), $file_revFrom);

                        $file_revTo = $this->getFile($file, $revTo);
                        $file_revTo = $this->explodeX(array("\r\n", "\r", "\n"), $file_revTo);


                        /* Create the Diff object. */
                        $diff = new Text_Diff('auto', array($file_revFrom, $file_revTo));

                        /* Output the diff in unified format. */
                        $renderer = new Text_Diff_Renderer_unified();
                        $result = $renderer->render($diff);
                        if (strlen($result) > 1) {
                        /*
                            echo "Index: " . $file . " \r\n";
                            echo "===================================================================" . " \r\n";
                            echo "--- " . $file . "     (revision " . $revFrom . ")" . " \r\n";
                            echo "+++ " . $file . "     (revision " . $revTo . ")" . " \r\n";
                            echo $renderer->render($diff) . " \r\n";
                         */
                        }
                    }
                }
            }
            foreach ($objects_list['filesDelete'] as $file) {
                if ($file != '') {

                    $path_info = pathinfo($file);
                    $mime_type = $this->mime_array[$path_info['extension']];

                    if (strpos($mime_type, "text") !== false) {

                        $file_revFrom = $this->getFile($file, $revFrom);
                        $file_revFrom =
                                $this->explodeX(array("\r\n", "\r", "\n"), $file_revFrom);

                        $file_revTo = $this->getFile($file, $revTo);
                        $file_revTo = $this->explodeX(array("\r\n", "\r", "\n"), $file_revTo);


                        /* Create the Diff object. */
                        $diff = new Text_Diff('auto', array($file_revFrom, $file_revTo));

                        /* Output the diff in unified format. */
                        $renderer = new Text_Diff_Renderer_unified();
                        $result = $renderer->render($diff);
                        if (strlen($result) > 1) {
                        /*
                            echo "Index: " . $file . " \r\n";
                            echo "===================================================================" . " \r\n";
                            echo "--- " . $file . "     (revision " . $revFrom . ")" . " \r\n";
                            echo "+++ " . $file . "     (revision " . $revTo . ")" . " \r\n";
                            echo $renderer->render($diff) . " \r\n";
                         */
                        }
                    }
                }
            }
        }
    }

    /**
     *  rawDirectoryDump
     *
     * Dumps SVN data for $folder in the version $version of the repository.
     *
     *  @param string  $folder Folder to get data
     *  @param integer $version Repository version, -1 means actual
     *  @return array SVN data dump.
     */
    public function rawDirectoryDump($folder = '/', $version = -1) {

        if ($version == -1 || $version > $this->actVersion) {
            $version = $this->actVersion;
        }
        $url = $this->cleanURL($this->_url . "/!svn/bc/" . $version . "/" . $folder . "/");
        $this->initQuery($args, "PROPFIND", $url);
        $args['Body'] = PHPSVN_NORMAL_REQUEST;
        $args['Headers']['Content-Length'] = strlen(PHPSVN_NORMAL_REQUEST);

        if (!$this->Request($args, $headers, $body)) {
            return false;
        }
        $xml2Array = new xml2Array();
        return $xml2Array->xmlParse($body);
    }

    /**
     *  getDirectoryFiles
     *
     *  Returns all the files in $folder in the version $version of 
     *  the repository.
     *
     *  @param string  $folder Folder to get files
     *  @param integer $version Repository version, -1 means actual
     *  @return array List of files.     
     */
    public function getDirectoryFiles($folder = '/', $version = -1) {
        if ($arrOutput = $this->rawDirectoryDump($folder, $version)) {
            $files = array();
            foreach ($arrOutput['children'] as $key => $value) {
                array_walk_recursive($value, array($this, 'storeDirectoryFiles'));
                array_push($files, $this->storeDirectoryFiles);
                unset($this->storeDirectoryFiles);
            }
            return $files;
        }
        return false;
    }

    /**
     *  getDirectoryTree
     *
     *   Returns the complete tree of files and directories in $folder from the
     *  version $version of the repository. Can also be used to get the info 
     *  for a single file or directory.
     *
     *  @param string  $folder Folder to get tree
     *  @param integer $version Repository version, -1 means current
     *  @param boolean $recursive Whether to get the tree recursively, or just
     *  the specified directory/file.
     *
     *  @return array List of files and directories.
     */
    public function getDirectoryTree($folder = '/', $version = -1, $recursive = true) {
        $directoryTree = array();

        if (!($arrOutput = $this->getDirectoryFiles($folder, $version)))
            return false;

        if (!$recursive)
            return $arrOutput[0];

        while (count($arrOutput) && is_array($arrOutput)) {
            $array = array_shift($arrOutput);

            array_push($directoryTree, $array);

            if (trim($array['path'], '/') == trim($folder, '/'))
                continue;

            if ($array['type'] == 'directory') {
                $walk = $this->getDirectoryFiles($array['path'], $version);

                if (is_Array($walk)) {
                array_shift($walk);

                foreach ($walk as $step) {
                    array_unshift($arrOutput, $step);
                }

                }
            }
        }
        return $directoryTree;
    }

    /**
     *  Returns file contents
     *
     *  @param  string  $file File pathname
     *  @param  integer $version File Version
     *  @return string  File content and information, false on error, or if a
     *                                  directory is requested
     */
    public function getFile($file, $version = -1) {
        if ($version == -1 || $version > $this->actVersion) {
            $version = $this->actVersion;
        }

        // check if this is a directory... if so, return false, otherwise we
        // get the HTML output of the directory listing from the SVN server. 
        // This is maybe a bit heavy since it makes another connection to the
        // SVN server. Maybe add this as an option/parameter? ES 23/06/08
        $fileInfo = $this->getDirectoryTree($file, $version, false);
        if ($fileInfo["type"] == "directory")
            return false;

        $url = $this->cleanURL($this->_url . "/!svn/bc/" . $version . "/" . $file . "/");
        $this->initQuery($args, "GET", $url);
        if (!$this->Request($args, $headers, $body))
            return false;

        return $body;
    }

    /**
     *  Get changes logs of a file.
     *
     *  Get repository change logs between version
     *  $vini and $vend.
     *
     *  @param integer $vini Initial Version
     *  @param integer $vend End Version
     *  @return Array Respository Logs
     */
    public function getRepositoryLogs($path = "/", $vini = 0, $vend = -1) {
        return $this->getFileLogs($path, $vini, $vend);
    }

    /**
     *  Get changes logs of a file.
     *
     *  Get repository change of a file between version
     *  $vini and $vend.
     *
     *  @param string $file File for which to get log data
     *  @param integer $vini Initial Version
     *  @param integer $vend End Version
     *  @return array Respository Logs
     */
    public function getFileLogs($file, $vini = 0, $vend = -1) {
        $fileLogs = array();

        if ($vend == -1 || $vend > $this->actVersion)
            $vend = $this->actVersion;

        if ($vini < 0)
            $vini = 0;
        if ($vini > $vend)
            $vini = $vend;

        $url = $this->cleanURL($this->_url . "/!svn/bc/" . $this->actVersion . "/" . $file . "/");
        $this->initQuery($args, "REPORT", $url);
        $args['Body'] = sprintf(PHPSVN_LOGS_REQUEST, $vini, $vend);
        $args['Headers']['Content-Length'] = strlen($args['Body']);
        $args['Headers']['Depth'] = 1;

        if (!$this->Request($args, $headers, $body))
            return false;

        $xml2Array = new xml2Array();
        $arrOutput = $xml2Array->xmlParse($body);

        foreach ($arrOutput['children'] as $value) {
            $array = array();
            foreach ($value['children'] as $entry) {
                if ($entry['name'] == 'D:VERSION-NAME')
                    $array['version'] = $entry['tagData'];
                if ($entry['name'] == 'D:CREATOR-DISPLAYNAME')
                    $array['author'] = $entry['tagData'];
                if ($entry['name'] == 'S:DATE')
                    $array['date'] = $entry['tagData'];
                if ($entry['name'] == 'D:COMMENT')
                    $array['comment'] = $entry['tagData'];

                if (($entry['name'] == 'S:ADDED-PATH') ||
                        ($entry['name'] == 'S:MODIFIED-PATH') ||
                        ($entry['name'] == 'S:DELETED-PATH')) {
                    // For backward compatability
                    $array['files'][] = $entry['tagData'];

                    if ($entry['name'] == 'S:ADDED-PATH')
                        $array['add_files'][] = $entry['tagData'];
                    if ($entry['name'] == 'S:MODIFIED-PATH')
                        $array['mod_files'][] = $entry['tagData'];
                    if ($entry['name'] == 'S:DELETED-PATH')
                        $array['del_files'][] = $entry['tagData'];
                }
            }
            array_push($fileLogs, $array);
        }

        return $fileLogs;
    }

    public function getLogsForUpdate($file, $vini = 0, $vend = -1, $checkvend = true) {
        $fileLogs = array();

        if (($vend == -1 || $vend > $this->actVersion) && $checkvend) {
            $vend = $this->actVersion;
        }

        if ($vini < 0)
            $vini = 0;

        if ($vini > $vend) {
            $vini = $vend;
            //echo "Nothing updated";
            $this->logging("Nothing updated");
            return null;
        }

        $url = $this->cleanURL($this->_url . "/!svn/bc/" . $this->actVersion . "/" . $file . "/");
        $this->initQuery($args, "REPORT", $url);
        $args['Body'] = sprintf(PHPSVN_LOGS_REQUEST, $vini, $vend);
        $args['Headers']['Content-Length'] = strlen($args['Body']);
        $args['Headers']['Depth'] = 1;

        if (!$this->Request($args, $headers, $body)) {
            //echo "ERROR in request";
            return false;
        }

        $xml2Array = new xml2Array();
        $arrOutput = $xml2Array->xmlParse($body);

        $array = array();
        foreach ($arrOutput['children'] as $value) {
            foreach ($value['children'] as $entry) {

                if (($entry['name'] == 'S:ADDED-PATH') ||
                        ($entry['name'] == 'S:MODIFIED-PATH') ||
                        ($entry['name'] == 'S:DELETED-PATH')) {
                    if ($entry['attrs']['NODE-KIND'] == "file") {
                        $array['objects'][] = array('object_name' => $entry['tagData'], 'action' => $entry['name'], 'type' => 'file');
                    } else if ($entry['attrs']['NODE-KIND'] == "dir") {
                        $array['objects'][] = array('object_name' => $entry['tagData'], 'action' => $entry['name'], 'type' => 'dir');
                    }
                }
            }
        }
        $files = "";
        $filesDelete = "";
        $dirs = "";
        $dirsDelete = "";

        foreach ($array['objects'] as $objects) {
            if ($objects['type'] == "file") {
                if ($objects['action'] == "S:ADDED-PATH" || $objects['action'] == "S:MODIFIED-PATH") {
                    $file = $objects['object_name'] . "/*+++*/";
                    $files.=$file;
                    $filesDelete = str_replace($file, "", $filesDelete, $count);
                }
                if ($objects['action'] == "S:DELETED-PATH") {
                    if (strpos($files, $objects['object_name']) !== false) {
                        $file = $objects['object_name'] . "/*+++*/";
                        $count = 1;
                        $files = str_replace($file, "", $files, $count);
                    } else {
                        $filesDelete.=$objects['object_name'] . "/*+++*/";
                    }
                }
            }
            if ($objects['type'] == "dir") {
                if ($objects['action'] == "S:ADDED-PATH" || $objects['action'] == "S:MODIFIED-PATH") {
                    $dir = $objects['object_name'] . "/*+++*/";
                    $dirs.=$dir;
                    $dirsDelete = str_replace($dir, "", $dirsDelete, $count);
                }
                if ($objects['action'] == "S:DELETED-PATH") {
                    // Delete files from filelist
                    $dir = $objects['object_name'] . "/";
                    $files1 = explode("/*+++*/", $files);
                    for ($x = 0; $x < count($files1); $x++) {
                        if (strpos($files1[$x], $dir) !== false) {
                            unset($files1[$x]);
                        }
                    }
                    $files = implode("/*+++*/", $files1);
                    // END OF Delete files from filelist
                    // Delete dirs from dirslist
                    if (strpos($dirs, $objects['object_name']) !== false) {
                        $dir = $objects['object_name'] . "/*+++*/";
                        $count = 1;
                        $dirs = str_replace($dir, "", $dirs, $count);
                    } else {
                        $dirsDelete.=$objects['object_name'] . "/*+++*/";
                    }
                    // END OF Delete dirs from dirslist
                }
            }
        }
        $files = explode("/*+++*/", $files);
        $filesDelete = explode("/*+++*/", $filesDelete);
        $dirs = explode("/*+++*/", $dirs);
        $dirsDelete = explode("/*+++*/", $dirsDelete);
        $out = array();
        $out['files'] = $files;
        $out['filesDelete'] = $filesDelete;
        $out['dirs'] = $dirs;
        $out['dirsDelete'] = $dirsDelete;
        return $out;
    }

    /**
     *  Returns the repository version
     *
     *  @return integer Repository version
     *  @access public
     */
    public function getVersion() {
        if ($this->_repVersion > 0)
            return $this->_repVersion;

        $this->_repVersion = -1;
        $this->initQuery($args, "PROPFIND", $this->cleanURL($this->_url . "/!svn/vcc/default"));
        $args['Body'] = PHPSVN_VERSION_REQUEST;
        $args['Headers']['Content-Length'] = strlen(PHPSVN_NORMAL_REQUEST);
        $args['Headers']['Depth'] = 0;

        //        echo $vini."\r\n";
//        echo $vend."\r\n";
/*
        echo "Args: \r\n";
        print_r($args);
        echo "Headers: \r\n";
        print_r($tmp);
        */

        if (!$this->Request($args, $tmp, $body)) {
            return $this->_repVersion;
        }

        $parser = new xml_parser_class;
        $parser->Parse($body, true);
        $enable = false;
        foreach ($parser->structure as $value) {
            if ($enable) {
                $t = explode("/", $value);

                // start from the end and move backwards until we find a non-blank entry
                $index = count($t) - 1;
                while ($t[$index] == "") {
                    $index--;
                }

                // check the last non-empty element to see if it's numeric. If so, it's the revision number
                if (is_numeric($t[$index])) {
                    $this->_repVersion = $t[$index];
                    break;
                } else {
                    $enable = false;
                    continue;
                }
            }
            if (is_array($value) && $value['Tag'] == 'D:href')
                $enable = true;
        }
        return $this->_repVersion;
    }

    /**
     *  Deprecated functions for backward comatability
     */

    /**
     *  Set URL
     *
     *  Set the project repository URL.
     *
     *  @param string $url URL of the project.
     *  @access public
     */
    public function setRepository($url) {
        $this->_url = $url;
        $this->_repVersion = 0;
        $this->actVersion = $this->getVersion();
    }

    /**
     *  Old method; there's a typo in the name. This is now a wrapper for setRepository
     */
    public function setRespository($url) {
        return $this->setRepository($url);
    }

    /**
     *  Add Authentication  settings
     *
     *  @param string $user Username
     *  @param string $pass Password
     */
    public function setAuth($user, $pass) {
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     *  Private Functions
     */

    /**
     *  Callback for array_walk_recursive in public function getDirectoryFiles
     *
     *  @access private
     */
    private function storeDirectoryFiles($item, $key) {
        if ($key == 'name') {
            if (($item == 'D:HREF') ||
                    ($item == 'LP1:GETLASTMODIFIED') ||
                    ($item == 'LP1:VERSION-NAME') ||
                    ($item == 'LP2:BASELINE-RELATIVE-PATH') ||
                    ($item == 'LP3:BASELINE-RELATIVE-PATH') ||
                    ($item == 'D:STATUS')) {
                $this->lastDirectoryFiles = $item;
            }
        } elseif (($key == 'tagData') && ($this->lastDirectoryFiles != '')) {

            // Unsure if the 1st of two D:HREF's always returns the result we want, but for now...
            if (($this->lastDirectoryFiles == 'D:HREF') && (isset($this->storeDirectoryFiles['type'])))
                return;

            // Dump into the array 
            switch ($this->lastDirectoryFiles) {
                case 'D:HREF':
                    $var = 'type';
                    break;
                                case 'LP1:VERSION-NAME':
                                        $var = 'version';
                    break;
                case 'LP1:GETLASTMODIFIED':
                    $var = 'last-mod';
                    break;
                case 'LP2:BASELINE-RELATIVE-PATH':
                case 'LP3:BASELINE-RELATIVE-PATH':
                    $var = 'path';
                    break;
                case 'D:STATUS':
                    $var = 'status';
                    break;
            }
            $this->storeDirectoryFiles[$var] = $item;
            $this->lastDirectoryFiles = '';

            // Detect 'type' as either a 'directory' or 'file'
            if ((isset($this->storeDirectoryFiles['type'])) &&
                    (isset($this->storeDirectoryFiles['last-mod'])) &&
                    (isset($this->storeDirectoryFiles['path'])) &&
                    (isset($this->storeDirectoryFiles['status']))) {
                $this->storeDirectoryFiles['path'] = str_replace(' ', '%20', $this->storeDirectoryFiles['path']); //Hack to make filenames with spaces work.
                $len = strlen($this->storeDirectoryFiles['path']);
                if (substr($this->storeDirectoryFiles['type'], strlen($this->storeDirectoryFiles['type']) - $len) == $this->storeDirectoryFiles['path']) {
                    $this->storeDirectoryFiles['type'] = 'file';
                    $this->storeDirectoryFiles['size'] = $this->getFileSize($this->storeDirectoryFiles['path']);
                } else {
                    $this->storeDirectoryFiles['type'] = 'directory';
                }
            }
        } else {
            $this->lastDirectoryFiles = '';
        }
    }

    /**
     *  Prepare HTTP CLIENT object
     *
     *  @param array &$arguments Byreferences variable.
     *  @param string $method Method for the request (GET,POST,PROPFIND, REPORT,ETC).
     *  @param string $url URL for the action.
     *  @access private
     */
    private function initQuery(&$arguments, $method, $url) {
        $http = & $this->_http;
        $http->GetRequestArguments($url, $arguments);
        if (isset($this->user) && isset($this->pass)) {
            $arguments["Headers"]["Authorization"] = " Basic " . base64_encode($this->user . ":" . $this->pass);
        }
        $arguments["RequestMethod"] = $method;
        $arguments["Headers"]["Content-Type"] = "text/xml";
        $arguments["Headers"]["Depth"] = 1;
    }

    /**
     *  Open a connection, send request, read header
     *  and body.
     *
     *  @param Array $args Connetion's argument
     *  @param Array &$headers Array with the header response.
     *  @param string &$body Body response.
     *  @return boolean True is query success
     *  @access private
     */
    private function Request($args, &$headers, &$body) {
        $args['RequestURI'] = str_replace(' ', '%20', $args['RequestURI']); //Hack to make filenames with spaces work.
        //DebMes("Request: ".serialize($args));
        $http = & $this->_http;
        $http->Open($args);
        $http->SendRequest($args);
        $http->ReadReplyHeaders($headers);
        if ($http->response_status[0] != 2) {
            switch ($http->response_status) {
                case 404:
                    $this->errNro = NOT_FOUND;
                    break;
                case 401:
                    $this->errNro = AUTH_REQUIRED;
                    break;
                default:
                    $this->errNro = UNKNOWN_ERROR;
                    break;
            }
//            trigger_error("request to $args[RequestURI] failed: $http->response_status
//Error: $http->error");
            $http->close();
            return false;
        }
        $this->errNro = NO_ERROR;
        $body = '';
        $tbody = '';
        for (;;) {
            $error = $http->ReadReplyBody($tbody, 1000);
            if ($error != "" || strlen($tbody) == 0) {
                break;
            }
            $body.= ( $tbody);
        }
        //print_r($tbody);
        $http->close();
        return true;
    }

    /**
     *  Returns $url stripped of '//'
     *
     *  Delete "//" on URL requests.
     *
     *  @param string $url URL
     *  @return string New cleaned URL.
     *  @access private
     */
    private function cleanURL($url) {
        return preg_replace("/((^:)\/\/)/", "//", $url);
    }

    /**
     * Private function for executing external script.
     */
    private function exec_after_completition() {
        require_once $this->path_exec_after_completition;
    }

    /**
     * Function to specify a script that should be executed 
     * after the checkout or update a local repository.
     * @param type $path_to_file - Path to file (script) for execution
     */
    function set_job_for_exec_after_completition($path_to_file) {
        $this->path_exec_after_completition = $path_to_file;
    }

    private function get_mime_array() {
        $regex = "/([\w\+\-\.\/]+)\t+([\w\s]+)/i";
        $lines = file("ext/mime/mime.types", FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            if (substr($line, 0, 1) == '#')
                continue; // skip comments 
            if (!preg_match($regex, $line, $matches))
                continue; // skip mime types w/o any extensions 
            $mime = $matches[1];
            $extensions = explode(" ", $matches[2]);
            foreach ($extensions as $ext)
                $mimeArray[trim($ext)] = $mime;
        }
        return ($mimeArray);
    }

    private function explodeX($delimiters, $string) {
        $return_array = Array($string); // The array to return
        $d_count = 0;
        while (isset($delimiters[$d_count])) { // Loop to loop through all delimiters
            $new_return_array = Array();
            foreach ($return_array as $el_to_split) { // Explode all returned elements by the next delimiter
                $put_in_new_return_array = explode($delimiters[$d_count], $el_to_split);
                foreach ($put_in_new_return_array as $substr) { // Put all the exploded elements in array to return
                    $new_return_array[] = $substr;
                }
            }
            $return_array = $new_return_array; // Replace the previous return array by the next version
            $d_count++;
        }
        return $return_array; // Return the exploded elements
    }

    public function getFileSize($file = '/', $version = -1) {

        if ($version == -1 || $version > $this->actVersion) {
            $version = $this->actVersion;
        }
        $url = $this->cleanURL($this->_url . "/!svn/bc/" . $version . "/" . $file . "/");
        $this->initQuery($args, "PROPFIND", $url);
        $args['Body'] = PHPSVN_GET_FILE_SIZE;
        $args['Headers']['Content-Length'] = strlen(PHPSVN_GET_FILE_SIZE);

        if (!$this->Request($args, $headers, $body)) {
            return false;
        }
        $xml2Array = new xml2Array();
        $arrOutput = $xml2Array->xmlParse($body);

        if ($arrOutput) {
            $files = array();
            foreach ($arrOutput['children'] as $key => $value) {
                array_walk_recursive($value, array($this, 'get_file_size_resursively'));
            }
            return $this->file_size;
        }
    }

    private function get_file_size_resursively($item, $key) {
        if ($key == 'name') {
            if ($item == 'LP1:GETCONTENTLENGTH') {
                $this->file_size_founded = true;
            }
        } elseif (($key == 'tagData') && $this->file_size_founded) {
            $this->file_size = $item;
            $this->file_size_founded = false;
        }
    }

}

?>
