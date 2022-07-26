<?php

namespace dokuwiki\Cache;

/**
 * Handle the caching of modified (resized/cropped) images
 */
class CacheImageMod extends Cache
{

    /** @var string source file */
    protected $file;

    /**
     * @param string $file Original source file
     * @param int $w new width in pixel
     * @param int $h new height in pixel
     * @param string $ext Image extension - no leading dot
     * @param bool $crop Is this a crop?
     */
    public function __construct($file, $w, $h, $ext, $crop)
    {
        $fullext = '.media.' . $w . 'x' . $h;
        $fullext .= $crop ? '.crop' : '';
        $fullext .= ".$ext";

        $this->file = $file;

        $this->setEvent('IMAGEMOD_CACHE_USE');
        parent::__construct($file, $fullext);
    }

    /** @inheritdoc */
    public function makeDefaultCacheDecision()
    {
        if (!file_exists($this->file)) {
            return false;
        }
        return parent::makeDefaultCacheDecision();
    }

    /**
     * Caching depends on the source and the wiki config
     * @inheritdoc
     */
    protected function addDependencies()
    {
        parent::addDependencies();

        $this->depends['files'] = array_merge(
            [$this->file],
            getConfigFiles('main')
        );
    }

}
