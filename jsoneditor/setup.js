/**
 * Initializes the JSON Editor for the schema editor
 *
 * This script and the editor script itself is only loaded on the admin screen.
 */
jQuery(function () {
    var $schema_editor = jQuery('#plugin__struct_editor');

    /**
     * Initialize the Editor
     */
    $schema_editor.find('textarea.config').each(function () {
        var $config = jQuery(this);
        var container = document.createElement('DIV');
        $config.before(container);
        var editor = new JSONEditor(container, {
            onChange: function () {
                $config.val(editor.getText());
            },
            history: false,
            mode: 'form',
            search: false,
            name: 'config'
        });
        editor.setText($config.val());
        $config.hide();
        // define a function to reload later
        this.updateEditor = function () {
            editor.setText($config.val());
        };
    });

    /**
     * Autoload the correct configuration
     */
    $schema_editor.find('td.class select').change(function () {
        var type = jQuery(this).val();
        var $editor = jQuery(this).parents('tr').find('textarea.config');
        var conf = $editor.val();
        $editor.val('"..."')[0].updateEditor();

        jQuery.post(
            DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: 'plugin_struct_config',
                type: type,
                conf: conf
            },
            function (conf) {
                $editor.val(conf)[0].updateEditor();
            }
        )
    });
});
