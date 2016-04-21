/* SWITCH */
.type_switch {
 margin-right: -2px;
 margin-bottom: 1px;
 color:black;
 border:1px solid rgba(255,255,255,0.4);
 text-align: center;
 background-color:rgba(255,255,255,0.7);
 padding:2px;
 width:67px;
 height:67px;
 background-image: url({$smarty.const.ROOTHTML}img/elements/i_light_off.png);
 background-repeat: no-repeat;
 background-position: center 4px;
}
.type_switch span {
 font-size:12px;
 display: inline-block;
/* width:64px;*/
 height:34px;
 vertical-align: middle;
 padding-top:35px;
}

/* SWITCH ON */
.type_switch.state_on {
 background-color:rgba(249,229,91,0.9);
 border:1px solid rgba(255,255,0,0.5);
 background-image: url({$smarty.const.ROOTHTML}img/elements/i_light_on.png);
 background-repeat: no-repeat;
 background-position: center 4px;
}

/* SWITCH MID */
.type_switch.state_mid {
 background-color:rgba(249,229,91,0.9);
 border:1px solid rgba(255,255,0,0.5);
 background-image: url({$smarty.const.ROOTHTML}img/elements/i_light_off.png);
 background-repeat: no-repeat;
 background-position: center 4px;
}

/* SWITCH NA */
.type_switch.state_na {
 background-color:rgba(249,229,91,0.9);
 border:1px solid rgba(255,255,0,0.5);
 background-image: url({$smarty.const.ROOTHTML}img/elements/i_light_off.png);
 background-repeat: no-repeat;
 background-position: center 4px;
}

.light_circle {
 position: relative;
 width: 10px;
 height: 10px;
 border: 1px solid rgba(0,0,0,0.2);
 border-radius: 6px;
}

{foreach $TYPE.STYLES as $STYLE}
{if $STYLE.HAS_DEFAULT!=""}
.type_switch.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/switch/{$STYLE.HAS_DEFAULT});
     background-repeat: no-repeat;
     background-position: center 4px;
}
{/if}
{if $STYLE.HAS_ON!=""}
.type_switch.state_on.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/switch/{$STYLE.HAS_ON});
     background-repeat: no-repeat;
     background-position: center 4px;
}
{/if}
{if $STYLE.HAS_OFF!=""}
.type_switch.state_off.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/switch/{$STYLE.HAS_OFF});
     background-repeat: no-repeat;
     background-position: center 4px;
}
{/if}
{if $STYLE.HAS_MID!=""}
.type_switch.state_mid.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/switch/{$STYLE.HAS_MID});
     background-repeat: no-repeat;
     background-position: center 4px;
}
{/if}

{if $STYLE.HAS_NA!=""}
.type_switch.state_na.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/switch/{$STYLE.HAS_NA});
     background-repeat: no-repeat;
     background-position: center 4px;
}
{/if}


{/foreach}