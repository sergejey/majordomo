<?php

//groupEco
$objects=getObjectsByProperty('groupEcoOn','=',1);
$total = count($objects);
for ($i = 0; $i < $total; $i++) {
    callMethodSafe($objects[$i].'.turnOn');
}