<?php

namespace dokuwiki\test\Remote\Mock;

use dokuwiki\Extension\RemotePlugin;

class TestPlugin2 extends RemotePlugin
{
    /**
     * This is a dummy method
     *
     * @param string $str some more parameter description
     * @param int $int
     * @param bool $bool
     * @param array $array
     * @return array
     */
    public function commented($str, $int, $bool, $array = [])
    {
        return array($str, $int, $bool);
    }

    private function privateMethod()
    {
        return true;
    }

    protected function protectedMethod()
    {
        return true;
    }

    public function _underscore()
    {
        return true;
    }
}
