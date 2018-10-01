/*! jQuery adapter for KCFinder
 *  http://kcfinder.sunhater.com
 *  Pavel Tzonkov <sunhater@sunhater.com>
 */
/*  BASE USAGE:
 *     <div id="filemanager" style="width:700px;height:400px"></div>
 *     <script>
 *         $('#filemanager').kcfinder();
 *     </script>
 */

(function($) {
    var defaultURL = "browse.php"; // Define here your default URL to KCFinder

    $.fn.kcfinder = function(options) {

        var url, i,
            t = $(this).get(0),

            // Default options
            o = {
                url: defaultURL,
                lang: "",
                theme: "",
                type: "",
                dir: "",
                callback: false,
                callbackMultiple: false
            },
            ifr = $('<iframe></iframe>'),

            // GET parameters to parse URL
            parse = ['lang', 'theme', 'type', 'dir'];

        $.extend(true, o, options);

        // Parse URL
        url = o.url;
        url += (url.indexOf('?') === -1) ? '?' : "&";
        for (i in parse) {
            i = parse[i];
            if (o[i].length)
                url += i + "=" + encodeURIComponent(o[i]) + "&";
        }
        url = url.substr(0, url.length - 1);

        // Iframe setup
        ifr.css({
            margin: 0,
            padding: 0,
            width: $(t).innerWidth(),
            height: $(t).innerHeight(),
            border: "none"
        }).attr({
            src: url
        });

        $(t).html(ifr);

        // Callbacks
        if ($.isFunction(o.callback) || $.isFunction(o.callbackMultiple)) {
            if (!window.KCFinder)
                window.KCFinder = {};

            // Single file callback
            if ($.isFunction(o.callback))
                window.KCFinder.callBack = o.callback;
            else if (window.KCFinder && window.KCFinder.callback)
                delete window.KCFinder.callback;

            // Multiple files callback
            if ($.isFunction(o.callbackMultiple))
                window.KCFinder.callBackMultiple = o.callbackMultiple;
            else if (window.KCFinder && window.KCFinder.callbackMultiple)
                delete window.KCFinder.callbackMultiple;

        // No callbacks
        } else if (window.KCFinder)
            delete window.KCFinder;
    }

})(jQuery);