<?php

class init_creationmodes_test extends DokuWikiTest
{

    protected $oldumask;
    protected $dir;
    protected $file;

    /** @inheritDoc */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            self::markTestSkipped('Permission checks skipped on Windows');
        }
    }

    /**
     * set up the file and directory we use for testing
     */
    protected function init()
    {
        $this->dir = getCacheName('dir', '.creationmode_test');
        $this->file = getCacheName('bar', '.creationmode_test');
    }

    /** @inheritDoc */
    public function setUp(): void
    {
        parent::setUp();

        if (!isset($this->dir)) $this->init();
        $this->oldumask = umask();
    }

    /** @inheritDoc */
    protected function tearDown(): void
    {
        umask($this->oldumask);

        chmod($this->dir, 0777);
        rmdir($this->dir);

        chmod($this->file, 0777);
        unlink($this->file);

        parent::tearDown();

    }

    /**
     * @return Generator|string[]
     * @see testFilemodes
     */
    public function provideFilemodes()
    {
        $umasks = [0000, 0022, 0002, 0007];
        $fmodes = [0777, 0666, 0644, 0640, 0664, 0660];
        $dmodes = [0777, 0775, 0755, 0750, 0770, 0700];

        foreach ($umasks as $umask) {
            foreach ($dmodes as $dmode) {
                foreach ($fmodes as $fmode) {
                    yield [$umask, $dmode, $fmode];
                }
            }
        }
    }

    /**
     * @dataProvider provideFilemodes
     */
    public function testFilemodes($umask, $dmode, $fmode)
    {
        global $conf;

        // setup
        $conf['dmode'] = $dmode;
        $conf['fmode'] = $fmode;
        umask($umask);

        // create
        init_creationmodes();
        io_mkdir_p($this->dir);
        io_saveFile($this->file, 'test');

        // get actual values (removing the status bits)
        clearstatcache();
        $dperm = fileperms($this->dir) - 0x4000;
        $fperm = fileperms($this->file) - 0x8000;


        $this->assertSame($dmode, $dperm,
            sprintf(
                'dir had %04o, expected %04o with umask %04o (fperm: %04o)',
                $dperm, $dmode, $umask, $conf['dperm']
            )
        );
        $this->assertSame($fmode, $fperm,
            sprintf(
                'file had %04o, expected %04o with umask %04o (fperm: %04o)',
                $fperm, $fmode, $umask, $conf['fperm']
            )
        );
    }

}

