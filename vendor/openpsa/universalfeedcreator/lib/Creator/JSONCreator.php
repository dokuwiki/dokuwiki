<?php

/**
 * JSONCreator is a FeedCreator that implements the JSON Feed specification,
 * as in https://jsonfeed.org/version/1.1
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
class JSONCreator extends FeedCreator
{
    /** @inheritdoc */
    public function createFeed()
    {
        $data = array();

        $data['version'] = 'https://jsonfeed.org/version/1.1';
        $data['title'] = (string)$this->title;
        $data['home_page_url'] = (string)$this->link;
        $data['feed_url'] = (string)$this->syndicationURL;
        $data['description'] = (string)$this->description;
        $data['user_comment'] = 'Created by ' . FEEDCREATOR_VERSION;
        if ($this->image != null) {
            $data['icon'] = $this->image->url;
        }
        if ($this->language != '') {
            $data['language'] = $this->language;
        }

        $data['items'] = array();
        foreach ($this->items as $item) {
            $entry = array();
            $entry['id'] = $item->guid ? (string)$item->guid : (string)$item->link;
            $entry['url'] = (string)$item->link;
            if ($item->source) {
                $entry['external_url'] = (string)$item->source;
            }
            $entry['title'] = strip_tags((string)$item->title);
            $entry['content_text'] = strip_tags((string)$item->description);
            $entry['content_html'] = (string)$item->description;
            $entry['date_published'] = (new FeedDate($item->date))->iso8601();
            if ($item->author) {
                // We only support one author, JSONFeed 1.1 accepts multiple
                $entry['authors'] = array(array('name' => (string)$item->author));
                // 1.0 only supported one, for compatibility we set it as well
                $entry['author'] = array('name' => (string)$item->author);
            }
            if ($item->category) {
                $entry['tags'] = (array)$item->category;
            }
            if ($item->enclosure) {
                // We only support one enclosure, JSONFeed 1.1 accepts multiple
                $entry['attachments'] = array(
                    array(
                        'url' => $item->enclosure['url'],
                        'mime_type' => $item->enclosure['type'],
                        'size_in_bytes' => $item->enclosure['length']
                    )
                );
            }

            $data['items'][] = $entry;
        }

        return json_encode($data);
    }
}
