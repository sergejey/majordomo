/*
* @version 0.1 (auto-set)
*/
//<!--
// Ultimate client-side JavaScript client sniff. Version 3.03
// (C) Netscape Communications 1999-2001.  Permission granted to reuse and distribute.
// Revised 17 May 99 to add is_nav5up and is_ie5up (see below).
// Revised 20 Dec 00 to add is_gecko and change is_nav5up to is_nav6up
//                      also added support for IE5.5 Opera4&5 HotJava3 AOLTV
// Revised 22 Feb 01 to correct Javascript Detection for IE 5.x, Opera 4, 
//                      correct Opera 5 detection
//                      add support for winME and win2k
//                      synch with browser-type-oo.js
// Revised 26 Mar 01 to correct Opera detection
// Revised 02 Oct 01 to add IE6 detection

// Everything you always wanted to know about your JavaScript client
// but were afraid to ask. Creates "is_" variables indicating:
// (1) browser vendor:
//     is_nav, is_ie, is_opera, is_hotjava, is_webtv, is_TVNavigator, is_AOLTV
// (2) browser version number:
//     is_major (integer indicating major version number: 2, 3, 4 ...)
//     is_minor (float   indicating full  version number: 2.02, 3.01, 4.04 ...)
// (3) browser vendor AND major version number
//     is_nav2, is_nav3, is_nav4, is_nav4up, is_nav6, is_nav6up, is_gecko, is_ie3,
//     is_ie4, is_ie4up, is_ie5, is_ie5up, is_ie5_5, is_ie5_5up, is_ie6, is_ie6up, is_hotjava3, is_hotjava3up,
//     is_opera2, is_opera3, is_opera4, is_opera5, is_opera5up
// (4) JavaScript version number:
//     is_js (float indicating full JavaScript version number: 1, 1.1, 1.2 ...)
// (5) OS platform and version:
//     is_win, is_win16, is_win32, is_win31, is_win95, is_winnt, is_win98, is_winme, is_win2k
//     is_os2
//     is_mac, is_mac68k, is_macppc
//     is_unix
//     is_sun, is_sun4, is_sun5, is_suni86
//     is_irix, is_irix5, is_irix6
//     is_hpux, is_hpux9, is_hpux10
//     is_aix, is_aix1, is_aix2, is_aix3, is_aix4
//     is_linux, is_sco, is_unixware, is_mpras, is_reliant
//     is_dec, is_sinix, is_freebsd, is_bsd
//     is_vms
//
// See http://www.it97.de/JavaScript/JS_tutorial/bstat/navobj.html and
// http://www.it97.de/JavaScript/JS_tutorial/bstat/Browseraol.html
// for detailed lists of userAgent strings.
//
// Note: you don't want your Nav4 or IE4 code to "turn off" or
// stop working when new versions of browsers are released, so
// in conditional code forks, use is_ie5up ("IE 5.0 or greater") 
// is_opera5up ("Opera 5.0 or greater") instead of is_ie5 or is_opera5
// to check version in code which you want to work on future
// versions.
    // convert all characters to lowercase to simplify testing
    var agt=navigator.userAgent.toLowerCase();

    // *** BROWSER VERSION ***
    // Note: On IE5, these return 4, so use is_ie5up to detect IE5.
    var is_major = parseInt(navigator.appVersion);
    var is_minor = parseFloat(navigator.appVersion);

    // Note: Opera and WebTV spoof Navigator.  We do strict client detection.
    // If you want to allow spoofing, take out the tests for opera and webtv.
    var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
                && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
                && (agt.indexOf('webtv')==-1) && (agt.indexOf('hotjava')==-1));
    var is_nav2 = (is_nav && (is_major == 2));
    var is_nav3 = (is_nav && (is_major == 3));
    var is_nav4 = (is_nav && (is_major == 4));
    var is_nav4up = (is_nav && (is_major >= 4));
    var is_navonly      = (is_nav && ((agt.indexOf(";nav") != -1) ||
                          (agt.indexOf("; nav") != -1)) );
    var is_nav6 = (is_nav && (is_major == 5));
    var is_nav6up = (is_nav && (is_major >= 5));
    var is_gecko = (agt.indexOf('gecko') != -1);


    var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
    var is_ie3    = (is_ie && (is_major < 4));
    var is_ie4    = (is_ie && (is_major == 4) && (agt.indexOf("msie 4")!=-1) );
    var is_ie4up  = (is_ie && (is_major >= 4));
    var is_ie5    = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")!=-1) );
    var is_ie5_5  = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.5") !=-1));
    var is_ie5up  = (is_ie && !is_ie3 && !is_ie4);
    var is_ie5_5up =(is_ie && !is_ie3 && !is_ie4 && !is_ie5);
    var is_ie6    = (is_ie && (is_major == 4) && (agt.indexOf("msie 6.")!=-1) );
    var is_ie6up  = (is_ie && !is_ie3 && !is_ie4 && !is_ie5 && !is_ie5_5);

    // KNOWN BUG: On AOL4, returns false if IE3 is embedded browser
    // or if this is the first browser window opened.  Thus the
    // variables is_aol, is_aol3, and is_aol4 aren't 100% reliable.
    var is_aol   = (agt.indexOf("aol") != -1);
    var is_aol3  = (is_aol && is_ie3);
    var is_aol4  = (is_aol && is_ie4);
    var is_aol5  = (agt.indexOf("aol 5") != -1);
    var is_aol6  = (agt.indexOf("aol 6") != -1);

    var is_opera = (agt.indexOf("opera") != -1);
    var is_opera2 = (agt.indexOf("opera 2") != -1 || agt.indexOf("opera/2") != -1);
    var is_opera3 = (agt.indexOf("opera 3") != -1 || agt.indexOf("opera/3") != -1);
    var is_opera4 = (agt.indexOf("opera 4") != -1 || agt.indexOf("opera/4") != -1);
    var is_opera5 = (agt.indexOf("opera 5") != -1 || agt.indexOf("opera/5") != -1);
    var is_opera5up = (is_opera && !is_opera2 && !is_opera3 && !is_opera4);

    var is_webtv = (agt.indexOf("webtv") != -1); 

    var is_TVNavigator = ((agt.indexOf("navio") != -1) || (agt.indexOf("navio_aoltv") != -1)); 
    var is_AOLTV = is_TVNavigator;

    var is_hotjava = (agt.indexOf("hotjava") != -1);
    var is_hotjava3 = (is_hotjava && (is_major == 3));
    var is_hotjava3up = (is_hotjava && (is_major >= 3));

    // *** JAVASCRIPT VERSION CHECK ***
    var is_js;
    if (is_nav2 || is_ie3) is_js = 1.0;
    else if (is_nav3) is_js = 1.1;
    else if (is_opera5up) is_js = 1.3;
    else if (is_opera) is_js = 1.1;
    else if ((is_nav4 && (is_minor <= 4.05)) || is_ie4) is_js = 1.2;
    else if ((is_nav4 && (is_minor > 4.05)) || is_ie5) is_js = 1.3;
    else if (is_hotjava3up) is_js = 1.4;
    else if (is_nav6 || is_gecko) is_js = 1.5;
    // NOTE: In the future, update this code when newer versions of JS
    // are released. For now, we try to provide some upward compatibility
    // so that future versions of Nav and IE will show they are at
    // *least* JS 1.x capable. Always check for JS version compatibility
    // with > or >=.
    else if (is_nav6up) is_js = 1.5;
    // NOTE: ie5up on mac is 1.4
    else if (is_ie5up) is_js = 1.3

    // HACK: no idea for other browsers; always check for JS version with > or >=
    else is_js = 0.0;

    // *** PLATFORM ***
    var is_win   = ( (agt.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1) );
    // NOTE: On Opera 3.0, the userAgent string includes "Windows 95/NT4" on all
    //        Win32, so you can't distinguish between Win95 and WinNT.
    var is_win95 = ((agt.indexOf("win95")!=-1) || (agt.indexOf("windows 95")!=-1));

    // is this a 16 bit compiled version?
    var is_win16 = ((agt.indexOf("win16")!=-1) || 
               (agt.indexOf("16bit")!=-1) || (agt.indexOf("windows 3.1")!=-1) || 
               (agt.indexOf("windows 16-bit")!=-1) );  

    var is_win31 = ((agt.indexOf("windows 3.1")!=-1) || (agt.indexOf("win16")!=-1) ||
                    (agt.indexOf("windows 16-bit")!=-1));

    var is_winme = ((agt.indexOf("win 9x 4.90")!=-1));
    var is_win2k = ((agt.indexOf("windows nt 5.0")!=-1));

    // NOTE: Reliable detection of Win98 may not be possible. It appears that:
    //       - On Nav 4.x and before you'll get plain "Windows" in userAgent.
    //       - On Mercury client, the 32-bit version will return "Win98", but
    //         the 16-bit version running on Win98 will still return "Win95".
    var is_win98 = ((agt.indexOf("win98")!=-1) || (agt.indexOf("windows 98")!=-1));
    var is_winnt = ((agt.indexOf("winnt")!=-1) || (agt.indexOf("windows nt")!=-1));
    var is_win32 = (is_win95 || is_winnt || is_win98 || 
                    ((is_major >= 4) && (navigator.platform == "Win32")) ||
                    (agt.indexOf("win32")!=-1) || (agt.indexOf("32bit")!=-1));

    var is_os2   = ((agt.indexOf("os/2")!=-1) || 
                    (navigator.appVersion.indexOf("OS/2")!=-1) ||   
                    (agt.indexOf("ibm-webexplorer")!=-1));

    var is_mac    = (agt.indexOf("mac")!=-1);
    // hack ie5 js version for mac
    if (is_mac && is_ie5up) is_js = 1.4;
    var is_mac68k = (is_mac && ((agt.indexOf("68k")!=-1) || 
                               (agt.indexOf("68000")!=-1)));
    var is_macppc = (is_mac && ((agt.indexOf("ppc")!=-1) || 
                                (agt.indexOf("powerpc")!=-1)));

    var is_sun   = (agt.indexOf("sunos")!=-1);
    var is_sun4  = (agt.indexOf("sunos 4")!=-1);
    var is_sun5  = (agt.indexOf("sunos 5")!=-1);
    var is_suni86= (is_sun && (agt.indexOf("i86")!=-1));
    var is_irix  = (agt.indexOf("irix") !=-1);    // SGI
    var is_irix5 = (agt.indexOf("irix 5") !=-1);
    var is_irix6 = ((agt.indexOf("irix 6") !=-1) || (agt.indexOf("irix6") !=-1));
    var is_hpux  = (agt.indexOf("hp-ux")!=-1);
    var is_hpux9 = (is_hpux && (agt.indexOf("09.")!=-1));
    var is_hpux10= (is_hpux && (agt.indexOf("10.")!=-1));
    var is_aix   = (agt.indexOf("aix") !=-1);      // IBM
    var is_aix1  = (agt.indexOf("aix 1") !=-1);    
    var is_aix2  = (agt.indexOf("aix 2") !=-1);    
    var is_aix3  = (agt.indexOf("aix 3") !=-1);    
    var is_aix4  = (agt.indexOf("aix 4") !=-1);    
    var is_linux = (agt.indexOf("inux")!=-1);
    var is_sco   = (agt.indexOf("sco")!=-1) || (agt.indexOf("unix_sv")!=-1);
    var is_unixware = (agt.indexOf("unix_system_v")!=-1); 
    var is_mpras    = (agt.indexOf("ncr")!=-1); 
    var is_reliant  = (agt.indexOf("reliantunix")!=-1);
    var is_dec   = ((agt.indexOf("dec")!=-1) || (agt.indexOf("osf1")!=-1) || 
           (agt.indexOf("dec_alpha")!=-1) || (agt.indexOf("alphaserver")!=-1) || 
           (agt.indexOf("ultrix")!=-1) || (agt.indexOf("alphastation")!=-1)); 
    var is_sinix = (agt.indexOf("sinix")!=-1);
    var is_freebsd = (agt.indexOf("freebsd")!=-1);
    var is_bsd = (agt.indexOf("bsd")!=-1);
    var is_unix  = ((agt.indexOf("x11")!=-1) || is_sun || is_irix || is_hpux || 
                 is_sco ||is_unixware || is_mpras || is_reliant || 
                 is_dec || is_sinix || is_aix || is_linux || is_bsd || is_freebsd);

    var is_vms   = ((agt.indexOf("vax")!=-1) || (agt.indexOf("openvms")!=-1));

