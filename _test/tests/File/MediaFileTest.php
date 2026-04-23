<?php

namespace dokuwiki\test\File;

use dokuwiki\File\MediaFile;

/**
 * Tests for dokuwiki\File\MediaFile display-dimension helpers.
 *
 * The fixture wiki:exif-orient-6.jpg is a 20x30 raw JPEG carrying EXIF
 * orientation 6, so its display orientation is 30x20 (portrait -> landscape swap).
 */
class MediaFileTest extends \DokuWikiTest
{
    /** @var string */
    private $rotated = 'wiki:exif-orient-6.jpg';

    /** @var string */
    private $unrotated = 'wiki:dokuwiki-128.png';

    public function testDisplayDimsForNonImageReturnsZero()
    {
        $mf = new MediaFile('nonexistent:file.jpg');
        $this->assertSame([0, 0], $mf->getDisplayDimensions(500, 500));
    }

    public function testGetDisplayDimensionsBboxFitRotated()
    {
        $mf = new MediaFile($this->rotated);
        // raw 20x30 -> rotated 30x20 -> fit into 500x500 preserves aspect,
        // max dimension scales from 30 to 500 => 500 x round(500 * 20/30) = 500 x 333
        $this->assertSame([500, 333], $mf->getDisplayDimensions(500, 500, false));
    }

    public function testGetDisplayDimensionsCropPassthrough()
    {
        $mf = new MediaFile($this->rotated);
        $this->assertSame([100, 100], $mf->getDisplayDimensions(100, 100, true));
    }

    public function testGetDisplayDimensionsNativeWhenNoRequest()
    {
        $mf = new MediaFile($this->rotated);
        $this->assertSame([30, 20], $mf->getDisplayDimensions(0, 0, false));
    }

    public function testGetDisplayDimensionsUnrotatedImage()
    {
        $mf = new MediaFile($this->unrotated);
        // non-JPEG: display dims equal raw dims
        $this->assertSame([$mf->getWidth(), $mf->getHeight()], $mf->getDisplayDimensions(0, 0, false));
    }
}
