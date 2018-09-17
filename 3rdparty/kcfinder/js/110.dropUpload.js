/** This file is part of KCFinder project
  *
  *      @desc Upload files using drag and drop
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  */

_.initDropUpload = function() {

    if (!_.access.files.upload)
        return;

    var files = $('#files'),
        folders = $('#folders').find('div.folder > a'),
        i, dlg, filesSize, uploaded, errors,

    precheck = function(e) {
        filesSize = uploaded = 0; errors = [];
        var fs = e.dataTransfer.files;
        for (i = 0; i < fs.length; i++)
            filesSize += fs[i].size;

        dlg = $('<div><div class="info count">&nbsp;</div><div class="bar count"></div><div class="info size">&nbsp;</div><div class="bar size"></div><div class="info errors">&nbsp;</div></div>');

        dlg.find('.bar.count').progressbar({max: fs.length, value: 0});
        dlg.find('.bar.size').progressbar({max: filesSize, value: 0});
        dlg.find('.info').css('padding', "5px 0").first().css('paddingTop', 0);
        dlg.find('.info').last().css('paddingBottom', 0);

        dlg = _.dialog(_.label("Uploading files"), dlg, {
            closeOnEscape: false,
            buttons: []
        });

        dlg.parent().css('paddingBottom', 0).find('.ui-dialog-titlebar button').css('visibility', 'hidden').get(0).disabled = true;

        return true;
    },

    localOptions = {
        param: "upload[]",
        maxFilesize: _.dropUploadMaxFilesize,

        begin: function(xhr, currentFile, count) {

            dlg.find('.info.count').html(_.label("Uploading file {current} of {count}", {
                current: currentFile,
                count: count
            }));

            dlg.find('.info.size').html(_.label("Uploaded {uploaded} of {total}", {
                uploaded: _.humanSize(uploaded),
                total: _.humanSize(filesSize)
            }));

            dlg.find('.info.errors').html(_.label("Errors:") + " " + errors.length);
            dlg.find('.bar.count').progressbar({value: currentFile});
            dlg.find('.bar.size').progressbar({value: uploaded});
        },

        success: function(xhr, currentFile, count) {
            uploaded += xhr.file.size;
            var response = xhr.responseText;
            if (response.substr(0, 1) != "/")
                errors.push($.$.htmlData(response));
        },

        error: function(xhr, currentFile, count) {
            uploaded += xhr.file.size;
            errors.push($.$.htmlData(xhr.file.name + ": " + _.label("Failed to upload {filename}!", {
                filename: xhr.file.name
            })));
        },

        abort: function(xhr, currentFile, filesCount) {
            uploaded += xhr.file.size;
            errors.push($.$.htmlData(xhr.file.name + ": " + _.label("Failed to upload {filename}!", {
                filename: xhr.file.name
            })));
        },

        filesizeCallback: function(xhr, currentFile, filesCount) {
            uploaded += xhr.file.size;
            errors.push($.$.htmlData(xhr.file.name + ": " + _.label("The uploaded file exceeds {size} bytes.", {
                size: _.dropUploadMaxFilesize
            })));
        },

        finish: function() {
            _.refresh();
            dlg.find('.bar.size').progressbar({value: uploaded});
            dlg.find('.info.size').html(_.label("Uploaded: {uploaded} of {total}", {
                uploaded: _.humanSize(uploaded),
                total: _.humanSize(filesSize)
            }));
            dlg.find('.info.errors').html(_.label("Errors:") + " " + errors.length);
            var err = errors;
            setTimeout(function() {
                dlg.dialog('destroy').detach();
                if (err.length)
                    _.alert(err.join('<br />'));
            }, 500);
        }
    },

    remoteOptions = {
        ajax: {
            success: function(data) {
                 _.refresh();
                if (data.error) {
                    _.alert(data.error)
                    return;
                }
            },
            error: function() {
                 _.refresh();
                 _.alert(_.label("Unknown error."));
            },
            abort: function() {
                 _.refresh();
            }
        }
    },

    url = "&dir=" + encodeURIComponent(_.dir);

    files.shDropUpload($.extend(localOptions, {
        url: _.getURL('upload') + url,
        precheck: function(e) {
            if (!$('#folders span.current').first().parent().data('writable')) {
                _.alert(_.label("Cannot write to upload folder."));
                return false;
            }
            return precheck(e);
        }
    }), $.extend(true, remoteOptions, {
        ajax: {
            url: _.getURL('dragUrl') + url
        }
    }));

    folders.each(function() {
        var folder = this,
            url = "&dir=" + encodeURIComponent($(folder).data('path'));
        $(folder).shDropUpload($.extend(localOptions, {
            url: _.getURL('upload') + url,
            precheck: function(e) {
                if (!$(folder).data('writable')) {
                    _.alert(_.label("Cannot write to upload folder."));
                    return false;
                }
                return precheck(e);
            }
        }), $.extend(true, remoteOptions, {
            ajax: {
                url: _.getURL('dragUrl') + url
            }
        }));
    });
};
