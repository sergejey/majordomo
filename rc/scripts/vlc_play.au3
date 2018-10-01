Opt("WinTitleMatchMode", 2)

$param = $CmdLine[1]
$param = StringReplace($param, "'", '"')

; AutoIt >= 3.3.2.0
;$param = StringReplace($param, "'", '"', 1)
;$param = StringReplace($param, "'", '"', -1)

ShellExecute ("\_majordomo\apps\vlc\vlc.exe", $param)

