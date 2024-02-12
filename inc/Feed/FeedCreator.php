<?php

namespace dokuwiki\Feed;

use dokuwiki\Extension\Event;

class FeedCreator
{
    /** @var \UniversalFeedCreator */
    protected $feed;

    /** @var FeedCreatorOptions */
    protected $options;

    /**
     * @param FeedCreatorOptions $options
     */
    public function __construct(FeedCreatorOptions $options)
    {
        $this->options = $options;

        $this->feed = new \UniversalFeedCreator();
        $this->feed->title = $this->options->get('title');
        $this->feed->description = $this->options->get('subtitle');
        $this->feed->link = DOKU_URL;
        $this->feed->syndicationURL = DOKU_URL . 'feed.php';
        $this->feed->cssStyleSheet = DOKU_URL . 'lib/exe/css.php?s=feed';

        $this->initLogo();
    }

    /**
     * Build the feed
     *
     * @return string The raw XML for the feed
     */
    public function build()
    {
        switch ($this->options->get('feed_mode')) {
            case 'list':
                $items = $this->fetchItemsFromNamespace();
                break;
            case 'search':
                $items = $this->fetchItemsFromSearch();
                break;
            case 'recent':
                $items = $this->fetchItemsFromRecentChanges();
                break;
            default:
                $items = $this->fetchItemsFromPlugin();
        }

        $eventData = [
            'rss' => $this->feed,
            'data' => &$items,
            'opt' =>  &$this->options->options,
        ];
        $event = new Event('FEED_DATA_PROCESS', $eventData);
        if ($event->advise_before(false)) {
            foreach ($items as $item) {
                $this->createAndAddItem($item);
            }
        }
        $event->advise_after();

        return $this->feed->createFeed($this->options->getType());
    }

    /**
     * Process the raw data, create feed item and add it to the feed
     *
     * @param array|string $data raw item data
     * @return \FeedItem
     * @triggers FEED_ITEM_ADD
     */
    protected function createAndAddItem($data)
    {
        if (is_string($data)) {
            $data = ['id' => $data];
        }

        if (($data['mode'] ?? '') == 'media' || isset($data['media'])) {
            $data['id'] = $data['media'] ?? $data['id'];
            $proc = new FeedMediaProcessor($data);
        } else {
            $proc = new FeedPageProcessor($data);
        }

        $item = new \FeedItem();
        $item->title = $proc->getTitle();
        if ($this->options->get('show_summary') && $proc->getSummary()) {
            $item->title .= ' - ' . $proc->getSummary();
        }
        $item->date = $proc->getRev();
        [$item->authorEmail, $item->author] = $proc->getAuthor();
        $item->link = $proc->getURL($this->options->get('link_to'));
        $item->description = $proc->getBody($this->options->get('item_content'));

        $evdata = [
            'item' => $item,
            'opt' => &$this->options->options,
            'ditem' => &$data,
            'rss' => $this->feed,
        ];

        $evt = new Event('FEED_ITEM_ADD', $evdata);
        if ($evt->advise_before()) {
            $this->feed->addItem($item);
        }
        $evt->advise_after();

        return $item;
    }

    /**
     * Read all pages from a namespace
     *
     * @todo this currently does not honor the rss_media setting and only ever lists pages
     * @return array
     */
    protected function fetchItemsFromNamespace()
    {
        global $conf;

        $ns = ':' . cleanID($this->options->get('namespace'));
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));

        $data = [];
        $search_opts = [
            'depth' => 1,
            'pagesonly' => true,
            'listfiles' => true
        ];
        search(
            $data,
            $conf['datadir'],
            'search_universal',
            $search_opts,
            $ns,
            $lvl = 1,
            $this->options->get('sort')
        );

        return $data;
    }

    /**
     * Add the result of a full text search to the feed object
     *
     * @return array
     */
    protected function fetchItemsFromSearch()
    {
        if (!actionOK('search')) throw new \RuntimeException('search is disabled');
        if (!$this->options->get('search_query')) return [];

        $data = ft_pageSearch($this->options->get('search_query'), $poswords);
        return array_keys($data);
    }

    /**
     * Add recent changed pages to the feed object
     *
     * @return array
     */
    protected function fetchItemsFromRecentChanges()
    {
        global $conf;
        $flags = 0;
        if (!$this->options->get('show_deleted')) $flags += RECENTS_SKIP_DELETED;
        if (!$this->options->get('show_minor')) $flags += RECENTS_SKIP_MINORS;
        if ($this->options->get('only_new')) $flags += RECENTS_ONLY_CREATION;
        if ($this->options->get('content_type') == 'media' && $conf['mediarevisions']) {
            $flags += RECENTS_MEDIA_CHANGES;
        }
        if ($this->options->get('content_type') == 'both' && $conf['mediarevisions']) {
            $flags += RECENTS_MEDIA_PAGES_MIXED;
        }

        return getRecents(0, $this->options->get('items'), $this->options->get('namespace'), $flags);
    }

    /**
     * Add items from a plugin to the feed object
     *
     * @triggers FEED_MODE_UNKNOWN
     * @return array
     */
    protected function fetchItemsFromPlugin()
    {
        $eventData = [
            'opt' => $this->options->options,
            'data' => [],
        ];
        $event = new Event('FEED_MODE_UNKNOWN', $eventData);
        if ($event->advise_before(true)) {
            throw new \RuntimeException('unknown feed mode');
        }
        $event->advise_after();

        return $eventData['data'];
    }

    /**
     * Add a logo to the feed
     *
     * Looks at different possible candidates for a logo and adds the first one
     *
     * @return void
     */
    protected function initLogo()
    {
        global $conf;

        $this->feed->image = new \FeedImage();
        $this->feed->image->title = $conf['title'];
        $this->feed->image->link = DOKU_URL;
        $this->feed->image->url = tpl_getMediaFile([
            ':wiki:logo.svg',
            ':logo.svg',
            ':wiki:logo.png',
            ':logo.png',
            ':wiki:logo.jpg',
            ':logo.jpg',
            ':wiki:favicon.ico',
            ':favicon.ico',
            ':wiki:dokuwiki.svg',
            ':wiki:dokuwiki-128.png',
            'images/favicon.ico'
        ], true);
    }
}
