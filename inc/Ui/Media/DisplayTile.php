<?php

namespace dokuwiki\Ui\Media;

use dokuwiki\File\MediaFile;

/**
 * Display a MediaFile in the FullScreen MediaManager
 */
class DisplayTile extends Display
{
    /** @var string URL to open this file in the media manager */
    protected $mmUrl;

    /** @inheritDoc */
    public function __construct(MediaFile $mediaFile)
    {
        parent::__construct($mediaFile);

        // FIXME we may want to integrate this function here or in another class
        $this->mmUrl = media_managerURL([
            'image' => $this->mediaFile->getId(),
            'ns' => getNS($this->mediaFile->getId()),
            'tab_details' => 'view',
        ]);
    }

    /**
     * Display the tile
     */
    public function show()
    {
        $jump = $this->scrollIntoView ? 'id="scroll__here"' : '';

        echo '<dl title="' . $this->mediaFile->getDisplayName() . '"' . $jump . '>';
        echo '<dt>';
        echo '<a id="l_:' . $this->mediaFile->getId() . '" class="image thumb" href="' . $this->mmUrl . '">';
        echo $this->getPreviewHtml(90, 90);
        echo '</a>';
        echo '</dt>';

        echo '<dd class="name">';
        echo '<a href="' . $this->mmUrl . '" id="h_:' . $this->mediaFile->getId() . '">' .
            $this->formatDisplayName() .
            '</a>';
        echo '</dd>';

        echo '<dd class="size">' . $this->formatDimensions() . '</dd>';
        echo '<dd class="date">' . $this->formatDate() . '</dd>';
        echo '<dd class="filesize">' . $this->formatFileSize() . '</dd>';

        echo '</dl>';
    }
}
