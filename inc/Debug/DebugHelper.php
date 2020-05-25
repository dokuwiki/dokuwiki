<?php


namespace dokuwiki\Debug;

use Doku_Event;
use dokuwiki\Extension\EventHandler;

class DebugHelper
{
    const INFO_DEPRECATION_LOG_EVENT = 'INFO_DEPRECATION_LOG';

    /**
     * Log accesses to deprecated fucntions to the debug log
     *
     * @param string $alternative  (optional) The function or method that should be used instead
     * @param int    $callerOffset (optional) How far the deprecated method is removed from this one
     *
     * @triggers \dokuwiki\Debug::INFO_DEPRECATION_LOG_EVENT
     */
    public static function dbgDeprecatedFunction($alternative = '', $callerOffset = 1)
    {
        global $conf;
        /** @var EventHandler $EVENT_HANDLER */
        global $EVENT_HANDLER;
        if (
            !$conf['allowdebug'] &&
            ($EVENT_HANDLER === null || !$EVENT_HANDLER->hasHandlerForEvent('INFO_DEPRECATION_LOG'))
        ){
            // avoid any work if no one cares
            return;
        }

        $backtrace = debug_backtrace();
        for ($i = 0; $i < $callerOffset; $i += 1) {
            array_shift($backtrace);
        }

        list($self, $call) = $backtrace;

        self::triggerDeprecationEvent(
            $backtrace,
            $alternative,
            trim(
                (!empty($self['class']) ? ($self['class'] . '::') : '') .
                $self['function'] . '()', ':'),
            trim(
                (!empty($call['class']) ? ($call['class'] . '::') : '') .
                $call['function'] . '()', ':'),
            $call['file'],
            $call['line']
        );
    }

    /**
     * This marks logs a deprecation warning for a property that should no longer be used
     *
     * This is usually called withing a magic getter or setter.
     * For logging deprecated functions or methods see dbgDeprecatedFunction()
     *
     * @param string $class        The class with the deprecated property
     * @param string $propertyName The name of the deprecated property
     *
     * @triggers \dokuwiki\Debug::INFO_DEPRECATION_LOG_EVENT
     */
    public static function dbgDeprecatedProperty($class, $propertyName)
    {
        global $conf;
        global $EVENT_HANDLER;
        if (!$conf['allowdebug'] && !$EVENT_HANDLER->hasHandlerForEvent(self::INFO_DEPRECATION_LOG_EVENT)) {
            // avoid any work if no one cares
            return;
        }

        $backtrace = debug_backtrace();
        array_shift($backtrace);
        $call = $backtrace[1];
        $caller = trim($call['class'] . '::' . $call['function'] . '()', ':');
        $qualifiedName = $class . '::$' . $propertyName;
        self::triggerDeprecationEvent(
            $backtrace,
            '',
            $qualifiedName,
            $caller,
            $backtrace[0]['file'],
            $backtrace[0]['line']
        );
    }

    /**
     * Trigger a custom deprecation event
     *
     * Usually dbgDeprecatedFunction() or dbgDeprecatedProperty() should be used instead.
     * This method is intended only for those situation where they are not applicable.
     *
     * @param string $alternative
     * @param string $deprecatedThing
     * @param string $caller
     * @param string $file
     * @param int    $line
     * @param int    $callerOffset How many lines should be removed from the beginning of the backtrace
     */
    public static function dbgCustomDeprecationEvent(
        $alternative,
        $deprecatedThing,
        $caller,
        $file,
        $line,
        $callerOffset = 1
    ) {
        global $conf;
        /** @var EventHandler $EVENT_HANDLER */
        global $EVENT_HANDLER;
        if (!$conf['allowdebug'] && !$EVENT_HANDLER->hasHandlerForEvent(self::INFO_DEPRECATION_LOG_EVENT)) {
            // avoid any work if no one cares
            return;
        }

        $backtrace = array_slice(debug_backtrace(), $callerOffset);

        self::triggerDeprecationEvent(
            $backtrace,
            $alternative,
            $deprecatedThing,
            $caller,
            $file,
            $line
        );

    }

    /**
     * @param array  $backtrace
     * @param string $alternative
     * @param string $deprecatedThing
     * @param string $caller
     * @param string $file
     * @param int    $line
     */
    private static function triggerDeprecationEvent(
        array $backtrace,
        $alternative,
        $deprecatedThing,
        $caller,
        $file,
        $line
    ) {
        $data = [
            'trace' => $backtrace,
            'alternative' => $alternative,
            'called' => $deprecatedThing,
            'caller' => $caller,
            'file' => $file,
            'line' => $line,
        ];
        $event = new Doku_Event(self::INFO_DEPRECATION_LOG_EVENT, $data);
        if ($event->advise_before()) {
            $msg = $event->data['called'] . ' is deprecated. It was called from ';
            $msg .= $event->data['caller'] . ' in ' . $event->data['file'] . ':' . $event->data['line'];
            if ($event->data['alternative']) {
                $msg .= ' ' . $event->data['alternative'] . ' should be used instead!';
            }
            dbglog($msg);
        }
        $event->advise_after();
    }
}
