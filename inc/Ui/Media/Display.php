<?php

namespace dokuwiki\Ui\Media;

use dokuwiki\File\MediaFile;

class Display
{
    /** @var MediaFile */
    protected $mediaFile;

    /** @var string should IDs be shown relative to this namespace? Used in search results */
    protected $relativeDisplay = null;

    /** @var bool scroll to this file on display? */
    protected $scrollIntoView = false;

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
            $src = ml($this->mediaFile->getId(), ['w' => $w, 'h' => $h]);
        } else {
            $src = $this->getIconUrl();
        }

        $attr = [
            'alt' => $this->mediaFile->getDisplayName(),
            'loading' => 'lazy',
            'width' => $w,
            'height' => $h,
        ];

        return '<img src="' . $src . '" ' . buildAttributes($attr) . ' />';
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

    /**
     * Show IDs relative to this namespace
     *
     * @param string|null $ns Use null to disable
     */
    public function relativeDisplay($ns)
    {
        $this->relativeDisplay = $ns;
    }

    /**
     * Scroll to this file on display?
     *
     * @param bool $set
     */
    public function scrollIntoView($set = true)
    {
        $this->scrollIntoView = $set;
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
    protected function formatDimensions($empty = '&#160;')
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

    /** @return string */
    protected function formatDisplayName()
    {
        if ($this->relativeDisplay !== null) {
            $id = $this->mediaFile->getId();
            if (substr($id, 0, strlen($this->relativeDisplay)) == $this->relativeDisplay) {
                $id = substr($id, strlen($this->relativeDisplay));
            }
            return ltrim($id, ':');
        } else {
            return $this->mediaFile->getDisplayName();
        }
    }
}
