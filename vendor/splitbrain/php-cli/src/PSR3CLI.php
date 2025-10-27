<?php

namespace splitbrain\phpcli;

use Psr\Log\LoggerInterface;

/**
 * Class PSR3CLI
 *
 * This class can be used instead of the CLI class when a class implementing
 * PSR3 version 2 is needed.
 *
 * @see PSR3CLIv3 for a version 3 compatible class
 */
abstract class PSR3CLI extends CLI implements LoggerInterface {
}
