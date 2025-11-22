<?php

namespace easywiki\Extension;

use splitbrain\phpcli\CLI;

/**
 * CLI plugin prototype
 *
 * Provides EasyWiki plugin functionality on top of php-cli
 */
abstract class CLIPlugin extends CLI implements PluginInterface
{
    use PluginTrait;
}
