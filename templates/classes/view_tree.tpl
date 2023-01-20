<div class="row" style="margin-right: -15px;margin-left: -15px;">
    <div class="col-lg-6 col-md-5 col-sm-12 col-xs-12">
        <script>
            function filterProp() {
                term = $('#filterProp').val().toLowerCase();
                if(term.length <= 2) {
                    $('ul.classSearch>li').attr('style', 'padding: 5px;display: block;border: 0px;border-radius: 0px;border-bottom: 1px solid #ddd;');
                    $('ul.classSearch>li').removeAttr('id');
                    return;
                }
                $('ul.classSearch>li').each(function(index) {
                    textForSearch = $(this).children().children().text().trim();
                    searchResult = textForSearch.toLowerCase().indexOf(term);

                    if(searchResult >= 0) {
                        $(this).attr('style', 'padding: 5px;display: block;border: 0px;border-radius: 0px;border-bottom: 1px solid #ddd;color: red;background: #fff2f2;');
                        $($(this).prev()).attr('id', 'foundSearch');
                    } else {
                        $(this).attr('style', 'padding: 5px;display: block;border: 0px;border-radius: 0px;border-bottom: 1px solid #ddd;');
                        $(this).removeAttr('id');
                    }
                });

                if($('#foundSearch').length == 1) {
                    $('html, body').stop().animate({
                        scrollTop: $('#foundSearch').offset().top
                    }, 1000);
                }
            }

            function toggleClass(sub_id) {
                splitSub = sub_id.split(',');
                splitSubLength = splitSub.length-1;

                $('#sub_'+splitSub[0]).collapse('toggle');

                if($('#sub_'+splitSub[0]).attr('style') == 'height: 0px;') {
                    console.log('vis');
                    $.cookie("sub_classes_"+splitSub[0], '0');
                } else {
                    console.log('hid');
                    $.cookie("sub_classes_"+splitSub[0], '1');
                }

                if(splitSubLength != 1) {
                    $.each(splitSub,function(index,value){
                        if($('#sub_'+splitSub[0]).is(':hidden')) {
                            $('#sub_'+value).collapse('hide');
                        }
                    });
                }
            }
        </script>
        <input type="text" class="form-control" id="filterProp" oninput="filterProp();" placeholder="{$smarty.const.LANG_NEWMARKET_SEARCH_INPUT_PLACEHOLDER}">
    </div>
    <div class="col-lg-6 col-md-7 col-sm-12 col-xs-12 text-right" style="margin-bottom: 15px;">
        <div class="visible-sm visible-xs" style="margin-top: 5px;"></div>
        <a href="?view_mode=edit_classes" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> {$smarty.const.LANG_ADD_NEW_CLASS}</a>
        <div class="visible-sm visible-xs" style="margin-top: 5px;"></div>
        <a href="{$smarty.const.ROOTHTML}panel/class/0/object/0.html?md=objects&view_mode=edit_objects&id=" class="btn btn-success" title="{$smarty.const.LANG_EDIT}"><i class="glyphicon glyphicon-plus"></i> {$smarty.const.LANG_ADD_NEW_OBJECT}</a>
    </div>
</div>

