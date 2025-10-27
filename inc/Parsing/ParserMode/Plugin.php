<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Extension\PluginInterface;
use dokuwiki\Extension\PluginTrait;

/**
 * A syntax Plugin is a ParserMode
 */
abstract class Plugin extends AbstractMode implements PluginInterface
{
    use PluginTrait;
}
