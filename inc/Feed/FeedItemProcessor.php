<?php

namespace dokuwiki\Feed;

use dokuwiki\Extension\AuthPlugin;
use RuntimeException;

/**
 * Accept more or less arbitrary data to represent data to later construct a feed item from.
 * Provide lazy loading accessors to all the data we need for feed generation.
 */
abstract class FeedItemProcessor
{
    /** @var string This page's ID */
    protected $id;

    /** @var array bag of holding */
    protected $data;


    /**
     * Constructor
     *
     * @param array $data Needs to have at least an 'id' key
     */
    public function __construct($data)
    {
        if (!isset($data['id'])) throw new RuntimeException('Missing ID');
        $this->id = cleanID($data['id']);
        $this->data = $data;
    }

    /**
     * Get the page ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the revision timestamp of this page
     *
     * If the input gave us a revision, date or lastmodified already, we trust that it is correct.
     *
     * Note: we only handle most current revisions in feeds, so the revision is usually just the
     * lastmodifed timestamp of the page file. However, if the item does not exist, we need to
     * determine the revision from the changelog.
     *
     * @return int
     */
    public function getRev()
    {
        if ($this->data['rev'] ?? 0) return $this->data['rev'];

        if (isset($this->data['date'])) {
            $this->data['rev'] = (int)$this->data['date'];
        }

        if (isset($this->data['lastmodified'])) {
            $this->data['rev'] = (int)$this->data['lastmodified'];
        }

        return $this->data['rev'] ?? 0;
    }

    /**
     * Construct the URL for the feed item based on the link_to option
     *
     * @param string $linkto The link_to option
     * @return string URL
     */
    abstract public function getURL($linkto);

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title'] ?? noNS($this->getId());
    }

    /**
     * Construct the body of the feed item based on the item_content option
     *
     * @param string $content The item_content option
     * @return string
     */
    abstract public function getBody($content);

    /**
     * Get the change summary for this item if any
     *
     * @return string
     */
    public function getSummary()
    {
        return (string)($this->data['sum'] ?? '');
    }

    /**
     * Get the author info for this item
     *
     * @return string[] [email, author]
     */
    public function getAuthor()
    {
        global $conf;
        global $auth;

        $user = $this->data['user'] ?? '';
        $author = 'Anonymous';
        $email = 'anonymous@undisclosed.example.com';

        if (!$user) return [$email, $author];
        $author = $user;
        $email = $user . '@undisclosed.example.com';

        if ($conf['useacl'] && $auth instanceof AuthPlugin) {
            $userInfo = $auth->getUserData($user);
            if ($userInfo) {
                switch ($conf['showuseras']) {
                    case 'username':
                    case 'username_link':
                        $author = $userInfo['name'];
                        break;
                }
            }
        }
        return [$email, $author];
    }

    /**
     * Get the categories for this item
     *
     * @return string[]
     */
    abstract public function getCategory();


    /**
     * Clean HTML for the use in feeds
     *
     * @param string $html
     * @return string
     */
    protected function cleanHTML($html)
    {
        global $conf;

        // no TOC in feeds
        $html = preg_replace('/(<!-- TOC START -->).*(<!-- TOC END -->)/s', '', $html);

        // add alignment for images
        $html = preg_replace('/(<img .*?class="medialeft")/s', '\\1 align="left"', $html);
        $html = preg_replace('/(<img .*?class="mediaright")/s', '\\1 align="right"', $html);

        // make URLs work when canonical is not set, regexp instead of rerendering!
        if (!$conf['canonical']) {
            $base = preg_quote(DOKU_REL, '/');
            $html = preg_replace(
                '/(<a href|<img src)="(' . $base . ')/s',
                '$1="' . DOKU_URL,
                $html
            );
        }

        return $html;
    }
}
