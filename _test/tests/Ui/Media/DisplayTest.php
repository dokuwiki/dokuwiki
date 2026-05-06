<?php

namespace dokuwiki\test\Ui\Media;

use dokuwiki\File\MediaFile;
use dokuwiki\Ui\Media\Display;

/**
 * Tests for dokuwiki\Ui\Media\Display::getDetailHtml().
 *
 * Fixture wiki:exif-orient-6.jpg is a 20x30 JPEG carrying EXIF orientation 6,
 * so its rotated display orientation is 30x20.
 */
class DisplayTest extends \DokuWikiTest
{
    /** @var string */
    private $rotated = 'wiki:exif-orient-6.jpg';

    public function testGetDetailHtmlEmitsBboxFittedRotatedDims()
    {
        $display = new Display(new MediaFile($this->rotated));
        $html = $display->getDetailHtml(500, 500);

        // rotated 30x20 fit into 500x500 bbox -> 500x333 (matches MediaFileTest)
        $this->assertStringContainsString('width="500"', $html);
        $this->assertStringContainsString('height="333"', $html);
    }

    public function testGetDetailHtmlStructure()
    {
        $display = new Display(new MediaFile($this->rotated));
        $html = $display->getDetailHtml();

        $this->assertStringContainsString('<div class="image">', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('fit=1', $html);
        $this->assertStringContainsString('<img ', $html);
    }

    public function testGetDetailHtmlReturnsEmptyForNonImage()
    {
        $display = new Display(new MediaFile('nonexistent:file.jpg'));
        $this->assertSame('', $display->getDetailHtml());
    }

    public function testGetDetailHtmlUsesRevParamWhenRevIsSet()
    {
        $rev = 1700000000;
        $atticPath = mediaFN($this->rotated, $rev);
        if (!is_dir(dirname($atticPath))) mkdir(dirname($atticPath), 0777, true);
        copy(mediaFN($this->rotated), $atticPath);
        try {
            $html = (new Display(new MediaFile($this->rotated, $rev)))->getDetailHtml();
            $this->assertMatchesRegularExpression('/[?&][^f]*?rev=' . $rev . '/', $html);
            $this->assertDoesNotMatchRegularExpression('/[?&](?:amp;)*t=\d/', $html);
        } finally {
            unlink($atticPath);
        }
    }

    public function testGetDetailHtmlUsesTimestampWhenNoRev()
    {
        $html = (new Display(new MediaFile($this->rotated)))->getDetailHtml();
        $this->assertMatchesRegularExpression('/[?&](?:amp;)*t=\d/', $html);
        $this->assertStringNotContainsString('rev=', $html);
    }
}
