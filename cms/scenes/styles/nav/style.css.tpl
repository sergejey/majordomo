/* NAV BUTTON */
.type_nav {
 margin-right: -2px;
 margin-bottom: 1px;
 color:black;
 border:1px solid rgba(255,255,255,0.5);
 font-size:14px;
 background-color:rgba(255,255,255,0.7);
 padding:0px;
 align:left;
 width:145px;
 height:34px;
}

.type_nav span {
 display: inline-block;
 width: 124px;
 position: relative;
 padding-left: 12px;
 padding-top: 9px;
 vertical-align: middle;

 height: 28px;
 background-image: url({$smarty.const.ROOTHTML}img/elements/nav_arrow.png);
 background-repeat: no-repeat;
 background-position: right center;
}

{foreach $TYPE.STYLES as $STYLE}
{if $STYLE.HAS_DEFAULT!=""}
.type_nav.style_{$STYLE.TITLE} {
     background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/nav/{$STYLE.HAS_DEFAULT});
     background-repeat: no-repeat;
     background-position: 7px 1px;
}
.type_nav.style_{$STYLE.TITLE} span {
 margin-left:36px;
 width: 88px;
}
{/if}
{/foreach}