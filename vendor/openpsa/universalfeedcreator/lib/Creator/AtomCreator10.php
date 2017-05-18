<?php

/**
 * AtomCreator10 is a FeedCreator that implements the atom specification,
 * as in http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * Please note that just by using AtomCreator10 you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 * Some elements have not been implemented yet. These are (incomplete list):
 * author URL, item author's email and URL, item contents, alternate links,
 * other link content types than text/html. Some of them may be created with
 * AtomCreator10::additionalElements.
 *
 * @see     FeedCreator#additionalElements
 * @since   1.7.2-mod (modified)
 * @author  Mohammad Hafiz Ismail (mypapit@gmail.com)
 * @package de.bitfolge.feedcreator
 */
class AtomCreator10 extends FeedCreator
{

    /**
     * AtomCreator10 constructor.
     */
    public function __construct()
    {
        $this->contentType = "application/atom+xml";
        $this->encoding = "utf-8";
    }

    /** @inheritdoc */
    public function createFeed()
    {
        $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
        $feed .= $this->_createGeneratorComment();
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<feed xmlns=\"http://www.w3.org/2005/Atom\"";
        if (!empty($this->items[0]->lat)) {
            $feed .= " xmlns:georss=\"http://www.georss.org/georss\"\n";
        }
        if ($this->language != "") {
            $feed .= " xml:lang=\"".$this->language."\"";
        }
        $feed .= ">\n";
        $feed .= "    <title>".htmlspecialchars($this->title)."</title>\n";
        $feed .= "    <subtitle>".htmlspecialchars($this->description)."</subtitle>\n";
        $feed .= "    <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->link)."\"/>\n";
        $feed .= "    <id>".htmlspecialchars($this->link)."</id>\n";
        $now = new FeedDate();
        $feed .= "    <updated>".htmlspecialchars($now->iso8601())."</updated>\n";
        if ($this->editor != "") {
            $feed .= "    <author>\n";
            $feed .= "        <name>".$this->editor."</name>\n";
            if ($this->editorEmail != "") {
                $feed .= "        <email>".$this->editorEmail."</email>\n";
            }
            $feed .= "    </author>\n";
        }
        if ($this->category != "") {

            $feed .= "    <category term=\"".htmlspecialchars($this->category)."\" />\n";
        }
        if ($this->copyright != "") {
            $feed .= "    <rights>".FeedCreator::iTrunc(htmlspecialchars($this->copyright), 100)."</rights>\n";
        }
        $feed .= "    <generator>".$this->version()."</generator>\n";

        $feed .= "    <link rel=\"self\" type=\"application/atom+xml\" href=\"".htmlspecialchars(
                $this->syndicationURL
            )."\" />\n";
        $feed .= $this->_createAdditionalElements($this->additionalElements, "    ");
        for ($i = 0; $i < count($this->items); $i++) {
            $feed .= "    <entry>\n";
            $feed .= "        <title>".htmlspecialchars(strip_tags($this->items[$i]->title))."</title>\n";
            $feed .= "        <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars(
                    $this->items[$i]->link
                )."\"/>\n";
            if ($this->items[$i]->date == "") {
                $this->items[$i]->date = time();
            }
            $itemDate = new FeedDate($this->items[$i]->date);
            $feed .= "        <published>".htmlspecialchars($itemDate->iso8601())."</published>\n";
            $feed .= "        <updated>".htmlspecialchars($itemDate->iso8601())."</updated>\n";

            $tempguid = $this->items[$i]->link;
            if ($this->items[$i]->guid != "") {
                $tempguid = $this->items[$i]->guid;
            }

            $feed .= "        <id>".htmlspecialchars($tempguid)."</id>\n";
            $feed .= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            if ($this->items[$i]->author != "") {
                $feed .= "        <author>\n";
                $feed .= "            <name>".htmlspecialchars($this->items[$i]->author)."</name>\n";
                if ($this->items[$i]->authorEmail != "") {
                    $feed .= "            <email>".htmlspecialchars($this->items[$i]->authorEmail)."</email>\n";
                }

                if ($this->items[$i]->authorURL != "") {
                    $feed .= "            <uri>".htmlspecialchars($this->items[$i]->authorURL)."</uri>\n";
                }

                $feed .= "        </author>\n";
            }

            if ($this->items[$i]->category != "") {
                $feed .= "        <category ";

                if ($this->items[$i]->categoryScheme != "") {
                    $feed .= " scheme=\"".htmlspecialchars($this->items[$i]->categoryScheme)."\" ";
                }

                $feed .= " term=\"".htmlspecialchars($this->items[$i]->category)."\" />\n";
            }

            if ($this->items[$i]->description != "") {

                /*
                 * ATOM should have at least summary tag, however this implementation may be inaccurate
                */
                $tempdesc = $this->items[$i]->getDescription();
                $temptype = "";

                if ($this->items[$i]->descriptionHtmlSyndicated) {
                    $temptype = " type=\"html\"";
                    $tempdesc = $this->items[$i]->getDescription();

                }

                if (empty($this->items[$i]->descriptionTruncSize)) {
                    $feed .= "        <content".$temptype.">".$tempdesc."</content>\n";
                }

                $feed .= "        <summary".$temptype.">".$tempdesc."</summary>\n";
            } else {

                $feed .= "     <summary>no summary</summary>\n";

            }

            if ($this->items[$i]->enclosure != null) {
                $feed .= "        <link rel=\"enclosure\" href=\"".$this->items[$i]->enclosure->url."\" type=\"".$this->items[$i]->enclosure->type."\"  length=\"".$this->items[$i]->enclosure->length."\"";

                if ($this->items[$i]->enclosure->language != "") {
                    $feed .= " xml:lang=\"".$this->items[$i]->enclosure->language."\" ";
                }

                if ($this->items[$i]->enclosure->title != "") {
                    $feed .= " title=\"".$this->items[$i]->enclosure->title."\" ";
                }

                $feed .= " /> \n";
            }
            if ($this->items[$i]->lat != "") {
                $feed .= "        <georss:point>".$this->items[$i]->lat." ".$this->items[$i]->long."</georss:point>\n";
            }
            $feed .= "    </entry>\n";
        }
        $feed .= "</feed>\n";

        return $feed;
    }
}
