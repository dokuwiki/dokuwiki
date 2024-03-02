<?php

namespace dokuwiki\Debug;

use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Logger;

class DebugHelper
{
    protected const INFO_DEPRECATION_LOG_EVENT = 'INFO_DEPRECATION_LOG';

    /**
     * Check if deprecation messages shall be handled
     *
     * This is either because its logging is not disabled or a deprecation handler was registered
     *
     * @return bool
     */
    public static function isEnabled()
    {
        /** @var EventHandler $EVENT_HANDLER */
        global $EVENT_HANDLER;
        if (
            !Logger::getInstance(Logger::LOG_DEPRECATED)->isLogging() &&
            (!$EVENT_HANDLER instanceof EventHandler || !$EVENT_HANDLER->hasHandlerForEvent('INFO_DEPRECATION_LOG'))
        ) {
            // avoid any work if no one cares
            return false;
        }
        return true;
    }

    /**
     * Log accesses to deprecated fucntions to the debug log
     *
     * @param string $alternative (optional) The function or method that should be used instead
     * @param int $callerOffset (optional) How far the deprecated method is removed from this one
     * @param string $thing (optional) The deprecated thing, defaults to the calling method
     * @triggers \dokuwiki\Debug::INFO_DEPRECATION_LOG_EVENT
     */
    public static function dbgDeprecatedFunction($alternative = '', $callerOffset = 1, $thing = '')
    {
        if (!self::isEnabled()) return;

        $backtrace = debug_backtrace();
        for ($i = 0; $i < $callerOffset; ++$i) {
            if (count($backtrace) > 1) array_shift($backtrace);
        }

        [$self, $call] = $backtrace;

        self::triggerDeprecationEvent(
            $backtrace,
            $alternative,
            self::formatCall($self),
            self::formatCall($call),
            $self['file'] ?? $call['file'] ?? '',
            $self['line'] ?? $call['line'] ?? 0
        );
    }

    /**
     * Format the given backtrace info into a proper function/method call string
     * @param array $call
     * @return string
     */
    protected static function formatCall($call)
    {
        $thing = '';
        if (!empty($call['class'])) {
            $thing .= $call['class'] . '::';
        }
        $thing .= $call['function'] . '()';
        return trim($thing, ':');
    }

    /**
     * This marks logs a deprecation warning for a property that should no longer be used
     *
     * This is usually called withing a magic getter or setter.
     * For logging deprecated functions or methods see dbgDeprecatedFunction()
     *
     * @param string $class The class with the deprecated property
     * @param string $propertyName The name of the deprecated property
     *
     * @triggers \dokuwiki\Debug::INFO_DEPRECATION_LOG_EVENT
     */
    public static function dbgDeprecatedProperty($class, $propertyName)
    {
        if (!self::isEnabled()) return;

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
     * @param int $line
     * @param int $callerOffset How many lines should be removed from the beginning of the backtrace
     */
    public static function dbgCustomDeprecationEvent(
        $alternative,
        $deprecatedThing,
        $caller,
        $file,
        $line,
        $callerOffset = 1
    ) {
        if (!self::isEnabled()) return;

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
     * @param array $backtrace
     * @param string $alternative
     * @param string $deprecatedThing
     * @param string $caller
     * @param string $file
     * @param int $line
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
        $event = new Event(self::INFO_DEPRECATION_LOG_EVENT, $data);
        if ($event->advise_before()) {
            $msg = $event->data['called'] . ' is deprecated. It was called from ';
            $msg .= $event->data['caller'] . ' in ' . $event->data['file'] . ':' . $event->data['line'];
            if ($event->data['alternative']) {
                $msg .= ' ' . $event->data['alternative'] . ' should be used instead!';
            }
            Logger::getInstance(Logger::LOG_DEPRECATED)->log($msg);
        }
        $event->advise_after();
    }
}
