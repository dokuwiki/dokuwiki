<?php

/**
 * Base class for CLI plugins
 *
 * Provides DokuWiki plugin functionality on top of phpcli
 */
abstract class DokuWiki_CLI_Plugin extends \splitbrain\phpcli\CLI implements DokuWiki_PluginInterface {
    use DokuWiki_PluginTrait;

}
