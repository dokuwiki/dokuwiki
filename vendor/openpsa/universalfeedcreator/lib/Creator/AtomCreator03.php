<?php

/**
 * AtomCreator03 is a FeedCreator that implements the atom specification,
 * as in http://www.intertwingly.net/wiki/pie/FrontPage.
 * Please note that just by using AtomCreator03 you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 * Some elements have not been implemented yet. These are (incomplete list):
 * author URL, item author's email and URL, item contents, alternate links,
 * other link content types than text/html. Some of them may be created with
 * AtomCreator03::additionalElements.
 *
 * @see     FeedCreator#additionalElements
 * @since   1.6
 * @author  Kai Blankenhorn <kaib@bitfolge.de>, Scott Reynen <scott@randomchaos.com>
 */
class AtomCreator03 extends FeedCreator
{

    /**
     * AtomCreator03 constructor.
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
        $feed .= "<feed version=\"0.3\" xmlns=\"http://purl.org/atom/ns#\"";
        if ($this->format == 'TOOLBAR') {
            $feed .= " xmlns:gtb=\"http://toolbar.google.com/custombuttons/\"";
        }
        if ($this->language != "") {
            $feed .= " xml:lang=\"".$this->language."\"";
        }
        $feed .= ">\n";
        $feed .= "    <title>".htmlspecialchars($this->title)."</title>\n";
        $feed .= "    <tagline>".htmlspecialchars($this->description)."</tagline>\n";
        $feed .= "    <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->link)."\"/>\n";
        $feed .= "    <id>".htmlspecialchars($this->link)."</id>\n";
        $now = new FeedDate();
        $feed .= "    <modified>".htmlspecialchars($now->iso8601())."</modified>\n";
        if ($this->editor != "") {
            $feed .= "    <author>\n";
            $feed .= "        <name>".$this->editor."</name>\n";
            if ($this->editorEmail != "") {
                $feed .= "        <email>".$this->editorEmail."</email>\n";
            }
            $feed .= "    </author>\n";
        }
        $feed .= "    <generator>".FEEDCREATOR_VERSION."</generator>\n";
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
            $feed .= "        <created>".htmlspecialchars($itemDate->iso8601())."</created>\n";
            $feed .= "        <issued>".htmlspecialchars($itemDate->iso8601())."</issued>\n";
            $feed .= "        <modified>".htmlspecialchars($itemDate->iso8601())."</modified>\n";
            $feed .= "        <id>".htmlspecialchars($this->items[$i]->link)."</id>\n";
            $feed .= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
            if ($this->items[$i]->author != "") {
                $feed .= "        <author>\n";
                $feed .= "            <name>".htmlspecialchars($this->items[$i]->author)."</name>\n";
                $feed .= "        </author>\n";
            }
            if ($this->items[$i]->description != "") {
                $feed .= "        <summary>".htmlspecialchars($this->items[$i]->description)."</summary>\n";
            }
            if (isset($this->items[$i]->thumbdata)) {
                $feed .= "        <gtb:icon mode=\"base64\" type=\"image/jpeg\">\n";
                $feed .= chunk_split(base64_encode($this->items[$i]->thumbdata))."\n";
                $feed .= "        </gtb:icon>\n";
            }
            $feed .= "    </entry>\n";
        }
        $feed .= "</feed>\n";

        return $feed;
    }
}
