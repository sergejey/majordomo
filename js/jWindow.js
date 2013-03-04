/**
 * jWindow: jQuery Windows Engine 2
 * Copyright (c) 2011 Dominik Marczuk
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * The name of Dominik Marczuk may not be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY DOMINIK MARCZUK "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL DOMINIK MARCZUK BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 *  based on jQuery Windows Engine Plugin
 *
 *  Copyright(c)  Hernan Amiune (hernan.amiune.com)
 *  Licensed under MIT license:
 *  http://www.opensource.org/licenses/mit-license.php
 */

(function ($) {
        /**
         * The zIndex value for window arranging
         */
        var zIndex = 100;
        
        /**
         * The array containing all of the defined windows
         */
        var jWindows = [];

        var focusList = [];

        /**
         * A counter for tabs IDs
         */
        var tabCounter = 0;
        
        /**
         * The jWindow object is what controls the entire widget.
         * @param params options an object containing the options values.
         */
        function jWindow (params) {
                // this, for faster reference :)
                var $jWindow = this;

                // user-assignable options
                var options = {
                        id:             "",
                        title:          "",
                        parentElement:  'body',
                        width:          300,
                        height:         200,
                        posx:           50,
                        posy:           50,
                        fixed:          true,
                        marginTop:      10,
                        marginRight:    10,
                        marginBottom:   10,
                        marginLeft:     10,
                        onDragStart:    null,
                        onDragEnd:      null,
                        onResizeStart:  null,
                        onResizeEnd:    null,
                        onUpdate:       null,
                        onClose:        null,
                        onMaximise:     null,
                        onRestore:      null,
                        onMinimise:     null,
                        statusBar:      true,
                        refreshButton:  false,
                        minimiseButton: true,
                        maximiseButton: true,
                        closeButton:    true,
                        draggable:      true,
                        resizeable:     true,
                        type:           "iframe",
                        url:            "",
                        modal:          false,
                        tabs:           false
                };
                $.extend(options,params);

                /**
                 * Retrieve an option value
                 * @param param property's name to retrieve
                 * @return the value of the selected option or undefined if ther is no such option
                 */
                this.get = function (param) {
                        return options[param];
                };
                
                // different states of the window
                var state = {
                        minimised: false,
                        maximised: false,
                        hidden: true,
                        focus: false
                };
                
                // create the DOM structure for the jWindow
                var domNodes = {
                        parentElement:   $(options.parentElement),
                        modalBackground: $('<div class="jWindow-modalBackground"></div>').css({zIndex: 10000, position: 'fixed', top: '0', left: '0', width: '100%', height: '100%'}),
                        container:       $('<div id="' + options.id + '" class="jWindow"></div>').css({position: (options.fixed) ? "fixed" : "absolute", width: options.width+'px', top: options.posy+'px', left: options.posx+'px', overflow: 'hidden'}),
                        titleBar:        $('<div class="jWindow-titleBar"></div>').css({position: 'relative', overflow: 'hidden'}),
                        title:           $('<div class="jWindow-title"></div>').text(options.title),
                        refreshButton:   $('<div class="jWindow-button jWindow-refreshButton"></div>'),
                        minimiseButton:  $('<div class="jWindow-button jWindow-minimiseButton"></div>'),
                        maximiseButton:  $('<div class="jWindow-button jWindow-maximiseButton"></div>'),
                        closeButton:     $('<div class="jWindow-button jWindow-closeButton"></div>'),
                        tabsBar:         $('<div class="jWindow-tabsBar"></div>').css({position: 'relative', overflow: 'hidden'}),
                        tabs:            $('<ul class="jWindow-tabs"></ul>'),
                        wrapper:         $('<div class="jWindow-wrapper"></div>').css({overflow: 'hidden'}),
                        content:         $('<div class="jWindow-content"></div>').css({height: options.height + 'px'}),
                        statusBar:       $('<div class="jWindow-statusBar"></div>').css({position: 'relative'}),
                        resizeIcon:      $('<div class="jWindow-resizeIcon"></div>').css({position: 'absolute', bottom: '0', right: '0'}),
                        iframeCover:     $('<div class="jWindow-iframeCover"></div>').css({position: 'absolute', width: '100%', height: '100%', zIndex: 10002}),
                        output:          null
                };
                
                domNodes.container.appendTo(domNodes.modalBackground);
                domNodes.titleBar.appendTo(domNodes.container);
                domNodes.tabs.appendTo(domNodes.tabsBar);
                if (options.tabs) domNodes.tabsBar.appendTo(domNodes.wrapper);
                domNodes.content.appendTo(domNodes.wrapper);
                if (options.statusBar) domNodes.statusBar.appendTo(domNodes.wrapper);
                domNodes.wrapper.appendTo(domNodes.container);
                domNodes.title.appendTo(domNodes.titleBar);
                if (options.refreshButton) domNodes.refreshButton.appendTo(domNodes.titleBar);
                if (options.minimiseButton) domNodes.minimiseButton.appendTo(domNodes.titleBar);
                if (options.maximiseButton) domNodes.maximiseButton.appendTo(domNodes.titleBar);
                if (options.closeButton) domNodes.closeButton.appendTo(domNodes.titleBar);
                
                if (options.modal) {
                        domNodes.output = domNodes.modalBackground;
                        domNodes.container.css('zIndex',10001);
                } else {
                        domNodes.output = domNodes.container.css('zIndex',++zIndex);
                }
                domNodes.output.css({opacity: '0'});
                
                // ----------------------------------
                // BIND EVENTS TO DIFFERENT DOM NODES
                // ----------------------------------
                
                // click on anything
                $.each(domNodes,function () {
                        if (this != domNodes.parentElement) {
                                $(this).on({
                                        mousedown: function () {
                                                $jWindow.focus();
                                        }
                                });
                        }
                });
                
                // click on the close button
                domNodes.closeButton.on({
                        click: function () {
                                $jWindow.hide(options.onClose);
                        }
                });
                
                // click on the minimise button
                domNodes.minimiseButton.on({
                        click: function () {
                                if (domNodes.container.hasClass('minimised')) {
                                        $jWindow.restore({
                                                type: 'min',
                                                complete: options.onRestore
                                        });
                                } else {
                                        $jWindow.minimise(options.onMinimise);
                                }
                        }
                });
                
                // click on the maximise button
                domNodes.maximiseButton.on({
                        click: function (event) {
                                if (domNodes.container.hasClass('maximised')) {
                                        $jWindow.restore({
                                                type: 'max',
                                                complete: options.onRestore
                                        });
                                } else {
                                        $jWindow.maximise(options.onMaximise);
                                }
                        }
                });
                
                // click on the refresh button
                domNodes.refreshButton.on({
                        click: function () {
                                if (options.type != 'custom') {
                                        $jWindow.refresh();
                                }
                        }
                });
                
                // double click on the title bar to maximise, mousedown for dragging
                domNodes.titleBar.on({
                        dblclick: function () {
                                if (domNodes.container.hasClass('maximised')) {
                                        $jWindow.restore({
                                                type: 'max',
                                                complete: options.onRestore
                                        });
                                } else {
                                        $jWindow.maximise(options.onMaximise);
                                }
                        }
                });
                
                // --------------
                // SPECIAl EVENTS
                // --------------

                // set focus on mouse down:
                domNodes.content.on({
                        jWindowCover: function () {
                                domNodes.iframeCover.prependTo(domNodes.content);
                        },
                        jWindowUncover: function () {
                                domNodes.iframeCover.detach();
                        }
                });
                
                // resize the window (using a custom event) -> adjust the windows:
                $(window).resize(function () {
                        if(this.resizeTO) clearTimeout(this.resizeTO);
                        this.resizeTO = setTimeout(function () {
                                $(this).trigger('resizeEnd');
                        }, 1000);
                }).on({
                        resizeEnd: function () {
                                for (var i = 0; i < jWindows.length; ++i) {
                                        jWindows[i].set({}); // will trigger fitting in viewport
                                }
                        }
                });
                
                // -----------------------
                // JWINDOW PRIVATE METHODS
                // -----------------------
                
                /**
                 * Perform a cleanup after dragging or resizing a window or the viewport - adjust the position and size of the window to fit the viewport.
                 * @return jWindow Provides a fluent interface
                 */
                var fitInViewport = function () {
                        // calculate margins
                        var marginX = domNodes.container.outerWidth() - options.width;
                        var marginY = domNodes.container.outerHeight() - options.height;
                        
                        // step 1: check if the size isn't larger than the viewport:
                        if (domNodes.container.outerWidth() > $(window).width() - options.marginLeft - options.marginRight) {
                                options.width = $(window).width() - options.marginLeft - options.marginRight - marginX;
                        }
                        if (domNodes.container.outerHeight() > $(window).height() - options.marginTop - options.marginBottom) {
                                options.height = $(window).height() - options.marginTop - options.marginBottom - marginY;
                        }
                        
                        // step 2: check if the size isn't too small:
                        if (options.width < 50) {
                                options.width = 50;
                        }
                        if (options.height < 0) {
                                options.height = 0;
                        }
                        
                        // step 3: check if the window doesn't go outside the right/bottom edge of the viewport:
                        if (options.posx + domNodes.container.outerWidth() > $(window).width() - options.marginRight) {
                                options.posx = $(window).width() - options.marginRight - options.width - marginX;
                        }
                        if (options.posy + domNodes.container.outerHeight() > $(window).height() - options.marginBottom) {
                                options.posy = $(window).height() - options.marginBottom - options.height - marginY;
                        }
                        
                        // step 4: make sure the window doesn't go outside the left/top edge of the viewport:
                        if (options.posx < options.marginLeft) {
                                options.posx = options.marginLeft;
                        }
                        if (options.posy < options.marginTop) {
                                options.posy = options.marginTop;
                        }
                        
                        // adjust the window:
                        domNodes.container.animate({top: options.posy + 'px', left: options.posx + 'px', width: options.width + 'px'}, 350, 'swing');
                        domNodes.content.animate({height: options.height + 'px'}, 350, 'swing');
                };
                
                /**
                 * Sets the draggable option on a window, and attaches or detaches a onmousedown event associated with it.
                 * @param draggable whether to make the window draggable or not draggable (optional parametre; defaults to true)
                 * @return jWindow Provides a fluent interface 
                 */
                var setDraggable = function (draggable) {
                        if (typeof draggable == 'undefined' || draggable == undefined) draggable = true;
                        options.draggable = !!draggable; // double negation to ensure the parametre is a boolean
                        
                        var startX = 0, startY = 0;
                        var startPosX = 0, startPosY = 0;

                        if (options.draggable && !state.maximised) {
                                domNodes.titleBar.css('cursor','move').on({
                                        mousedown: function (event) {
                                                // get initial mouse position
                                                startX = event.screenX;
                                                startY = event.screenY;
                                                startPosX = options.posx;
                                                startPosY = options.posy;
                                                
                                                domNodes.content.trigger('jWindowCover');

                                                $(document).on({
                                                        mousemove: function (event) {
                                                                if (options.draggable) {
                                                                        options.posx = startPosX + event.screenX - startX;
                                                                        options.posy = startPosY + event.screenY - startY;

                                                                        domNodes.container.css({
                                                                                'top': options.posy + 'px',
                                                                                'left': options.posx + 'px'
                                                                        });
                                                                }
                                                        },
                                                        mouseup: function () {
                                                                // unbind the events
                                                                $(document).off('mousemove mouseup');
                                                                domNodes.content.trigger('jWindowUncover');

                                                                fitInViewport();

                                                                // launch the callback
                                                                if (typeof options.onDragEnd == 'function') {
                                                                        options.onDragEnd();
                                                                }
                                                        }
                                                });

                                                // drag start callback
                                                if (typeof options.onDragStart == 'function') {
                                                        options.onDragStart();
                                                }

                                                // disable selection, so that no text is selected while dragging
                                                domNodes.titleBar[0].onselectstart = function () {return false;}; //IE
                                                return false; //other browsers
                                        }
                                });
                        } else {
                                domNodes.titleBar.css('cursor','auto').off('mousedown');
                                // re-enable selection in IE
                                domNodes.titleBar[0].onselectstart = null;
                        }

                        return $jWindow;
                };

                // make the window draggable (or not)
                setDraggable(options.draggable);
                
                /**
                 * Sets the resizeable option on a window, and attaches or detaches the events associated with it.
                 * @param resizeable whether to make the window resizeable or static-sized (optional parametre; defaults to true)
                 * @return jWindow Provides a fluent interface
                 */
                var setResizeable = function (resizeable) {
                        if (typeof resizeable == 'undefined' || resizeable == undefined) resizeable = true;
                        options.resizeable = !!resizeable; // double negation to ensure the parametre is a boolean
                        
                        var startX = 0, startY = 0;
                        var startW = 0, startH = 0;

                        if (options.resizeable && !state.maximised && !state.minimised) {
                                domNodes.resizeIcon.appendTo(domNodes.statusBar);
                                domNodes.resizeIcon.css('cursor','se-resize').on({
                                        mousedown: function (event) {
                                                // get initial mouse position and sizes
                                                startX = event.screenX;
                                                startY = event.screenY;
                                                startW = domNodes.container.width();
                                                startH = domNodes.content.height();
                                                
                                                domNodes.content.trigger('jWindowCover');
                                                
                                                $(document).on({
                                                        mousemove: function (event) {
                                                                if (options.resizeable) {
                                                                        options.width = startW + event.screenX - startX;
                                                                        options.height = startH + event.screenY - startY;

                                                                        domNodes.container.css({
                                                                                width: options.width + 'px'
                                                                        });
                                                                        domNodes.content.css({
                                                                                height: options.height + 'px'
                                                                        });
                                                                }
                                                        },
                                                        mouseup: function (event) {
                                                                // unbind the events
                                                                $(document).off('mousemove mouseup');
                                                                domNodes.content.trigger('jWindowUncover');

                                                                fitInViewport();

                                                                // launch the callback
                                                                if (typeof options.onResizeEnd == 'function') {
                                                                        options.onResizeEnd();
                                                                }
                                                        }
                                                });

                                                // drag start callback
                                                if (typeof options.onResizeStart == 'function') {
                                                        options.onResizeStart();
                                                }

                                                // disable selection, so that no text is selected while resizing
                                                domNodes.resizeIcon[0].onselectstart = function () {return false;}; //IE
                                                return false; //other browsers
                                        }
                                });
                        } else {
                                domNodes.resizeIcon.detach();
                                domNodes.resizeIcon.css('cursor','auto').off('mousedown');
                                // re-enable selection in IE
                                domNodes.resizeIcon[0].onselectstart = null;
                        }

                        return $jWindow;
                };
                
                // make the window resizeable (or not)
                setResizeable(options.resizeable);

                /**
                 * Bring the last focused window back to focus. Used after hiding a window.
                 * @return jWindow Provides a fluent interface
                 */
                var restoreFocus = function () {
                        var done = false;
                        do {
                                var i = focusList.pop();
                                console.log("popped: "+i);
                                console.log(focusList);
                                if (!jWindows[i].isHidden()) {
                                        jWindows[i].focus();
                                        done = true;
                                }
                        } while (!done && focusList.length > 0);
                        return $jWindow;
                };
                
                // ----------------------
                // JWINDOW PUBLIC METHODS
                // ----------------------
                
                /**
                 * Add the window widget to the DOM tree and fade it in
                 * @param params can be one of several things:<br>
                 *        a number - denotes the animation's duration (in milliseconds)<br>
                 *        a string - denotes the animation's easing<br>
                 *        a function - a complete callback to the animation<br>
                 *        an object - duration, easing and complete properties will be used
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.show = function (params) {
                        if (!state.hidden) return $jWindow;

                        var _options = {
                                duration: 350,
                                easing: 'linear',
                                complete: function () {}
                        };

                        switch (typeof params) {
                                case 'number':_options.duration = params;break;
                                case 'string':_options.easing = params;break;
                                case 'function':_options.complete = params;break;
                                case 'object':$.extend(_options, params);break;
                        }

                        domNodes.parentElement.append(domNodes.output.css({top: '+=15px'}));
                        domNodes.output.animate({opacity: '1', top: '-=15px'}, _options.duration, _options.easing, _options.complete);
                        state.hidden = false;
                        $jWindow.focus();

                        return $jWindow;
                };
                
                /**
                 * Fade the window widget out and detach it from the DOM tree
                 * @param params can be one of several things:<br>
                 *        a number - denotes the animation's duration (in milliseconds)<br>
                 *        a string - denotes the animation's easing<br>
                 *        a function - a complete callback to the animation<br>
                 *        an object - duration, easing and complete properties will be used
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.hide = function (params) {
                        if (state.hidden) return $jWindow;

                        var _options = {
                                duration: 350,
                                easing: 'linear',
                                complete: function () {}
                        };

                        switch (typeof params) {
                                case 'number':_options.duration = params;break;
                                case 'string':_options.easing = params;break;
                                case 'function':_options.complete = params;break;
                                case 'object':$.extend(_options, params);break;
                        }

                        domNodes.output.animate({top: '-=15px', opacity: '0'}, _options.duration, _options.easing, function () {
                                domNodes.output = domNodes.output.css({top: '+=15px'}).detach();
                                _options.complete();
                        });
                        state.hidden = true;
                        $jWindow.focus(false);
                        restoreFocus();

                        return $jWindow;
                };

                /**
                 * Check whether a window is hidden or not
                 * @return boolean
                 */
                $jWindow.isHidden = function () {
                        return state.hidden;
                };
                
                /**
                 * Minimise the window
                 * @param params can be one of several things:<br>
                 *        a number - denotes the animation's duration (in milliseconds)<br>
                 *        a string - denotes the animation's easing<br>
                 *        a function - a complete callback to the animation<br>
                 *        an object - duration, easing and complete properties will be used
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.minimise = function (params) {
                        if (state.minimised) return $jWindow;

                        var _options = {
                                duration: 350,
                                easing: 'linear',
                                complete: function () {}
                        };

                        switch (typeof params) {
                                case 'number':_options.duration = params;break;
                                case 'string':_options.easing = params;break;
                                case 'function':_options.complete = params;break;
                                case 'object':$.extend(_options, params);break;
                        }

                        domNodes.wrapper.slideUp(_options.duration, _options.easing, _options.complete);
                        domNodes.container.addClass('minimised');
                        state.minimised = true;
                        
                        setResizeable(options.resizeable);

                        return $jWindow;
                };
                
                /**
                 * Maximise the window
                 * @param params can be one of several things:<br>
                 *        a number - denotes the animation's duration (in milliseconds)<br>
                 *        a string - denotes the animation's easing<br>
                 *        a function - a complete callback to the animation<br>
                 *        an object - duration, easing and complete properties will be used
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.maximise = function (params) {
                        if (state.maximised) return $jWindow;

                        var _options = {
                                duration: 350,
                                easing: 'linear',
                                complete: function () {}
                        };

                        switch (typeof params) {
                                case 'number':_options.duration = params;break;
                                case 'string':_options.easing = params;break;
                                case 'function':_options.complete = params;break;
                                case 'object':$.extend(_options, params);break;
                        }

                        var w = $(window).width() - options.marginLeft - options.marginRight;
                        var h = $(window).height() - options.marginTop - options.marginBottom - (domNodes.container.outerHeight() - domNodes.content.height());

                        domNodes.container.animate({width: w + 'px', top: options.marginTop + 'px', left: options.marginLeft + 'px'}, _options.duration, _options.easing, _options.complete);
                        domNodes.content.animate({height: h + 'px'}, _options.duration, _options.easing);
                        domNodes.container.addClass('maximised');
                        state.maximised = true;
                        
                        setResizeable(options.resizeable);
                        setDraggable(options.draggable);

                        return $jWindow;
                };
                
                /**
                 * Restore the window from the minimised or maximised state
                 * @param params can be one of several things:<br>
                 *        a number - denotes the animation's duration (in milliseconds)<br>
                 *        a string - denotes the animation's easing<br>
                 *        a function - a complete callback to the animation<br>
                 *        an object - duration, easing, complete and type ('min', 'max' or 'both') properties will be used
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.restore = function (params) {
                        if (!state.minimised && !state.maximised) return $jWindow;

                        var _options = {
                                duration: 350,
                                easing: 'linear',
                                complete: function () {},
                                type: 'both'
                        };

                        switch (typeof params) {
                                case 'number':_options.duration = params;break;
                                case 'string':_options.easing = params;break;
                                case 'function':_options.complete = params;break;
                                case 'object':$.extend(_options, params);break;
                        }

                        if (domNodes.container.hasClass('minimised') && $.inArray(_options.type, ['min', 'both']) != -1) {
                                domNodes.wrapper.slideDown(_options.duration, _options.easing, _options.complete);
                                domNodes.container.removeClass('minimised');
                                state.minimised = false;
                        }
                        if (domNodes.container.hasClass('maximised') && $.inArray(_options.type, ['max', 'both']) != -1) {
                                domNodes.container.animate({width: options.width+'px', top: options.posy+'px', left: options.posx+'px'}, _options.duration, _options.easing, _options.complete);
                                domNodes.content.animate({height: options.height+'px'}, _options.duration, _options.easing);
                                domNodes.container.removeClass('maximised');
                                state.maximised = false;
                        }
                        
                        setResizeable(options.resizeable);
                        setDraggable(options.draggable);

                        return $jWindow;
                };
                
                /**
                 * Set focus on the window. Remove focus from all other windows.
                 * @param focus whether to add or remove focus from the window
                 * @return jWindow Provides a fluent interface 
                 */
                $jWindow.focus = function (focus) {
                        if (typeof focus == 'undefined' || focus == undefined) focus = true;
                        focus = !!focus; //make sure focus is a boolean
                        
                        // if the window's focus is already set correctly, do nothing
                        if (state.focus == focus) return $jWindow;
                        
                        if (focus) {
                                // blur all windows
                                for (var i = 0; i < jWindows.length; ++i) {
                                        if (jWindows[i].hasFocus()) {
                                                jWindows[i].focus(false);
                                                focusList.push(i);
                                                console.log(focusList);
                                        }
                                }
                                // focus the current window
                                domNodes.container.removeClass('blur').addClass('focus');
                                domNodes.content.trigger('jWindowUncover');
                                state.focus = true;
                                if (options.modal) {
                                        domNodes.container.css('zIndex','10001');
                                } else {
                                        domNodes.container.css('zIndex',++zIndex);
                                }
                        } else {
                                if (!options.modal) {
                                        domNodes.container.removeClass('focus').addClass('blur');
                                        domNodes.content.trigger('jWindowCover');
                                        
                                        state.focus = false;
                                }
                        }
                        return $jWindow;
                };

                /**
                 * Check the window's focus.
                 * @return boolean
                 */
                $jWindow.hasFocus = function () {
                        return state.focus;     
                };
                
                /**
                 * Update the content of a window.
                 * @param param In case of an iframe window, this parametre is optional. If specified, it will be treated as an URL that will be loaded in the iframe. If left empty, the iframe's content will just be loaded (if the URL option has been passed to the jWindow's constructor) or reloaded (if the iframe has been loaded previously).<br>
                 *              In an AJAX window, the parametre is optional. If specified, it will be used as the URL to load via AJAX. If not specified, the URL set in the jWindow's constructor will be used. If that is not present either, nothing will happen.<br>
                 *              In a custom content window, the parametre is the custom HTML that will be placed inside the window. The parametre is mandatory.<br>
                 *              In either case, passing a NULL value will clear the jWindow's content.
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.update = function (param) {
                        if (param === null) {
                                options.url = null;
                                domNodes.content.empty();
                        } else if (options.type == 'iframe') {
                                if (typeof param == 'string') {
                                        options.url = param;
                                        domNodes.content.html('<iframe src="' + options.url + '" />').find('iframe').css({border: '0', width: '100%', height: '100%'});
                                } else {
                                        var frame = domNodes.content.find('iframe');
                                        if (frame.length > 0) {
                                                if (options.url.length == 0) options.url = frame[0].src;
                                                else frame[0].src = options.url;
                                        } else {
                                                if (options.url.length > 0) {
                                                        domNodes.content.html('<iframe src="' + options.url + '" />').find('iframe').css({border: '0', width: '100%', height: '100%'});
                                                }
                                        }
                                }
                        } else if (options.type == 'ajax') {
                                if (typeof param == 'string') {
                                        options.url = param;
                                }
                                $.ajax({
                                        url: options.url,
                                        dataType: 'html',
                                        success: function (data) {
                                                domNodes.content.html('<div style="padding: 1px; margin: -1px;">'+data+'</div>');
                                        }
                                });
                        } else {
                                domNodes.content.html('<div style="padding: 1px; margin: -1px;">'+param+'</div>');
                        }
                        return $jWindow;
                };
                
                /**
                 * Refresh the content of the iframe
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.refresh = function () {
                        if (options.type == 'iframe') domNodes.content.find('iframe').get(0).contentWindow.location.reload();
                        return $jWindow;
                };
                
                /**
                 * A universal setter for jWindow options
                 * @param param Either the name of the value to change or an object with name-value pairs.
                 * @param value The new value of the property (use only if the first parametre is a string)
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.set = function (param, value) {
                        if (typeof param == 'string') {
                                var tmp = {};
                                tmp[param] = value;
                                param = tmp;
                        }
                        if (typeof param != 'object') {
                                param = {};
                        }
                        
                        $.each(param, function (prop, val) {
                                switch (prop) {
                                        case 'title':
                                                options.title = val;
                                                domNodes.title.text(options.title);
                                                break;
                                        case 'posx':
                                                options.posx = val;
                                                domNodes.container.css({left: options.posx + 'px'});
                                                break;
                                        case 'posy':
                                                options.posy = val;
                                                domNodes.container.css({top: options.posy + 'px'});
                                                break;
                                        case 'width':
                                                options.width = val;
                                                domNodes.container.css({width: options.width + 'px'});
                                                break;
                                        case 'height':
                                                options.height = val;
                                                domNodes.content.css({height: options.height + 'px'});
                                                break;
                                        case 'resizeable':
                                                options.resizeable = val;
                                                setResizeable(options.resizeable);
                                                break;
                                        case 'draggable':
                                                options.draggable = val;
                                                setDraggable(options.draggable);
                                                break;
                                        case 'onDragStart':
                                        case 'onDragEnd':
                                        case 'onResizeStart':
                                        case 'onResizeEnd':
                                        case 'onUpdate':
                                        case 'onClose':
                                        case 'onMaximise':
                                        case 'onRestore':
                                        case 'onMinimise':
                                        case 'url':
                                                options[prop] = val;
                                                break;
                                        case 'tabs':
                                                var initial = options.tabs;
                                                options.tabs = !!val;
                                                if (options.tabs != initial) {
                                                        if (options.tabs) {
                                                                $.each(_tabs,function(idx, value) {
                                                                        if (options.url == value.href) value.active(true,false);
                                                                });
                                                                domNodes.tabsBar.prependTo(domNodes.wrapper);
                                                        }
                                                        else domNodes.tabsBar.detach();
                                                }
                                                break;
                                        default:
                                                console.log('Cannot set "'+prop+'".');
                                                break;
                                }
                        });
                        fitInViewport();
                        return $jWindow;
                };

                // ------------
                // JWINDOW TABS
                // ------------

                /**
                 * Array of tabs
                 */
                var _tabs = [];
                
                // ----------------------------
                // JWINDOW TABS PRIVATE METHODS
                // ----------------------------

                /**
                 * The tab
                 * @param params An object containing two properties: href (the iframe's src attribute) and title (the text of the tab's anchor)
                 */
                function jWindowTab (params) {
                        if (typeof params.href == 'undefined' || typeof params.title == 'undefined') throw "Missing parametres!";

                        var $tab = this;
                        $tab.href = params.href;
                        $tab.title = params.title;
                        var isActive = false;
                        var id = tabCounter++;
                        
                        $tab.name = (typeof params.name != 'undefined') ? params.name : null;

                        /**
                         * Retrieve the tab's ID
                         * @return the tab's ID
                         */
                        $tab.getId = function () {
                                return id;
                        };
                        
                        /**
                         * Get or set the active status. Without a parametre, the function acts as a getter. Otherwise, it is a setter.
                         * @param active whether the tab is to be activated or deactivated.
                         * @param update whether to update the window contents or not. Defaults to false.
                         * @return a boolean indicating whether the tab is active or not
                         */
                        $tab.active = function(active, update) {
                                if (typeof active != 'undefined') {
                                        active = !!active;
                                        
                                        update = (typeof update != 'undefined') ? !!update : false;
                                        
                                        // remove the window content if a currently active tab is being deactivated
                                        if (isActive && !active && update) {
                                                $jWindow.update(null);
                                        }
                                        
                                        // update the window contents if an inactive tab is being activated
                                        if (!isActive && active && update) {
                                                $jWindow.update($tab.href);
                                        }
                                        
                                        isActive = active;
                                        
                                        // add/remove classes as needed
                                        if (active) $tab.domNode.addClass('active');
                                        else $tab.domNode.removeClass('active');
                                }
                                return isActive;
                        };

                        $tab.domNode = $('<li class="jWindow-tab">'+$tab.title+'</li>').css({display: 'inline-block', cursor: 'pointer'}).on({
                                click: function (event) {
                                        event.preventDefault();
                                        $tab.domNode.trigger('jWindowOpenTab');
                                },
                                jWindowOpenTab: function () {
                                        $.each(_tabs, function (idx, value) {
                                                value.active(false);
                                        });
                                        $tab.active(true, true);
                                },
                                jWindowCloseTab: function () {
                                        $tab.domNode.detach();
                                        $tab.active(false, true);
                                        var toRemove = 0;
                                        $.each(_tabs, function (idx, value) {
                                                console.log(value.getId());
                                                if (value.getId() == id) {
                                                        toRemove = idx;
                                                }
                                        });
                                        _tabs.splice(toRemove, 1);
                                }
                        });
                };
                
                /**
                 * Check whether the tab name is already taken.
                 * @param params The parametres, as passed to the appendTab/prependTab methods
                 * @return boolean <code>true</code> if the name is free, <code>false</code> otherwise
                 */
                var checkTabNameAvailability = function (params) {
                        var ret = true;
                        if (typeof params.name != 'undefined' && params !== null) {
                                $.each(_tabs, function (idx, value) {
                                        if (value.name == params.name) {
                                                console.log('Tab name must be unique.');
                                                ret = false;
                                        }
                                });
                        }
                        return ret;
                }
                
                // ---------------------------
                // JWINDOW TABS PUBLIC METHODS
                // ---------------------------

                /**
                 * Append a new tab to the tabs list
                 * @param params an object of parametres:<br>
                 *               href - the URL of the content the tab will link to (mandatory)<br>
                 *               title - the text displayed on the tab (mandatory)<br>
                 *               name - a custom unique name for tab referencing (optional)
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.appendTab = function (params) {
                        if (checkTabNameAvailability(params)) {
                                var t = new jWindowTab(params);
                                _tabs.push(t);
                                t.domNode.appendTo(domNodes.tabs);
                        }
                        return $jWindow;
                };

                /**
                 * Prepend a new tab to the tabs list
                 * @param params an object of parametres:<br>
                 *               href - the URL of the content the tab will link to (mandatory)<br>
                 *               title - the text displayed on the tab (mandatory)<br>
                 *               name - a custom unique name for tab referencing (optional)
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.prependTab = function (params) {
                        if (checkTabNameAvailability(params)) {
                                var tmp = _tabs;
                                var t = new jWindowTab(params);

                                $.each(_tabs, function (idx, value) {
                                        value.domNode.detach();
                                });

                                _tabs = [t];
                                $.each(tmp, function (idx, value) {
                                        _tabs.push(value);
                                });
                                $.each(_tabs,function (idx, value) {
                                        value.domNode.appendTo(domNodes.tabs);
                                });
                        }
                        return $jWindow;
                };
                
                /**
                 * Activate a tab and load the contents its href points to
                 * @param name The name of the tab to open
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.openTab = function (name) {
                        if (name === null) return $jWindow;
                        $.each(_tabs, function (idx, value) {
                                if (value.name == name) {
                                        value.domNode.trigger('jWindowOpenTab');
                                }
                        });
                        return $jWindow;
                };
                
                /**
                 * Deactivate and remove a tab
                 * @param name The name of the tab to close
                 * @return jWindow Provides a fluent interface
                 */
                $jWindow.closeTab = function (name) {
                        if (name === null) return $jWindow;
                        $.each(_tabs, function (idx, value) {
                                if (value.name == name) {
                                        value.domNode.trigger('jWindowCloseTab');
                                }
                        });
                        return $jWindow;
                };
        }
        
        // Extend the jQuery object with the jWindow function
        $.extend({
                jWindow: function (param) {
                        switch(typeof param) {
                                case 'string':
                                        for (var i = 0; i < jWindows.length; ++i) {
                                                if (jWindows[i].get('id') == param) return jWindows[i];
                                        }
                                        return null;
                                        break;
                                case 'object':
                                        if (typeof param.id != 'string' || param.id.length == 0) console.log("An ID is required.");
                                        else {
                                                var tmp = new jWindow(param);
                                                var cmp = $.jWindow(param.id);
                                                if (cmp === null) {
                                                        jWindows.push(tmp);
                                                        return tmp;
                                                } else {
                                                        console.log('jWindow id ' + param.id + ' already exists.');
                                                        return cmp;
                                                }
                                        }
                                        break;
                                default:
                                        console.log("Bad or no parametre!");
                                        break;
                        }
                }
        });

})(jQuery);
