<?php

$result = preg_replace('/<!--\s+(\w)/','<!--$1',$result);
$result = preg_replace('/([\w\]])\s+-->/','$1-->',$result);
