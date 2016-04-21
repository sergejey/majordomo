/* MODE */
.type_mode {
        margin-right: -2px;
        margin-bottom: 1px;
        color:black;
        border:1px solid rgba(255,255,255,0.5);
        text-align: center;
        background-color:rgba(255,255,255,0.7);
        padding:2px;
        width:67px;
        height:67px;
}
.type_mode span {
 font-size:12px;
 display: inline-block;
 height:34px;
 vertical-align: middle;
 padding-top:35px;

}

/* MODE ON */
.type_mode.state_on {
 background-color:rgba(249,229,91,0.9);
 border:1px solid rgba(255,255,0,0.5);
}
.type_mode.state_sleep
{
 background-color:rgba(249,229,91,0.5);
 border:1px solid rgba(255,255,0,0.5);;
}

.type_mode.state_active
{
 color:white;
 border:1px solid rgba(0,0,0,0.2);
 background-color:rgba(0,0,0,0.5);
}

.type_mode.state_mid
{
 color:white;
 border:1px solid rgba(0,0,0,0.2);
 background-color:rgba(0,0,0,0.5);
}

.type_mode.state_na
{
 color:white;
 border:1px solid rgba(0,0,0,0.2);
 background-color:rgba(0,0,0,0.5);
}

{foreach $TYPE.STYLES as $STYLE}
{if $STYLE.HAS_DEFAULT!=""}
.type_mode.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_DEFAULT});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}
{if $STYLE.HAS_ON!=""}
.type_mode.state_on.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_ON});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}
{if $STYLE.HAS_OFF!=""}
.type_mode.state_off.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_OFF});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}

{if $STYLE.HAS_MID!=""}
.type_mode.state_mid.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_MID});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}

{if $STYLE.HAS_NA!=""}
.type_mode.state_na.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_NA});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}

/*{if $STYLE.HAS_SLEEP!=""}
.type_mode.state_sleep.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_SLEEP});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}
{if $STYLE.HAS_ACTIVE!=""}
.type_mode.state_active.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/mode/{$STYLE.HAS_ACTIVE});
  background-repeat: no-repeat;
  background-position: center 4px;
}
{/if}*/
{/foreach}