//--> end hide JavaScript


/**
* Filename.......: calendar.js
* Project........: Popup Calendar
* Last Modified..: $Date$
* CVS Revision...: $Revision$
* Copyright......: 2001, 2002 Richard Heyes
*/

/**
* Global variables
*/
        dynCalendar_layers          = new Array();
        dynCalendar_mouseoverStatus = false;
        dynCalendar_mouseX          = 0;
        dynCalendar_mouseY          = 0;

/**
* The calendar constructor
*
* @access public
* @param string objName      Name of the object that you create
* @param string callbackFunc Name of the callback function
* @param string OPTIONAL     Optional layer name
* @param string OPTIONAL     Optional images path
*/
        function dynCalendar(objName, callbackFunc)
        {
                /**
        * Properties
        */
                // Todays date
                this.today          = new Date();
                this.date           = this.today.getDate();
                this.month          = this.today.getMonth();
                this.year           = this.today.getFullYear();

                this.objName        = objName;
                this.callbackFunc   = callbackFunc;
                this.imagesPath     = arguments[2] ? arguments[2] : '<#ROOTHTML#>templates/dateselect/';
                this.layerID        = arguments[3] ? arguments[3] : 'dynCalendar_layer_' + dynCalendar_layers.length;

                this.offsetX        = 5;
                this.offsetY        = 5;

                this.useMonthCombo  = true;
                this.useYearCombo   = true;
                this.yearComboRange = 5;

                this.currentMonth   = this.month;
                this.currentYear    = this.year;

                /**
        * Public Methods
        */
                this.show              = dynCalendar_show;
                this.writeHTML         = dynCalendar_writeHTML;

                // Accessor methods
                this.setOffset         = dynCalendar_setOffset;
                this.setOffsetX        = dynCalendar_setOffsetX;
                this.setOffsetY        = dynCalendar_setOffsetY;
                this.setImagesPath     = dynCalendar_setImagesPath;
                this.setMonthCombo     = dynCalendar_setMonthCombo;
                this.setYearCombo      = dynCalendar_setYearCombo;
                this.setCurrentMonth   = dynCalendar_setCurrentMonth;
                this.setCurrentYear    = dynCalendar_setCurrentYear;
                this.setYearComboRange = dynCalendar_setYearComboRange;

                /**
        * Private methods
        */
                // Layer manipulation
                this._getLayer         = dynCalendar_getLayer;
                this._hideLayer        = dynCalendar_hideLayer;
                this._showLayer        = dynCalendar_showLayer;
                this._setLayerPosition = dynCalendar_setLayerPosition;
                this._setHTML          = dynCalendar_setHTML;

                // Miscellaneous
                this._getDaysInMonth   = dynCalendar_getDaysInMonth;
                this._mouseover        = dynCalendar_mouseover;

                /**
        * Constructor type code
        */
                dynCalendar_layers[dynCalendar_layers.length] = this;
                this.writeHTML();
        }

