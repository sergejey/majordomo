// ┌────────────────────────────────────────────────────────────────────┐ \\
// │ freeboard-actuator-plugin                                          │ \\
// ├────────────────────────────────────────────────────────────────────┤ \\
// │ http://blog.onlinux.fr/actuator-plugin-for-freeboard-io/           │ \\
// ├────────────────────────────────────────────────────────────────────┤ \\
// │ Licensed under the MIT license.                                    │ \\
// ├────────────────────────────────────────────────────────────────────┤ \\
// │ Freeboard widget plugin.                                           │ \\
// └────────────────────────────────────────────────────────────────────┘ \\
(function () {
    //
    // DECLARATIONS
    //
    var LOADING_INDICATOR_DELAY = 1000;

    //

    freeboard.loadWidgetPlugin({
        type_name: "actuator",
        display_name: "Actuator",
        description: "Actuator which can send a value as well as receive one",
        settings: [
            {
                name: "title",
                display_name: "Title",
                type: "text"
            },
            {
                name: "value",
                display_name: "Value",
                type: "calculated"
            },
            {
                name: "urlOn",
                display_name: "url On ",
                type: "calculated"
            },
            {
                name: "urlOff",
                display_name: "url Off ",
                type: "calculated"
            },
            {
                name: "on_text",
                display_name: "On Text",
                type: "calculated"
            },
            {
                name: "off_text",
                display_name: "Off Text",
                type: "calculated"
            }

        ],
        newInstance: function (settings, newInstanceCallback) {
            newInstanceCallback(new actuator(settings));
        }
    });

    freeboard.addStyle('.indicator-light.interactive:hover', "box-shadow: 0px 0px 15px #FF9900; cursor: pointer;");
    var actuator = function (settings) {
        var self = this;
        var titleElement = $('<h2 class="section-title"></h2>');
        var stateElement = $('<div class="indicator-text"></div>');
        var indicatorElement = $('<div class="indicator-light interactive"></div>');
        var currentSettings = settings;
        var isOn = false;
        var onText;
        var offText;
        var url;

        function updateState() {
            indicatorElement.toggleClass("on", isOn);

            if (isOn) {
                stateElement.text((_.isUndefined(onText) ? (_.isUndefined(currentSettings.on_text) ? "" : currentSettings.on_text) : onText));
            }
            else {
                stateElement.text((_.isUndefined(offText) ? (_.isUndefined(currentSettings.off_text) ? "" : currentSettings.off_text) : offText));
            }
        }


        this.onClick = function(e) {
            e.preventDefault()

            var new_val = !isOn
            this.onCalculatedValueChanged('value', new_val);
            url = (new_val) ? currentSettings.urlOn : currentSettings.urlOff;
            if (_.isUndefined(url))
                freeboard.showDialog($("<div align='center'>url undefined</div>"), "Error!", "OK", null, function () {
                });
            else {
                this.sendValue(url, new_val);
            }
        }


        this.render = function (element) {
            $(element).append(titleElement).append(indicatorElement).append(stateElement);
            $(indicatorElement).click(this.onClick.bind(this));
        }

        this.onSettingsChanged = function (newSettings) {
            currentSettings = newSettings;
            titleElement.html((_.isUndefined(newSettings.title) ? "" : newSettings.title));
            updateState();
        }

        this.onCalculatedValueChanged = function (settingName, newValue) {
            if (settingName == "value") {
                isOn = Boolean(newValue);
            }
            if (settingName == "on_text") {
                onText = newValue;
            }
            if (settingName == "off_text") {
                offText = newValue;
            }
            updateState();
        }

        var request;

        this.sendValue = function (url, options) {
            console.log(url, options);
            request = new XMLHttpRequest();
            if (!request) {
                console.log('Giving up :( Cannot create an XMLHTTP instance');
                return false;
            }
            request.onreadystatechange = this.alertContents;
            request.open('GET', url, true);
            freeboard.showLoadingIndicator(true);
            request.send();
        }

        this.alertContents = function () {
            if (request.readyState === XMLHttpRequest.DONE) {
                if (request.status === 200) {
                    console.log(request.responseText);
                    setTimeout(function () {
                        freeboard.showLoadingIndicator(false);
                        //freeboard.showDialog($("<div align='center'>Request response 200</div>"),"Success!","OK",null,function(){});
                    }, LOADING_INDICATOR_DELAY);
                } else {
                    console.log('There was a problem with the request.');
                    setTimeout(function () {
                        freeboard.showLoadingIndicator(false);
                        freeboard.showDialog($("<div align='center'>There was a problem with the request. Code " + request.status + request.responseText + " </div>"), "Error!", "OK", null, function () {
                        });
                    }, LOADING_INDICATOR_DELAY);
                }

            }

        }

        this.onDispose = function () {
        }

        this.getHeight = function () {
            return 1;
        }

        this.onSettingsChanged(settings);
    };

}());