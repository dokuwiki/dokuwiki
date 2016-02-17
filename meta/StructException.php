<?php

namespace plugin\struct\meta;

/**
 * Class StructException
 *
 * A translatable exception
 *
 * @package plugin\struct\meta
 */
class StructException extends \RuntimeException {

    protected $trans_prefix = 'Exception ';

    /**
     * StructException constructor.
     *
     * @param string $message
     * @param ...string $vars
     */
    public function __construct($message) {
        /** @var \action_plugin_struct_autoloader $plugin */
        $plugin = plugin_load('action', 'struct_autoloader');
        $trans = $plugin->getLang($this->trans_prefix . $message);
        if(!$trans) $trans = $message;

        $args = func_get_args();
        array_shift($args);

        $trans = vsprintf($trans, $args);

        parent::__construct($trans, -1, null);
    }
}
