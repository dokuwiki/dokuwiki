<?php

namespace dokuwiki\Ui\Media;

use dokuwiki\Utf8\PhpString;

/**
 * Display a MediaFile in the Media Popup
 */
class DisplayRow extends DisplayTile
{
    /** @inheritDoc */
    public function show()
    {
        global $lang;
        // FIXME Zebra classes have been dropped and need to be readded via CSS

        $id = $this->mediaFile->getId();
        $class = 'select mediafile mf_' . $this->mediaFile->getIcoClass();
        $info = trim($this->formatDimensions('') . ' ' . $this->formatDate() . ' ' . $this->formatFileSize());
        $jump = $this->scrollIntoView ? 'id="scroll__here"' : '';

        echo '<div title="' . $id . '" ' . $jump . '>';
        echo '<a id="h_:' . $id . '" class="' . $class . '">' .
            $this->formatDisplayName() .
            '</a> ';
        echo '<span class="info">(' . $info . ')</span>' . NL;

        // view button
        $link = ml($id, '', true);
        echo ' <a href="' . $link . '" target="_blank"><img src="' . DOKU_BASE . 'lib/images/magnifier.png" ' .
            'alt="' . $lang['mediaview'] . '" title="' . $lang['mediaview'] . '" class="btn" /></a>';

        // mediamanager button
        $link = wl('', array('do' => 'media', 'image' => $id, 'ns' => getNS($id)));
        echo ' <a href="' . $link . '" target="_blank"><img src="' . DOKU_BASE . 'lib/images/mediamanager.png" ' .
            'alt="' . $lang['btn_media'] . '" title="' . $lang['btn_media'] . '" class="btn" /></a>';

        // delete button
        if ($this->mediaFile->isWritable() && $this->mediaFile->userPermission() >= AUTH_DELETE) {
            $link = DOKU_BASE . 'lib/exe/mediamanager.php?delete=' . rawurlencode($id) .
                '&amp;sectok=' . getSecurityToken();
            echo ' <a href="' . $link . '" class="btn_media_delete" title="' . $id . '">' .
                '<img src="' . DOKU_BASE . 'lib/images/trash.png" alt="' . $lang['btn_delete'] . '" ' .
                'title="' . $lang['btn_delete'] . '" class="btn" /></a>';
        }

        echo '<div class="example" id="ex_' . str_replace(':', '_', $id) . '">';
        echo $lang['mediausage'] . ' <code>{{:' . $id . '}}</code>';
        echo '</div>';
        if ($this->mediaFile->isImage()) $this->showDetails();
        echo '<div class="clearer"></div>' . NL;
        echo '</div>' . NL;

    }

    /**
     * Show Thumbnail and EXIF data
     */
    protected function showDetails()
    {
        $id = $this->mediaFile->getId();

        echo '<div class="detail">';
        echo '<div class="thumb">';
        echo '<a id="d_:' . $id . '" class="select">';
        echo $this->getPreviewHtml(120, 120);
        echo '</a>';
        echo '</div>';

        // read EXIF/IPTC data
        $t = $this->mediaFile->getMeta()->getField(array('IPTC.Headline', 'xmp.dc:title'));
        $d = $this->mediaFile->getMeta()->getField(array(
            'IPTC.Caption',
            'EXIF.UserComment',
            'EXIF.TIFFImageDescription',
            'EXIF.TIFFUserComment',
        ));
        if (PhpString::strlen($d) > 250) $d = PhpString::substr($d, 0, 250) . '...';
        $k = $this->mediaFile->getMeta()->getField(array('IPTC.Keywords', 'IPTC.Category', 'xmp.dc:subject'));

        // print EXIF/IPTC data
        if ($t || $d || $k) {
            echo '<p>';
            if ($t) echo '<strong>' . hsc($t) . '</strong><br />';
            if ($d) echo hsc($d) . '<br />';
            if ($t) echo '<em>' . hsc($k) . '</em>';
            echo '</p>';
        }
        echo '</div>';
    }

}
