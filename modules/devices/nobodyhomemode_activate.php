<?php

//groupEco
$objects=getObjectsByProperty('groupEco','=',1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    callMethod($objects[$i].'.turnOff');
}