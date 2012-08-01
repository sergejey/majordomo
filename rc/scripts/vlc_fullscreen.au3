;MsgBox(0, "Hi", $CmdLine[1])
;$pid=ProcessExists("vlc.exe")
;MsgBox(0, "Example", $pid)
;If ProcessExists("vlc.exe") Then
;    MsgBox(0, "Example", "VLC is running.")
;EndIf
Opt("WinTitleMatchMode", 2)
If WinExists("VLC") Then
    WinActivate("VLC", "")
    Send("f")
;    WinSetState("VLC", "", @SW_MINIMIZE)
EndIf