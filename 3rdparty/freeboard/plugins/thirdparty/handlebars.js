(function () {

  var handlebarsWidget = function (settings) {

    var self = this;
    var htmlElement = $('<div></div>');
    var currentSettings = settings;
    var template;

    this.render = function (element) {
      $(element).append(htmlElement);
    }

    this.onSettingsChanged = function (newSettings) {
      currentSettings = newSettings;
    }

    this.onCalculatedValueChanged = function (settingName, newValue) {

      //helpers need to be registered before compile is called
      if (settingName === 'helpers') {
        console.log('registering helpers');

        //unregister (in case it's already been registered)
        Handlebars.unregisterHelper(newValue);
        
        Handlebars.registerHelper(newValue);
      }      

      //the template needs to be re-compiled when the view changes
      if (settingName === 'view') {
        console.log('view changed');

        //compile the template
        template = Handlebars.compile(newValue);
      }

      //the model is injected into the compiled template to be rendered as HTML
      if (settingName === 'model') {
        console.log('model changed');        

        //evaluate the handlebars template by executing the template with a context
        var html = template(newValue);
        htmlElement.html(html);
      }
    }

    this.onDispose = function () {
    }

    this.getHeight = function () {
      return Number(currentSettings.height);
    }

    this.onSettingsChanged(settings);
  };

  freeboard.loadWidgetPlugin({
    "type_name": "handlebarsWidget",
    "display_name": "Handlebars",    
    "fill_size": true,
    "external_scripts": [
      "http://builds.handlebarsjs.com.s3.amazonaws.com/handlebars-v2.0.0.js"
    ],    
    "settings": [
      {
        "name": "helpers",
        "display_name": "Helpers",
        "type": "calculated",
        "description": "Code that gets passed to Handlebars.registerHelper().  See Handlebars docs."
      },    
      {
        "name": "view",
        "display_name": "view",
        "type": "calculated",
        "description": "HTML view with handlebars template bound to model"
      },
      {
        "name": "model",
        "display_name": "model",
        "type": "calculated",
        "description": "Model bound to view. Typically an exposed datasource or some code to manipulate data for the view"
      },
      {
        "name": "height",
        "display_name": "Height Blocks",
        "type": "number",
        "default_value": 4,
        "description": "A height block is around 60 pixels"
      }
    ],
    newInstance: function (settings, newInstanceCallback) {
      newInstanceCallback(new handlebarsWidget(settings));
    }
  });

}());