/**
* Shows the calendar, or updates the layer if
* already visible.
*
* @access public
* @param integer month Optional month number (0-11)
* @param integer year  Optional year (YYYY format)
*/
        function dynCalendar_show()
        {
                // Variable declarations to prevent globalisation
                var month, year, monthnames, numdays, thisMonth, firstOfMonth;
                var ret, row, i, cssClass, linkHTML, previousMonth, previousYear;
                var nextMonth, nextYear, prevImgHTML, prevLinkHTML, nextImgHTML, nextLinkHTML;
                var monthComboOptions, monthCombo, yearComboOptions, yearCombo, html;
                
                this.currentMonth = month = arguments[0] != null ? arguments[0] : this.currentMonth;
                this.currentYear  = year  = arguments[1] != null ? arguments[1] : this.currentYear;

                monthnames = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                numdays    = this._getDaysInMonth(month, year);

                thisMonth    = new Date(year, month, 1);
                firstOfMonth = thisMonth.getDay();

                // First few blanks up to first day
                ret = new Array(new Array());
                for(i=0; i<firstOfMonth; i++){
                        ret[0][ret[0].length] = '<td>&nbsp;</td>';
                }

                // Main body of calendar
                row = 0;
                i   = 1;
                while(i <= numdays){
                        if(ret[row].length == 7){
                                ret[++row] = new Array();
                        }

                        /**
            * Generate this cells' HTML
            */
                        cssClass = (i == this.date && month == this.month && year == this.year) ? 'dynCalendar_today' : 'dynCalendar_day';
                        linkHTML = sprintf('<a href="javascript: %s(%s, %s, %s); %s._hideLayer()">%s</a>',
                                           this.callbackFunc,
                                                           i,
                                                           Number(month) + 1,
                                                           year,
                                                           this.objName,
                                                           i++);

                        ret[row][ret[row].length] = sprintf('<td align="center" class="%s">%s</td>', cssClass, linkHTML);
                }

           // Format the HTML
                var retCnt = ret.length;
                for (i = 0; i < retCnt; i++) {
                        ret[i] = ret[i].join('\n') + '\n';
                }

                previousYear  = thisMonth.getFullYear();
                previousMonth = thisMonth.getMonth() - 1;
                if(previousMonth < 0){
                        previousMonth = 11;
                        previousYear--;
                }
                
                nextYear  = thisMonth.getFullYear();
                nextMonth = thisMonth.getMonth() + 1;
                if(nextMonth > 11){
                        nextMonth = 0;
                        nextYear++;
                }

                prevImgHTML  = sprintf('<img src="%s/prev.gif" alt="<<" border="0" />', this.imagesPath);
                prevLinkHTML = sprintf('<a href="javascript: %s.show(%s, %s)">%s</a>',  this.objName, previousMonth, previousYear, prevImgHTML);
                nextImgHTML  = sprintf('<img src="%s/next.gif" alt="<<" border="0" />', this.imagesPath);
                nextLinkHTML = sprintf('<a href="javascript: %s.show(%s, %s)">%s</a>',  this.objName, nextMonth, nextYear, nextImgHTML);

                /**
        * Build month combo
        */
                if (this.useMonthCombo) {
                        monthComboOptions = '';
                        for (i=0; i<12; i++) {
                                selected = (i == thisMonth.getMonth() ? 'selected="selected"' : '');
                                monthComboOptions += sprintf('<option value="%s" %s>%s</option>', i, selected, monthnames[i]);
                        }
                        monthCombo = sprintf('<select name="months" onchange="%s.show(this.options[this.selectedIndex].value, %s.currentYear)">%s</select>', this.objName, this.objName, monthComboOptions);
                } else {
                        monthCombo = monthnames[thisMonth.getMonth()];
                }
                
                /**
        * Build year combo
        */
                if (this.useYearCombo) {
                        yearComboOptions = '';
                        for (i = thisMonth.getFullYear() - this.yearComboRange; i <= (thisMonth.getFullYear() + this.yearComboRange); i++) {
                                selected = (i == thisMonth.getFullYear() ? 'selected="selected"' : '');
                                yearComboOptions += sprintf('<option value="%s" %s>%s</option>', i, selected, i);
                        }
                        yearCombo = sprintf('<select style="border: 1px groove" name="years" onchange="%s.show(%s.currentMonth, this.options[this.selectedIndex].value)">%s</select>', this.objName, this.objName, yearComboOptions);
                } else {
                        yearCombo = thisMonth.getFullYear();
                }

                html = '<table border="0" bgcolor="#eeeeee">';
                html += sprintf('<tr><td class="dynCalendar_header">%s</td><td colspan="5" align="center" class="dynCalendar_header">%s %s</td><td align="right" class="dynCalendar_header">%s</td></tr>', prevLinkHTML, monthCombo, yearCombo, nextLinkHTML);
                html += '<tr>';
                html += '<td class="dynCalendar_dayname">Su</td>';
                html += '<td class="dynCalendar_dayname">Mo</td>';
                html += '<td class="dynCalendar_dayname">Tu</td>';
                html += '<td class="dynCalendar_dayname">We</td>';
                html += '<td class="dynCalendar_dayname">Th</td>';
                html += '<td class="dynCalendar_dayname">Fr</td>';
                html += '<td class="dynCalendar_dayname">Sa</td></tr>';
                html += '<tr>' + ret.join('</tr>\n<tr>') + '</tr>';
                html += '</table>';

                this._setHTML(html);
                if (!arguments[0] && !arguments[1]) {
                        this._showLayer();
                        this._setLayerPosition();
                }
        }

