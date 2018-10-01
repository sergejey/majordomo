/** This file is part of KCFinder project
  *
  *      @desc Image viewer
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  */

_.viewImage = function(data) {

    var ts = new Date().getTime(),
        dlg = false,
        images = [],
        min_h = 100,
        w = $(window),
        min_w, dd, dv, dh,

    showImage = function(data) {
        _.lock = true;

        var url = $.$.escapeDirs(_.uploadURL + "/" + _.dir + "/" + data.name) + "?ts=" + ts,
            img = new Image(),
            i = $(img),

        onImgLoad = function() {
            _.lock = false;

            $('#files .file').each(function() {
                if ($(this).data('name') == data.name) {
                    _.ssImage = this;
                    return false;
                }
            });

            i.hide().appendTo('body');

            var w_w = w.width(),
                w_h = w.height(),
                o_w = i.width(),
                o_h = i.height(),
                i_w = o_w,
                i_h = o_h,
                openDlg = false,
                t = $('<div class="img"></div>'),

            goTo = function(i) {
                if (!_.lock) {
                    var nimg = images[i];
                    _.currImg = i;
                    showImage(nimg);
                }
            },

            nextFunc = function() {
                goTo((_.currImg >= images.length - 1) ? 0 : (_.currImg + 1));
            },

            prevFunc = function() {
                goTo((_.currImg ? _.currImg : images.length) - 1);
            },

            selectFunc = function(e) {
                if (_.ssImage)
                    _.selectFile($(_.ssImage), e);
                dlg.dialog('destroy').detach();
            };

            i.detach().appendTo(t);

            if (!dlg) {
                openDlg = true;

                var closeFunc = function() {
                    dlg.dialog('destroy').detach();
                },

                focusFunc = function() {
                    setTimeout(function() {
                        dlg.find('input').get(0).focus();
                    }, 100);
                };

                dlg = _.dialog(".", "", {
                    draggable: false,
                    nopadding: true,
                    close: closeFunc,
                    show: false,
                    hide: false,
                    buttons: [
                        {
                            text: _.label("Previous"),
                            icons: {primary: "ui-icon-triangle-1-w"},
                            click: prevFunc

                        }, {
                            text: _.label("Next"),
                            icons: {secondary: "ui-icon-triangle-1-e"},
                            click: nextFunc

                        }, {
                            text: _.label("Select"),
                            icons: {primary: "ui-icon-check"},
                            click: selectFunc

                        }, {
                            text: _.label("Close"),
                            icons: {primary: "ui-icon-closethick"},
                            click: closeFunc
                        }
                    ]
                });

                dlg.click(nextFunc).css({overflow: "hidden"}).parent().css({width: "auto", height: "auto"});

                dd = dlg.parent().click(focusFunc).rightClick(focusFunc).disableTextSelect().addClass('kcfImageViewer');
                dv = dd.find('.ui-dialog-titlebar').outerHeight() + dd.find('.ui-dialog-buttonpane').outerHeight() + dd.outerVSpace('b');
                dh = dd.outerHSpace('b');
                min_w = dd.outerWidth() - dh;
            }

            var max_w = w_w - dh,
                max_h = w_h - dv + 1,
                top = 0,
                left = 0,
                width = o_w,
                height = o_h;

            // Too big
            if ((o_w > max_w) || (o_h > max_h)) {

                if ((max_h / max_w) < (o_h / o_w)) {
                    height = max_h;
                    width = (o_w * height) / o_h;

                } else {
                    width = max_w;
                    height = (o_h * width) / o_w;
                }

                i_w = width;
                i_h = height;

            // Too small
            } else if ((o_w < min_w) || (o_h < min_h)) {
                width = (o_w < min_w) ? min_w : o_w;
                height = (o_h < min_h) ? min_h : o_h;
                left = (o_w < min_w) ? (min_w - o_w) / 2 : 0;
                top = (o_h < min_h) ? (min_h - o_h) / 2 : 0;
            }

            var show = function() {
                dlg.animate({width: width, height: height}, 150);
                dlg.parent().animate({top: (w_h - height - dv) / 2, left: (w_w - width - dh) / 2}, 150, function() {
                    dlg.html(t.get(0)).append('<input style="width:1px;height:1px;position:fixed;top:-1000px;left:-1000px" type="text" />');
                    dlg.find('input').keydown(function(e) {
                        if (!_.lock) {
                            if (e.metaKey || e.ctrlKey || e.altKey || e.shiftKey)
                                return;
                            var kc = e.keyCode;
                            if ((kc == 37)) prevFunc();
                            if ((kc == 39)) nextFunc();
                            if ((kc == 13) || (kc == 32)) selectFunc(e);
                        }
                    }).get(0).focus();
                    i.css({padding: top + "px 0 0 " + left + "px", width: i_w, height: i_h}).show();
                    dlg.children().first().css({width: width, height: height, display: "none"}).fadeIn(150, function() {
                        loadingStop();
                        var title = data.name + " (" + o_w + " x " + o_h + ")";
                        dlg.prev().find('.ui-dialog-title').css({width:width - dlg.prev().find('.ui-dialog-titlebar-close').outerWidth() - 20}).text(title).attr({title: title}).css({cursor: "default"});
                    });
                });
            }

            if (openDlg)
                show();
            else
                dlg.children().first().fadeOut(150, show);
        },

        loadingStart = function() {
            if (dlg)
                dlg.prev().addClass("loading").find('.ui-dialog-title').text(_.label("Loading image...")).css({width: "auto"});
            else
                $('#loading').text(_.label("Loading image...")).show();
        },

        loadingStop = function() {
            if (dlg)
                dlg.prev().removeClass("loading");
            $('#loading').hide();
        };

        loadingStart();
        img.src = url;

        if (img.complete)
            onImgLoad();
        else {
            img.onload = onImgLoad;
            img.onerror = function() {
                _.lock = false;
                loadingStop();
                _.alert(_.label("Unknown error."));
                _.refresh();
            };
        }
    };

    $.each(_.files, function(i, file) {
        i = images.length;
        if (file.thumb || file.smallThumb)
            images[i] = file;
        if (file.name == data.name)
            _.currImg = i;
    });

    showImage(data);
    return false;
};
