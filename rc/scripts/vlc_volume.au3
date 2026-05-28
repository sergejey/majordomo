Opt('WinTitleMatchMode', 2)
If WinExists('VLC') Then
    WinActivate('VLC', '')

    $volume = StringSplit($CmdLine[1], ':')

    if $volume[0] == 2 Then
        $steps = (volRound($volume[2]) - volRound($volume[1])) / 5
        Send('{CTRLDOWN}')
        For $i = 1 To Abs($steps)
            If $steps > 0 Then
                Send('{UP}')
            Else
                Send('{DOWN}')
            EndIf
            Sleep(50)
        Next
        Send('{CTRLUP}')
    EndIf
EndIf

Func volRound($volume)
    $volume = Round($volume/5)*5
    Return $volume
EndFunc

