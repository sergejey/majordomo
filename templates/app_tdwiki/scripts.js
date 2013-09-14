/*
* @version 0.1 (auto-set)
*/
var version = {major: 1, minor: 2, revision: 32, date: new Date("Aug 18, 2005"), extensions: {}};

// ---------------------------------------------------------------------------------
// Configuration repository
// ---------------------------------------------------------------------------------

var config = {
        // Options that can be set in the options panel and/or cookies
        options: {
                chkRegExpSearch: false,
                chkCaseSensitiveSearch: false,
                chkAnimate: false,
                txtUserName: "YourName",
                chkSaveBackups: true,
                chkAutoSave: true,
                chkGenerateAnRssFeed: false,
                chkSaveEmptyTemplate: false,
                chkOpenInNewWindow: true,
                chkToggleLinks: false,
                chkHttpReadOnly: false,
                txtMainTab: "tabTimeline",
                txtMoreTab: "moreTabAll"
                },
        // Hashmap of notification functions to be called when certain tiddlers are changed or deleted
        notifyNamedTiddlers: {
                SiteTitle: refreshTitle,
                SiteSubtitle: refreshSubtitle,
                SideBarOptions: refreshSidebar,
                StyleSheet: refreshStyles
                },
        // List of notification functions to be called when any tiddler is changed or deleted
        notifyTiddlers: [
                refreshMenu,
                refreshStory,
                refreshTabs
                ],
        // Shadow tiddlers for emergencies
        shadowTiddlers: {
                SideBarOptions: "<<search>><<closeAll>><<permaview>><<saveChanges>><<slider chkSliderOptionsPanel OptionsPanel options 'Change TiddlyWiki advanced options'>>",
                OptionsPanel: "These InterfaceOptions for customising TiddlyWiki are saved in your browser\n\nYour username for signing your edits. Write it as a WikiWord (eg JoeBloggs)\n\n<<option txtUserName>>\n<<option chkSaveBackups>> SaveBackups\n<<option chkAutoSave>> AutoSave\n<<option chkGenerateAnRssFeed>> GenerateAnRssFeed\n<<option chkRegExpSearch>> RegExpSearch\n<<option chkCaseSensitiveSearch>> CaseSensitiveSearch\n<<option chkAnimate>> EnableAnimations\n\nSee AdvancedOptions",
                AdvancedOptions: "<<option chkOpenInNewWindow>> OpenLinksInNewWindow\n<<option chkSaveEmptyTemplate>> SaveEmptyTemplate\n<<option chkToggleLinks>> Clicking on links to tiddlers that are already open causes them to close\n^^(override with Control or other modifier key)^^\n<<option chkHttpReadOnly>> HideEditingFeatures when viewed over HTTP",
                SideBarTabs: "<<tabs txtMainTab Timeline Timeline TabTimeline All 'All tiddlers' TabAll Tags 'All tags' TabTags More 'More lists' TabMore>>",
                TabTimeline: "<<timeline>>",
                TabAll: "<<list all>>",
                TabTags: "<<allTags>>",
                TabMore: "<<tabs txtMoreTab Missing 'Missing tiddlers' TabMoreMissing Orphans 'Orphaned tiddlers' TabMoreOrphans>>",
                TabMoreMissing: "<<list missing>>",
                TabMoreOrphans: "<<list orphans>>"
                },
        // Miscellaneous options
        numRssItems: 20, // Number of items in the RSS feed
        animFast: 0.12, // Speed for animations (lower == slower)
        animSlow: 0.01, // Speed for EasterEgg animations
        // Messages
        messages: {
                customConfigError: "Error in customConfig - %0",
                savedSnapshotError: "It appears that this TiddlyWiki has been incorrectly saved. Please see http://www.tiddlywiki.com/#DownloadSoftware for details",
                subtitleUnknown: "(unknown)",
                undefinedTiddlerToolTip: "The tiddler '%0' doesn't yet exist",
                externalLinkTooltip: "External link to %0",
                noTags: "There are no tagged tiddlers",
                notFileUrlError: "You need to save this TiddlyWiki to a file before you can save changes",
                cantSaveError: "It's not possible to save changes using this browser. Use FireFox if you can",
                invalidFileError: "The original file '%0' does not appear to be a valid TiddlyWiki",
                backupSaved: "Backup saved",
                backupFailed: "Failed to save backup file",
                rssSaved: "RSS feed saved",
                rssFailed: "Failed to save RSS feed file",
                emptySaved: "Empty template saved",
                emptyFailed: "Failed to save empty template file",
                mainSaved: "Main TiddlyWiki file saved",
                mainFailed: "Failed to save main TiddlyWiki file. Your changes have not been saved",
                macroError: "Error executing macro '%0'",
                overwriteWarning: "A tiddler named '%0' already exists. Choose OK to overwrite it",
                unsavedChangesWarning: "WARNING! There are unsaved changes in TiddlyWiki\n\nChoose OK to save\nChoose CANCEL to discard",
                dates: {
                        months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November","December"],
                        days: ["Sunday", "Monday","Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
                        }
                },
        views: {
                wikified: {
                        tag: {labelNoTags: "no tags", labelTags: "tags: ", tooltip: "Show tiddlers tagged with '%0'", openAllText: "Open all", openAllTooltip: "Open all of these tiddlers", popupNone: "No other tiddlers tagged with '%0'"},
                        toolbarClose: {text: "close", tooltip: "Close this tiddler"},
                        toolbarEdit: {text: "edit", tooltip: "Edit this tiddler"},
                        toolbarPermalink: {text: "permalink", tooltip: "Permalink for this tiddler"},
                        toolbarReferences: {text: "references", tooltip: "Show tiddlers that link to this one", popupNone: "No references"},
                        defaultText: "The tiddler '%0' doesn't yet exist. Double-click to create it"
                        },
                editor: {
                        tagPrompt: "Type tags separated with spaces, [[use double square brackets]] if necessary, or add existing",
                        tagChooser: {text: "tags", tooltip: "Choose existing tags to add to this tiddler", popupNone: "There are no tags defined", tagTooltip: "Add the tag '%0'"},
                        toolbarDone: {text: "done", tooltip: "Save changes to this tiddler"},
                        toolbarCancel: {text: "cancel", tooltip: "Undo changes to this tiddler"},
                        toolbarDelete: {text: "delete", tooltip: "Delete this tiddler"},
                        defaultText: "Type the text for '%0'"
                        }
                },
        macros: { // Each has a 'handler' member that is inserted later
                today: {},
                version: {},
                search: {label: "search", prompt: "Search this TiddlyWiki", sizeTextbox: 15, successMsg: "%0 tiddlers found matching %1", failureMsg: "No tiddlers found matching %0"},
                tiddler: {},
                tag: {},
                timeline: {dateFormat: "DD MMM YYYY"},
                allTags: {tooltip: "Show tiddlers tagged with '%0'", noTags: "There are no tagged tiddlers"},
                list: {
                        all: {prompt: "All tiddlers in alphabetical order"},
                        missing: {prompt: "Tiddlers that have links to them but are not defined"},
                        orphans: {prompt: "Tiddlers that are not linked to from any other tiddlers"}
                        },
                closeAll: {label: "close all", prompt: "Close all displayed tiddlers (except any that are being edited)"},
                permaview: {label: "permaview", prompt: "Link to an URL that retrieves all the currently displayed tiddlers"},
                saveChanges: {label: "save changes", prompt: "Save all tiddlers to create a new TiddlyWiki"},
                slider: {},
                option: {},
                newTiddler: {label: "new tiddler", prompt: "Create a new tiddler", title: "New Tiddler"},
                newJournal: {label: "new journal", prompt: "Create a new tiddler from the current date and time"},
                sparkline: {},
                tabs: {}
                }
};

// ---------------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------------

// TiddlyWiki storage
var store = new TiddlyWiki();

// Animation engine
var anim = new Animator();

var readOnly = false;

// Starting up
function main()
{
        readOnly = (document.location.toString().substr(0,7) == "http://") ? config.options.chkHttpReadOnly : false;
        setupRegexp();
        saveTest();
        loadOptionsCookie();
        var s;
        for(s in config.notifyNamedTiddlers)
                store.addNotification(s,config.notifyNamedTiddlers[s]);
        for(s=0; s<config.notifyTiddlers.length; s++)
                store.addNotification(null,config.notifyTiddlers[s]);
        store.loadFromDiv("storeArea","store");
        loadSystemConfig();
        store.notifyAll();
        var start = store.getTiddlerText("DefaultTiddlers");
        if(window.location.hash)
                displayTiddlers(null,(decodeURI(window.location.hash.substr(1))),1,null,null);
        else if(start)
                displayTiddlers(null,start,1,null,null);
}

function saveTest()
{
        var saveTest = document.getElementById("saveTest");
        if(saveTest.hasChildNodes())
                alert(config.messages.savedSnapshotError);
        saveTest.appendChild(document.createTextNode("savetest"));
}

function loadSystemConfig()
{
        var configTiddlers = store.getTaggedTiddlers("systemConfig");
        for(var t=0; t<configTiddlers.length; t++)
                {
                var ex = processConfig(configTiddlers[t].text);
                if(ex)
                        displayMessage(config.messages.customConfigError.format([ex]));
                }
}

// ---------------------------------------------------------------------------------
// Macro definitions
// ---------------------------------------------------------------------------------

config.macros.today.handler = function(place)
{
        createTiddlyElement(place,"span",null,null,(new Date()).toLocaleString());
}

config.macros.version.handler = function(place)
{
        createTiddlyElement(place,"span",null,null,version.major + "." + version.minor + "." + version.revision);
}

config.macros.list.handler = function(place,macroName,params)
{
        var type = params[0] ? params[0] : "all";
        var theList = document.createElement("ul");
        place.appendChild(theList);
        if(this[type].prompt)
                createTiddlyElement(theList,"li",null,"listTitle",this[type].prompt);
        var results;
        if(this[type].handler)
                results = this[type].handler(params);
        for (t = 0; t < results.length; t++)
                {
                theListItem = document.createElement("li")
                theList.appendChild(theListItem);
                if(typeof results[t] == "string")
                        createTiddlyLink(theListItem,results[t],true);
                else
                        createTiddlyLink(theListItem,results[t].title,true);
                }
}

config.macros.list.all.handler = function(params)
{
        return store.reverseLookup("tags","excludeLists",false,"title");
}

config.macros.list.missing.handler = function(params)
{
        return store.getMissingLinks();
}

config.macros.list.orphans.handler = function(params)
{
        return store.getOrphans();
}

config.macros.allTags.handler = function(place,macroName,params)
{
        var tags = store.getTags();
        var theDateList = createTiddlyElement(place,"ul",null,null,null);
        if(tags.length == 0)
                createTiddlyElement(theDateList,"li",null,"listTitle",this.noTags);
        for (t=0; t<tags.length; t++)
                {
                var theListItem =createTiddlyElement(theDateList,"li",null,null,null);
                var theTag = createTiddlyButton(theListItem,tags[t][0] + " (" + tags[t][1] + ")",this.tooltip.format([tags[t][0]]),onClickTag);
                theTag.setAttribute("tag",tags[t][0]);
                }
}

config.macros.timeline.handler = function(place,macroName,params)
{
        var tiddlers = store.reverseLookup("tags","excludeLists",false,"modified");
        var lastDay = "";
        for (t=tiddlers.length-1; t>=0; t--)
                {
                var tiddler = tiddlers[t];
                var theDay = tiddler.modified.convertToYYYYMMDDHHMM().substr(0,8);
                if(theDay != lastDay)
                        {
                        var theDateList = document.createElement("ul");
                        place.appendChild(theDateList);
                        createTiddlyElement(theDateList,"li",null,"listTitle",tiddler.modified.formatString(this.dateFormat));
                        lastDay = theDay;
                        }
                var theDateListItem = createTiddlyElement(theDateList,"li",null,"listLink",null);
                theDateListItem.appendChild(createTiddlyLink(place,tiddler.title,true));
                }
}

config.macros.search.handler = function(place,macroName,params)
{
        var lastSearchText = "";
        var searchTimeout = null;
        var doSearch = function(txt)
                {
                closeAllTiddlers();
                var matches = store.search(txt.value,config.options.chkCaseSensitiveSearch,config.options.chkRegExpSearch,"title","excludeSearch");
                for(var t=matches.length-1; t>=0; t--)
                        displayTiddler(null,matches[t].title,0,txt.value,config.options.chkCaseSensitiveSearch,false,false);
                var q = config.options.chkRegExpSearch ? "/" : "'";
                if(matches.length > 0)
                        displayMessage(config.macros.search.successMsg.format([matches.length.toString(),q + txt.value + q]));
                else
                        displayMessage(config.macros.search.failureMsg.format([q + txt.value + q]));
                lastSearchText = txt.value;
                };
        var clickHandler = function(e)
                {
                doSearch(this.nextSibling);
                };
        var keyHandler = function(e)
                {
                if (!e) var e = window.event;
                switch(e.keyCode)
                        {
                        case 27:
                                this.value = "";
                                clearMessage();
                                break;
                        }
                if((this.value.length > 2) && (this.value != lastSearchText))
                        {
                        if(searchTimeout)
                                clearTimeout(searchTimeout);
                        var txt = this;
                        searchTimeout = setTimeout(function() {doSearch(txt);},200);
                        }
                };
        var focusHandler = function(e)
                {
                this.select();
                };
        var btn = createTiddlyButton(place,this.label,this.prompt,clickHandler);
        var txt = createTiddlyElement(place,"input",null,null,null);
        if(params[0])
                txt.value = params[0];
        txt.onkeyup = keyHandler;
        txt.onfocus = focusHandler;
        txt.setAttribute("size",this.sizeTextbox);
        txt.setAttribute("autocomplete","off");
        if(navigator.userAgent.toLowerCase().indexOf("safari") == -1)
                txt.setAttribute("type","text");
        else
                {
                txt.setAttribute("type","search");
                txt.setAttribute("results","5");
                }
}

config.macros.tiddler.handler = function(place,macroName,params)
{
        var wrapper = createTiddlyElement(place,"span",null,params[1] ? params[1] : null,null);
        var text = store.getTiddlerText(params[0]);
        if(text)
                wikify(text,wrapper,null,null);
}

config.macros.tag.handler = function(place,macroName,params)
{
        createTagButton(place,params[0]);
}

config.macros.closeAll.handler = function(place)
{
        createTiddlyButton(place,this.label,this.prompt,closeAllTiddlers);
}

config.macros.permaview.handler = function(place)
{
        createTiddlyButton(place,this.label,this.prompt,onClickPermaView);
}

config.macros.saveChanges.handler = function(place)
{
        if(!readOnly)
                createTiddlyButton(place,this.label,this.prompt,saveChanges);
}

config.macros.slider.onClickSlider = function(e)
{
        if (!e) var e = window.event;
        var n = this.nextSibling;
        var cookie = n.getAttribute("cookie");
        var isOpen = n.style.display != "none";
        if(config.options.chkAnimate)
                anim.startAnimating(new Slider(n,!isOpen,e.shiftKey || e.altKey,"none"));
        else
                n.style.display = isOpen ? "none" : "block";
        config.options[cookie] = !isOpen;
        saveOptionCookie(cookie);
}

config.macros.slider.handler = function(place,macroName,params)
{
        var cookie = params[0] ? params[0] : "";
        var text = store.getTiddlerText(params[1]);
        var btn = createTiddlyButton(place,params[2],params[3],this.onClickSlider);
        var panel = createTiddlyElement(place,"div",null,"sliderPanel",null);
        panel.setAttribute("cookie",cookie);
        panel.style.display = config.options[cookie] ? "block" : "none";
        if(text)
                wikify(text,panel,null,null);
}

config.macros.option.onChangeOption = function(e)
{
        var opt = this.getAttribute("option");
        var elementType,valueField;
        if(opt)
                {
                switch(opt.substr(0,3))
                        {
                        case "txt":
                                elementType = "input";
                                valueField = "value";
                                break;
                        case "chk":
                                elementType = "input";
                                valueField = "checked";
                                break;
                        }
                config.options[opt] = this[valueField];
                saveOptionCookie(opt);
                var nodes = document.getElementsByTagName(elementType);
                for(var t=0; t<nodes.length; t++)
                        {
                        var optNode = nodes[t].getAttribute("option");
                        if(opt == optNode)
                                nodes[t][valueField] = this[valueField];
                        }
                }
        return(true);
}

config.macros.option.handler = function(place,macroName,params)
{
        var opt = params[0];
        if(config.options[opt] == undefined)
                return;
        var c;
        switch(opt.substr(0,3))
                {
                case "txt":
                        c = document.createElement("input");
                        c.onkeyup = this.onChangeOption;
                        c.setAttribute("option",opt);
                        c.size = 15;
                        c.value = config.options[opt];
                        place.appendChild(c);
                        break;
                case "chk":
                        c = document.createElement("input");
                        c.setAttribute("type","checkbox");
                        c.onclick = this.onChangeOption;
                        c.setAttribute("option",opt);
                        c.checked = config.options[opt];
                        place.appendChild(c);
                        break;
                }
}

config.macros.newTiddler.onClick = function()
{
        displayTiddler(null,config.macros.newTiddler.title,2,null,null,false,false);
        var e = document.getElementById("editorTitle" + config.macros.newTiddler.title);
        e.focus();
        e.select();
}

config.macros.newTiddler.handler = function(place)
{
        if(!readOnly)
                createTiddlyButton(place,this.label,this.prompt,this.onClick);
}

config.macros.newJournal.handler = function(place,macroName,params)
{
        if(!readOnly)
                {
                var now = new Date();
                var title = now.formatString(params[0].trim());
                var createJournal = function() {
                        displayTiddler(null,title,2,null,null,false,false);
                        var tagsBox = document.getElementById("editorTags" + title);
                        if(tagsBox && params[1])
                                tagsBox.value += " " + String.encodeTiddlyLink(params[1]);
                        };
                createTiddlyButton(place,this.label,this.prompt,createJournal);
                }
}

config.macros.sparkline.handler = function(place,macroName,params)
{
        var data = [];
        var min = 0;
        var max = 0;
        for(var t=0; t<params.length; t++)
                {
                var v = parseInt(params[t]);
                if(v < min)
                        min = v;
                if(v > max)
                        max = v;
                data.push(v);
                }
        if(data.length < 1)
                return;
        var box = createTiddlyElement(place,"span",null,"sparkline",String.fromCharCode(160));
        box.title = data.join(",");
        var w = box.offsetWidth;
        var h = box.offsetHeight;
        box.style.paddingRight = (data.length * 2 - w) + "px";
        box.style.position = "relative";
        for(var d=0; d<data.length; d++)
                {
                var tick = document.createElement("img");
                tick.border = 0;
                tick.className = "sparktick";
                tick.style.position = "absolute";
                tick.src = "data:image/gif,GIF89a%01%00%01%00%91%FF%00%FF%FF%FF%00%00%00%C0%C0%C0%00%00%00!%F9%04%01%00%00%02%00%2C%00%00%00%00%01%00%01%00%40%02%02T%01%00%3B";
                tick.style.left = d*2 + "px";
                tick.style.width = "2px";
                var v = Math.floor(((data[d] - min)/(max-min)) * h);
                tick.style.top = (h-v) + "px";
                tick.style.height = v + "px";
                box.appendChild(tick);
                }
}

config.macros.tabs.handler = function(place,macroName,params)
{
        var cookie = params[0];
        var numTabs = (params.length-1)/3;
        var wrapper = createTiddlyElement(place,"div",null,cookie,null);
        var tabset = createTiddlyElement(wrapper,"div",null,"tabset",null);
        tabset.setAttribute("cookie",cookie);
        var validTab = false;
        for(var t=0; t<numTabs; t++)
                {
                var label = params[t*3+1];
                var prompt = params[t*3+2];
                var content = params[t*3+3];
                var tab = createTiddlyButton(tabset,label,prompt,this.onClickTab,"tab tabUnselected");
                tab.setAttribute("href","javascript:;");
                tab.onclick = this.onClickTab;
                tab.setAttribute("tab",label);
                tab.setAttribute("content",content);
                tab.title = prompt;
                if(config.options[cookie] == label)
                        validTab = true;
                }
        if(!validTab)
                config.options[cookie] = params[1];
        this.switchTab(tabset,config.options[cookie]);
}

config.macros.tabs.onClickTab = function(e)
{
        config.macros.tabs.switchTab(this.parentNode,this.getAttribute("tab"));
}

config.macros.tabs.switchTab = function(tabset,tab)
{
        var cookie = tabset.getAttribute("cookie");
        var theTab = null
        var nodes = tabset.childNodes;
        for(var t=0; t<nodes.length; t++)
                if(nodes[t].getAttribute && nodes[t].getAttribute("tab") == tab)
                        {
                        theTab = nodes[t];
                        theTab.className = "tab tabSelected";
                        }
                else
                        nodes[t].className = "tab tabUnselected"
        if(theTab)
                {
                if(tabset.nextSibling && tabset.nextSibling.className == "tabContents")
                        tabset.parentNode.removeChild(tabset.nextSibling);
                var tabContent = createTiddlyElement(null,"div",null,"tabContents",null);
                tabset.parentNode.insertBefore(tabContent,tabset.nextSibling);
                wikify(store.getTiddlerText(theTab.getAttribute("content")),tabContent,null,null);
                if(cookie)
                        {
                        config.options[cookie] = tab;
                        saveOptionCookie(cookie);
                        }
                }
}

// ---------------------------------------------------------------------------------
// Config and macro stuff
// ---------------------------------------------------------------------------------

// Merge a custom configuration over the top of the current configuration
// Returns a string error message or null if it went OK
function processConfig(customConfig)
{
        try
                {
                if(customConfig && customConfig != "")
                        window.eval(customConfig);
                }
        catch(e)
                {
                return(e.toString());
                }
        return null;
}

// Render a macro
function insertMacro(place,macroName,macroParams)
{
        var params = macroParams.readMacroParams();
        try
                {
                var macro = config.macros[macroName];
                if(macro && macro.handler)
                        macro.handler(place,macroName,params);
                else
                        createTiddlyElement(place,"span",null,"errorNoSuchMacro","<<" + macroName + ">>");
                }
        catch(e)
                {
                displayMessage(config.messages.macroError.format([macroName]));
                displayMessage(e.toString());
                }
}

// ---------------------------------------------------------------------------------
// Tiddler() object
// ---------------------------------------------------------------------------------

function Tiddler()
{
        this.title = null;
        this.text = null;
        this.modifier = null;
        this.modified = new Date();
        this.links = [];
        this.tags = [];
        return this;
}

// Load a tiddler from an HTML DIV
Tiddler.prototype.loadFromDiv = function(divRef,title)
{
        var text = Tiddler.unescapeLineBreaks(divRef.firstChild ? divRef.firstChild.nodeValue : "");
        var modifier = divRef.getAttribute("modifier");
        var modified = Date.convertFromYYYYMMDDHHMM(divRef.getAttribute("modified"));
        var tags = divRef.getAttribute("tags");
        this.set(title,text,modifier,modified,tags);
        return this;
}

// Format the text for storage in an HTML DIV
Tiddler.prototype.saveToDiv = function()
{
        return '<div tiddler="' + this.title + '" modified="' +
                                                        this.modified.convertToYYYYMMDDHHMM() + '" modifier="' + this.modifier +
                                                        '" tags="' + this.getTags() + '">' +
                                                        this.escapeLineBreaks().htmlEncode() + '</div>';
}

// Format the text for storage in an RSS item
Tiddler.prototype.saveToRss = function(url)
{
        var s = [];
        s.push("<item>");
        s.push("<title>" + this.title.htmlEncode() + "</title>");
        s.push("<description>" + this.text.replace(regexpNewLine,"<br />").htmlEncode() + "</description>");
        for(var t=0; t<this.tags.length; t++)
                s.push("<category>" + this.tags[t] + "</category>");
        s.push("<link>" + url + "#" + encodeURIComponent(String.encodeTiddlyLink(this.title)) + "</link>");
        s.push("<pubDate>" + this.modified.toGMTString() + "</pubDate>");
        s.push("</item>");
        return(s.join("\n"));
}

// Change the text and other attributes of a tiddler
Tiddler.prototype.set = function(title,text,modifier,modified,tags)
{
        if(title != undefined)
                this.title = title;
        if(text != undefined)
                this.text = text;
        if(modifier != undefined)
                this.modifier = modifier;
        if(modified != undefined)
                this.modified = modified;
        if(tags != undefined)
                this.tags = (typeof tags == "string") ? tags.readBracketedList() : tags;
        else
                this.tags = [];
        this.changed();
        return this;
}

// Get the tags for a tiddler as a string (space delimited, using [[brackets]] for tags containing spaces)
Tiddler.prototype.getTags = function()
{
        if(this.tags)
                {
                var results = [];
                for(var t=0; t<this.tags.length; t++)
                        results.push(String.encodeTiddlyLink(this.tags[t]));
                return results.join(" ");
                }
        else
                return "";
}

var regexpBackSlashEn = new RegExp("\\\\n","mg");
var regexpBackSlash = new RegExp("\\\\","mg");
var regexpBackSlashEss = new RegExp("\\\\s","mg");
var regexpNewLine = new RegExp("\n","mg");
var regexpCarriageReturn = new RegExp("\r","mg");

// Static method to Convert "\n" to newlines, "\s" to "\"
Tiddler.unescapeLineBreaks = function(text)
{
        if(text && text != "")
                return text.replace(regexpBackSlashEn,"\n").replace(regexpBackSlashEss,"\\").replace(regexpCarriageReturn,"");
        else
                return "";
}

// Convert newlines to "\n", "\" to "\s"
Tiddler.prototype.escapeLineBreaks = function()
{
        return this.text.replace(regexpBackSlash,"\\s").replace(regexpNewLine,"\\n").replace(regexpCarriageReturn,"");
}

// Updates the secondary information (like links[] array) after a change to a tiddler
Tiddler.prototype.changed = function()
{
        this.links = [];
        var nextPos = 0;
        var theLink;
        do {
                var formatMatch = wikiNameRegExp.exec(this.text);
                if(formatMatch)
                        {
                        if(!formatMatch[1] && formatMatch[2] && formatMatch[2] != this.title)
                                this.links.pushUnique(formatMatch[2]);
                        else if(formatMatch[4] && store.tiddlers[formatMatch[5]] != undefined)
                                this.links.pushUnique(formatMatch[5]);
                        else if(formatMatch[6] && formatMatch[6] != this.title)
                                this.links.pushUnique(formatMatch[6]);
                        }
        } while(formatMatch);
        return;
}

Tiddler.prototype.getSubtitle = function()
{
        var theModifier = this.modifier;
        if(!theModifier)
                theModifier = config.messages.subtitleUnknown;
        var theModified = this.modified;
        if(theModified)
                theModified = theModified.toLocaleString();
        else
                theModified = config.messages.subtitleUnknown;
        return(theModifier + ", " + theModified);
}

// ---------------------------------------------------------------------------------
// TiddlyWiki() object contains Tiddler()s
// ---------------------------------------------------------------------------------

function TiddlyWiki()
{
        this.tiddlers = {}; // Hashmap by name of tiddlers
        this.namedNotifications = {}; // Hashmap by name of array of notification functions
        this.blanketNotifications = []; // Array of blanket notifications to be invoked on any change
        this.dirty = false;
}

// Set the dirty flag
TiddlyWiki.prototype.setDirty = function(dirty)
{
        this.dirty = dirty;
}

// Invoke the notification handlers for a particular tiddler
TiddlyWiki.prototype.notify = function(title,doBlanket)
{
        var notification = this.namedNotifications[title];
        if(notification)
                for(var t=0; t<notification.length; t++)
                        notification[t](title);
        if(doBlanket)
                for(var n=0; n<this.blanketNotifications.length; n++)
                        this.blanketNotifications[n](title);
}

// Invoke the notification handlers for all tiddlers
TiddlyWiki.prototype.notifyAll = function()
{
        var notifyTitle;
        for(notifyTitle in this.tiddlers)
                this.notify(notifyTitle,false);
        for(notifyTitle in config.shadowTiddlers)
                if(this.tiddlers[notifyTitle] == undefined)
                        this.notify(notifyTitle,false);
        for(var n=0; n<this.blanketNotifications.length; n++)
                this.blanketNotifications[n]();
}

// Add a notification handler to a tiddler
TiddlyWiki.prototype.addNotification = function(title,fn)
{
        var notification;
        if(title)
                {
                notification = this.namedNotifications[title];
                if(!notification)
                        {
                        notification = [];
                        this.namedNotifications[title] = notification;
                        }
                }
        else
                notification = this.blanketNotifications;
        notification.push(fn);
        return this;
}

// Clear a TiddlyWiki so that it contains no tiddlers
TiddlyWiki.prototype.clear = function(src)
{
        this.tiddlers = {};
        this.dirty = false;
}

TiddlyWiki.prototype.removeTiddler = function(title)
{
        var tiddler = this.tiddlers[title];
        if(tiddler)
                {
                delete this.tiddlers[title];
                this.notify(title,true);
                this.dirty = true;
                }
}

TiddlyWiki.prototype.getTiddlerText = function(title,defaultText)
{
        if(!title)
                return(defaultText);
        var tiddler = this.tiddlers[title];
        if(tiddler)
                return tiddler.text;
        else if(config.shadowTiddlers[title])
                return config.shadowTiddlers[title];
        else if(defaultText)
                return defaultText;
        else
                return null;
}

TiddlyWiki.prototype.getRecursiveTiddlerText = function(title,defaultText,ignoreList)
{
        var childIgnoreList = ignoreList ? ignoreList : {};
        childIgnoreList[title] = true;
        var bracketRegExp = new RegExp("(?:\\[\\[([^\\]]+)\\]\\])","mg");
        var text = this.getTiddlerText(title,defaultText);
        if(text == null)
                return "";
        var textOut = [];
        var lastPos = 0;
        do {
                var match = bracketRegExp.exec(text);
                if(match)
                        {
                        textOut.push(text.substr(lastPos,match.index-lastPos));
                        if(match[1])
                                {
                                if(childIgnoreList[match[1]])
                                        textOut.push(match[1]);
                                else
                                        {
                                        var subText = this.getRecursiveTiddlerText(match[1],match[1],childIgnoreList);
                                        textOut.push(subText);
                                        }
                                }
                        lastPos = match.index + match[1].length + 4;
                        }
                else
                        textOut.push(text.substr(lastPos));
        } while(match);
        delete childIgnoreList[title];
        return(textOut.join(""));
}

TiddlyWiki.prototype.saveTiddler = function(title,newTitle,newBody,modifier,modified,tags)
{
        var tiddler = this.tiddlers[title];
        if(tiddler)
                delete this.tiddlers[title];
        else
                tiddler = new Tiddler();
        tiddler.set(newTitle,newBody,modifier,modified,tags);
        this.tiddlers[newTitle] = tiddler;
        if(title != newTitle)
                this.notify(title,true);
        this.notify(newTitle,true);
        this.dirty = true;
        return tiddler;
}

TiddlyWiki.prototype.createTiddler = function(title)
{
        tiddler = this.tiddlers[title];
        if(!tiddler)
                {
                tiddler = new Tiddler();
                this.tiddlers[title] = tiddler;
                this.dirty = true;
                }
        return tiddler;
}

// Load contents of a tiddlywiki from an HTML DIV
TiddlyWiki.prototype.loadFromDiv = function(srcID,idPrefix)
{
        if(document.normalize)
                document.normalize();
        var lenPrefix = idPrefix.length;
        var store = document.getElementById(srcID).childNodes;
        for(var t = 0; t < store.length; t++)
                {
                var e = store[t];
                var title = null;
                if(e.getAttribute)
                        title = e.getAttribute("tiddler");
                if(!title && e.id && e.id.substr(0,lenPrefix) == idPrefix)
                        title = e.id.substr(lenPrefix);
                if(title && title != "")
                        {
                        var tiddler = this.createTiddler(title);
                        tiddler.loadFromDiv(e,title);
                        }
                }
        this.dirty = false;
}

// Return an array of tiddlers matching a search string
TiddlyWiki.prototype.search = function(searchText,caseSensitive,useRegExp,sortField,excludeTag)
{
        if (!useRegExp)
                searchText = searchText.escapeRegExp();
        var regExp = new RegExp(searchText,caseSensitive ? "m" : "im");
        var candidates = this.reverseLookup("tags",excludeTag,false);
        var results = [];
        for(var t=0; t<candidates.length; t++)
                {
                if(regExp.test(candidates[t].title) || regExp.test(candidates[t].text))
                        results.push(candidates[t]);
                }
        if(!sortField)
                sortField = "title";
        results.sort(function (a,b) {if(a[sortField] == b[sortField]) return(0); else return (a[sortField] < b[sortField]) ? -1 : +1; });
        return results;
}

// Return an array of all the tags in use. Each member of the array is another array where [0] is the name of the tag and [1] is the number of occurances
TiddlyWiki.prototype.getTags = function()
{
        var results = [];
        for(var t in this.tiddlers)
                {
                var tiddler = this.tiddlers[t];
                for(g=0; g<tiddler.tags.length; g++)
                        {
                        var tag = tiddler.tags[g];
                        var f = false;
                        for(var c=0; c<results.length; c++)
                                if(results[c][0] == tag)
                                        {
                                        f = true;
                                        results[c][1]++;
                                        }
                        if(!f)
                                results.push([tag,1]);
                        }
                }
        results.sort(function (a,b) {if(a[0] == b[0]) return(0); else return (a[0] < b[0]) ? -1 : +1; });
        return results;
}

// Return an array of the tiddlers that are tagged with a given tag
TiddlyWiki.prototype.getTaggedTiddlers = function(tag,sortField)
{
        return this.reverseLookup("tags",tag,true,sortField);
}

// Return an array of the tiddlers that link to a given tiddler
TiddlyWiki.prototype.getReferringTiddlers = function(title,exclude,sortField)
{
        return this.reverseLookup("links",title,true,sortField);
}

// Return an array of the tiddlers that do or do not have a specified entry in the specified storage array (ie, "links" or "tags")
// lookupMatch == true to match tiddlers, false to exclude tiddlers
TiddlyWiki.prototype.reverseLookup = function(lookupField,lookupValue,lookupMatch,sortField)
{
        var results = [];
        for(var t in this.tiddlers)
                {
                var tiddler = this.tiddlers[t];
                var f = !lookupMatch;
                for(var lookup=0; lookup<tiddler[lookupField].length; lookup++)
                        if(tiddler[lookupField][lookup] == lookupValue)
                                f = lookupMatch;
                if(f)
                        results.push(tiddler);
                }
        if(!sortField)
                sortField = "title";
        results.sort(function (a,b) {if(a[sortField] == b[sortField]) return(0); else return (a[sortField] < b[sortField]) ? -1 : +1; });
        return results;
}

// Return the tiddlers as a sorted array
TiddlyWiki.prototype.getTiddlers = function(field)
{
        var results = [];
        for(var t in this.tiddlers)
                results.push(this.tiddlers[t]);
        if(field)
                results.sort(function (a,b) {if(a[field] == b[field]) return(0); else return (a[field] < b[field]) ? -1 : +1; });
        return results;
}

// Return array of names of tiddlers that are referred to but not defined
TiddlyWiki.prototype.getMissingLinks = function(sortField)
{
        var results = [];
        for(var t in this.tiddlers)
                {
                var tiddler = this.tiddlers[t];
                for(var n=0; n<tiddler.links.length;n++)
                        {
                        var link = tiddler.links[n];
                        if(this.tiddlers[link] == null)
                                results.pushUnique(link);
                        }
                }
        results.sort();
        return results;
}

// Return an array of names of tiddlers that are defined but not referred to
TiddlyWiki.prototype.getOrphans = function()
{
        var results = [];
        for(var t in this.tiddlers)
                if(this.getReferringTiddlers(t).length == 0)
                        results.push(t);
        results.sort();
        return results;
}

// ---------------------------------------------------------------------------------
// Tiddler functions
// ---------------------------------------------------------------------------------

// Display several tiddlers from a list of space separated titles
function displayTiddlers(src,titles,state,highlightText,highlightCaseSensitive,animate,slowly)
{
        var tiddlerNames = titles.readBracketedList();
        for(var t = tiddlerNames.length-1;t>=0;t--)
                displayTiddler(src,tiddlerNames[t],state,highlightText,highlightCaseSensitive,animate,slowly);
}

// Display a tiddler with animation and scrolling, as though a link to it has been clicked on
//      src = source element object (eg link) for animation effects and positioning
//      title = title of tiddler to display
//      state = 0 is default or current state, 1 is read only and 2 is edittable
//      highlightText = text to highlight in the displayed tiddler
//      highlightCaseSensitive = flag for whether the highlight text is case sensitive
function displayTiddler(src,title,state,highlightText,highlightCaseSensitive,animate,slowly)
{
        var place = document.getElementById("tiddlerDisplay");
        var after = findContainingTiddler(src); // Which tiddler this one will be positioned after
        var before;
        if(after == null)
                before = place.firstChild;
        else if(after.nextSibling)
                before = after.nextSibling;
        else
                before = null;
        var theTiddler = createTiddler(place,before,title,state,highlightText,highlightCaseSensitive);
        if(src)
                {
                if(config.options.chkAnimate && (animate == undefined || animate == true))
                        anim.startAnimating(new Zoomer(title,src,theTiddler,slowly),new Scroller(theTiddler,slowly));
                else
                        window.scrollTo(0,ensureVisible(theTiddler));
                }
}

// Create a tiddler if it doesn't exist (with no fancy animating)
//      place = parent element
//      before = node before which to create/move the tiddler
//      title = title of tiddler to display
//      state = 0 is default or current state, 1 is read only and 2 is edittable
//      highlightText = text to highlight in the displayed tiddler
//      highlightCaseSensitive = flag for whether the highlight text is case sensitive
function createTiddler(place,before,title,state,highlightText,highlightCaseSensitive)
{
        var theTiddler = createTiddlerSkeleton(place,before,title);
        createTiddlerTitle(title,highlightText,highlightCaseSensitive);
        var theViewer = document.getElementById("viewer" + title);
        var theEditor = document.getElementById("editorWrapper" + title);
        switch(state)
                {
                case 0:
                        if(!theViewer && !theEditor)
                                {
                                createTiddlerToolbar(title,false);
                                createTiddlerViewer(title,highlightText,highlightCaseSensitive);
                                createTiddlerFooter(title,false);
                                }
                        break;
                case 1: // Viewer
                        if(theViewer)
                                theViewer.parentNode.removeChild(theViewer);
                        if(theEditor)
                                theEditor.parentNode.removeChild(theEditor);
                        createTiddlerToolbar(title,false);
                        createTiddlerViewer(title,highlightText,highlightCaseSensitive);
                        createTiddlerFooter(title,false);
                        break;
                case 2: // Editor
                        if(!theEditor)
                                {
                                if(theViewer)
                                        theViewer.parentNode.removeChild(theViewer);
                                createTiddlerToolbar(title,true);
                                createTiddlerEditor(title);
                                createTiddlerFooter(title,true);
                                }
                        break;
                }
        return(theTiddler);
}

function refreshTiddler(title)
{
        var theViewer = document.getElementById("viewer" + title);
        if(theViewer)
                {
                theViewer.parentNode.removeChild(theViewer);
                createTiddlerViewer(title,null,null);
                }
}

function createTiddlerSkeleton(place,before,title)
{
        var theTiddler = document.getElementById("tiddler" + title);
        if(!theTiddler)
                {
                theTiddler = createTiddlyElement(null,"div","tiddler" + title,"tiddler",null);
                theTiddler.onmouseover = onMouseOverTiddler;
                theTiddler.onmouseout = onMouseOutTiddler;
                theTiddler.ondblclick = onDblClickTiddler;
                var theInnerTiddler = createTiddlyElement(theTiddler,"div",null,"unselectedTiddler",null);
                var theToolbar = createTiddlyElement(theInnerTiddler,"div","toolbar" + title,"toolbar", null);
                var theTitle = createTiddlyElement(theInnerTiddler,"div","title" + title,"title",null);
                var theBody = createTiddlyElement(theInnerTiddler,"div","body" + title,"body",null);
                var theFooter = createTiddlyElement(theInnerTiddler,"div","footer" + title,"footer",null);
                place.insertBefore(theTiddler,before);
                }
        return(theTiddler);
}

function createTiddlerTitle(title,highlightText,highlightCaseSensitive)
{
        var theTitle = document.getElementById("title" + title);
        if(theTitle)
                {
                removeChildren(theTitle);
                if(highlightText == "")
                        highlightText = null;
                var highlightRegExp,highlightMatch;
                if(highlightText)
                        {
                        highlightRegExp = new RegExp(highlightText,highlightCaseSensitive ? "mg" : "img");
                        highlightMatch = highlightRegExp.exec(title);
                        }
                highlightMatch = subWikify(theTitle,title,0,title.length,highlightRegExp,highlightMatch);
                var tiddler = store.tiddlers[title];
                if(tiddler)
                        theTitle.title = tiddler.getSubtitle();
                }
}

// Create a tiddler toolbar according to whether it's an editor or not
function createTiddlerToolbar(title,isEditor)
{
        var theToolbar = document.getElementById("toolbar" + title);
        var lingo = config.views;
        if(theToolbar)
                {
                removeChildren(theToolbar);
                insertSpacer(theToolbar);
                if(isEditor)
                        {
                        // Editor toolbar
                        lingo = lingo.editor;
                        createTiddlyButton(theToolbar,lingo.toolbarDone.text,lingo.toolbarDone.tooltip,onClickToolbarSave);
                        insertSpacer(theToolbar);
                        createTiddlyButton(theToolbar,lingo.toolbarCancel.text,lingo.toolbarCancel.tooltip,onClickToolbarUndo);
                        /*
                        insertSpacer(theToolbar);
                        createTiddlyButton(theToolbar,lingo.toolbarDelete.text,lingo.toolbarDelete.tooltip,onClickToolbarDelete);
                        */
                        }
                else
                        {
                        // Viewer toolbar
                        lingo = lingo.wikified;
                        createTiddlyButton(theToolbar,lingo.toolbarClose.text,lingo.toolbarClose.tooltip,onClickToolbarClose);
                        insertSpacer(theToolbar);
                        if(!readOnly)
                                {
                                createTiddlyButton(theToolbar,lingo.toolbarEdit.text,lingo.toolbarEdit.tooltip,onClickToolbarEdit);
                                insertSpacer(theToolbar);
                                }
                        createTiddlyButton(theToolbar,lingo.toolbarPermalink.text,lingo.toolbarPermalink.tooltip,onClickToolbarPermaLink);
                        insertSpacer(theToolbar);
                        createTiddlyButton(theToolbar,lingo.toolbarReferences.text,lingo.toolbarReferences.tooltip,onClickToolbarReferences);
                        }
                insertSpacer(theToolbar);
                }
}

function createTiddlerPopup(srcElement)
{
        var popup = document.getElementById("popup");
        if(popup && popup.nextSibling == srcElement)
                {
                hideTiddlerPopup();
                return null;
                }
        if(popup)
                popup.parentNode.removeChild(popup);
        popup = createTiddlyElement(null,"div","popup",null,null);
        var leftPx = srcElement.offsetLeft;
        var topPx = srcElement.offsetTop;
        var heightPx = srcElement.offsetHeight;
        if (leftPx <= 1 && srcElement.parentNode.offsetLeft > 0)
                leftPx = srcElement.parentNode.offsetLeft;
        if (topPx <= 1 && srcElement.parentNode.offsetTop > 0)
                topPx = srcElement.parentNode.offsetTop;
        if (heightPx <= 1 && srcElement.parentNode.offsetHeight > 0)
                heightPx = srcElement.parentNode.offsetHeight;
        popup.style.left = leftPx + "px";
        popup.style.top = topPx + heightPx + "px";
        popup.style.display = "block";
        srcElement.onmouseout = onMouseOutTiddlerPopup;
        srcElement.appendChild(popup);
        return popup;
}

function scrollToTiddlerPopup(popup,slowly)
{
        if(config.options.chkAnimate)
                anim.startAnimating(new Scroller(popup,slowly));
        else
                window.scrollTo(0,ensureVisible(popup));
}

function onMouseOutTiddlerPopup(e)
{
        if (!e) var e = window.event;
        var related = (e.relatedTarget) ? e.relatedTarget : e.toElement;
        try
                {
                while (related != this && related && related.nodeName && related.nodeName.toLowerCase() != "body")
                        related = related.parentNode;
                }
        catch(e)
                {
                related = null;
                }
        if(related != this)
                {
                this.onmouseout = null;
                hideTiddlerPopup();
                }
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

function hideTiddlerPopup()
{
        var popup = document.getElementById("popup");
        if(popup)
                popup.parentNode.removeChild(popup);
}

// Create the body section of a read-only tiddler
function createTiddlerViewer(title,highlightText,highlightCaseSensitive,htmlElement)
{
        var theBody = document.getElementById("body" + title);
        if(theBody)
                {
                var tiddler = store.tiddlers[title];
                var tiddlerText = store.getTiddlerText(title);
                var theViewer = createTiddlyElement(theBody,htmlElement ? htmlElement : "div","viewer" + title,"viewer",null);
                if(tiddler)
                        theViewer.setAttribute("tags",tiddler.tags.join(" "));
                if(tiddlerText == null)
                        {
                        tiddlerText = config.views.wikified.defaultText.format([title]);
                        theViewer.style.fontStyle = "italic";
                        }
                wikify(tiddlerText,theViewer,highlightText,highlightCaseSensitive);
                }
}

// Create the footer section of a tiddler
function createTiddlerFooter(title,isEditor)
{
        var theFooter = document.getElementById("footer" + title);
        var tiddler = store.tiddlers[title];
        if(theFooter && tiddler)
                {
                removeChildren(theFooter);
                insertSpacer(theFooter);
                if(isEditor)
                        {
                        }
                else
                        {
                        var lingo = config.views.wikified.tag;
                        var prompt = tiddler.tags.length == 0 ? lingo.labelNoTags : lingo.labelTags;
                        var theTags = createTiddlyElement(theFooter,"div",null,null,prompt);
                        for(var t=0; t<tiddler.tags.length; t++)
                                {
                                var theTag = createTagButton(theTags,tiddler.tags[t],tiddler.title);
                                insertSpacer(theTags);
                                }
                        }
                }
}

// Create a button for a tag with a popup listing all the tiddlers that it tags
function createTagButton(place,tag,excludeTiddler)
{
        var theTag = createTiddlyButton(place,tag,config.views.wikified.tag.tooltip.format([tag]),onClickTag);
        theTag.setAttribute("tag",tag);
        if(excludeTiddler)
                theTag.setAttribute("tiddler",excludeTiddler);
        return(theTag);
}

// Create the body section of an edittable tiddler
function createTiddlerEditor(title)
{
        var theBody = document.getElementById("body" + title);
        if(theBody)
                {
                var tiddlerText = store.getTiddlerText(title);
                var tiddlerExists = (tiddlerText != null);
                if(!tiddlerExists)
                        tiddlerText = config.views.editor.defaultText.format([title]);
                var theEditor = createTiddlyElement(theBody,"div","editorWrapper" + title,"editor",null);
                theEditor.onkeypress = onEditKey;
                var theTitleBox = createTiddlyElement(theEditor,"input","editorTitle" + title,null,null);
                theTitleBox.setAttribute("type","text");
                theTitleBox.value = title;
                theTitleBox.setAttribute("size","40");
                var theBodyBox = createTiddlyElement(theEditor,"textarea","editorBody" + title,null,null);
                theBodyBox.value = tiddlerText;
                var rows = 10;
                var lines = tiddlerText.match(regexpNewLine);
                if(lines != null && lines.length > rows)
                        rows = lines.length + 5;
                theBodyBox.setAttribute("rows",rows);
                var theTagsBox = createTiddlyElement(theEditor,"input","editorTags" + title,null,null);
                theTagsBox.setAttribute("type","text");
                var tiddler = store.tiddlers[title];
                theTagsBox.value = tiddler ? tiddler.getTags() : "";
                theTagsBox.setAttribute("size","40");
                var tagPrompt = createTiddlyElement(theEditor,"div",null,"editorFooter",config.views.editor.tagPrompt);
                insertSpacer(tagPrompt);
                var lingo = config.views.editor.tagChooser;
                var addTag = createTiddlyButton(tagPrompt,lingo.text,lingo.tooltip,onClickAddTag);
                addTag.setAttribute("tiddler",title);
                theBodyBox.focus();
                }
}

function saveTiddler(title)
{
        var titleBox = document.getElementById("editorTitle" + title);
        var newTitle = titleBox.value;
        if(store.tiddlers[newTitle])
                {
                if(newTitle != title && !confirm(config.messages.overwriteWarning.format([newTitle.toString()])))
                        {
                        titleBox.focus();
                        titleBox.select();
                        return;
                        }
                }
        var body = document.getElementById("editorBody" + title);
        var newBody = body.value;
        var newTags = document.getElementById("editorTags" + title).value;
        blurTiddler(title);
        store.saveTiddler(title,newTitle,newBody,config.options.txtUserName,new Date(),newTags);
        displayTiddler(null,newTitle,1,null,null,null,false,false);
        // Close the old tiddler if this is a rename
        if(title != newTitle)
                {
                var oldTiddler = document.getElementById("tiddler" + title);
                var newTiddler = document.getElementById("tiddler" + newTitle);
                oldTiddler.parentNode.replaceChild(newTiddler,oldTiddler);
                }
        if(config.options.chkAutoSave)
                saveChanges();
}

function selectTiddler(title)
{
        var e = document.getElementById("tiddler" + title);
        if(e != null)
                e.firstChild.className = "selectedTiddler";
}

function deselectTiddler(title)
{
        var e = document.getElementById("tiddler" + title);
        if(e != null)
                e.firstChild.className = "unselectedTiddler";
}

function blurTiddler(title)
{
        var body = document.getElementById("editorBody" + title);
        if(title)
                {
                body.focus();
                body.blur();
                }
}

function deleteTiddler(title)
{
 return;
        closeTiddler(title,false);
        store.removeTiddler(title);
        // Autosave
        if(config.options.chkAutoSave)
                saveChanges();
}

function closeTiddler(title,slowly)
{
        var tiddler = document.getElementById("tiddler" + title);
        if(tiddler != null)
                {
                scrubIds(tiddler);
                if(config.options.chkAnimate)
                        anim.startAnimating(new Slider(tiddler,false,slowly,"all"));
                else
                        tiddler.parentNode.removeChild(tiddler);
                }
}

function scrubIds(e)
{
        if(e.id)
                e.id = null;
        var children = e.childNodes;
        for(var t=0; t<children.length; t++)
                {
                var c = children[t];
                if(c.id)
                        c.id = null;
                }
}

function closeAllTiddlers()
{
        clearMessage();
        var place = document.getElementById("tiddlerDisplay");
        var tiddler = place.firstChild;
        var nextTiddler;
        while(tiddler)
                {
                nextTiddler = tiddler.nextSibling;
                if(tiddler.id)
                        if(tiddler.id.substr(0,7) == "tiddler")
                                {
                                var title = tiddler.id.substr(7);
                                if(!document.getElementById("editorWrapper" + title))
                                        place.removeChild(tiddler);
                                }
                tiddler = nextTiddler;
                }
}

// ---------------------------------------------------------------------------------
// Regular expression stuff
// ---------------------------------------------------------------------------------

var upperLetter = "[A-Z\u00c0-\u00de\u0150\u0170]";
var lowerLetter = "[a-z\u00df-\u00ff_0-9\\-\u0151\u0171]";
var anyLetter = "[A-Za-z\u00c0-\u00de\u00df-\u00ff_0-9\\-\u0150\u0170\u0151\u0171]";
var anyDigit = "[0-9]";
var anyNumberChar = "[0-9\\.E]";
var wikiNamePattern = "(~?)((?:" + upperLetter + "+" + lowerLetter + "+" + upperLetter + anyLetter + "*)|(?:" + upperLetter + "{2,}" + lowerLetter + "+))";
var urlPattern = "((?:http|https|mailto|ftp):[^\\s'\"]+(?:/|\\b))";
var explicitLinkPattern = "\\[\\[([^\\[\\]\\|]+)\\|([^\\[\\]\\|]+)\\]\\]";
var bracketNamePattern = "\\[\\[([^\\]]+)\\]\\]";

var wikiNamePatterns;
var wikiNameRegExp;
var structurePatterns;
var stylePatterns;
var tableRegExp;
var tableRowColRegExp;
var invalidPreWikiNamePattern;

function setupRegexp()
{
        // Table rows pattern
        var rowPattern = "^\\|([^\\n]*\\|)([fhc]?)$";
        tableRegExp = new RegExp(rowPattern,"mg");
        // Table columns pattern
        var elementPattern = "(?:(?:BGCOLOR|bgcolor)\\(([^\\)]+)\\):)?" +
                "("+
                "("+explicitLinkPattern+")?"+
                "("+bracketNamePattern+")?" +
                "[^\\|]*"+
                ")\\|";
        tableRowColRegExp = new RegExp(elementPattern,"g");
        // Link patterns
        wikiNamePatterns = "(?:" + wikiNamePattern +
                ")|(?:" + urlPattern +
                ")|(?:" + explicitLinkPattern +
                ")|(?:" + bracketNamePattern +
                ")";
        wikiNameRegExp = new RegExp(wikiNamePatterns,"mg");
        invalidPreWikiNamePattern = anyLetter;
        // Structural patterns
        var breakPattern = "\\n";
        var horizontalRulePattern = "^----$\\n?";
        var headerPattern = "^!{1,5}";
        var bulletListItemPattern = "^\\*+";
        var numberedListItemPattern = "^#+";
        var tablePattern = "(?:^\\|[^\\n]*$\\n?)+";
        var blockquotePattern = "(?:^>[^\\n]*$\\n?)+";
        var blockquotePattern2 = "^<<<\\n((?:^(?!<<<)[^\\n]*\\n)+)(^<<<$\\n?)";
        var imagePattern = "\\[[Ii][Mm][Gg]\\[(?:([^\\|\\]]+)\\|)?([^\\[\\]\\|]+)\\]\\]";
        var verbatimPattern = "^\\{\\{\\{\\n((?:^[^\\n]*\\n)+?)(^\\}\\}\\}$\\n?)";
        var macroPattern = "<<([^>\\s]+)(?:\\s*)([^>]*)>>";
        structurePatterns = "(" + breakPattern +
                ")|(" + horizontalRulePattern +
                ")|(" + headerPattern +
                ")|(" + bulletListItemPattern +
                ")|(" + numberedListItemPattern +
                ")|(" + tablePattern +
                ")|(" + blockquotePattern +
                ")|(?:" + blockquotePattern2 +
                ")|(?:" + imagePattern +
                ")|(?:" + verbatimPattern +
                ")|(?:" + macroPattern +
                ")";
        // Style patterns
        var boldPattern = "''((?:[^']+(?:'[^'])?)+)''";
        var strikePattern = "==([^=]+)==";
        var underlinePattern = "__([^_]+)__";
        var italicPattern = "//([^/]+)//";
        var supPattern = "\\^\\^([^\\^]+)\\^\\^";
        var subPattern = "~~([^~]+)~~";
        var monoPattern = "\\{\\{\\{(.*?)\\}\\}\\}";
        var colorPattern = "@@(?:color\\(([^\\)]+)\\):|bgcolor\\(([^\\)]+)\\):){0,2}([^@]+)@@";
        stylePatterns = "(?:" + boldPattern +
                ")|(?:" + strikePattern +
                ")|(?:" + underlinePattern +
                ")|(?:" + italicPattern +
                ")|(?:" + supPattern +
                ")|(?:" + subPattern +
                ")|(?:" + colorPattern +
                ")|(?:" + monoPattern +
                ")";
}

// Create child text nodes and link elements to represent a wiki-fied version of some text
function wikify(text,parent,highlightText,highlightCaseSensitive)
{
        // Prepare the regexp for the highlighted selection
        if(highlightText == "")
                highlightText = null;
        var highlightRegExp,highlightMatch;
        if(highlightText)
                {
                highlightRegExp = new RegExp(highlightText,highlightCaseSensitive ? "mg" : "img");
                highlightMatch = highlightRegExp.exec(text);
                }
        wikifyStructures(parent,text,text,0,text.length,highlightRegExp,highlightMatch);
}


function wikifyStructures(parent,text,targetText,startPos,endPos,highlightRegExp,highlightMatch)
{
        var body = parent;
        var structureRegExp = new RegExp(structurePatterns,"mg");
        var theList = []; // theList[0]: don't use
        var isInListMode = false;
        var isInHeaderMode = false;
        var isNewline = false;
        // The start of the fragment of the text being considered
        var nextPos = 0;
        // Loop through the bits of the body text
        do {
                // Get the next formatting match
                var formatMatch = structureRegExp.exec(targetText);
                var matchPos = formatMatch ? formatMatch.index : targetText.length;
                // Subwikify the plain text before the match
                if(nextPos < matchPos)
                        {
                        isNewline = false;
                        highlightMatch = wikifyStyles(body,text,targetText.substring(nextPos,matchPos),startPos+nextPos,startPos+matchPos,highlightRegExp,highlightMatch);
                        }
                // Dump out the formatted match
                var level;
                var theBlockquote;
                if(formatMatch)
                        {
                        // Dump out the link itself in the appropriate format
                        if(formatMatch[1])
                                {
                                if(isNewline && isInListMode)
                                        {
                                        theList = [];
                                        body = parent;
                                        isInListMode = false;
                                        }
                                else if(isInHeaderMode)
                                        {
                                        body = parent;
                                        isInHeaderMode = false;
                                        }
                                else
                                        {
                                        isNewline = true;
                                        body.appendChild(document.createElement("br"));
                                        }
                                }
                        else if(formatMatch[2])
                                {
                                isNewline = false;
                                body.appendChild(document.createElement("hr"));
                                }
                        else if(formatMatch[3])
                                {
                                level = formatMatch[3].length;
                                isNewline = false;
                                isInHeaderMode = true;
                                var theHeader = document.createElement("h" + level);
                                parent.appendChild(theHeader);
                                body = theHeader;
                                }
                        else if(formatMatch[4])
                                {
                                level = formatMatch[4].length;
                                isNewline = false;
                                isInListMode = true;
                                if (theList[level] == null)
                                        {
                                        theList[level] = document.createElement("ul");
                                        body.appendChild(theList[level]);
                                        }
                                theList = theList.slice(0,level + 1);
                                body = document.createElement("li");
                                theList[level].appendChild(body);
                                }
                        else if(formatMatch[5])
                                {
                                level = formatMatch[5].length;
                                isNewline = false;
                                isInListMode = true;
                                if (theList[level] == null)
                                        {
                                        theList[level] = document.createElement("ol");
                                        body.appendChild(theList[level]);
                                        }
                                theList = theList.slice(0,level + 1);
                                body = document.createElement("li");
                                theList[level].appendChild(body);
                                }
                        else if(formatMatch[6])
                                {
                                isNewline = false;
                                highlightMatch = wikifyTable(body,text,formatMatch[6],startPos+matchPos,startPos+structureRegExp.lastIndex,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[7])
                                {
                                isNewline = false;
                                var quotedText = formatMatch[7].replace(new RegExp("^>(>*)","mg"),"$1");
                                theBlockquote = document.createElement("blockquote");
                                var newHighlightRegExp,newHighlightMatch;
                                if (highlightRegExp) {
                                        newHighlightRegExp = new RegExp(highlightRegExp.toString(), "img");
                                        newHighlightMatch = newHighlightRegExp.exec(quotedText);
                                }
                                wikifyStructures(theBlockquote,quotedText,quotedText,0,quotedText.length,newHighlightRegExp,newHighlightMatch);
                                body.appendChild(theBlockquote);
                                }
                        else if(formatMatch[8])
                                {
                                isNewline = false;
                                theBlockquote = document.createElement("blockquote");
                                highlightMatch = wikifyStructures(theBlockquote,text,formatMatch[8],startPos+matchPos+4,startPos+structureRegExp.lastIndex-formatMatch[9].length,highlightRegExp,highlightMatch);
                                body.appendChild(theBlockquote);
                                }
                        else if(formatMatch[11])
                                {
                                isNewline = false;
                                var theImage = document.createElement("img");
                                theImage.alt = formatMatch[10];
                                theImage.src = formatMatch[11];
                                body.appendChild(theImage);
                                }
                        else if(formatMatch[12])
                                {
                                isNewline = false;
                                var theVerbatim = document.createElement("pre");
                                out = text.substr(startPos+matchPos+4,startPos+structureRegExp.lastIndex-formatMatch[13].length-startPos-matchPos-4);
                                out = out.replace(/\n/g,"\r\n");
                                theVerbatim.appendChild(document.createTextNode(out));
                                body.appendChild(theVerbatim);
                                }
                        else if(formatMatch[14])
                                {
                                isNewline = false;
                                insertMacro(body,formatMatch[14],formatMatch[15]);
                                }
                        }
                // Move the next position past the formatting match
                nextPos = structureRegExp.lastIndex;
        } while(formatMatch);
        return highlightMatch;
}

function wikifyLinks(parent,text,targetText,startPos,endPos,highlightRegExp,highlightMatch)
{
        // The start of the fragment of the text being considered
        var nextPos = 0;
        // Loop through the bits of the body text
        var theLink;
        do {
                // Get the next formatting match
                var formatMatch = wikiNameRegExp.exec(targetText);
                var matchPos = formatMatch ? formatMatch.index : targetText.length;
                // Subwikify the plain text before the match
                if(nextPos < matchPos)
                        highlightMatch = subWikify(parent,text,startPos+nextPos,startPos+matchPos,highlightRegExp,highlightMatch);
                // Dump out the formatted match
                if(formatMatch)
                        {
                        // Dump out the link itself in the appropriate format
                        if(formatMatch[2])
                                {
                                if(formatMatch[1])
                                        {
                                        theLink = parent;
                                        matchPos++;
                                        }
                                else if(matchPos > 0 && new RegExp(invalidPreWikiNamePattern,"").exec(targetText.charAt(matchPos - 1)))
                                        theLink = parent;
                                else
                                        theLink = createTiddlyLink(parent,formatMatch[2],false);
                                highlightMatch = subWikify(theLink,text,startPos+matchPos,startPos+wikiNameRegExp.lastIndex,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[3])
                                {
                                theLink = createExternalLink(parent,formatMatch[3]);
                                highlightMatch = subWikify(theLink,text,startPos+matchPos,startPos+wikiNameRegExp.lastIndex,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[4])
                                {
                                if(store.tiddlers[formatMatch[5]] != undefined)
                                        theLink = createTiddlyLink(parent,formatMatch[5],false);
                                else
                                        theLink = createExternalLink(parent,formatMatch[5]);
                                highlightMatch = subWikify(theLink,text,startPos+matchPos+2,startPos+matchPos+2+formatMatch[4].length,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[6])
                                {
                                theLink = createTiddlyLink(parent,formatMatch[6],false);
                                highlightMatch = subWikify(theLink,text,startPos+matchPos+2,startPos+wikiNameRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        }
                // Move the next position past the formatting match
                nextPos = wikiNameRegExp.lastIndex;
        } while(formatMatch);
        return highlightMatch;
}

function wikifyStyles(parent,text,targetText,startPos,endPos,highlightRegExp,highlightMatch)
{
        var formatRegExp = new RegExp(stylePatterns,"mg");
        // The start of the fragment of the text being considered
        var nextPos = 0;
        // Loop through the bits of the body text
        do {
                // Get the next formatting match
                var formatMatch = formatRegExp.exec(targetText);
                var matchPos = formatMatch ? formatMatch.index : targetText.length;
                // Subwikify the plain text before the match
                if(nextPos < matchPos)
                        highlightMatch = wikifyLinks(parent,text,targetText.substring(nextPos,matchPos),startPos+nextPos,startPos+matchPos,highlightRegExp,highlightMatch);
                // Dump out the formatted match
                if(formatMatch)
                        {
                        // Dump out the link itself in the appropriate format
                        if(formatMatch[1])
                                {
                                var theBold = createTiddlyElement(parent,"b",null,null,null);
                                highlightMatch = wikifyStyles(theBold,text,formatMatch[1],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[2])
                                {
                                var theStrike = createTiddlyElement(parent,"strike",null,null,null);
                                highlightMatch = wikifyStyles(theStrike,text,formatMatch[2],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[3])
                                {
                                var theUnderline = createTiddlyElement(parent,"u",null,null,null);
                                highlightMatch = wikifyStyles(theUnderline,text,formatMatch[3],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[4])
                                {
                                var theItalic = createTiddlyElement(parent,"i",null,null,null);
                                highlightMatch = wikifyStyles(theItalic,text,formatMatch[4],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[5])
                                {
                                var theSup = createTiddlyElement(parent,"sup",null,null,null);
                                highlightMatch = wikifyStyles(theSup,text,formatMatch[5],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[6])
                                {
                                var theSub = createTiddlyElement(parent,"sub",null,null,null);
                                highlightMatch = wikifyStyles(theSub,text,formatMatch[6],startPos+matchPos+2,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[9])
                                {
                                var theSpan;
                                if ((formatMatch[7] == "" || formatMatch[7] == null) && (formatMatch[8] == "" || formatMatch[8] == null))
                                        {
                                        theSpan = createTiddlyElement(parent,"span",null,"marked",null);
                                        }
                                        else
                                        {
                                        theSpan = createTiddlyElement(parent,"span",null,null,null);
                                        if (formatMatch[7] != "") theSpan.style.color = formatMatch[7];
                                        if (formatMatch[8] != "") theSpan.style.background = formatMatch[8];
                                        }
                                highlightMatch = wikifyStyles(theSpan,text,formatMatch[9],startPos+formatRegExp.lastIndex-2-formatMatch[9].length,startPos+formatRegExp.lastIndex-2,highlightRegExp,highlightMatch);
                                }
                        else if(formatMatch[10])
                                {
                                var theCode = createTiddlyElement(parent,"code",null,null,null);
                                highlightMatch = wikifyStyles(theCode,text,formatMatch[10],startPos+matchPos+3,startPos+formatRegExp.lastIndex-3,highlightRegExp,highlightMatch);
                                }
                        }
                // Move the next position past the formatting match
                nextPos = formatRegExp.lastIndex;
        } while(formatMatch);
        return highlightMatch;
}

// Create a table
function wikifyTable(parent,text,targetText,startPos,endPos,highlightRegExp,highlightMatch)
{
        // The start of the fragment of the text being considered
        var nextPos = 0;
        var theTable = document.createElement("table");
        var bodyRowLen = 0;
        var headRowLen = 0;
        var footRowLen = 0;
        var bodyRows = [];
        var headRows = [];
        var footRows = [];
        var theCaption = null;
        // Loop through the bits of the body text
        do {
                // Get the next formatting match
                var formatMatch = tableRegExp.exec(targetText);
                var matchPos = formatMatch ? formatMatch.index : targetText.length;
                // Dump out the formatted match
                if(formatMatch) {
                        if (formatMatch[2] == "c") {
                                var cap = formatMatch[1].substring(0,formatMatch[1].length-1);
                                theCaption = document.createElement("caption");
                                highlightMatch = wikifyStyles(theCaption,text,cap,startPos+matchPos+1,startPos+cap.length,highlightRegExp,highlightMatch);
                                if (bodyRowLen == 0 && headRowLen == 0 && footRowLen == 0) {
                                        theCaption.setAttribute("align", "top");
                                } else {
                                        theCaption.setAttribute("align", "bottom");
                                }
                        } else if (formatMatch[2] == "h") {
                                highlightMatch = wikifyTableRow(headRows,headRowLen,text,formatMatch[1],startPos+matchPos,startPos+matchPos+formatMatch[1].length,highlightRegExp,highlightMatch);
                                headRowLen++;
                        } else if (formatMatch[2] == "f") {
                                highlightMatch = wikifyTableRow(footRows,footRowLen,text,formatMatch[1],startPos+matchPos,startPos+matchPos+formatMatch[1].length,highlightRegExp,highlightMatch);
                                footRowLen++;
                        } else {
                                highlightMatch = wikifyTableRow(bodyRows,bodyRowLen,text,formatMatch[1],startPos+matchPos,startPos+matchPos+formatMatch[1].length,highlightRegExp,highlightMatch);
                                bodyRowLen++;
                        }
                }
                nextPos = tableRegExp.lastIndex;
        } while(formatMatch);

        if (theCaption != null) {
                theTable.appendChild(theCaption);
        }

        if (headRowLen > 0) {
                var theTableHead = document.createElement("thead");
                createTableRows(headRows,theTableHead);
                theTable.appendChild(theTableHead);
        }

        if (bodyRowLen > 0) {
                var theTableBody = document.createElement("tbody");
                createTableRows(bodyRows,theTableBody);
                theTable.appendChild(theTableBody);
        }

        if (footRowLen > 0) {
                var theTableFoot = document.createElement("tfoot");
                createTableRows(footRows,theTableFoot);
                theTable.appendChild(theTableFoot);
        }

        parent.appendChild(theTable);
        return highlightMatch;
}

function wikifyTableRow(rows,rowIndex,text,targetText,startPos,endPos,highlightRegExp,highlightMatch)
{
        // The start of the fragment of the text being considered
        var eIndex = 0;
        var elements = [];
        // Loop through the bits of the body text
        do {
                // Get the next formatting match
                var formatMatch = tableRowColRegExp.exec(targetText);
                var matchPos = formatMatch ? formatMatch.index : targetText.length;
                if(formatMatch) {
                        var eText = formatMatch[2];
                        if (eText == "~" || eText == ">") {
                                elements[eIndex] = eText;
                        } else {
                                var eTextLen = eText.length;
                                var align = "";
                                if (eTextLen >= 1 && eText.charAt(0) == " ") {
                                        if (eTextLen >= 3 && eText.charAt(eTextLen - 1) == " ") {
                                                align = "center";
                                                eText = eText.substring(1,eTextLen - 1);
                                                //eTextLen -= 2;
                                                eTextLen--;
                                        } else {
                                                align = "right";
                                                eText = eText.substring(1);
                                                eTextLen--;
                                        }
                                } else if (eTextLen >= 2 && eText.charAt(eTextLen - 1) == " ") {
                                        align = "left";
                                        eText = eText.substring(0,eTextLen - 1);
                                        //eTextLen--;
                                }
                                var theElement;
                                if (eTextLen >= 1 && eText.charAt(0) == "!") {
                                        theElement = document.createElement("th");
                                        eText = eText.substring(1);
                                        eTextLen--;
                                } else {
                                        theElement = document.createElement("td");
                                }
                                if (align != "") {
                                        theElement.align = align;
                                }
                                if (formatMatch[1]) {
                                        theElement.style.background = formatMatch[1];
                                }
                                highlightMatch = wikifyStyles(theElement,text,eText,startPos+tableRowColRegExp.lastIndex-eTextLen,startPos+tableRowColRegExp.lastIndex-1,highlightRegExp,highlightMatch);
                                elements[eIndex] = theElement;
                        }
                        eIndex++;
                }
        } while(formatMatch);
        rows[rowIndex] = elements;
        return highlightMatch;
}

function createTableRows(rows,parent)
{
        var i, j, k, cols;
        for (i = 0; i < rows.length; i++) {
                cols = rows[i];
                var theRow = document.createElement("tr");
                for (j = 0; j < cols.length; j++) {
                        if (cols[j] == "~") continue;
                        var rowspan = 1;
                        for (k = i+1; k < rows.length; k++) {
                                if (rows[k][j] != "~") break;
                                rowspan++;
                        }
                        var colspan = 1;
                        for (; j < cols.length - 1; j++) {
                                if (cols[j] != ">") break;
                                colspan++;
                        }
                        var theElement = cols[j];
                        if (rowspan > 1) {
                                theElement.setAttribute("rowSpan",rowspan);
                                theElement.setAttribute("rowspan",rowspan);
                                theElement.valign = "center";
                        }
                        if (colspan > 1) {
                                theElement.setAttribute("colSpan",colspan);
                                theElement.setAttribute("colspan",colspan);
                        }
                        theRow.appendChild(theElement);
                }
                parent.appendChild(theRow);
        }
}

// Helper for wikify that handles highlights within runs of text
function subWikify(parent,text,startPos,endPos,highlightRegExp,highlightMatch)
{
        // Check for highlights
        while(highlightMatch && (highlightRegExp.lastIndex > startPos) && (highlightMatch.index < endPos) && (startPos < endPos))
                {
                // Deal with the plain text before the highlight
                if(highlightMatch.index > startPos)
                        {
                        parent.appendChild(document.createTextNode(text.substring(startPos,highlightMatch.index)));
                        startPos = highlightMatch.index;
                        }
                // Deal with the highlight
                var highlightEnd = Math.min(highlightRegExp.lastIndex,endPos);
                var theHighlight = createTiddlyElement(parent,"span",null,"highlight",text.substring(startPos,highlightEnd));
                startPos = highlightEnd;
                // Nudge along to the next highlight if we're done with this one
                if(startPos >= highlightRegExp.lastIndex)
                        highlightMatch = highlightRegExp.exec(text);
                }
        // Do the unhighlighted text left over
        if(startPos < endPos)
                {
                parent.appendChild(document.createTextNode(text.substring(startPos,endPos)));
                //startPos = endPos;
                }
        return(highlightMatch);
}

// ---------------------------------------------------------------------------------
// Message area
// ---------------------------------------------------------------------------------

function displayMessage(text,linkText)
{
        var msgArea = document.getElementById("messageArea");
        var msg;
        if(linkText)
                {
                msg = createTiddlyElement(msgArea,"div",null,null,null);
                var link = createTiddlyElement(msg,"a",null,null,text);
                link.href = linkText;
                link.target = "_blank";
                }
        else
                msg = createTiddlyElement(msgArea,"div",null,null,text);
        msgArea.style.display = "block";
}

function clearMessage()
{
        var msgArea = document.getElementById("messageArea");
        removeChildren(msgArea);
        msgArea.style.display = "none";
}

// ---------------------------------------------------------------------------------
// Menu and sidebar functions
// ---------------------------------------------------------------------------------

function refreshStory(hint)
{
        var hits = hint ? store.getReferringTiddlers(hint) : null;
        var displayNodes = document.getElementById("tiddlerDisplay").childNodes;
        for(var t=0;t<displayNodes.length;t++)
                {
                var theId = displayNodes[t].id;
                if(theId && theId.substr(0,7) == "tiddler")
                        {
                        var title = theId.substr(7);
                        if(hint)
                                {
                                var f = false;
                                for(var h=0; h<hits.length; h++)
                                        if(hits[h].title == title)
                                                f = true
                                if(f)
                                        refreshTiddler(title);
                                }
                        else
                                refreshTiddler(title);
                        }
                }
}

function refreshTabs(hint)
{
        refreshSpecialItem("sidebarTabs","SideBarTabs","SideBarTabs");
}

function refreshMenu(hint)
{
        refreshSpecialItem("mainMenu","MainMenu","MainMenu");
}

function refreshTitle(title)
{
        refreshSpecialItem("siteTitle",title,"SiteTitle");
        refreshPageTitle();
}

function refreshSubtitle(title)
{
        refreshSpecialItem("siteSubtitle",title,"SiteSubtitle");
        refreshPageTitle();
}

function refreshPageTitle()
{
        document.title = getElementText("siteTitle") + " - " + getElementText("siteSubtitle");
}

function refreshSidebar(title)
{
        refreshSpecialItem("sidebarOptions",title,"SideBarOptions");
}

function refreshSpecialItem(elementID,title,defaultText)
{
        var place = document.getElementById(elementID);
        removeChildren(place);
        wikify(store.getTiddlerText(title,defaultText),place,null,null);
}

function refreshStyles(title)
{
        setStylesheet(title == null ? "" : store.getRecursiveTiddlerText(title,""));
}

// ---------------------------------------------------------------------------------
// Options cookie stuff
// ---------------------------------------------------------------------------------

function loadOptionsCookie()
{
        var cookies = document.cookie.split(";");
        for(var c=0; c<cookies.length; c++)
                {
                var p = cookies[c].indexOf("=");
                if(p != -1)
                        {
                        var name = cookies[c].substr(0,p).trim();
                        var value = cookies[c].substr(p+1).trim();
                        switch(name.substr(0,3))
                                {
                                case "txt":
                                        config.options[name] = unescape(value);
                                        break;
                                case "chk":
                                        config.options[name] = value == "true";
                                        break;
                                }
                        }
                }
}

function saveOptionCookie(name)
{
        var c = name + "=";
        switch(name.substr(0,3))
                {
                case "txt":
                        c += escape(config.options[name].toString());
                        break;
                case "chk":
                        c += config.options[name] ? "true" : "false";
                        break;
                }
        c += "; expires=Fri, 1 Jan 2038 12:00:00 UTC; path=/";
        document.cookie = c;
}

// ---------------------------------------------------------------------------------
// Saving
// ---------------------------------------------------------------------------------

var saveUsingSafari = false;
var startSaveArea = '<div id="' + 'storeArea">'; // Split up into two so that indexOf() of this source doesn't find it
var endSaveArea = '</d' + 'iv>';

// Check if there any unsaved changes before exitting
function checkUnsavedChanges()
{
        if(store.dirty)
                {
                if(confirm(config.messages.unsavedChangesWarning))
                        saveChanges();
                }
}

function Win2EscapeWK(AStr){
var Letters=new Array('%C0','%C1','%C2','%C3','%C4','%C5','%C6','%C7','%C8','%C9','%CA','%CB','%CC','%CD','%CE','%CF','%D0','%D1','%D2','%D3','%D4','%D5','%D6','%D7','%D8','%D9','%DA','%DB','%DC','%DD','%DE','%DF','%E0','%E1','%E2','%E3','%E4','%E5','%E6','%E7','%E8','%E9','%EA','%EB','%EC','%ED','%EE','%EF','%F0','%F1','%F2','%F3','%F4','%F5','%F6','%F7','%F8','%F9','%FA','%FB','%FC','%FD','%FE','%FF','%A8','%B8');
var Result='';
for(var i=0;i<AStr.length;i++)
if(AStr.charAt(i)>='�' && AStr.charAt(i)<='�')
Result+=Letters[AStr.charCodeAt(i)-0x0410];
else if(AStr.charAt(i)=='�')
Result+=Letters[64];
else if(AStr.charAt(i)=='�')
Result+=Letters[65];
else if(AStr.charAt(i)=='=')
Result+='%3D';
else if(AStr.charAt(i)=='&')
Result+='%26';
else
Result+=AStr.charAt(i);
return Result;
}//Win2Escape

function URLencodeWK(sStr) {
    return (Win2EscapeWK(sStr)).replace(/\+/g, '%2B').replace(/\"/g,'%22').replace(/\'/g, '%27');
}


// Save this tiddlywiki with the pending changes
function saveChanges()
{
        clearMessage();
        // Locate the storeArea div's
        //var elem=document.getElementById('storeArea');
        //var old_content=elem.innerHTML;
        //var content=allTiddlersAsHtml();

        // Save the backup
        /*
        if(config.options.chkSaveBackups)
                {
                var backup = true; // backup saving routine should be here
                if(backup)
                        displayMessage(config.messages.backupSaved,"file://" + backupPath);
                else
                        alert(config.messages.backupFailed);
                }
        // Save Rss
        if(config.options.chkGenerateAnRssFeed)
                {
                var rssSave = 1; // RSS saving routine should be here
                if(rssSave)
                        displayMessage(config.messages.rssSaved,"file://" + rssPath);
                else
                        alert(config.messages.rssFailed);
                }
        */
        // Save new file
        var save = 1; // saving routine should be here

// var url="?";

//alert(post_url);
$('#contentField').val(allTiddlersAsHtml());
$.post(post_url, $("#postForm").serialize(),
  function(data) {
                displayMessage(config.messages.mainSaved + ' Status:' + data,"");
                store.setDirty(false);
  }
 );

/*
  $.ajax({
   type: "POST",
   url: url,
   data: data,
   success: success,
   dataType: dataType
  });
*/

 /*
 var xmlhttp=false;

  if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
   xmlhttp = new XMLHttpRequest();
  }

 
  var params='view_mode=edit_tdwiki&mode=update&md=tdwiki';
  params+='&content='+URLencodeWK(content);

  //post_url=post_url.replace('/nf.php?', '&');
  var add_params=post_url.replace('/crm.php?', '&');
  var add_params2=add_params.replace('/nf.php?', '&');
  params+=add_params2;
  //alert(params);
  //alert(post_url);
  xmlhttp.open("POST", post_url, true);
  xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  xmlhttp.send(params);

  xmlhttp.onreadystatechange=function() {
   if (xmlhttp.readyState==4) {
                displayMessage(config.messages.mainSaved + ' Status:' + xmlhttp.responseText,"");
                store.setDirty(false);
   }
  }
  */

}

function generateRss()
{
        var s = [];
        var d = new Date();
        var u = store.getTiddlerText("SiteUrl",null);
        // Assemble the header
        s.push("<" + "?xml version=\"1.0\"?" + ">");
        s.push("<rss version=\"2.0\">");
        s.push("<channel>");
        s.push("<title>" + store.getTiddlerText("SiteTitle","").htmlEncode() + "</title>");
        if(u)
                s.push("<link>" + u.htmlEncode() + "</link>");
        s.push("<description>" + store.getTiddlerText("SiteSubtitle","").htmlEncode() + "</description>");
        s.push("<language>en-us</language>");
        s.push("<copyright>Copyright " + d.getFullYear() + " " + config.options.txtUserName.htmlEncode() + "</copyright>");
        s.push("<pubDate>" + d.toGMTString() + "</pubDate>");
        s.push("<lastBuildDate>" + d.toGMTString() + "</lastBuildDate>");
        s.push("<docs>http://blogs.law.harvard.edu/tech/rss</docs>");
        s.push("<generator>TiddlyWiki " + version.major + "." + version.minor + "." + version.revision + "</generator>");
        // The body
        var tiddlers = store.getTiddlers("modified");
        var n = config.numRssItems > tiddlers.length ? 0 : tiddlers.length-config.numRssItems;
        for (var t=tiddlers.length-1; t>=n; t--)
                s.push(tiddlers[t].saveToRss(u));
        // And footer
        s.push("</channel>");
        s.push("</rss>");
        // Save it all
        return s.join("\n");
}

function generateEmpty()
{
        var systemTiddlers = store.getTaggedTiddlers("systemTiddlers");
        var savedTiddlers = [];
        for(var s=0;s<systemTiddlers.length;s++)
                savedTiddlers.push(systemTiddlers[s].saveToDiv());
        return savedTiddlers.join("\n");
}

function allTiddlersAsHtml()
{
        var savedTiddlers = [];
        var tiddlers = store.getTiddlers("modified");
        for (var t = 0; t < tiddlers.length; t++)
                savedTiddlers.push(tiddlers[t].saveToDiv());
        return savedTiddlers.join("\n");
}

// UTF-8 encoding rules:
// 0x0000 - 0x007F:     0xxxxxxx
// 0x0080 - 0x07FF:     110xxxxx 10xxxxxx
// 0x0800 - 0xFFFF:     1110xxxx 10xxxxxx 10xxxxxx

function convertUTF8ToUnicode(u)
{
        var s = "";
        var t = 0;
        var b1, b2, b3;
        while(t < u.length)
                {
                b1 = u.charCodeAt(t++);
                if(b1 < 0x80)
                        s += String.fromCharCode(b1);
                else if(b1 < 0xE0)
                        {
                        b2 = u.charCodeAt(t++);
                        s += String.fromCharCode(((b1 & 0x1F) << 6) | (b2 & 0x3F));
                        }
                else
                        {
                        b2 = u.charCodeAt(t++);
                        b3 = u.charCodeAt(t++);
                        s += String.fromCharCode(((b1 & 0xF) << 12) | ((b2 & 0x3F) << 6) | (b3 & 0x3F));
                        }
        }
        return(s);
}

function convertUnicodeToUTF8(s)
{
        if(saveUsingSafari)
                return s;
        else if(window.Components)
                return mozConvertUnicodeToUTF8(s);
        else
                return manualConvertUnicodeToUTF8(s);
}

function manualConvertUnicodeToUTF8(s)
{
        var u = [];
        for(var t=0;t<s.length;t++)
                {
                var c = s.charCodeAt(t);
                if(c <= 0x7F)
                        u.push(String.fromCharCode(c));
                else
                        u.push("&#" + c.toString() + ";");
                }
        return(u.join(""));
}

function mozConvertUnicodeToUTF8(s)
{
        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        var converter = Components.classes["@mozilla.org/intl/scriptableunicodeconverter"].createInstance(Components.interfaces.nsIScriptableUnicodeConverter);
        converter.charset = "UTF-8";
        var u = converter.ConvertFromUnicode(s);
        var fin = converter.Finish();
        if(fin.length > 0)
                return u + fin;
        else
                return u;
}

function saveFile(fileUrl, content)
{
        var r = null;
        if(saveUsingSafari)
                r = safariSaveFile(fileUrl, content);
        if((r == null) || (r == false))
                r = mozillaSaveFile(fileUrl, content);
        if((r == null) || (r == false))
                r = ieSaveFile(fileUrl, content);
        return(r);
}

function loadFile(fileUrl)
{
        var r = null;
        if(saveUsingSafari)
                r = safariLoadFile(fileUrl);
        if((r == null) || (r == false))
                r = mozillaLoadFile(fileUrl);
        if((r == null) || (r == false))
                r = ieLoadFile(fileUrl);
        return(r);
}

// Returns null if it can't do it, false if there's an error, true if it saved OK
function ieSaveFile(filePath, content)
{
        try
                {
                var fso = new ActiveXObject("Scripting.FileSystemObject");
                }
        catch(e)
                {
                //alert("Exception while attempting to save\n\n" + e.toString());
                return(null);
                }
        var file = fso.OpenTextFile(filePath,2,-1,0);
        file.Write(content);
        file.Close();
        return(true);
}

// Returns null if it can't do it, false if there's an error, or a string of the content if successful
function ieLoadFile(filePath)
{
        try
                {
                var fso = new ActiveXObject("Scripting.FileSystemObject");
                }
        catch(e)
                {
                //alert("Exception while attempting to load\n\n" + e.toString());
                return(null);
                }
        var file = fso.OpenTextFile(filePath,1);
        var content = file.ReadAll();
        file.Close();
        return(content);
}

// Returns null if it can't do it, false if there's an error, true if it saved OK
function mozillaSaveFile(filePath, content)
{
        if(window.Components)
                try
                        {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                        var file = Components.classes["@mozilla.org/file/local;1"].createInstance(Components.interfaces.nsILocalFile);
                        file.initWithPath(filePath);
                        if (!file.exists())
                                file.create(0, 0664);
                        var out = Components.classes["@mozilla.org/network/file-output-stream;1"].createInstance(Components.interfaces.nsIFileOutputStream);
                        out.init(file, 0x20 | 0x02, 00004,null);
                        out.write(content, content.length);
                        out.flush();
                        out.close();
                        return(true);
                        }
                catch(e)
                        {
                        //alert("Exception while attempting to save\n\n" + e);
                        return(false);
                        }
        return(null);
}

// Returns null if it can't do it, false if there's an error, or a string of the content if successful
function mozillaLoadFile(filePath)
{
        if(window.Components)
                try
                        {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                        var file = Components.classes["@mozilla.org/file/local;1"].createInstance(Components.interfaces.nsILocalFile);
                        file.initWithPath(filePath);
                        if (!file.exists())
                                return(null);
                        var inputStream = Components.classes["@mozilla.org/network/file-input-stream;1"].createInstance(Components.interfaces.nsIFileInputStream);
                        inputStream.init(file, 0x01, 00004, null);
                        var sInputStream = Components.classes["@mozilla.org/scriptableinputstream;1"].createInstance(Components.interfaces.nsIScriptableInputStream);
                        sInputStream.init(inputStream);
                        return(sInputStream.read(sInputStream.available()));
                        }
                catch(e)
                        {
                        //alert("Exception while attempting to load\n\n" + e);
                        return(false);
                        }
        return(null);
}

function safariFilenameToUrl(filename) {
        return ("file://" + filename);
}

function safariLoadFile(url)
{
        url = safariFilenameToUrl(url);
        var plugin = document.embeds["tiddlyWikiSafariSaver"];
        return plugin.readURL(url);
}

function safariSaveFile(url,content)
{
        url = safariFilenameToUrl(url);
        var plugin = document.embeds["tiddlyWikiSafariSaver"];
        return plugin.writeStringToURL(content,url);
}

// Lifted from http://developer.apple.com/internet/webcontent/detectplugins.html
function detectPlugin()
{
        var daPlugins = detectPlugin.arguments;
        var pluginFound = false;
        if (navigator.plugins && navigator.plugins.length > 0)
                {
                var pluginsArrayLength = navigator.plugins.length;
                for (pluginsArrayCounter=0; pluginsArrayCounter < pluginsArrayLength; pluginsArrayCounter++ )
                        {
                        var numFound = 0;
                        for(namesCounter=0; namesCounter < daPlugins.length; namesCounter++)
                                {
                                if( (navigator.plugins[pluginsArrayCounter].name.indexOf(daPlugins[namesCounter]) >= 0) || 
                                                (navigator.plugins[pluginsArrayCounter].description.indexOf(daPlugins[namesCounter]) >= 0) )
                                        numFound++;
                                }
                        if(numFound == daPlugins.length)
                                {
                                pluginFound = true;
                                break;
                                }
                        }
        }
        return pluginFound;
}

// ---------------------------------------------------------------------------------
// Event handlers
// ---------------------------------------------------------------------------------

function onEditKey(e)
{
        if (!e) var e = window.event;
        clearMessage();
        var consume = false;
        switch(e.keyCode)
                {
                case 13: // Ctrl-Enter
                case 10: // Ctrl-Enter on IE PC
                case 77: // Ctrl-Enter is "M" on some platforms
                        if(e.ctrlKey && this.id && this.id.substr(0,13) == "editorWrapper")
                                {
                                blurTiddler(this.id.substr(13));
                                saveTiddler(this.id.substr(13));
                                consume = true;
                                }
                        break;
                case 27: // Escape
                        if(this.id && this.id.substr(0,13) == "editorWrapper")
                                {
                                blurTiddler(this.id.substr(13));
                                displayTiddler(null,this.id.substr(13),1,null,null,false,false);
                                consume = true;
                                }
                        break;
                }
        e.cancelBubble = consume;
        if(consume)
                if (e.stopPropagation) e.stopPropagation();
        return(!consume);

}

// Event handler for clicking on a tiddly link
function onClickTiddlerLink(e)
{
        if (!e) var e = window.event;
        var theTarget = resolveTarget(e);
        var theLink = theTarget;
        var title = null;
        do {
                title = theLink.getAttribute("tiddlyLink");
                theLink = theLink.parentNode;
        } while(title == null && theLink != null);
        if(title)
                {
                var toggling = e.metaKey || e.ctrlKey;
                if(config.options.chkToggleLinks)
                        toggling = !toggling;
                var opening;
                if(toggling && document.getElementById("tiddler" + title))
                        closeTiddler(title,e.shiftKey || e.altKey);
                else
                        displayTiddler(theTarget,title,0,null,null,true,e.shiftKey || e.altKey);
                }
        clearMessage();
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for mouse over a tiddler
function onMouseOverTiddler(e)
{
        var tiddler;
        if(this.id.substr(0,7) == "tiddler")
                tiddler = this.id.substr(7);
        if(tiddler)
                selectTiddler(tiddler);
}

// Event handler for mouse out of a tiddler
function onMouseOutTiddler(e)
{
        var tiddler;
        if(this.id.substr(0,7) == "tiddler")
                tiddler = this.id.substr(7);
        if(tiddler)
                deselectTiddler(tiddler);
}

// Event handler for double click on a tiddler
function onDblClickTiddler(e)
{
        if(!readOnly)
                {
                clearMessage();
                if(document.selection)
                        document.selection.empty();
                var tiddler;
                if(this.id.substr(0,7) == "tiddler")
                        tiddler = this.id.substr(7);
                if(tiddler)
                        displayTiddler(null,tiddler,2,null,null,false,false);
                }
}

// Event handler for clicking on toolbar close
function onClickToolbarClose(e)
{
        if (!e) var e = window.event;
        clearMessage();
        if(this.parentNode.id)
                closeTiddler(this.parentNode.id.substr(7),e.shiftKey || e.altKey);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for clicking on toolbar permalink
function onClickToolbarPermaLink(e)
{
        if(this.parentNode.id)
                {
                var title = this.parentNode.id.substr(7);
                var t = encodeURIComponent(String.encodeTiddlyLink(title));
                if(window.location.hash != t)
                        window.location.hash = t;
                }
}

// Event handler for clicking on toolbar close
function onClickToolbarDelete(e)
{
        clearMessage();
        if(this.parentNode.id)
                deleteTiddler(this.parentNode.id.substr(7));
}

// Event handler for clicking on the toolbar references button
function onClickToolbarReferences(e)
{
        if (!e) var e = window.event;
        var theTarget = resolveTarget(e);
        var popup = createTiddlerPopup(this);
        if(popup && this.parentNode.id)
                {
                var title = this.parentNode.id.substr(7);
                var references = store.getReferringTiddlers(title);
                var c = false;
                for(var r=0; r<references.length; r++)
                        if(references[r].title != title)
                                {
                                createTiddlyLink(popup,references[r].title,true);
                                c = true;
                                }
                if(!c)
                        popup.appendChild(document.createTextNode(config.views.wikified.toolbarReferences.popupNone));
                }
        scrollToTiddlerPopup(popup,false);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for clicking on a tiddler tag
function onClickTag(e)
{
        if (!e) var e = window.event;
        var theTarget = resolveTarget(e);
        var popup = createTiddlerPopup(this);
        var tag = this.getAttribute("tag");
        var title = this.getAttribute("tiddler");
        if(popup && tag)
                {
                var tagged = store.getTaggedTiddlers(tag);
                var c = false;
                for(var r=0;r<tagged.length;r++)
                        if(tagged[r].title != title)
                                {
                                createTiddlyLink(popup,tagged[r].title,true);
                                c = true;
                                }
                var lingo = config.views.wikified.tag;
                if(c)
                        {
                        popup.insertBefore(document.createElement("hr"),popup.firstChild);
                        var openAll = createTiddlyButton(null,lingo.openAllText.format([tag]),lingo.openAllTooltip,onClickTagOpenAll);
                        openAll.setAttribute("tag",tag);
                        popup.insertBefore(openAll,popup.firstChild);
                        }
                else
                        popup.appendChild(document.createTextNode(lingo.popupNone.format([tag])));
                }
        scrollToTiddlerPopup(popup,false);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for 'open all' on a tiddler popup
function onClickTagOpenAll(e)
{
        if (!e) var e = window.event;
        var tag = this.getAttribute("tag");
        var tagged = store.getTaggedTiddlers(tag);
        for(var t=tagged.length-1; t>=0; t--)
                displayTiddler(this,tagged[t].title,0,null,null,false,e.shiftKey || e.altKey);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for clicking on the 'add tag' button
function onClickAddTag(e)
{
        if (!e) var e = window.event;
        var theTarget = resolveTarget(e);
        var popup = createTiddlerPopup(this);
        var tiddler = this.getAttribute("tiddler");
        var tags = store.getTags();
        var lingo = config.views.editor.tagChooser;
        if(tags.length == 0)
                createTiddlyElement(popup,"div",null,null,lingo.popupNone);
        for (t=0; t<tags.length; t++)
                {
                var theTag = createTiddlyButton(popup,tags[t][0],lingo.tagTooltip.format([tags[t][0]]),onClickAddTagPopup);
                theTag.setAttribute("tag",tags[t][0]);
                theTag.setAttribute("tiddler",tiddler);
                }
        scrollToTiddlerPopup(popup,false);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for clicking on a tag in the 'add tag' popup
function onClickAddTagPopup(e)
{
        if (!e) var e = window.event;
        var theTarget = resolveTarget(e);
        var tiddler = this.getAttribute("tiddler");
        var tag = this.getAttribute("tag");
        var tagsBox = document.getElementById("editorTags" + tiddler);
        if(tagsBox)
                tagsBox.value += " " + String.encodeTiddlyLink(tag);
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();
        return(false);
}

// Event handler for clicking on toolbar close
function onClickToolbarEdit(e)
{
        clearMessage();
        if(this.parentNode.id)
                displayTiddler(null,this.parentNode.id.substr(7),2,null,null,false,false);
}

// Event handler for clicking on toolbar save
function onClickToolbarSave(e)
{
        if(this.parentNode.id)
                saveTiddler(this.parentNode.id.substr(7));
}

// Event handler for clicking on toolbar save
function onClickToolbarUndo(e)
{
        if(this.parentNode.id)
                displayTiddler(null,this.parentNode.id.substr(7),1,null,null,false,false);
}

// Eek... it's bad that this is done via a function rather than a normal, copy-able href
function onClickPermaView()
{
        var tiddlerDisplay = document.getElementById("tiddlerDisplay");
        var links = [];
        for(var t=0;t<tiddlerDisplay.childNodes.length;t++)
                {
                var tiddlerName = tiddlerDisplay.childNodes[t].id.substr(7);
                links.push(String.encodeTiddlyLink(tiddlerName));
                }
        window.location.hash = encodeURIComponent(links.join(" "));
}

// ---------------------------------------------------------------------------------
// Animation engine
// ---------------------------------------------------------------------------------

function Animator()
{
        this.running = 0; // Incremented at start of each animation, decremented afterwards. If zero, the interval timer is disabled
        this.timerID; // ID of the timer used for animating
        this.animations = []; // List of animations in progress
        return this;
}

// Start animation engine
Animator.prototype.startAnimating = function() // Variable number of arguments
{
        for(var t=0; t<arguments.length; t++)
                this.animations.push(arguments[t]);
        if(this.running == 0)
                {
                var me = this;
                this.timerID = window.setInterval(function() {me.doAnimate(me);},25);
                }
        this.running += arguments.length;
}

// Perform an animation engine tick, calling each of the known animation modules
Animator.prototype.doAnimate = function(me)
{
        var a = 0;
        while(a<me.animations.length)
                {
                var animation = me.animations[a];
                animation.progress += animation.step;
                if(animation.progress < 0 || animation.progress > 1)
                        {
                        animation.stop();
                        me.animations.splice(a,1);
                        if(--me.running == 0)
                                window.clearInterval(me.timerID);
                        }
                else
                        {
                        animation.tick();
                        a++;
                        }
                }
}

// Map a 0..1 value to 0..1, but slow down at the start and end
Animator.slowInSlowOut = function(progress)
{
        return(1-((Math.cos(progress * Math.PI)+1)/2));
}

// ---------------------------------------------------------------------------------
// Zoomer animation
// ---------------------------------------------------------------------------------

function Zoomer(text,startElement,targetElement,slowly)
{
        this.element = document.createElement("div");
        this.element.appendChild(document.createTextNode(text));
        this.element.className = "zoomer";
        document.body.appendChild(this.element);
        this.startElement = startElement;
        this.startLeft = findPosX(this.startElement);
        this.startTop = findPosY(this.startElement);
        this.startWidth = this.startElement.offsetWidth;
        this.startHeight = this.startElement.offsetHeight;
        this.targetElement = targetElement;
        this.targetLeft = findPosX(this.targetElement);
        this.targetTop = findPosY(this.targetElement);
        this.targetWidth = this.targetElement.offsetWidth;
        this.targetHeight = this.targetElement.offsetHeight;
        this.progress = 0;
        this.step = slowly ? config.animSlow : config.animFast;
        this.targetElement.style.opacity = 0;
        return this;
}

Zoomer.prototype.stop = function()
{
        this.element.parentNode.removeChild(this.element);
        this.targetElement.style.opacity = 1;
}

Zoomer.prototype.tick = function()
{
        var f = Animator.slowInSlowOut(this.progress);
        this.element.style.left = this.startLeft + (this.targetLeft-this.startLeft) * f + "px";
        this.element.style.top = this.startTop + (this.targetTop-this.startTop) * f + "px";
        this.element.style.width = this.startWidth + (this.targetWidth-this.startWidth) * f + "px";
        this.element.style.height = this.startHeight + (this.targetHeight-this.startHeight) * f + "px";
        this.element.style.display = "block";
        this.targetElement.style.opacity = this.progress;
        this.targetElement.style.filter = "alpha(opacity:" + this.progress * 100 + ")";
}

// ---------------------------------------------------------------------------------
// Scroller animation
// ---------------------------------------------------------------------------------

function Scroller(targetElement,slowly)
{
        this.targetElement = targetElement;
        this.startScroll = findScrollY();
        this.targetScroll = ensureVisible(targetElement);
        this.progress = 0;
        this.step = slowly ? config.animSlow : config.animFast;
        return this;
}

Scroller.prototype.stop = function()
{
        window.scrollTo(0,this.targetScroll);
}

Scroller.prototype.tick = function()
{
        var f = Animator.slowInSlowOut(this.progress);
        window.scrollTo(0,this.startScroll + (this.targetScroll-this.startScroll) * f);
}

// ---------------------------------------------------------------------------------
// Slider animation
// ---------------------------------------------------------------------------------

// deleteMode - "none", "all" [delete target element and it's children], [only] "children" [but not the target element]
function Slider(element,opening,slowly,deleteMode)
{
        this.element = element;
        element.style.display = "block";
        this.deleteMode = deleteMode;
        this.element.style.height = "auto";
        this.realHeight = element.offsetHeight;
        this.opening = opening;
        this.step = slowly ? config.animSlow : config.animFast;
        if(opening)
                {
                this.progress = 0;
                element.style.height = "0px";
                element.style.display = "block";
                }
        else
                {
                this.progress = 1;
                this.step = -this.step;
                }
        element.style.overflow = "hidden";
        return this;
}

Slider.prototype.stop = function()
{
        if(this.opening)
                this.element.style.height = "auto";
        else
                {
                switch(this.deleteMode)
                        {
                        case "none":
                                this.element.style.display = "none";
                                break;
                        case "all":
                                this.element.parentNode.removeChild(this.element);
                                break;
                        case "children":
                                removeChildren(this.element);
                                break;
                        }
                }
}

Slider.prototype.tick = function()
{
        var f = Animator.slowInSlowOut(this.progress);
        var h = this.realHeight * f;
        this.element.style.height = h + "px";
        this.element.style.opacity = f;
}

// ---------------------------------------------------------------------------------
// Augmented methods for the JavaScript Number(), Array() and String() objects
// ---------------------------------------------------------------------------------

// Clamp a number to a range
Number.prototype.clamp = function(min,max)
{
        c = this;
        if(c < min)
                c = min;
        if(c > max)
                c = max;
        return c;
}

// Find an entry in an array. Returns the array index or null
Array.prototype.find = function(item)
{
        var p = null;
        for(var t=0; t<this.length; t++)
                if(this[t] == item)
                        {
                        p = t;
                        break;
                        }
        return p;
}

// Push a new value into an array only if it is not already present in the array. If the optional unique parameter is false, it reverts to a normal push
Array.prototype.pushUnique = function(item,unique)
{
        if(unique != undefined && unique == false)
                this.push(item);
        else
                {
                if(this.find(item) == null)
                        this.push(item);
                }
}

// Get characters from the right end of a string
String.prototype.right = function(n)
{
        if(n < this.length)
                return this.slice(this.length-n);
        else
                return this;
}

// Trim whitespace from both ends of a string
String.prototype.trim = function()
{
        var regexpTrim = new RegExp("^\\s*(.*)\\s*$","mg");
        return(this.replace(regexpTrim,"$1"));
}

// Substitute substrings from an array into a format string that includes '%1'-type specifiers
String.prototype.format = function(substrings)
{
        var subRegExp = new RegExp("(?:%(\\d+))","mg");
        var currPos = 0;
        var r = [];
        do {
                var match = subRegExp.exec(this);
                if(match && match[1])
                        {
                        if(match.index > currPos)
                                r.push(this.substring(currPos,match.index));
                        r.push(substrings[parseInt(match[1])]);
                        currPos = subRegExp.lastIndex;
                        }
        } while(match);
        if(currPos < this.length)
                r.push(this.substring(currPos,this.length));
        return r.join("");
}

// Escape any special RegExp characters with that character preceded by a backslash
String.prototype.escapeRegExp = function()
{
        return(this.replace(new RegExp("[\\\\\\^\\$\\*\\+\\?\\(\\)\\=\\!\\|\\,\\{\\}\\[\\]\\.]","g"),"\\$&"));
}

// Convert & to "&amp;", < to "&lt;", > to "&gt;" and " to "&quot;"
String.prototype.htmlEncode = function()
{
        var regexpAmp = new RegExp("&","mg");
        var regexpLessThan = new RegExp("<","mg");
        var regexpGreaterThan = new RegExp(">","mg");
        var regexpQuote = new RegExp("\"","mg");
        return(this.replace(regexpAmp,"&amp;").replace(regexpLessThan,"&lt;").replace(regexpGreaterThan,"&gt;").replace(regexpQuote,"&quot;"));
}

// Process a string list of macro parameters into an array. Parameters can be quoted with "", '', [[]] or left unquoted (and therefore space-separated)
String.prototype.readMacroParams = function()
{
        var regexpMacroParam = new RegExp("(?:\\s*)(?:(?:\"([^\"]*)\")|(?:'([^']*)')|(?:\\[\\[([^\\]]*)\\]\\])|([^\"'\\s]\\S*))","mg");
        var params = [];
        do {
                var match = regexpMacroParam.exec(this);
                if(match)
                        {
                        if(match[1]) // Double quoted
                                params.push(match[1]);
                        else if(match[2]) // Single quoted
                                params.push(match[2]);
                        else if(match[3]) // Double-square-bracket quoted
                                params.push(match[3]);
                        else if(match[4]) // Unquoted
                                params.push(match[4]);
                        }
        } while(match);
        return params;
}

// Process a string list of tiddler names into an array. Tiddler names that have spaces in them must be [[bracketed]]
String.prototype.readBracketedList = function(unique)
{
        var bracketedPattern = "\\[\\[([^\\]]+)\\]\\]";
        var unbracketedPattern = "[^\\s$]+";
        var pattern = "(?:" + bracketedPattern + ")|(" + unbracketedPattern + ")";
        var re = new RegExp(pattern,"mg");
        var tiddlerNames = [];
        do {
                var match = re.exec(this);
                if(match)
                        {
                        if(match[1]) // Bracketed
                                tiddlerNames.pushUnique(match[1],unique);
                        else if(match[2]) // Unbracketed
                                tiddlerNames.pushUnique(match[2],unique);
                        }
        } while(match);
        return(tiddlerNames);
}

// Static method to bracket a string with double square brackets if it contains a space
String.encodeTiddlyLink = function(title)
{
        if(title.indexOf(" ") == -1)
                return(title);
        else
                return("[[" + title + "]]");
}

// Static method to left-pad a string with 0s to a certain width
String.zeroPad = function(n,d)
{
        var s = n.toString();
        if(s.length < d)
                s = "000000000000000000000000000".substr(0,d-s.length) + s;
        return(s);
}

// ---------------------------------------------------------------------------------
// RGB colour object
// ---------------------------------------------------------------------------------

// Construct an RGB colour object from a '#rrggbb' or 'rgb(n,n,n)' string or from separate r,g,b values
function RGB(r,g,b)
{
        this.r = 0;
        this.g = 0;
        this.b = 0;
        if(typeof r == "string")
                {
                if(r.substr(0,1) == "#")
                        {
                        this.r = parseInt(r.substr(1,2),16)/255;
                        this.g = parseInt(r.substr(3,2),16)/255;
                        this.b = parseInt(r.substr(5,2),16)/255;
                        }
                else
                        {
                        var rgbPattern = /rgb\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/ ;
                        var c = r.match(rgbPattern);
                        if (c)
                                {
                                this.r = parseInt(c[1],10)/255;
                                this.g = parseInt(c[2],10)/255;
                                this.b = parseInt(c[3],10)/255;
                                }
                        }
                }
        else
                {
                this.r = r;
                this.g = g;
                this.b = b;
                }
        return this;
}

// Mixes this colour with another in a specified proportion
// c = other colour to mix
// f = 0..1 where 0 is this colour and 1 is the new colour
// Returns an RGB object
RGB.prototype.mix = function(c,f)
{
        return new RGB(this.r + (c.r-this.r) * f,this.g + (c.g-this.g) * f,this.b + (c.b-this.b) * f);
}

// Return an rgb colour as a #rrggbb format hex string
RGB.prototype.toString = function()
{
        var r = this.r.clamp(0,1);
        var g = this.g.clamp(0,1);
        var b = this.b.clamp(0,1);
        return("#" + ("0" + Math.floor(r * 255).toString(16)).right(2) +
                                 ("0" + Math.floor(g * 255).toString(16)).right(2) +
                                 ("0" + Math.floor(b * 255).toString(16)).right(2));
}

// ---------------------------------------------------------------------------------
// Augmented methods for the JavaScript Date() object
// ---------------------------------------------------------------------------------

// Substitute date components into a string
Date.prototype.formatString = function(template)
{
        template = template.replace("YYYY",this.getFullYear());
        template = template.replace("YY",String.zeroPad(this.getFullYear()-2000,2));
        template = template.replace("MMM",config.messages.dates.months[this.getMonth()]);
        template = template.replace("0MM",String.zeroPad(this.getMonth()+1,2));
        template = template.replace("MM",this.getMonth()+1);
        template = template.replace("DDD",config.messages.dates.days[this.getDay()]);
        template = template.replace("0DD",String.zeroPad(this.getDate(),2));
        template = template.replace("DD",this.getDate());
        template = template.replace("hh",this.getHours());
        template = template.replace("mm",this.getMinutes());
        template = template.replace("ss",this.getSeconds());
        return template;
}

// Convert a date to UTC YYYYMMDDHHMM string format
Date.prototype.convertToYYYYMMDDHHMM = function()
{
        return(String.zeroPad(this.getFullYear(),4) + String.zeroPad(this.getMonth()+1,2) + String.zeroPad(this.getDate(),2) + String.zeroPad(this.getHours(),2) + String.zeroPad(this.getMinutes(),2));
}

// Convert a date to UTC YYYYMMDD.HHMMSSMMM string format
Date.prototype.convertToYYYYMMDDHHMMSSMMM = function()
{
        return(String.zeroPad(this.getFullYear(),4) + String.zeroPad(this.getMonth()+1,2) + String.zeroPad(this.getDate(),2) + "." + String.zeroPad(this.getHours(),2) + String.zeroPad(this.getMinutes(),2) + String.zeroPad(this.getSeconds(),2) + String.zeroPad(this.getMilliseconds(),4));
}

// Static method to create a date from a UTC YYYYMMDDHHMM format string
Date.convertFromYYYYMMDDHHMM = function(d)
{
        var theDate = new Date(parseInt(d.substr(0,4),10),
                                                        parseInt(d.substr(4,2),10)-1,
                                                        parseInt(d.substr(6,2),10),
                                                        parseInt(d.substr(8,2),10),
                                                        parseInt(d.substr(10,2),10),0,0);
        return(theDate);
}

// ---------------------------------------------------------------------------------
// DOM utilities - many derived from www.quirksmode.org 
// ---------------------------------------------------------------------------------

function createTiddlyElement(theParent,theElement,theID,theClass,theText)
{
        var e = document.createElement(theElement);
        if(theClass != null)
                e.className = theClass;
        if(theID != null)
                e.setAttribute("id",theID);
        if(theText != null)
                e.appendChild(document.createTextNode(theText));
        if(theParent != null)
                theParent.appendChild(e);
        return(e);
}

function createTiddlyButton(theParent,theText,theTooltip,theAction,theClass,theId)
{
        var theButton = document.createElement("a");
        theButton.className = "button";
        if(theAction)
                {
                theButton.onclick = theAction;
                theButton.setAttribute("href","JavaScript:;");
                }
        theButton.setAttribute("title",theTooltip);
        if(theText)
                theButton.appendChild(document.createTextNode(theText));
        if(theClass)
                theButton.className = theClass;
        if(theId)
                theButton.id = theId;
        if(theParent)
                theParent.appendChild(theButton);
        return(theButton);
}

function createTiddlyLink(place,title,includeText)
{
        var text = includeText ? title : null;
        var subTitle;
        var tiddler = store.tiddlers[title];
        if(tiddler)
                subTitle = tiddler.getSubtitle();
        else
                subTitle = config.messages.undefinedTiddlerToolTip.format([title]);
        var theClass = tiddler ? "tiddlyLinkExisting tiddlyLink" : "tiddlyLinkNonExisting tiddlyLink";
        var btn = createTiddlyButton(place,text,subTitle,onClickTiddlerLink,theClass);
        btn.setAttribute("tiddlyLink",title);
        return(btn);
}

function createExternalLink(place,url)
{
        var theLink = document.createElement("a");
        theLink.className = "externalLink";
        theLink.href = url;
        theLink.title = config.messages.externalLinkTooltip.format([url]);
        if(config.options.chkOpenInNewWindow)
                theLink.target = "_blank";
        place.appendChild(theLink);
        return(theLink);
}

// Find the tiddler instance (if any) containing a specified element
function findContainingTiddler(e)
{
        if(e == null)
                return(null);
        do {
                if(e != document)
                        {
                        if(e.id)
                                if(e.id.substr(0,7) == "tiddler")
                                        return(e);
                        }
                e = e.parentNode;
        } while(e != document);
        return(null);
}

// Resolve the target object of an event
function resolveTarget(e)
{
        var obj;
        if (e.target)
                obj = e.target;
        else if (e.srcElement)
                obj = e.srcElement;
        if (obj.nodeType == 3) // defeat Safari bug
                obj = obj.parentNode;
        return(obj);
}

// Return the content of an element as plain text with no formatting
function getElementText(elementID)
{
        var e = document.getElementById(elementID);
        var text = "";
        if(e.innerText)
                text = e.innerText;
        else if(e.textContent)
                text = e.textContent;
        return text;
}

// Get the scroll position for window.scrollTo necessary to scroll a given element into view
function ensureVisible(e)
{
        var posTop = findPosY(e);
        var posBot = posTop + e.offsetHeight;
        var winTop = findScrollY();
        var winHeight = findWindowHeight();
        var winBot = winTop + winHeight;
        if(posTop < winTop)
                return(posTop);
        else if(posBot > winBot)
                {
                if(e.offsetHeight < winHeight)
                        return(posTop - (winHeight - e.offsetHeight));
                else
                        return(posTop);
                }
        else
                return(winTop);
}

// Get the current height of the display window
function findWindowHeight()
{
        return(window.innerHeight ? window.innerHeight : document.body.clientHeight);
}

// Get the current vertical page scroll position
function findScrollY()
{
        return(window.scrollY ? window.scrollY : document.body.scrollTop);
}

function findPosX(obj)
{
        var curleft = 0;
        while (obj.offsetParent)
                {
                curleft += obj.offsetLeft;
                obj = obj.offsetParent;
                }
        return curleft;
}

function findPosY(obj)
{
        var curtop = 0;
        while (obj.offsetParent)
                {
                curtop += obj.offsetTop;
                obj = obj.offsetParent;
                }
        return curtop;
}

// Create a non-breaking space
function insertSpacer(place)
{
        var e = document.createTextNode(String.fromCharCode(160));
        if(place)
                place.appendChild(e);
        return e;
}

// Remove all children of a node
function removeChildren(e)
{
        while(e.hasChildNodes())
                e.removeChild(e.firstChild);
}

// Add a stylesheet, replacing any previous custom stylesheet
function setStylesheet(s,id)
{
        if(!id)
                id = "customStyleSheet";
        var n = document.getElementById(id);
        if(document.createStyleSheet) // Test for IE's non-standard createStyleSheet method
                {
                if(n)
                        n.parentNode.removeChild(n);
                // This failed without the &nbsp;
                document.body.insertAdjacentHTML("beforeEnd","&nbsp;<style id='" + id + "'>" + s + "</style>");
                }
        else
                {
                if(n)
                        n.replaceChild(document.createTextNode(s),n.firstChild);
                else
                        {
                        var n = document.createElement("style");
                        n.type = "text/css";
                        n.id = id;
                        n.appendChild(document.createTextNode(s));
                        document.getElementsByTagName("head")[0].appendChild(n);
                        }
                }
}

// ---------------------------------------------------------------------------------
// End of scripts
// ---------------------------------------------------------------------------------