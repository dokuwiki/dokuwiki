<?php

/**
 * PHPCreator is a FeedCreator that implements a PHP output, suitable for an include
 *
 * @since   1.7.3
 * @author  Barry Hunter <geo@barryhunter.co.uk>
 * @package de.bitfolge.feedcreator
 */
class PHPCreator extends FeedCreator
{

    /**
     * PHPCreator constructor.
     */
    public function __construct()
    {
        $this->contentType = "text/plain";
        $this->encoding = "utf-8";
    }

    /** @inheritdoc */
    public function createFeed()
    {
        $feed = "<?php\n";
        $feed .= "class FeedItem {}\n";
        $feed .= "  \$feedTitle='".addslashes(FeedCreator::iTrunc(htmlspecialchars($this->title), 100))."';\n";
        $this->truncSize = 500;
        $feed .= "  \$feedDescription='".addslashes($this->getDescription())."';\n";
        $feed .= "  \$feedLink='".$this->link."';\n";
        $feed .= "  \$feedItem = array();\n";
        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "   \$feedItem[$i] = new FeedItem();\n";
            if ($this->items[$i]->guid != "") {
                $feed .= "    \$feedItem[$i]->id='".htmlspecialchars($this->items[$i]->guid)."';\n";
            }
            $feed .= "    \$feedItem[$i]->title='".addslashes(
                    FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)), 100)
                )."';\n";
            $feed .= "    \$feedItem[$i]->link='".htmlspecialchars($this->items[$i]->link)."';\n";
            $feed .= "    \$feedItem[$i]->date=".htmlspecialchars($this->items[$i]->date).";\n";
            if ($this->items[$i]->author != "") {
                $feed .= "    \$feedItem[$i]->author='".htmlspecialchars($this->items[$i]->author)."';\n";
                if ($this->items[$i]->authorEmail != "") {
                    $feed .= "    \$feedItem[$i]->authorEmail='".$this->items[$i]->authorEmail."';\n";
                }
            }
            $feed .= "    \$feedItem[$i]->description='".addslashes($this->items[$i]->getDescription())."';\n";
            if ($this->items[$i]->thumb != "") {
                $feed .= "    \$feedItem[$i]->thumbURL='".htmlspecialchars($this->items[$i]->thumb)."';\n";
            }
        }
        $feed .= "?>\n";

        return $feed;
    }
}