/**
* Writes HTML to document for layer
*
* @access public
*/
        function dynCalendar_writeHTML()
        {
                if (is_ie5up || is_nav6up || is_gecko) {
                        document.write(sprintf('<a href="javascript: %s.show()"><img src="%sdynCalendar.gif" border="0" width="16" height="16" /></a>', this.objName, this.imagesPath));
                        document.write(sprintf('<div class="dynCalendar" id="%s" onmouseover="%s._mouseover(true)" onmouseout="%s._mouseover(false)"></div>', this.layerID, this.objName, this.objName));
                }
        }

/**
* Sets the offset to the mouse position
* that the calendar appears at.
*
* @access public
* @param integer Xoffset Number of pixels for vertical
*                        offset from mouse position
* @param integer Yoffset Number of pixels for horizontal
*                        offset from mouse position
*/
        function dynCalendar_setOffset(Xoffset, Yoffset)
        {
                this.setOffsetX(Xoffset);
                this.setOffsetY(Yoffset);
        }

/**
* Sets the X offset to the mouse position
* that the calendar appears at.
*
* @access public
* @param integer Xoffset Number of pixels for horizontal
*                        offset from mouse position
*/
        function dynCalendar_setOffsetX(Xoffset)
        {
                this.offsetX = Xoffset;
        }

