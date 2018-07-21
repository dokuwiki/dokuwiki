<?php

use dokuwiki\Extension\RemotePlugin;

/**
 * For testing automatically extracted method descriptions
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class remote_plugin_testing_auto extends RemotePlugin
{
    /**
     * This is a dummy method
     *
     * @param string $str some more parameter description
     * @param int $int
     * @param bool $bool
     * @param Object $unknown
     * @return array
     */
    public function commented($str, $int, $bool, $unknown)
    {
        return array($str, $int, $bool);
    }

    /**
     * This should not be accessible via API
     * @return bool
     */
    private function privateMethod()
    {
        return true;
    }

    /**
     * This should not be accessible via API
     * @return bool
     */
    protected function protectedMethod()
    {
        return true;
    }

    /**
     * This should not be accessible via API
     * @return bool
     */
    public function _underscore()
    {
        return true;
    }
}
