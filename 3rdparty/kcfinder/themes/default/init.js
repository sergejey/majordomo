// Preload some images
$.each([
    "loading.gif",
    "ui-icons_black.png",
    "ui-icons_blue.png",
    "ui-icons_white.png"
], function(i, img) {
    new Image().src = "themes/default/img/" + img;
});
