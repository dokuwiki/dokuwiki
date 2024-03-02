<?php

namespace LesserPHP;

/**
 * An exception signalling a problem in the LESS source
 */
class ParserException extends \Exception
{
    protected string $error = '';
    protected string $culprit = '';
    protected string $sourceFile = '';
    protected int $sourceLine = -1;

    public function __construct(
        string     $message = '',
        ?string    $culprit = '',
        ?string    $sourceFile = '',
        ?int       $sourceLine = -1,
        \Throwable $previous = null
    ) {
        $this->error = $message;

        if ($culprit) {
            $this->culprit = $culprit;
            $message .= " `$culprit`";
        }
        if ($sourceFile) {
            $this->sourceFile = $sourceFile;
            $message .= " in $sourceFile";
        }

        if ($sourceLine !== null && $sourceLine > -1) {
            $this->sourceLine = $sourceLine;
            $message .= " line: $sourceLine";
        }

        parent::__construct($message, 0, $previous);
    }

    /**
     * This is the error message without any additional context
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * The LESS code that triggered the error
     *
     * This is the line the parser choked on. Not always available.
     */
    public function getCulprit(): string
    {
        return $this->culprit;
    }

    /**
     * The LESS source file where the error was triggered
     *
     * This is the file the parser was parsing, will usually only be available when
     * parsing an import or when compileFile() was used.
     */
    public function getSourceFile(): string
    {
        return $this->sourceFile;
    }

    /**
     * The line number where the error was triggered
     */
    public function getSourceLine(): int
    {
        return $this->sourceLine;
    }
}
