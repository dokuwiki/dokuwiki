<?php

namespace easywiki\Extension;

/**
 * EasyWiki Base Plugin
 *
 * Most plugin types inherit from this class
 */
abstract class Plugin implements PluginInterface
{
    use PluginTrait;
}
