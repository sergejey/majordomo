Opt("WinTitleMatchMode", 2)
If WinExists("VLC") Then
    WinActivate("VLC", "")
    Send("!{F4}")
EndIf
If (ProcessExists("vlc.exe")) Then
 ProcessClose ("vlc.exe")
EndIf
