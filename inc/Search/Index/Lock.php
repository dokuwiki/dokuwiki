<?php

namespace dokuwiki\Search\Index;

/**
 * Manage locking for index writing
 *
 * Locks are directories in the dta/lock directory named after the index name
 */
class Lock
{
    protected $lockDir = '';
    protected $indexName = '';

    /**
     * @param string $name Name of the index
     */
    public function __construct($name)
    {
        global $conf;
        $this->indexName = $name;
        $this->lockDir = $conf['lockdir'] . $name . '.index';
    }

    /**
     * Try to acquire a lock for an index
     *
     * @return bool true if a lock was acquired, otherwise false
     */
    public function acquire()
    {
        if(@mkdir($this->lockDir)) return true;
        // creation of the lockdir failed, check if it's stale
        if(time() - filemtime($this->lockDir) > 60*5) {
            // try to release, then lock again
            $this->release();
            return @mkdir($this->lockDir);
        }
        return false;
    }

    /**
     * Release the lock for this index
     * 
     * @return void
     */
    public function release()
    {
        @rmdir($this->lockDir);
    }

}
