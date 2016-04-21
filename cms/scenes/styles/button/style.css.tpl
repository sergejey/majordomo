/* BUTTON */
.type_button {
        margin-right: -2px;
        margin-bottom: 1px;
        color:black;
        border:1px solid rgba(255,255,255,0.4);
        text-align: center;
        background-color:rgba(255,255,255,0.7);
        padding:2px;
        width:67px;
        height:67px;
}
.type_button span {
 font-size:12px;
 display: inline-block;
 width:64px;
 height:34px;
 vertical-align: middle;
 padding-top:35px;
}

.type_button.clicked {
 background-color:rgba(249,229,91,0.9);
 border:1px solid #FF0;
}


{foreach $TYPE.STYLES as $STYLE}
   {if $STYLE.HAS_DEFAULT!=""}
    .type_button.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/button/{$STYLE.HAS_DEFAULT});
     background-repeat: no-repeat;
     background-position: center 4px;
    }
   {/if}
{/foreach}