UniversalFeedCreator
====================

RSS and Atom feed generator by Kai Blankenhorn, slightly cleaned up and packaged for Composer.

Supported formats: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated), MBOX, OPML, ATOM, ATOM0.3,
HTML, JS, PHP

[![Build Status](https://travis-ci.org/flack/UniversalFeedCreator.png?branch=master)](https://travis-ci.org/flack/UniversalFeedCreator)

## General Usage

```php
require 'vendor/autoload.php';

$rss = new UniversalFeedCreator();
$rss->useCached(); // use cached version if age < 1 hour
$rss->title = "PHP news";
$rss->description = "daily news from the PHP scripting world";

//optional
$rss->descriptionTruncSize = 500;
$rss->descriptionHtmlSyndicated = true;

$rss->link = "http://www.dailyphp.net/news";
$rss->syndicationURL = "http://www.dailyphp.net/" . $_SERVER["PHP_SELF"];

$image = new FeedImage();
$image->title = "dailyphp.net logo";
$image->url = "http://www.dailyphp.net/images/logo.gif";
$image->link = "http://www.dailyphp.net";
$image->description = "Feed provided by dailyphp.net. Click to visit.";

//optional
$image->descriptionTruncSize = 500;
$image->descriptionHtmlSyndicated = true;

$rss->image = $image;

// get your news items from somewhere, e.g. your database:
mysql_select_db($dbHost, $dbUser, $dbPass);
$res = mysql_query("SELECT * FROM news ORDER BY newsdate DESC");
while ($data = mysql_fetch_object($res)) {
    $item = new FeedItem();
    $item->title = $data->title;
    $item->link = $data->url;
    $item->description = $data->short;

    //optional
    $item->descriptionTruncSize = 500;
    $item->descriptionHtmlSyndicated = true;

    $item->date = $data->newsdate;
    $item->source = "http://www.dailyphp.net";
    $item->author = "John Doe";

    $rss->addItem($item);
}

echo $rss->saveFeed("RSS1.0", "news/feed.xml");
```

## Changelog:

```
v1.8          12-03-13
    packaged for Composer & cleaned up slightly

v1.7.7(BH)    28-03-06
    added GPX Feed (Barry Hunter)


v1.7.6(BH)    20-02-06
    added GeoRSS Feed (Barry Hunter)


v1.7.5(BH)    16-11-05
    added BASE Feed (Barry Hunter)

v1.7.4(BH)    05-07-05
    added KML Feed (Barry Hunter)

v1.7.3(BH)    05-07-05
    added PHP Feed (Barry Hunter)

v1.7.2    10-11-04
    license changed to LGPL

v1.7.1
    fixed a syntax bug
    fixed left over debug code

v1.7    07-18-04
    added HTML and JavaScript feeds (configurable via CSS) (thanks to Pascal Van Hecke)
    added HTML descriptions for all feed formats (thanks to Pascal Van Hecke)
    added a switch to select an external stylesheet (thanks to Pascal Van Hecke)
    changed default content-type to application/xml
    added character encoding setting
    fixed numerous smaller bugs (thanks to Sören Fuhrmann of golem.de)
    improved changing ATOM versions handling (thanks to August Trometer)
    improved the UniversalFeedCreator's useCached method (thanks to Sören Fuhrmann of golem.de)
    added charset output in HTTP headers (thanks to Sören Fuhrmann of golem.de)
    added Slashdot namespace to RSS 1.0 (thanks to Sören Fuhrmann of golem.de)

v1.6    05-10-04
    added stylesheet to RSS 1.0 feeds
    fixed generator comment (thanks Kevin L. Papendick and Tanguy Pruvot)
    fixed RFC822 date bug (thanks Tanguy Pruvot)
    added TimeZone customization for RFC8601 (thanks Tanguy Pruvot)
    fixed Content-type could be empty (thanks Tanguy Pruvot)
    fixed author/creator in RSS1.0 (thanks Tanguy Pruvot)

v1.6 beta    02-28-04
    added Atom 0.3 support (not all features, though)
    improved OPML 1.0 support (hopefully - added more elements)
    added support for arbitrary additional elements (use with caution)
    code beautification :-)
    considered beta due to some internal changes

v1.5.1    01-27-04
    fixed some RSS 1.0 glitches (thanks to Stéphane Vanpoperynghe)
    fixed some inconsistencies between documentation and code (thanks to Timothy Martin)

v1.5    01-06-04
    added support for OPML 1.0
    added more documentation

v1.4    11-11-03
    optional feed saving and caching
    improved documentation
    minor improvements

v1.3    10-02-03
    renamed to FeedCreator, as it not only creates RSS anymore
    added support for mbox
    tentative support for echo/necho/atom/pie/???

v1.2    07-20-03
    intelligent auto-truncating of RSS 0.91 attributes
    don't create some attributes when they're not set
    documentation improved
    fixed a real and a possible bug with date conversions
    code cleanup

v1.1    06-29-03
    added images to feeds
    now includes most RSS 0.91 attributes
    added RSS 2.0 feeds

v1.0    06-24-03
    initial release
```

## Credits
```
originally (c) Kai Blankenhorn
www.bitfolge.de
kaib@bitfolge.de
v1.3 work by Scott Reynen (scott@randomchaos.com) and Kai Blankenhorn
v1.5 OPML support by Dirk Clemens
```
