<?php

namespace splitbrain\phpcli;

use Psr\Log\LoggerInterface;

/**
 * Class PSR3CLI
 *
 * The same as CLI, but implements the PSR-3 logger interface
 */
abstract class PSR3CLI extends CLI implements LoggerInterface {
}