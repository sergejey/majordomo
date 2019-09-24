<?php

// BEGIN: process subscriptions
if (preg_match_all('/<!--processSubscription (\w+?)-->/is', $result, $matches))
{
    $total = count($matches[0]);
    for($i=0;$i<$total;$i++) {
        $result = str_replace($matches[0][$i],'<span id="subscription'.$matches[1][$i].'"></span>',$result);
    }
    $result.="<script type='text/javascript'>";
    $result.="function postLoadSubscription(name) {
        var url=\"".ROOTHTML."objects/?processSubscriptionsOutput=1&event=\"+name;
        $.ajax({
            url: url
        }).done(function(data) {
         $('#subscription'+name).html(data);
        });
    }";
    $result.="\$(document).ready(function() {";
    foreach($matches[1] as $name) {
        $result.="postLoadSubscription('$name');";
    }
    $result.="});";
    $result.="</script>";
}
// END: process subscriptions
