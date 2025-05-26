<?php

namespace dokuwiki\plugin\extension;

use dokuwiki\Remote\Response\ApiResponse;

class ExtensionApiResponse extends ApiResponse
{
    protected Extension $extension;

    /** @var string The type of this extension ("plugin" or "template") */
    public $type;

    /** @var string The id of this extension (templates are prefixed with "template") */
    public $id;

    /** @var string The base name of this extension */
    public $base;

    /** @var string The display name of this extension */
    public $name;

    /** @var string The installed version/date of this extension */
    public $version;

    /** @var string The author of this extension */
    public $author;

    /** @var string The description of this extension */
    public $description;

    /** @var bool Whether this extension is installed */
    public $isInstalled;

    /** @var bool Whether this extension is enabled */
    public $isEnabled;

    /** @var bool Whether an update is available */
    public $updateAvailable;

    /** @var bool Whether this extension is bundled with DokuWiki */
    public $isBundled;

    /** @var bool Whether this extension is under git control */
    public $isGitControlled;

    /** @var string[] Notices for this extension */
    public $notices;

    /** @var string Documentation URL for this extension */
    public $url;

    /** @var string[] The component types this plugin provides */
    public $componentTypes;

    /** @var string The last available remote update date */
    public $lastUpdate;

    /** @var string The download URL for this extension */
    public string $downloadURL;

    /**
     * Constructor
     *
     * @param Extension $extension The extension to create the response for
     */
    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
        $this->type = $extension->getType();
        $this->id = $extension->getId();
        $this->base = $extension->getBase();
        $this->name = $extension->getDisplayName();
        $this->version = $extension->getInstalledVersion();
        $this->author = $extension->getAuthor();
        $this->description = $extension->getDescription();
        $this->isInstalled = $extension->isInstalled();
        $this->isEnabled = $extension->isEnabled();
        $this->updateAvailable = $extension->isUpdateAvailable();
        $this->isBundled = $extension->isBundled();
        $this->isGitControlled = $extension->isGitControlled();
        $this->componentTypes = $extension->getComponentTypes();
        $this->lastUpdate = $extension->getLastUpdate();
        $this->url = $extension->getURL();
        $this->downloadURL = $extension->getDownloadURL();

        // Add notices
        $this->notices = array_merge(...array_values(Notice::list($extension)));
    }

    /** @inheritdoc */
    public function __toString()
    {
        return $this->extension->getId();
    }
}
