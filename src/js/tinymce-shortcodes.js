(function() {
    tinymce.PluginManager.add('rrze_lecture_shortcode', function(editor) {

    var menuItems = [];
    menuItems.push({
        text: 'Lectures',
        icon: 'paste', 
        menu: [
            {
                type: 'menuitem',
                text: 'Alle',
                onclick: function() {
                    editor.insertContent('[lecture task="lectures-all"]');
                }
            },
            {
                type: 'menuitem',
                text: 'Einzelne',
                onclick: function() {
                    editor.insertContent('[lecture task="lectures-single" lv_id=""]');
                }
            },
        ]
    });

    editor.addMenuItem('insertShortcodesRRZELecture', {
        icon: 'orientation', 
        text: 'RRZE-DIP',
        menu: menuItems,
        context: 'insert',
    });
});
})();