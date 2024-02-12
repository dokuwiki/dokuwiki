<?php

namespace dokuwiki\Feed;

use dokuwiki\Extension\Event;

/**
 * Hold the options for feed generation
 */
class FeedCreatorOptions
{
    /** @var array[] supported feed types */
    protected $types = [
        'rss' => [
            'name' => 'RSS0.91',
            'mime' => 'text/xml; charset=utf-8',
        ],
        'rss1' => [
            'name' => 'RSS1.0',
            'mime' => 'text/xml; charset=utf-8',
        ],
        'rss2' => [
            'name' => 'RSS2.0',
            'mime' => 'text/xml; charset=utf-8',
        ],
        'atom' => [
            'name' => 'ATOM0.3',
            'mime' => 'application/xml; charset=utf-8',
        ],
        'atom1' => [
            'name' => 'ATOM1.0',
            'mime' => 'application/atom+xml; charset=utf-8',
        ],
    ];

    /** @var array[] the set options */
    public $options = [
        'type' => 'rss',
        'feed_mode' => 'recent',
        'link_to' => 'page',
        'item_content' => 'diff',
        'namespace' => '',
        'items' => 15,
        'show_minor' => false,
        'show_deleted' => false,
        'show_summary' => false,
        'only_new' => false,
        'sort' => 'natural',
        'search_query' => '',
        'content_type' => 'pages',
        'guardmail' => 'none',
        'title' => '',
    ];

    /**
     * Initialize the options from the request, falling back to config defaults
     *
     * @triggers FEED_OPTS_POSTPROCESS
     * @param array $options additional options to set (for testing)
     */
    public function __construct($options = [])
    {
        global $conf;
        global $INPUT;

        $this->options['type'] = $INPUT->valid(
            'type',
            array_keys($this->types),
            $conf['rss_type']
        );
        // we only support 'list', 'search', 'recent' but accept anything so plugins can take over
        $this->options['feed_mode'] = $INPUT->str('mode', 'recent');
        $this->options['link_to'] = $INPUT->valid(
            'linkto',
            ['diff', 'page', 'rev', 'current'],
            $conf['rss_linkto']
        );
        $this->options['item_content'] = $INPUT->valid(
            'content',
            ['abstract', 'diff', 'htmldiff', 'html'],
            $conf['rss_content']
        );
        $this->options['namespace'] = $INPUT->filter('cleanID')->str('ns');
        $this->options['items'] = max(0, $INPUT->int('num', $conf['recent']));
        $this->options['show_minor'] = $INPUT->bool('minor');
        $this->options['show_deleted'] = $conf['rss_show_deleted'];
        $this->options['show_summary'] = $conf['rss_show_summary'];
        $this->options['only_new'] = $INPUT->bool('onlynewpages');
        $this->options['sort'] = $INPUT->valid(
            'sort',
            ['natural', 'date'],
            'natural'
        );
        $this->options['search_query'] = $INPUT->str('q');
        $this->options['content_type'] = $INPUT->valid(
            'view',
            ['pages', 'media', 'both'],
            $conf['rss_media']
        );
        $this->options['guardmail'] = $conf['mailguard'];
        $this->options['title'] = $conf['title'];
        if ($this->options['namespace']) {
            $this->options['title'] .= ' - ' . $this->options['namespace'];
        }
        $this->options['subtitle'] = $conf['tagline'];

        $this->options = array_merge($this->options, $options);

        // initialization finished, let plugins know
        $eventData = [
            'opt' => &$this->options,
        ];
        Event::createAndTrigger('FEED_OPTS_POSTPROCESS', $eventData);
    }

    /**
     * The cache key to use for a feed with these options
     *
     * Does not contain user or host specific information yet
     *
     * @return string
     */
    public function getCacheKey()
    {
        return implode('', array_values($this->options));
    }

    /**
     * Return a feed option by name
     *
     * @param string $option The name of the option
     * @param mixed $default default value if option is not set (should usually not happen)
     * @return mixed
     */
    public function get($option, $default = null)
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * Return the feed type for UniversalFeedCreator
     *
     * This returns the apropriate type for UniversalFeedCreator
     *
     * @return string
     */
    public function getType()
    {
        return $this->types[$this->options['type']]['name'];
    }

    /**
     * Return the feed mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->types[$this->options['type']]['mime'];
    }
}
