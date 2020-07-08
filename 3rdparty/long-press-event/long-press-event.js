/*!
 * long-press-event.js
 * Pure JavaScript long-press-event
 * https://github.com/john-doherty/long-press-event
 * @author John Doherty <www.johndoherty.info>
 * @license MIT
 */
(function (window, document) {

    'use strict';

    var timer = null;

    // check if we're using a touch screen
    var isTouch = (('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0));

    // switch to touch events if using a touch screen
    var mouseDown = isTouch ? 'touchstart' : 'mousedown';
    var mouseOut = isTouch ? 'touchcancel' : 'mouseout';
    var mouseUp = isTouch ? 'touchend' : 'mouseup';
    var mouseMove = isTouch ? 'touchmove' : 'mousemove';

    // wheel/scroll events
    var mouseWheel = 'mousewheel';
    var wheel = 'wheel';
    var scrollEvent = 'scroll';

    // patch CustomEvent to allow constructor creation (IE/Chrome)
    if (typeof window.CustomEvent !== "function" ) {

        window.CustomEvent = function(event, params) {

            params = params || { bubbles: false, cancelable: false, detail: undefined };

            var evt = document.createEvent('CustomEvent');
            evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
            return evt;
        };

        window.CustomEvent.prototype = window.Event.prototype;
    }

    // listen to mousedown event on any child element of the body
    document.addEventListener(mouseDown, function(e) {

        var el = e.target;

        // get delay from html attribute if it exists, otherwise default to 1500
        var longPressDelayInMs = parseInt(el.getAttribute('data-long-press-delay') || '1500', 10);

        // start the timer
        timer = setTimeout(fireLongPressEvent.bind(el), longPressDelayInMs);
    });

    // clear the timeout if the user releases the mouse/touch
    document.addEventListener(mouseUp, function(e) {
        clearTimeout(timer);
    });

    // clear the timeout if the user leaves the element
    document.addEventListener(mouseOut, function(e) {
        clearTimeout(timer);
    });

    // clear if the mouse moves
    document.addEventListener(mouseMove, function(e) {
        clearTimeout(timer);
    });

    // clear if the Wheel event is fired in the element
    document.addEventListener(mouseWheel, function(e){ 
        clearTimeout(timer);
    });

    // clear if the Scroll event is fired in the element
    document.addEventListener(wheel, function(e){ 
        clearTimeout(timer);
    });

    // clear if the Scroll event is fired in the element
    document.addEventListener(scrollEvent, function(e){ 
        clearTimeout(timer);
    });

    /**
     * Fires the 'long-press' event on element
     * @returns {void}
     */
    function fireLongPressEvent() {

        // fire the long-press event
        this.dispatchEvent(new CustomEvent('long-press', { bubbles: true, cancelable: true }));

        clearTimeout(timer);

        if (console && console.log) console.log('long-press event fired on ' + this.outerHTML);
    }

}(this, document));