/**
* Sets the Y offset to the mouse position
* that the calendar appears at.
*
* @access public
* @param integer Yoffset Number of pixels for vertical
*                        offset from mouse position
*/
        function dynCalendar_setOffsetY(Yoffset)
        {
                this.offsetY = Yoffset;
        }
        
/**
* Sets the images path
*
* @access public
* @param string path Path to use for images
*/
        function dynCalendar_setImagesPath(path)
        {
                this.imagesPath = path;
        }

/**
* Turns on/off the month dropdown
*
* @access public
* @param boolean useMonthCombo Whether to use month dropdown or not
*/
        function dynCalendar_setMonthCombo(useMonthCombo)
        {
                this.useMonthCombo = useMonthCombo;
        }

/**
* Turns on/off the year dropdown
*
* @access public
* @param boolean useYearCombo Whether to use year dropdown or not
*/
        function dynCalendar_setYearCombo(useYearCombo)
        {
                this.useYearCombo = useYearCombo;
        }

/**
* Sets the current month being displayed
*
* @access public
* @param boolean month The month to set the current month to
*/
        function dynCalendar_setCurrentMonth(month)
        {
                this.currentMonth = month;
        }

/**
* Sets the current month being displayed
*
* @access public
* @param boolean year The year to set the current year to
*/
        function dynCalendar_setCurrentYear(year)
        {
                this.currentYear = year;
        }

