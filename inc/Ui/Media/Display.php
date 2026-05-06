<?php

namespace dokuwiki\Ui\Media;

use dokuwiki\File\MediaFile;

class Display
{
    /** @var MediaFile */
    protected $mediaFile;

    /** @var string should IDs be shown relative to this namespace? Used in search results */
    protected $relativeDisplay;

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
     * Get the HTML for the large detail-view preview
     *
     * Produces the <div class="image"><a><img/></a></div> block shown on the
     * mediamanager "View" tab and in the media diff view. Unlike the thumbnail
     * in {@see getPreviewHtml()}, this uses bounding-box fit so the full image
     * is visible with correct aspect ratio, including EXIF-rotated JPEGs.
     *
     * @param int $w bounding box width
     * @param int $h bounding box height
     * @return string empty string for non-images or unreadable files
     */
    public function getDetailHtml($w = 500, $h = 500)
    {
        global $lang;

        if (!$this->mediaFile->isImage()) return '';

        [$dw, $dh] = $this->mediaFile->getDisplayDimensions($w, $h, false);
        if ($dw <= 0) return '';

        $id = $this->mediaFile->getId();
        $rev = $this->mediaFile->getRev();
        $cacheBust = $rev
            ? ['rev' => $rev]
            : ['t' => filemtime($this->mediaFile->getPath())];

        // pass raw '&' separator so buildAttributes' hsc() encodes exactly once
        $imgAttr = [
            'src' => ml($id, ['w' => $w, 'h' => $h, 'fit' => 1, ...$cacheBust], true, '&'),
            'alt' => $this->mediaFile->getDisplayName(),
            'width' => $dw,
            'height' => $dh,
            'style' => 'max-width: ' . $dw . 'px;',
        ];
        $linkAttr = [
            'href' => ml($id, $cacheBust, true, '&'),
            'target' => '_blank',
            'title' => $lang['mediaview'],
        ];

        return '<div class="image">'
            . '<a ' . buildAttributes($linkAttr) . '>'
            . '<img ' . buildAttributes($imgAttr) . ' />'
            . '</a>'
            . '</div>';
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
            if (str_starts_with($id, $this->relativeDisplay)) {
                $id = substr($id, strlen($this->relativeDisplay));
            }
            return ltrim($id, ':');
        } else {
            return $this->mediaFile->getDisplayName();
        }
    }
}
