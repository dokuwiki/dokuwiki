<?php

namespace splitbrain\phpcli;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Class PSR3CLI
 *
 * This class can be used instead of the CLI class when a class implementing
 * PSR3 version 3 is needed.
 *
 * @see PSR3CLI for a version 2 compatible class
 */
abstract class PSR3CLIv3 extends Base implements LoggerInterface
{
    use LoggerTrait;

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logMessage($level, $message, $context);
    }
}