<div class="row" style="margin-right: -15px;margin-left: -15px;">
    <div class="col-md-12" style="margin-bottom: 15px;">
        <form action="?" method="post" name="frmList_classes" style="padding:0px">
            {function name=classes}
            {foreach $items as $item}
            <script language="javascript">
                $(function() {
                    if ($.cookie("sub_classes_{$item.ID}") == '1') {
                        $('#sub_{$item.ID}').collapse('show');
                    }
                });
            </script>
            <div class="panel panel-{if $item.TITLE == 'SDevices'}primary{else}{if $item.TITLE == 'Computer' OR $item.TITLE == 'systemStates' OR $item.TITLE == 'OperationalModes' OR $item.TITLE == 'Timer'}danger{else}default{/if}{/if}" style="margin-bottom: 5px;{if $item.LEVEL_PAD!=0}margin:10px;{/if}">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-7 col-xs-9" onclick="toggleClass('{if $item.SUB_LIST!=$item.ID}{$item.ID},{/if}{$item.SUB_LIST}');" style="cursor: pointer;{if $item.CAN_DELETE=="1"}opacity: 0.3;{/if}">
                        <h3 class="panel-title">
                            <i class="glyphicon glyphicon-search" style="margin-right: 10px;vertical-align: text-top;cursor: pointer;" onclick="$('#filter_modules').val('{$item.TITLE}');filterSearch();$('#mdmGlobalSearchModal').modal('show');"></i> {$item.TITLE}
                        </h3>
                        {if $item.DESCRIPTION!=''}<div style="font-size: 1rem;padding-left: 30px;">{$item.DESCRIPTION}</div>{/if}
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-5 col-xs-3 text-right">
                        <div class="btn-group btn-group-xs">
                            <a href="?view_mode=edit_classes&id={$item.ID}" class="btn btn-success btn-sm" title="{$smarty.const.LANG_EDIT}"><i class="glyphicon glyphicon-pencil"></i></a>
                            <a href="?view_mode=edit_classes&id={$item.ID}&tab=properties" class="btn btn-default btn-sm" title="{$smarty.const.LANG_PROPERTIES}"><i class="glyphicon glyphicon-th"></i></a>
                            <a href="?view_mode=edit_classes&id={$item.ID}&tab=methods" class="btn btn-default btn-sm hidden-xs" title="{$smarty.const.LANG_METHODS}"><i class="glyphicon glyphicon-th-list"></i></a>
                            <a href="?view_mode=edit_classes&id={$item.ID}&tab=objects" class="btn btn-default btn-sm hidden-xs" title="{$smarty.const.LANG_OBJECTS}"><i class="glyphicon glyphicon-th-large"></i></a>
                            <a href="?view_mode=edit_classes&parent_id={$item.ID}" class="btn btn-default btn-sm hidden-xs" title="{$smarty.const.LANG_EXPAND}"><i class=""></i>{$smarty.const.LANG_EXPAND}</a>
                            {if $item.CAN_DELETE=="1"}
                            <a href="?view_mode=delete_classes&id={$item.ID}" onClick="return confirm('{$smarty.const.LANG_ARE_YOU_SURE}')" class="btn btn-danger btn-sm" title="{$smarty.const.LANG_DELETE}"><i class="glyphicon glyphicon-remove"></i></a>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-collapse collapse out" id="sub_{$item.ID}">
                {if $item.OBJECTS}
                <ul class="list-group classSearch">
                    {foreach $item.OBJECTS as $object}
                    <li style="padding: 5px;display: block;border: 0px;border-radius: 0px;border-bottom: 1px solid #ddd;">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{$smarty.const.ROOTHTML}panel/class/{$item.ID}/object/{$object.ID}.html">{$object.TITLE}</a> {if $object.DESCRIPTION != ''}<span style="font-size: 1rem;color: gray;vertical-align: middle;">→ {$object.DESCRIPTION}</span>{/if}
                                {if $object.KEY_DATA!=""} → <i>{$object.KEY_DATA}</i>{/if}
                                {if $object.METHODS}
                                <div style="padding-left: 10px">
                                    {foreach $object.METHODS as $method}
                                    <p style="color: gray;font-size: 1.2rem;margin: 0px;">↳
                                        <span class="label label-primary" style="margin-bottom: 5px;">{$smarty.const.LANG_METHOD}:
                                         <a style="color: white;text-decoration: none;"
                                         href="{$smarty.const.ROOTHTML}panel/class/{$item.ID}/object/{$object.ID}.html?tab=methods&overwrite=1&method_id={$method.ID}">{$method.TITLE}</a></span></p>
                                    {/foreach}
                                </div>
                                {/if}
                            </div>
                        </div>
                    </li>
                    {/foreach}
                </ul>
                {/if}

                {if $item.RESULT}
                    {classes items=$item.RESULT}
                {/if}

            </div>
    </div>
     {/foreach}
    {/function}
    {classes items=$RESULT}
    <input type="hidden" name="data_source" value="<#DATA_SOURCE#>">
    <input type="hidden" name="view_mode" value="multiple_classes">
    </form>
</div>
</div>





&nbsp;
<a href="#" onClick="$('#tools').toggle();return false;" class="btn btn-default">{$smarty.const.LANG_TOOLS}</a>
<br>&nbsp;
<div id='tools' style="display:none">
    <form action="?" enctype="multipart/form-data" method="post">
        <table border="0">
            <tr>
                <td valign="top">
{$smarty.const.LANG_IMPORT_CLASS_FROM_FILE}:
                </td>
                <td valign="top"><input type="file" name="file" enctype="multipart/form-data"></td>
            </tr>
            <tr>
                <td valign="top">&nbsp;</td>
                <td valign="top"><label><input type="checkbox" name="overwrite" value="1">
{$smarty.const.LANG_OVERWRITE}
                    </label></td>
            </tr>
            <tr>
                <td valign="top">&nbsp;</td>
                <td valign="top"><label><input type="checkbox" name="only_classes" value="1">
{$smarty.const.LANG_ONLY_CLASSES}
                    </label></td>
            </tr>
            <tr>
                <td valign="top">&nbsp;</td>
                <td valign="top"><input type="submit" name="submit" value="{$smarty.const.LANG_IMPORT}" class="btn btn-default">
                </td>
            </tr>
        </table>
        <input type="hidden" name="view_mode" value="import_classes">
    </form>
</div>