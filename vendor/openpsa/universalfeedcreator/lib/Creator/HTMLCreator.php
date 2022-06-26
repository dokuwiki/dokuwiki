<?php

/**
 * HTMLCreator is a FeedCreator that writes an HTML feed file to a specific
 * location, overriding the createFeed method of the parent FeedCreator.
 * The HTML produced can be included over http by scripting languages, or serve
 * as the source for an IFrame.
 * All output by this class is embedded in <div></div> tags to enable formatting
 * using CSS.
 *
 * @author  Pascal Van Hecke
 * @since   1.7
 */
class HTMLCreator extends FeedCreator
{

    protected $contentType = "text/html";

    /**
     * Contains HTML to be output at the start of the feed's html representation.
     */
    public $header;

    /**
     * Contains HTML to be output at the end of the feed's html representation.
     */
    public $footer;

    /**
     * Contains HTML to be output between entries. A separator is only used in
     * case of multiple entries.
     */
    public $separator;

    /**
     * Used to prefix the stylenames to make sure they are unique
     * and do not clash with stylenames on the user's page.
     */
    public $stylePrefix;

    /** @var bool Determines whether the links open in a new window or not. */
    public $openInNewWindow = true;

    /** @var string image alignments in output */
    public $imageAlign = "right";

    /**
     * In case of very simple output you may want to get rid of the style tags,
     * hence this variable.  There's no equivalent on item level, but of course you can
     * add strings to it while iterating over the items ($this->stylelessOutput .= ...)
     * and when it is non-empty, ONLY the styleless output is printed, the rest is ignored
     * in the function createFeed().
     */
    public $stylelessOutput = "";

    /**
     * Writes the HTML.
     *
     * @inheritdoc
     */
    public function createFeed()
    {
        // if there is styleless output, use the content of this variable and ignore the rest
        if ($this->stylelessOutput != "") {
            return $this->stylelessOutput;
        }

        //if no stylePrefix is set, generate it yourself depending on the script name
        if ($this->stylePrefix == "") {
            $this->stylePrefix = str_replace(".", "_", $this->_generateFilename())."_";
        }

        //set an openInNewWindow_token_to be inserted or not
        if ($this->openInNewWindow) {
            $targetInsert = " class='target_blank'";
        } else {
            $targetInsert = '';
        }

        // use this array to put the lines in and implode later with "document.write" javascript
        $feedArray = array();
        if ($this->image != null) {
            $imageStr = "<a href='".$this->image->link."'".$targetInsert.">".
                "<img src='".$this->image->url."' border='0' alt='".
                FeedCreator::iTrunc(htmlspecialchars($this->image->title), 100).
                "' align='".$this->imageAlign."' ";
            if ($this->image->width) {
                $imageStr .= " width='".$this->image->width."' ";
            }
            if ($this->image->height) {
                $imageStr .= " height='".$this->image->height."' ";
            }
            $imageStr .= "/></a>";
            $feedArray[] = $imageStr;
        }

        if ($this->title) {
            $feedArray[] = "<div class='".$this->stylePrefix."title'><a href='".$this->link."' ".$targetInsert." class='".$this->stylePrefix."title'>".
                FeedCreator::iTrunc(htmlspecialchars($this->title), 100)."</a></div>";
        }
        if ($this->getDescription()) {
            $feedArray[] = "<div class='".$this->stylePrefix."description'>".
                str_replace("]]>", "", str_replace("<![CDATA[", "", $this->getDescription())).
                "</div>";
        }

        if ($this->header) {
            $feedArray[] = "<div class='".$this->stylePrefix."header'>".$this->header."</div>";
        }

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->separator and $i > 0) {
                $feedArray[] = "<div class='".$this->stylePrefix."separator'>".$this->separator."</div>";
            }

            if ($this->items[$i]->title) {
                if ($this->items[$i]->link) {
                    $feedArray[] =
                        "<div class='".$this->stylePrefix."item_title'><a href='".$this->items[$i]->link."' class='".$this->stylePrefix.
                        "item_title'".$targetInsert.">".FeedCreator::iTrunc(
                            htmlspecialchars(strip_tags($this->items[$i]->title)),
                            100
                        ).
                        "</a></div>";
                } else {
                    $feedArray[] =
                        "<div class='".$this->stylePrefix."item_title'>".
                        FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)), 100).
                        "</div>";
                }
            }
            if ($this->items[$i]->getDescription()) {
                $feedArray[] =
                    "<div class='".$this->stylePrefix."item_description'>".
                    str_replace("]]>", "", str_replace("<![CDATA[", "", $this->items[$i]->getDescription())).
                    "</div>";
            }
        }
        if ($this->footer) {
            $feedArray[] = "<div class='".$this->stylePrefix."footer'>".$this->footer."</div>";
        }

        $feed = "".join($feedArray, "\r\n");

        return $feed;
    }

    /**
     * Overrides parent to produce .html extensions
     *
     * @return string the feed cache filename
     * @since  1.4
     * @access private
     */
    protected function _generateFilename()
    {
        $fileInfo = pathinfo($_SERVER["PHP_SELF"]);

        return substr($fileInfo["basename"], 0, -(strlen($fileInfo["extension"]) + 1)).".html";
    }
}
