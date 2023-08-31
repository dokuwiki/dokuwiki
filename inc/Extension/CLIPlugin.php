<?php

namespace dokuwiki\Extension;

use splitbrain\phpcli\CLI;

/**
 * CLI plugin prototype
 *
 * Provides DokuWiki plugin functionality on top of php-cli
 */
abstract class CLIPlugin extends CLI implements PluginInterface
{
    use PluginTrait;
}
