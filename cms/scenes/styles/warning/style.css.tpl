/* MOTION */
.type_warning {
 margin-right: -2px;
 margin-bottom: 1px;
 color:black;
 border:none;
 /*border-radius:3px;  */
 text-align: center;
 background-image:url({$smarty.const.ROOTHTML}img/elements/circle_red.png);
 padding:4px;
 width:68px;
 height:68px;
}
.type_warning span {
 display: block;
 width:68px;
 height:68px;
 vertical-align: middle;
}

{foreach $TYPE.STYLES as $STYLE}
{if $STYLE.HAS_DEFAULT!=""}
    .type_warning.style_{$STYLE.TITLE}:before {
     content: url({$smarty.const.ROOTHTML}cms/scenes/styles/warning/{$STYLE.HAS_DEFAULT});
    }
{/if}
{/foreach}