/**
* Sets the range of the year combo. Displays this number of
* years either side of the year being displayed.
*
* @access public
* @param integer range The range to set
*/
        function dynCalendar_setYearComboRange(range)
        {
                this.yearComboRange = range;
        }

/**
* Returns the layer object
*
* @access private
*/
        function dynCalendar_getLayer()
        {
                var layerID = this.layerID;

                if (document.getElementById(layerID)) {

                        return document.getElementById(layerID);

                } else if (document.all(layerID)) {
                        return document.all(layerID);
                }
        }

/**
* Hides the calendar layer
*
* @access private
*/
        function dynCalendar_hideLayer()
        {
                this._getLayer().style.visibility = 'hidden';
        }

/**
* Shows the calendar layer
*
* @access private
*/
        function dynCalendar_showLayer()
        {
                this._getLayer().style.visibility = 'visible';
        }

/**
* Sets the layers position
*
* @access private
*/
        function dynCalendar_setLayerPosition()
        {
                this._getLayer().style.top  = (dynCalendar_mouseY + this.offsetY) + 'px';
                this._getLayer().style.left = (dynCalendar_mouseX + this.offsetX) + 'px';
        }

/**
* Sets the innerHTML attribute of the layer
*
* @access private
*/
        function dynCalendar_setHTML(html)
        {
                this._getLayer().innerHTML = html;
        }

