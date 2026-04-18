<?php

namespace dokuwiki\Parsing;

use dokuwiki\Extension\Event;
use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Parsing\Handler\Block;
use dokuwiki\Parsing\Handler\CallWriter;
use dokuwiki\Parsing\Handler\CallWriterInterface;
use dokuwiki\Parsing\ParserMode\ModeInterface;

/**
 * The Handler receives token events from the Lexer and turns them into
 * instruction calls for the Renderer.
 */
class Handler
{
    /** @var CallWriterInterface */
    protected $callWriter;

    /** @var array The current CallWriter will write directly to this list of calls, Parser reads it */
    public $calls = [];

    /** @var array internal status holders for some modes */
    protected $status = [
        'section' => false,
        'doublequote' => 0,
        'footnote' => false,
    ];

    /** @var bool should blocks be rewritten? FIXME seems to always be true */
    protected $rewriteBlocks = true;

    /** @var array<string, ModeInterface> mode name → mode object for dispatch */
    protected $modeObjects = [];

    /** @var string the original (pre-remap) mode name for the current token */
    protected $currentModeName = '';

    /**
     * Handler constructor.
     */
    public function __construct()
    {
        $this->callWriter = new CallWriter($this);
    }

    /**
     * Register a mode object for token dispatch.
     *
     * Called by the Parser when modes are added.
     *
     * @param string $name Mode name
     * @param ModeInterface $obj The mode object
     */
    public function registerModeObject($name, ModeInterface $obj)
    {
        $this->modeObjects[$name] = $obj;
    }

    /**
     * Get the original mode name for the current token.
     *
     * This is the mode name as registered in the Lexer, before any
     * mapHandler() remapping. Useful for modes that register multiple
     * patterns under different names mapped to the same mode object.
     *
     * @return string
     */
    public function getModeName()
    {
        return $this->currentModeName;
    }

    /**
     * Dispatch a token to the appropriate handler.
     *
     * This is the single entry point called by the Lexer for every token.
     * It dispatches to mode objects, plugins, or sub-mode handler methods.
     *
     * @param string $modeName The resolved mode name
     * @param string $match The matched text
     * @param int $state The lexer state (DOKU_LEXER_* constant)
     * @param int $pos Byte position in the source
     * @param string $originalModeName The original mode name before mapHandler remapping
     * @return bool
     */
    public function handleToken($modeName, $match, $state, $pos, $originalModeName = '')
    {
        $this->currentModeName = $originalModeName ?: $modeName;

        // core modes: dispatch through the mode object's handle() method
        if (isset($this->modeObjects[$modeName])) {
            return $this->modeObjects[$modeName]->handle($match, $state, $pos, $this);
        }

        // plugin modes: extract plugin name and call plugin()
        if (str_starts_with($modeName, 'plugin_')) {
            [, $plugin] = sexplode('_', $modeName, 2, '');
            return $this->plugin($match, $state, $pos, $plugin);
        }

        // should not be reached — all modes should have registered objects
        return false;
    }

    /**
     * Add a new call by passing it to the current CallWriter
     *
     * @param string $handler handler method name (see mode handlers below)
     * @param mixed $args arguments for this call
     * @param int $pos byte position in the original source file
     */
    public function addCall($handler, $args, $pos)
    {
        $call = [$handler, $args, $pos];
        $this->callWriter->writeCall($call);
    }

    /**
     * Accessor for the current CallWriter
     *
     * @return CallWriterInterface
     */
    public function getCallWriter()
    {
        return $this->callWriter;
    }

    /**
     * Set a new CallWriter
     *
     * @param CallWriterInterface $callWriter
     */
    public function setCallWriter($callWriter)
    {
        $this->callWriter = $callWriter;
    }

    /**
     * Return the current internal status of the given name
     *
     * @param string $status
     * @return mixed|null
     */
    public function getStatus($status)
    {
        if (!isset($this->status[$status])) return null;
        return $this->status[$status];
    }

    /**
     * Set a new internal status
     *
     * @param string $status
     * @param mixed $value
     */
    public function setStatus($status, $value)
    {
        $this->status[$status] = $value;
    }

    /** @deprecated 2019-10-31 use addCall() instead */
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- backward compatibility
    public function _addCall($handler, $args, $pos)
    {
        dbg_deprecated('addCall');
        $this->addCall($handler, $args, $pos);
    }

    /**
     * Similar to addCall, but adds a plugin call
     *
     * @param string $plugin name of the plugin
     * @param mixed $args arguments for this call
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $match matched syntax
     */
    public function addPluginCall($plugin, $args, $state, $pos, $match)
    {
        $call = ['plugin', [$plugin, $args, $state, $match], $pos];
        $this->callWriter->writeCall($call);
    }

    /**
     * Finishes handling
     *
     * Called from the parser. Calls finalise() on the call writer, closes open
     * sections, rewrites blocks and adds document_start and document_end calls.
     *
     * @triggers PARSER_HANDLER_DONE
     */
    public function finalize()
    {
        $this->callWriter->finalise();

        if ($this->status['section']) {
            $last_call = end($this->calls);
            $this->calls[] = ['section_close', [], $last_call[2]];
        }

        if ($this->rewriteBlocks) {
            $B = new Block();
            $this->calls = $B->process($this->calls);
        }

        Event::createAndTrigger('PARSER_HANDLER_DONE', $this);

        array_unshift($this->calls, ['document_start', [], 0]);
        $last_call = end($this->calls);
        $this->calls[] = ['document_end', [], $last_call[2]];
    }

    /**
     * Special plugin handler
     *
     * This handler is called for all modes starting with 'plugin_'.
     * An additional parameter with the plugin name is passed. The plugin's handle()
     * method is called here
     *
     * @param string $match matched syntax
     * @param int $state a LEXER_STATE_* constant
     * @param int $pos byte position in the original source file
     * @param string $pluginname name of the plugin
     * @return bool mode handled?
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    public function plugin($match, $state, $pos, $pluginname)
    {
        $data = [$match];
        /** @var SyntaxPlugin $plugin */
        $plugin = plugin_load('syntax', $pluginname);
        if ($plugin != null) {
            $data = $plugin->handle($match, $state, $pos, $this);
        }
        if ($data !== false) {
            $this->addPluginCall($pluginname, $data, $state, $pos, $match);
        }
        return true;
    }

    // region deprecated wrappers — called by plugins, delegate to mode objects

    /**
     * @deprecated 2026-04-16 use the Base mode object's handle() method
     */
    public function base($match, $state, $pos)
    {
        dbg_deprecated(ParserMode\Base::class . '::handle()');
        return $this->modeObjects['base']->handle($match, $state, $pos, $this);
    }

    /**
     * @deprecated 2026-04-16 use the Header mode object's handle() method
     */
    public function header($match, $state, $pos)
    {
        dbg_deprecated(ParserMode\Header::class . '::handle()');
        return $this->modeObjects['header']->handle($match, $state, $pos, $this);
    }

    /**
     * @deprecated 2026-04-16 use the Internallink mode object's handle() method
     */
    public function internallink($match, $state, $pos)
    {
        dbg_deprecated(ParserMode\Internallink::class . '::handle()');
        return $this->modeObjects['internallink']->handle($match, $state, $pos, $this);
    }

    /**
     * @deprecated 2026-04-16 use the Media mode object's handle() method
     */
    public function media($match, $state, $pos)
    {
        dbg_deprecated(ParserMode\Media::class . '::handle()');
        return $this->modeObjects['media']->handle($match, $state, $pos, $this);
    }

    // endregion deprecated wrappers
}
