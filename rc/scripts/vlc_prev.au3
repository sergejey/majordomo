Opt("WinTitleMatchMode", 2)
If WinExists("VLC") Then
    WinActivate("VLC", "")
EndIf
Send("{Media_Prev}")
