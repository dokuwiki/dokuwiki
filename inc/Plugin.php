<?php

/**
 * DokuWiki Plugin
 *
 * Most of DokuWiki's plugin types simply inherit from this. All it does is
 * add the DokuWiki_PluginTrait to the class.
 */
class DokuWiki_Plugin implements DokuWiki_PluginInterface {
    use DokuWiki_PluginTrait;
}
