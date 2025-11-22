<?php

namespace easywiki\Parsing\ParserMode;

use easywiki\Extension\PluginInterface;
use easywiki\Extension\PluginTrait;

/**
 * A syntax Plugin is a ParserMode
 */
abstract class Plugin extends AbstractMode implements PluginInterface
{
    use PluginTrait;
}
