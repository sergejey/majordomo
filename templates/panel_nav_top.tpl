<script type="text/javascript">
    function showNotyDate(id) {
        $('#notyAddtimeBlock_' + id).show();
        $('#notyMsgBlock_' + id).hide();
    }

    function hideNotyDate(id) {
        $('#notyMsgBlock_' + id).show();
        $('#notyAddtimeBlock_' + id).hide();
    }
</script>
{foreach $SUB_MODULES as $item}

    {if $item.NEW_CATEGORY}
        <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
           aria-expanded="false">{$item.CATEGORY} <b class="caret"></b></a>
        <ul class="dropdown-menu">
    {/if}

    {if !$item.DENIED}
        {if $item.LINKS}
            <li class="[#if SELECTED#]active[#endif#] category{$item.CATEGORY_ID} menu-item dropdown dropdown-submenu"
                id="module_{$item.NAME}_link"><a href='{$smarty.const.ROOTHTML}admin.php?action={$item.NAME}'
                                                 class="top-menu dropdown-toggle"><img src="{$item.ICON_SM}" width="24"
                                                                                       height="24">&nbsp;{$item.TITLE}
                </a>
                <ul class="dropdown-menu">
                    {foreach $item.LINKS as $link}
                        {if $link.DIVIDER==1}
                            <li class="divider" role="separator"></li>
                        {else}
                            <li class="menu-item"><a href="{$link.LINK}">{$link.TITLE}</a></li>
                        {/if}
                    {/foreach}
                </ul>
            </li>
        {else}
            <li class="[#if SELECTED#]active[#endif#] category{$item.CATEGORY_ID}" id="module_{$item.NAME}_link">
                <a href='<#ROOTHTML#>admin.php?action={$item.NAME}' class="top-menu"><img src="{$item.ICON_SM}"
                                                                                          width="24"
                                                                                          height="24">&nbsp;{$item.TITLE}
                    {if $item.NOTIFICATIONS_COUNT != 0}
                        <span class="badge pull-right alert-{$item.NOTIFICATIONS_TYPE}"
                              style="border: 1px solid;cursor: pointer;" data-container="body" data-toggle="popover"
                              data-trigger="manual" data-placement="bottom"
                              data-html="true">{$item.NOTIFICATIONS_COUNT}</span>
                    {/if}</a>
            </li>
            {if $item.NAME=="xray"}
                <li class="menu-item"><a href="#" onClick='return consoleToggle();'><i
                            class="glyphicon glyphicon-flash"></i> {$smarty.const.LANG_CONSOLE}</a></li>{/if}
        {/if}
    {/if}

    {if $item.LAST_IN_CATEGORY}
        </ul>
        </li>
    {/if}
{/foreach}