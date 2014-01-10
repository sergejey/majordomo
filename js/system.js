
$(document).ready(function(){
        var html = '';  
        html += '<div id="alert_overlay" onclick="$(\'#alert_overlay\').fadeOut(\'fast\'); $(\'#alert_outer\').fadeOut(\'fast\');"></div>';
        html += '<div id="alert_wrap">';
        html += '<div id="alert_outer" onclick="$(\'#alert_overlay\').fadeOut(\'fast\'); $(\'#alert_outer\').fadeOut(\'fast\');">';
        html += '<div id="alert_inner">';
        html += '<div id="alert_close" onclick="$(\'#alert_overlay\').fadeOut(\'fast\'); $(\'#alert_outer\').fadeOut(\'fast\');"></div>';
        html += '<div id="alert_bg"><div class="alert_bg alert_bg_n"></div><div class="alert_bg alert_bg_ne"></div><div class="alert_bg alert_bg_e"></div><div class="alert_bg alert_bg_se"></div><div class="alert_bg alert_bg_s"></div><div class="alert_bg alert_bg_sw"></div><div class="alert_bg alert_bg_w"></div><div class="alert_bg alert_bg_nw"></div></div>';
        html += '<div id="alert_content"></div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        $(html).appendTo("body");
        
        $(document).keydown(function(e){
                if ((e.which == 13 || e.which == 27) && $("#alert_outer").css("display") != "none")
                {
                        $("#alert_overlay").fadeOut("fast");
                        $("#alert_outer").fadeOut("fast");
                }
        });
});


function showAlertWindow(msg, type)
{
        var src = "";
        var color = "";
        switch(type)
        {
        case 'accept':
          src = "/img/system/accept.png";
          color = "green";
          break;
        case 'error':
          src = "/img/system/warning.png";
          color = "red";
          break;
        case 'message':
          src = "/img/system/message.png";
          color = "blue";
          break;  
        default:
          return;
        }
        
        var toShow = "<ul><li>";
        if (typeof(msg) == 'object' && (msg instanceof Array))
                msg = msg.join("</li><li>");
        toShow = toShow + msg + "</li></ul>";
        
        $("#alert_overlay").css("opacity", 0.3).fadeIn("normal");
        $("#alert_outer").fadeIn("normal");
        $("#alert_content").html("<img src=" + src + ">" + toShow);
        $("#alert_content li").css("color", color);
        $("#alert_outer").css("top", $(document).scrollTop() + Math.round(($(window).height()-$("#alert_outer").height())/2)).css("left", $(document).scrollLeft() + Math.round(($(window).width()-$("#alert_outer").width())/2));
        var isIE = ($.browser.msie && parseInt($.browser.version.substr(0,1)) < 8);
        if (isIE)
        {
                $("#alert_close, .alert_bg, #alert_content").each(function () {
                        var image = $(this).css('backgroundImage');

                        if (image.match(/^url\(["']?(.*\.png)["']?\)$/i)) {
                                image = RegExp.$1;
                                $(this).css({
                                        'backgroundImage': 'none',
                                        'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=" + ($(this).css('backgroundRepeat') == 'no-repeat' ? 'crop' : 'scale') + ", src='" + image + "')"
                                }).each(function () {
                                        var position = $(this).css('position');
                                        if (position != 'absolute' && position != 'relative')
                                                $(this).css('position', 'relative');
                                });
                        }
                });
        }
        $("#alert_inner").width($("#alert_content").width()).height($("#alert_content").height());
        setTimeout("$(\'#alert_overlay\').fadeOut(\'fast\'); $(\'#alert_outer\').fadeOut(\'fast\');", 3000);
}

function showAccept(msg)
{
        showAlertWindow(msg, 'accept');
}

function showMessage(msg)
{
        showAlertWindow(msg, 'message');
}

function showError(msg)
{
        showAlertWindow(msg, 'error');
}

function is_array(input)
{
        return typeof(input) == 'object' && (input instanceof Array);
}

function in_array(what, where)
{
        var a = false;
        for(var i = 0; i < where.length; i++)
        {
                if(what == where[i])
                {
                        a = true;
                        break;
        }
        }
        return a;
}



$(document).ready(function(){
        // Zoom picture by fancybox
/*
        $("a[auZoom=true]").fancybox({
                overlayShow: true,
        overlayOpacity: 0.3
        });
*/

        // show tips via betterTips
        $('.tTip').betterTooltip({speed: 150, delay: 300});     
});



jQuery.fn.fadeToggle = function(speed, easing, callback) {
    return this.animate({opacity: 'toggle'}, speed, easing, callback);
};


function sleep(msec)
{
        var now = new Date();
        var exitTime = now.getTime() + msec;
        while(true)
        {
                now = new Date();
                if(now.getTime() > exitTime) return;
        }
}