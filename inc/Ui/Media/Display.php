<?php

namespace dokuwiki\Ui\Media;

use dokuwiki\Media\MediaFile;

class Display
{

    protected $mediaFile;

    /**
     * Display constructor.
     * @param MediaFile $mediaFile
     */
    public function __construct(MediaFile $mediaFile)
    {
        $this->mediaFile = $mediaFile;
    }

    /**
     * Get the HTML to display a preview image if possible, otherwise show an icon
     *
     * @param int $w bounding box width to resize pixel based images to
     * @param int $h bounding box height to resize pixel based images to
     * @return string
     */
    public function getPreviewHtml($w, $h)
    {
        if ($this->mediaFile->isImage()) {
            $src = ml($this->mediaFile, ['w' => $w, 'h' => $h]);
        } else {
            $src = $this->getIconUrl();
        }

        return '<img src="' . $src . '" alt="' . hsc($this->mediaFile->getDisplayName()) . '" loading="lazy" />';
    }

    /**
     * Return the URL to the icon for this file
     *
     * @return string
     */
    public function getIconUrl()
    {
        $link = 'lib/images/fileicons/svg/' . $this->mediaFile->getIcoClass() . '.svg';
        if (!file_exists(DOKU_INC . $link)) $link = 'lib/images/fileicons/svg/file.svg';
        return DOKU_BASE . $link;
    }

    /** @return string */
    protected function formatDate()
    {
        return dformat($this->mediaFile->getLastModified());
    }

    /**
     * Output the image dimension if any
     *
     * @param string $empty what to show when no dimensions are available
     * @return string
     */
    protected function formatDimensions($empty = '&#160')
    {
        $w = $this->mediaFile->getWidth();
        $h = $this->mediaFile->getHeight();
        if ($w && $h) {
            return $w . '&#215;' . $h;
        } else {
            return $empty;
        }
    }

    /** @return string */
    protected function formatFileSize()
    {
        return filesize_h($this->mediaFile->getFileSize());
    }
}
