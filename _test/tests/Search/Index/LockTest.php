<?php

namespace dokuwiki\test\Search\Index;

use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Index\Lock;

class LockTest extends \DokuWikiTest
{
    protected function tearDown(): void
    {
        Lock::releaseAll();
        // restore the default wait timeout in case a test lowered it
        self::setInaccessibleProperty(new Lock(), 'waitTimeout', 3);
        parent::tearDown();
    }

    public function testAcquireAndRelease()
    {
        Lock::acquire('test_lock');

        // lock directory should exist
        global $conf;
        $this->assertDirectoryExists($conf['lockdir'] . '/test_lock.index');

        Lock::release('test_lock');

        // lock directory should be removed
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . '/test_lock.index');
    }

    public function testReferenceCounting()
    {
        global $conf;
        $dir = $conf['lockdir'] . '/refcount.index';

        Lock::acquire('refcount');
        Lock::acquire('refcount');
        $this->assertDirectoryExists($dir);

        // first release only decrements, lock stays
        Lock::release('refcount');
        $this->assertDirectoryExists($dir);

        // second release removes the lock
        Lock::release('refcount');
        $this->assertDirectoryDoesNotExist($dir);
    }

    public function testReleaseUnheldLockIsNoop()
    {
        // should not throw or error
        Lock::release('never_acquired');
        $this->assertTrue(true);
    }

    public function testAcquireFailsWhenAlreadyLockedByAnotherProcess()
    {
        global $conf;
        $dir = $conf['lockdir'] . '/foreign.index';

        // simulate a foreign lock by creating the directory directly
        mkdir($dir);
        // set mtime to now so it's not stale
        touch($dir);

        // don't wait for the foreign lock in the test
        self::setInaccessibleProperty(new Lock(), 'waitTimeout', 0);

        $this->expectException(IndexLockException::class);
        Lock::acquire('foreign');
    }

    public function testAcquireAppliesConfiguredDirectoryPermissions()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Permission checks skipped on Windows');
        }

        global $conf;
        $dir = $conf['lockdir'] . '/perm.index';

        $oldumask = umask(0);
        $conf['dperm'] = 0707;
        try {
            Lock::acquire('perm');

            clearstatcache();
            $this->assertSame(0707, fileperms($dir) & 0777);
        } finally {
            Lock::release('perm');
            umask($oldumask);
        }
    }

    public function testStaleLockIsOverridden()
    {
        global $conf;
        $dir = $conf['lockdir'] . '/stale.index';

        // simulate a stale lock (older than 5 minutes)
        mkdir($dir);
        touch($dir, time() - 400);

        // should succeed by removing the stale lock
        Lock::acquire('stale');
        $this->assertDirectoryExists($dir);

        Lock::release('stale');
    }

    public function testReleaseAll()
    {
        global $conf;

        Lock::acquire('all_a');
        Lock::acquire('all_b');
        Lock::acquire('all_a'); // refcount 2

        Lock::releaseAll();

        $this->assertDirectoryDoesNotExist($conf['lockdir'] . '/all_a.index');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . '/all_b.index');

        // releasing after releaseAll should be safe
        Lock::release('all_a');
    }

    public function testMultipleIndependentLocks()
    {
        global $conf;

        Lock::acquire('ind_a');
        Lock::acquire('ind_b');

        $this->assertDirectoryExists($conf['lockdir'] . '/ind_a.index');
        $this->assertDirectoryExists($conf['lockdir'] . '/ind_b.index');

        Lock::release('ind_a');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . '/ind_a.index');
        $this->assertDirectoryExists($conf['lockdir'] . '/ind_b.index');

        Lock::release('ind_b');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . '/ind_b.index');
    }

    public function testAcquireCreatesLockInsideConfiguredLockDirectory()
    {
        global $conf;

        $dir = $conf['lockdir'] . '/page.index';
        $wrongDir = dirname($conf['lockdir']) . '/page.index';

        Lock::acquire('page');

        $this->assertDirectoryExists($dir);
        $this->assertDirectoryDoesNotExist($wrongDir);

        Lock::release('page');
    }
}
