<?php

namespace dokuwiki\Extension;

/**
 * CLI plugin prototype
 *
 * Provides DokuWiki plugin functionality on top of php-cli
 */
abstract class CLIPlugin extends \splitbrain\phpcli\CLI implements PluginInterface
{
    use PluginTrait;
}
