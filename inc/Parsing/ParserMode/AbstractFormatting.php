<?php

namespace dokuwiki\Parsing\ParserMode;

use dokuwiki\Parsing\Handler;
use dokuwiki\Parsing\ModeRegistry;

/**
 * Base class for inline formatting modes (bold, italic, underline, etc.)
 *
 * Each concrete subclass defines its entry/exit patterns, mode name, and sort order.
 */
abstract class AbstractFormatting extends AbstractMode
{
    /**
     * Constructor. Sets up allowed modes for this formatting type.
     *
     * Formatting modes accept other formatting, substitutions, and disabled modes,
     * but exclude themselves to prevent self-nesting (e.g. bold inside bold).
     */
    public function __construct()
    {
        $self = $this->getModeName();
        $this->allowedModes = array_filter(
            ModeRegistry::getInstance()->getModesForCategories([
                ModeRegistry::CATEGORY_FORMATTING,
                ModeRegistry::CATEGORY_SUBSTITION,
                ModeRegistry::CATEGORY_DISABLED,
            ]),
            static fn($mode) => $mode !== $self
        );
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        // Can't nest formatting in itself
        if ($mode === $this->getModeName()) {
            return;
        }

        $this->Lexer->addEntryPattern(
            $this->getEntryPattern(),
            $mode,
            $this->getModeName()
        );
    }

    /**
     * @return string The regex pattern that starts this formatting
     */
    abstract protected function getEntryPattern(): string;

    /**
     * @return string The regex pattern that ends this formatting
     */
    abstract protected function getExitPattern(): string;

    /**
     * @return string The mode name used for lexer registration
     */
    abstract protected function getModeName(): string;

    /** @inheritdoc */
    public function postConnect()
    {
        $this->Lexer->addExitPattern(
            $this->getExitPattern(),
            $this->getModeName()
        );
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $name = $this->getModeName();
        match ($state) {
            DOKU_LEXER_ENTER => $handler->addCall($name . '_open', [], $pos),
            DOKU_LEXER_EXIT => $handler->addCall($name . '_close', [], $pos),
            DOKU_LEXER_UNMATCHED => $handler->addCall('cdata', [$match], $pos),
            default => true,
        };
        return true;
    }
}
