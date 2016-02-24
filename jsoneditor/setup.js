jQuery(function(){
    jQuery('#plugin__struct').find('textarea.config').each(function(){
        var $config = jQuery(this);
        var container = document.createElement('DIV');
        $config.before(container);
        var editor = new JSONEditor(container, {
            onChange: function() {
                $config.val(editor.getText());
            },
            history: false,
            mode: 'form',
            search: false,
            name: 'config'
        });
        editor.setText($config.val());
        $config.hide();
    });

});
