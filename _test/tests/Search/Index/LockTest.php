<?php

namespace dokuwiki\test\Search\Index;

use dokuwiki\Search\Exception\IndexLockException;
use dokuwiki\Search\Index\Lock;

class LockTest extends \DokuWikiTest
{
    protected function tearDown(): void
    {
        Lock::releaseAll();
        parent::tearDown();
    }

    public function testAcquireAndRelease()
    {
        Lock::acquire('test_lock');

        // lock directory should exist
        global $conf;
        $this->assertDirectoryExists($conf['lockdir'] . 'test_lock.index');

        Lock::release('test_lock');

        // lock directory should be removed
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . 'test_lock.index');
    }

    public function testReferenceCounting()
    {
        global $conf;
        $dir = $conf['lockdir'] . 'refcount.index';

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
        $dir = $conf['lockdir'] . 'foreign.index';

        // simulate a foreign lock by creating the directory directly
        mkdir($dir);
        // set mtime to now so it's not stale
        touch($dir);

        $this->expectException(IndexLockException::class);
        Lock::acquire('foreign');
    }

    public function testStaleLockIsOverridden()
    {
        global $conf;
        $dir = $conf['lockdir'] . 'stale.index';

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

        $this->assertDirectoryDoesNotExist($conf['lockdir'] . 'all_a.index');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . 'all_b.index');

        // releasing after releaseAll should be safe
        Lock::release('all_a');
    }

    public function testMultipleIndependentLocks()
    {
        global $conf;

        Lock::acquire('ind_a');
        Lock::acquire('ind_b');

        $this->assertDirectoryExists($conf['lockdir'] . 'ind_a.index');
        $this->assertDirectoryExists($conf['lockdir'] . 'ind_b.index');

        Lock::release('ind_a');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . 'ind_a.index');
        $this->assertDirectoryExists($conf['lockdir'] . 'ind_b.index');

        Lock::release('ind_b');
        $this->assertDirectoryDoesNotExist($conf['lockdir'] . 'ind_b.index');
    }
}
