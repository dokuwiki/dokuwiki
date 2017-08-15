<?php

/**
 * RSSCreator10 is a FeedCreator that implements RDF Site Summary (RSS) 1.0.
 *
 * @see     http://www.purl.org/rss/1.0/
 * @since   1.3
 * @author  Kai Blankenhorn <kaib@bitfolge.de>
 * @package de.bitfolge.feedcreator
 */
class RSSCreator10 extends FeedCreator
{

    /** @inheritdoc */
    public function createFeed()
    {
        $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
        $feed .= $this->_createGeneratorComment();
        if (empty($this->cssStyleSheet)) {
            $this->cssStyleSheet = "http://www.w3.org/2000/08/w3c-synd/style.css";
        }
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<rdf:RDF\n";
        $feed .= "    xmlns=\"http://purl.org/rss/1.0/\"\n";
        $feed .= "    xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
        $feed .= "    xmlns:slash=\"http://purl.org/rss/1.0/modules/slash/\"\n";
        if (!empty($this->items[0]->thumb)) {
            $feed .= "    xmlns:photo=\"http://www.pheed.com/pheed/\"\n";
        }
        if (!empty($this->items[0]->lat)) {
            $feed .= "    xmlns:georss=\"http://www.georss.org/georss\"\n";
        }
        $feed .= "    xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n";
        $feed .= "    <channel rdf:about=\"".$this->syndicationURL."\">\n";
        $feed .= "        <title>".htmlspecialchars($this->title)."</title>\n";
        $feed .= "        <description>".htmlspecialchars($this->description)."</description>\n";
        $feed .= "        <link>".$this->link."</link>\n";
        if ($this->image != null) {
            $feed .= "        <image rdf:resource=\"".$this->image->url."\" />\n";
        }
        $now = new FeedDate();
        $feed .= "       <dc:date>".htmlspecialchars($now->iso8601())."</dc:date>\n";
        $feed .= "        <items>\n";
        $feed .= "            <rdf:Seq>\n";
        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "                <rdf:li rdf:resource=\"".htmlspecialchars($this->items[$i]->link)."\"/>\n";
        }
        $feed .= "            </rdf:Seq>\n";
        $feed .= "        </items>\n";
        $feed .= "    </channel>\n";
        if ($this->image != null) {
            $feed .= "    <image rdf:about=\"".$this->image->url."\">\n";
            $feed .= "        <title>".$this->image->title."</title>\n";
            $feed .= "        <link>".$this->image->link."</link>\n";
            $feed .= "        <url>".$this->image->url."</url>\n";
            $feed .= "    </image>\n";
        }
        $feed .= $this->_createAdditionalElements($this->additionalElements, "    ");

        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "    <item rdf:about=\"".htmlspecialchars($this->items[$i]->link)."\">\n";
            $feed .= "        <dc:format>text/html</dc:format>\n";
            if ($this->items[$i]->date != null) {
                $itemDate = new FeedDate($this->items[$i]->date);
                $feed .= "        <dc:date>".htmlspecialchars($itemDate->iso8601())."</dc:date>\n";
            }
            if ($this->items[$i]->source != "") {
                $feed .= "        <dc:source>".htmlspecialchars($this->items[$i]->source)."</dc:source>\n";
            }
            $creator = $this->getAuthor($this->items[$i]->author, $this->items[$i]->authorEmail);
            if ($creator) {
                $feed .= "        <dc:creator>".htmlspecialchars($creator)."</dc:creator>\n";
            }
            if ($this->items[$i]->lat != "") {
                $feed .= "        <georss:point>".$this->items[$i]->lat." ".$this->items[$i]->long."</georss:point>\n";
            }
            if ($this->items[$i]->thumb != "") {
                $feed .= "        <photo:thumbnail>".htmlspecialchars($this->items[$i]->thumb)."</photo:thumbnail>\n";
            }
            $feed .= "        <title>".htmlspecialchars(
                    strip_tags(strtr($this->items[$i]->title, "\n\r", "  "))
                )."</title>\n";
            $feed .= "        <link>".htmlspecialchars($this->items[$i]->link)."</link>\n";
            $feed .= "        <description>".htmlspecialchars($this->items[$i]->description)."</description>\n";
            $feed .= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            $feed .= "    </item>\n";
        }
        $feed .= "</rdf:RDF>\n";

        return $feed;
    }

    /**
     * Compose the RSS-1.0 author field.
     *
     * @author Joe Lapp <joe.lapp@pobox.com>
     * @param string $author
     * @param string $email
     * @return string
     */
    protected function getAuthor($author, $email)
    {
        if ($author) {
            if ($email) {
                return $author.' ('.$email.')';
            }

            return $author;
        }

        return $email;
    }
}
