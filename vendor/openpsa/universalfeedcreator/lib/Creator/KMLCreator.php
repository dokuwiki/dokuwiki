<?php

/**
 * KMLCreator is a FeedCreator that implements a KML output, suitable for Keyhole/Google Earth
 *
 * @since   1.7.3
 * @author  Barry Hunter <geo@barryhunter.co.uk>
 * @package de.bitfolge.feedcreator
 */
class KMLCreator extends FeedCreator
{

    /**
     * KMLCreator constructor.
     */
    public function __construct()
    {
        $this->contentType = "application/vnd.google-earth.kml+xml";
        $this->encoding = "utf-8";
    }

    /** @inheritdoc */
    public function createFeed()
    {
        $feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
        $feed .= $this->_createStylesheetReferences();
        $feed .= "<kml xmlns=\"http://earth.google.com/kml/2.0\">\n";
        $feed .= "<Document>\n";
        if ($_GET['LinkControl']) {
            $feed .= "<NetworkLinkControl>\n<minRefreshPeriod>3600</minRefreshPeriod>\n</NetworkLinkControl>\n";
        }
        if (!empty($_GET['simple']) && count($this->items) > 0) {
            $feed .= "<Style id=\"defaultIcon\">
            <LabelStyle>
            <scale>0</scale>
            </LabelStyle>
            </Style>
            <Style id=\"hoverIcon\">".
                (($this->items[0]->thumb != "") ? "
                <IconStyle id=\"hoverIcon\">
                <scale>2.1</scale>
                </IconStyle>" : '')."
                </Style>
                <StyleMap id=\"defaultStyle\">
                <Pair>
                <key>normal</key>
                <styleUrl>#defaultIcon</styleUrl>
                </Pair>
                <Pair>
                <key>highlight</key>
                <styleUrl>#hoverIcon</styleUrl>
                </Pair>
                </StyleMap>
                ";
            $style = "#defaultStyle";
        } else {
            $style = "root://styleMaps#default?iconId=0x307";
        }
        $feed .= "<Folder>\n";
        $feed .= "  <name>".FeedCreator::iTrunc(htmlspecialchars($this->title), 100)."</name>
        <description>".$this->getDescription()."</description>
        <visibility>1</visibility>\n";
        $this->truncSize = 500;

        for ($i = 0; $i < count($this->items); $i++) {
            //added here beucase description gets auto surrounded by cdata
            $this->items[$i]->description = "<b>".$this->items[$i]->description."</b><br/>
            ".$this->items[$i]->licence."
            <br/><br/><a href=\"".htmlspecialchars($this->items[$i]->link)."\">View Online</a>";

            $feed .= "
            <Placemark>
            <description>".$this->items[$i]->getDescription(true)."</description>
            <name>".FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)), 100)."</name>
            <visibility>1</visibility>
            <Point>
            <coordinates>".$this->items[$i]->long.",".$this->items[$i]->lat.",25</coordinates>
            </Point>";
            if ($this->items[$i]->thumb != "") {
                $feed .= "
                <styleUrl>$style</styleUrl>
                <Style>
                <icon>".htmlspecialchars($this->items[$i]->thumb)."</icon>
                    </Style>";
            }
            $feed .= "
            </Placemark>\n";
        }
        $feed .= "</Folder>\n</Document>\n</kml>\n";

        return $feed;
    }

    /**
     * Generate a filename for the feed cache file. Overridden from FeedCreator to prevent XML data types.
     *
     * @return string the feed cache filename
     * @since  1.4
     * @access private
     */
    protected function _generateFilename()
    {
        $fileInfo = pathinfo($_SERVER["PHP_SELF"]);

        return substr($fileInfo["basename"], 0, -(strlen($fileInfo["extension"]) + 1)).".kml";
    }
}
