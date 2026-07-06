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
        // raw 20x30 -> rotated 30x20 -> already fits inside 500x500, so the
        // no-upscale bounding-box fit (fit=1 URLs) keeps it at its native size
        $this->assertSame([30, 20], $mf->getDisplayDimensions(500, 500, true));
    }

    public function testGetDisplayDimensionsBboxFitScaleDown()
    {
        $mf = new MediaFile($this->rotated);
        // rotated 30x20 fitted into a smaller 15x15 box preserves aspect,
        // scale = min(15/30, 15/20) = 0.5 => 15 x 10
        $this->assertSame([15, 10], $mf->getDisplayDimensions(15, 15, true));
    }

    public function testGetDisplayDimensionsResizeWithoutFitUpscales()
    {
        $mf = new MediaFile($this->rotated);
        // no fit flag + single dimension = plain resize, which enlarges small
        // images just as fetch.php does: rotated 30x20 to width 500 => 500 x round(500 * 20/30)
        $this->assertSame([500, 333], $mf->getDisplayDimensions(500, 0, false));
    }

    public function testGetDisplayDimensionsCropPassthrough()
    {
        // both dimensions without fit = center-crop, producing exactly the box
        $mf = new MediaFile($this->rotated);
        $this->assertSame([100, 100], $mf->getDisplayDimensions(100, 100, false));
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
