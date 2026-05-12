<?php

namespace dokuwiki\Search\Index;

use dokuwiki\Search\Exception\IndexLockException;

/**
 * Static lock registry for index writing
 *
 * Manages filesystem locks (directories in the lock dir) with in-process
 * reference counting. Multiple callers can acquire the same lock name —
 * the filesystem lock is only created on the first acquire and removed
 * on the last release.
 */
class Lock
{
    /** @var array<string, int> Lock names held by this process with reference counts */
    protected static array $held = [];

    /**
     * Acquire a filesystem lock and register it
     *
     * Idempotent within a process — if already held, increments
     * reference count without touching the filesystem.
     *
     * @param string $name The index base name to lock
     * @throws IndexLockException
     */
    public static function acquire(string $name): void
    {
        if (isset(self::$held[$name])) {
            self::$held[$name]++;
            return;
        }

        $dir = self::lockDir($name);
        if (!@mkdir($dir)) {
            // check for stale lock
            if (time() - @filemtime($dir) > 60 * 5) {
                @rmdir($dir);
                if (!@mkdir($dir)) {
                    throw new IndexLockException('Could not lock ' . $name);
                }
            } else {
                throw new IndexLockException('Could not lock ' . $name);
            }
        }

        self::$held[$name] = 1;
    }

    /**
     * Release a filesystem lock
     *
     * Decrements reference count. Only removes the filesystem lock
     * when the count reaches zero.
     *
     * @param string $name The index base name to unlock
     */
    public static function release(string $name): void
    {
        if (!isset(self::$held[$name])) return;

        self::$held[$name]--;
        if (self::$held[$name] <= 0) {
            unset(self::$held[$name]);
            @rmdir(self::lockDir($name));
        }
    }

    /**
     * Release all held locks
     *
     * Intended for test teardown to ensure a clean state.
     */
    public static function releaseAll(): void
    {
        foreach (array_keys(self::$held) as $name) {
            @rmdir(self::lockDir($name));
        }
        self::$held = [];
    }

    /**
     * Get the lock directory path for a given index name
     *
     * @param string $name The index base name
     * @return string
     */
    protected static function lockDir(string $name): string
    {
        global $conf;
        return $conf['lockdir'] . $name . '.index';
    }
}