/**
* Returns number of days in the supplied month
*
* @access private
* @param integer month The month to get number of days in
* @param integer year  The year of the month in question
*/
        function dynCalendar_getDaysInMonth(month, year)
        {
                monthdays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                if (month != 1) {
                        return monthdays[month];
                } else {
                        return ((year % 4 == 0 && year % 100 != 0) || year % 400 == 0 ? 29 : 28);
                }
        }

/**
* onMouse(Over|Out) event handler
*
* @access private
* @param boolean status Whether the mouse is over the
*                       calendar or not
*/
        function dynCalendar_mouseover(status)
        {
                dynCalendar_mouseoverStatus = status;
                return true;
        }

/**
* onMouseMove event handler
*/
        if (!mouseMoveEventAssigned) {
                dynCalendar_oldOnmousemove = document.onmousemove ? document.onmousemove : new Function;
        
                document.onmousemove = function ()
                {
                        if (arguments[0]) {
                                dynCalendar_mouseX = arguments[0].pageX;
                                dynCalendar_mouseY = arguments[0].pageY;
                        } else {
                                dynCalendar_mouseX = event.clientX + document.body.scrollLeft;
                                dynCalendar_mouseY = event.clientY + document.body.scrollTop;
                                arguments[0] = null;
                        }
        
                        dynCalendar_oldOnmousemove(arguments[0]);
                }
                
                var mouseMoveEventAssigned = true;
        }

/**
* Callbacks for document.onclick
*/
        if (!clickEventAssigned) {
                dynCalendar_oldOnclick = document.onclick ? document.onclick : new Function;
        
                document.onclick = function ()
                {
                   if(!dynCalendar_mouseoverStatus){
                      var layersCnt = dynCalendar_layers.length;
                      for (i = 0; i < layersCnt; ++i) {
                        dynCalendar_layers[i]._hideLayer();
                      }
                   }
        
                   dynCalendar_oldOnclick(arguments[0] ? arguments[0] : null);
                }
                var clickEventAssigned = true;
        }

/**
* Javascript mini implementation of sprintf()
*/
        function sprintf(strInput)
        {
                var strOutput  = '';
                var currentArg = 1;
        
                for (var i=0; i<strInput.length; i++) {
                        if (strInput.charAt(i) == '%' && i != (strInput.length - 1) && typeof(arguments[currentArg]) != 'undefined') {
                                switch (strInput.charAt(++i)) {
                                        case 's':
                                                strOutput += arguments[currentArg];
                                                break;
                                        case '%':
                                                strOutput += '%';
                                                break;
                                }
                                currentArg++;
                        } else {
                                strOutput += strInput.charAt(i);
                        }
                }
        
                return strOutput;
        } 