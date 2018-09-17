/*
 * jQuery shDropUpload v1.2
 * http://jquery.sunhater.com/shDropUpload
 * 2014-08-12
 *
 * Copyright (c) 2014 Pavel Tzonkov <sunhater@sunhater.com>
 * Dual licensed under the MIT and GPL licenses.
 */

(function($) {

  /** @param localOptions    Options about local files drag & drop
    * @param remoteOptions   Options about HTML objects drag & drop */

      $.fn.shDropUpload = function(localOptions, remoteOptions) {

        // Compatibility check
        if ((typeof XMLHttpRequest == "undefined") ||
            (typeof document.addEventListener == "undefined") ||
            (typeof File == "undefined") ||
            (typeof FileReader  == "undefined")
        )
            return;

        // Default options about local files drag & drop
        var lo = {

            // URL to upload handler script
            url: "",

            // File field name
            param: "upload",

            // Maximum filesize in bytes. If a dragged file is too big, the browser crashes
            maxFilesize: 10485760,

            // Called before all uploads. Useful for implementing some checks before uploads begins
            // If it returns false, the uploading will be canceled.
            precheck: function(evt) {
                console.log("shDropUpload: Upload process started");
                return true;
            },

            // Called when an upload begins
            begin: function(xhr, currentFile, filesCount) {
                console.log("shDropUpload:     Uploading file " + currentFile + " of " + filesCount + " (" + xhr.file.name + ")");
            },

            // Called after successful upload request
            success: function(xhr, currentFile, filesCount) {
                console.log("shDropUpload:     Upload success (" + xhr.file.name + ")");
            },

            // Called when an upload request fails
            error: function(xhr, currentFile, filesCount) {
                console.log("shDropUpload:     Upload request failed (" + xhr.file.name + ")");
            },

            // Called when an upload request is aborted
            abort: function(xhr, currentFile, filesCount) {
                console.log("shDropUpload:     Upload request aborted (" + xhr.file.name + ")");
            },

            // Called when a file exceeds the maxFilesize option
            filesizeCallback: function(xhr, currentFile, filesCount) {
                console.log("shDropUpload:     File is too big (" + xhr.file.name + ")");
            },

            // Called when all files are proceeded
            finish: function() {
                console.log("shDropUpload: Upload process finished");
            }
        },

        // Default options about HTML objects drag & drop
        ro = {

            // If a selection is dropped you could to fetch multiple URLs from selected HTML
            // You can define the selectors URLs will be fetched from. If you want only images
            // leave 'img[src]' only
            selectors: 'img[src]',
            //selectors: 'img[src], a[href], script[src], link[href]',

            // Check URLs for uniqueness
            unique: true,

            // Ajax options
            ajax: {
                url: "",
                type: "post",
                dataType: "json",
                data: {
                    url: "{url}",  // {url} marks the URL from dragged object
                    type: "{type}" // {type} marks the tag type ("a" or "img")
                },
                success: function(response) {
                    console.log("shDropUpload: URL has been passed to the server.");
                },
                error: function() {
                    console.log("shDropUpload: Request failed!");
                }
            }
        },

        utf8encode = function(string) {
            string = string.replace(/\r\n/g, "\n");
            var c, utftext = "";

            for (var n = 0; n < string.length; n++) {

                c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                } else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                } else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }
            }

            return utftext;
        };

        $.extend(true, ro, remoteOptions);
        $.extend(true, lo, localOptions);

        if (!XMLHttpRequest.prototype.sendAsBinary) {
            XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
                var ords = Array.prototype.map.call(datastr, function(x) {
                        return x.charCodeAt(0) & 0xff;
                    }),
                    ui8a = new Uint8Array(ords);
                this.send(ui8a);
            }
        }

        $(this).each(function() {
            var t = this,
                uploadQueue = [],
                uploadInProgress = false,
                filesCount = 0,
                boundary = "------multipartdropuploadboundary" + new Date().getTime(),
                currentFile,

            dragOver = function(e) {
                if (e.preventDefault) e.preventDefault();
                $(t).addClass('drag');
                return false;
            },

            dragEnter = function(e) {
                if (e.preventDefault) e.preventDefault();
                return false;
            },

            dragLeave = function(e) {
                if (e.preventDefault) e.preventDefault();
                $(t).removeClass('drag');
                return false;
            },

            drop = function(e) {
                if (e.preventDefault) e.preventDefault();
                if (e.stopPropagation) e.stopPropagation();
                $(t).removeClass('drag');

                try {
                    var el = e.dataTransfer.getData('text/html');
                } catch (e) {
                    var el = false;
                }

                // Remote drop
                if (el) {

                    if (!remoteOptions)
                        return false;

                    el = '<div>' + el.toString() + '</div>';
                    var urls = [], types = [];

                    var selectors = $.isArray(ro.selectors)
                        ? ro.selectors
                        : ro.selectors.split(/\s*,\s*/g);

                    $.each(selectors, function(i, selector) {
                        if (!/^[a-z0-9]+\[[a-z]+\]$/gi.test(selector))
                            return true;
                        var type = selector.split('[')[0],
                            attr = selector.split('[')[1].split(']')[0];
                        $(el).find(selector).each(function() {
                            var url = $(this).attr(attr);
                            if (ro.unique)
                                for (var i = 0; i < urls.length; i++)
                                    if ((urls[i] == url) && (types[i] == type))
                                        return true;
                            urls.push(url);
                            types.push(type);
                        });
                    });

                    if (!urls.length)
                        return false;

                    if (urls.length == 1) {
                        urls = urls[0];
                        types = types[0];
                    }

                    var opts = $.extend(true, {}, ro.ajax);
                    if (opts.data) {
                        $.each(opts.data, function(i, j) {
                            if (j == "{url}")
                                opts.data[i] = urls;
                            if (j == "{type}")
                                opts.data[i] = types;
                        });
                    }
                    $.ajax(opts);

                // Local drop
                } else {
                    if (!localOptions)
                        return false;

                    filesCount += e.dataTransfer.files.length;

                    if (!filesCount || !lo.precheck(e))
                        return false;

                    for (var i = 0; i < filesCount; i++) {
                        var file = e.dataTransfer.files[i];
                        uploadQueue.push(file);
                    }

                    uploadNext();
                }

                return false;
            },

            uploadNext = function() {
                if (uploadInProgress)
                    return false;

                if (uploadQueue && uploadQueue.length) {

                    var file = uploadQueue.shift(),
                        currentNum = filesCount - uploadQueue.length,
                        reader = new FileReader(),
                        ie = (typeof reader.readAsBinaryString == "undefined");

                    currentFile = reader.file = file;

                    reader.onerror = function(evt) {
                        evt.file = file;
                        lo.error(evt, currentNum, filesCount);
                        uploadNext();
                    };

                    reader.onload = function(evt) {
                        uploadInProgress = true;

                        var xhr = new XMLHttpRequest(),
                            postbody = '--' + boundary + '\r\nContent-Disposition: form-data; name="' + lo.param + '"';

                        xhr.file = evt.target.file;

                        lo.begin(xhr, currentNum, filesCount);

                        if (lo.maxFilesize && (xhr.file.size > lo.maxFilesize)) {
                            uploadInProgress = false;
                            lo.filesizeCallback(xhr, currentNum, filesCount);
                            uploadNext();
                            return;
                        }

                        if (ie) {
                            var binary = "",
                                bytes = new Uint8Array(evt.target.result);

                            for (var i = 0; i < bytes.byteLength; i++)
                                binary += String.fromCharCode(bytes[i]);
                        }

                        if (xhr.file.name)
                            postbody += '; filename="' + utf8encode(xhr.file.name) + '"';
                        postbody += '\r\n';
                        if (xhr.file.size)
                            postbody += "Content-Length: " + xhr.file.size + "\r\n";
                        postbody += "Content-Type: " + xhr.file.type + "\r\n\r\n" + (ie ? binary : evt.target.result) + "\r\n--" + boundary + "--\r\n";

                        xhr.open('post', lo.url, true);
                        xhr.setRequestHeader('Content-Type', "multipart/form-data; boundary=" + boundary);

                        xhr.onload = function() {
                            uploadInProgress = false;
                            lo.success(xhr, currentNum, filesCount);
                            uploadNext();
                        };

                        xhr.onerror = function() {
                            uploadInProgress = false;
                            lo.error(xhr, currentNum, filesCount);
                            uploadNext();
                        };

                        xhr.onabort = function() {
                            uploadInProgress = false;
                            lo.abort(xhr, currentNum, filesCount);
                            uploadNext();
                        };

                        xhr.sendAsBinary(postbody);
                    };

                    if (ie)
                        reader.readAsArrayBuffer(file);
                    else
                        reader.readAsBinaryString(file);

                } else {
                    filesCount = 0;
                    var loop = setInterval(function() {
                        if (uploadInProgress) return;
                        boundary = "------multipartdropuploadboundary" + new Date().getTime();
                        uploadQueue = [];
                        clearInterval(loop);
                        lo.finish();
                    }, 333);
                }
            };

            if (!$(t).data('shdu'))
                $(t).data('shdu', {
                    dragover: dragOver,
                    dragenter: dragEnter,
                    dragLeave: dragLeave,
                    drop: drop
                });

            var bind = function(event, callback) {
                t.removeEventListener(event, $(t).data('shdu')[event], false);
                var data = $(t).data('shdu'),
                    newData = {};
                newData[event] = callback;
                $.extend(data, newData);
                $(t).data('shdu', data);
                t.addEventListener(event, callback, false);
            };

            bind('dragover', dragOver);
            bind('dragenter', dragEnter);
            bind('dragleave', dragLeave);
            bind('drop', drop);
        });
    }

})(jQuery);
