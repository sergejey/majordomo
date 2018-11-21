
/* INFORMER */
.type_informer {
 margin-right: -2px;
 margin-bottom: 1px;
 padding:0px;
 color:white;
 border:1px solid rgba(0,0,0,0.2);
 font-size:14px;
 background-color:rgba(0,0,0,0.5);
 align:left;
 width:81px;
 height:34px;
 background-image: url({$smarty.const.ROOTHTML}img/elements/i_temp.png);
 background-repeat: no-repeat;
 background-position: -6;
}

.type_informer span {
 display:inline-block;
 width:62px;
 height:28px;
 position:relative;
 padding-left:25px;
 padding-top:9px;
}




.type_informer.state_low span {
color:#92e7ff; 
}
.type_informer.state_high span {
color:#f1b001; 
}
.type_informer.state_high.style_humidity span {
color:#92e7ff; 
}
.type_informer.state_low.style_humidity span {
color:#f1b001; 
}

{foreach $TYPE.STYLES as $STYLE}
{if $STYLE.HAS_DEFAULT!=""}
 .type_informer.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/informer/{$STYLE.HAS_DEFAULT});
  background-repeat: no-repeat;
  background-position: -6;
 }
{/if}
{if $STYLE.HAS_HIGH!=""}
 .type_informer.state_high.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/informer/{$STYLE.HAS_HIGH});
  background-repeat: no-repeat;
  background-position: -6;
 }
{/if}
{if $STYLE.HAS_LOW!=""}
 .type_informer.state_low.style_{$STYLE.TITLE} {
  background-image: url({$smarty.const.ROOTHTML}cms/scenes/styles/informer/{$STYLE.HAS_LOW});
  background-repeat: no-repeat;
  background-position: -6;
 }
{/if}
{/foreach}
