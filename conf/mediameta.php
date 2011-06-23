<?php
/**
 * This configures which meta data will be editable through
 * the media manager. Each field of the array is an array with the
 * following contents:
 *   fieldname - Where data will be saved (EXIF or IPTC field)
 *   label     - key to lookup in the $lang var, if not found printed as is
 *   htmltype  - 'text' or 'textarea'
 *   lookups   - array additional fields to lookup the data (EXIF or IPTC fields)
 *
 * The fields are not ordered continously to make inserting additional items
 * in between simpler.
 *
 * This is a PHP snippet, so PHP syntax applies.
 *
 * Note: $fields is not a global variable and will not be available to any
 *       other functions or templates later
 *
 * You may extend or overwrite this variable in a optional
 * conf/mediameta.local.php file
 *
 * For a list of available EXIF/IPTC fields refer to
 * http://www.dokuwiki.org/devel:templates:detail.php
 */


$fields = array(
    10 => array('Iptc.Headline',
                'img_title',
                'text'),

    20 => array('Iptc.Caption',
                'img_caption',
                'textarea',
                array('Exif.UserComment',
                      'Exif.TIFFImageDescription',
                      'Exif.TIFFUserComment')),

    30 => array('Iptc.Byline',
                'img_artist',
                'text',
                array('Exif.TIFFArtist',
                      'Exif.Artist',
                      'Iptc.Credit')),

    40 => array('Iptc.CopyrightNotice',
                'img_copyr',
                'text',
                array('Exif.TIFFCopyright',
                      'Exif.Copyright')),

    50 => array('Iptc.Keywords',
                'img_keywords',
                'text',
                array('Exif.Category')),
);


/**
 * This configures which meta data will be shown in details view
 * of the media manager. Each field of the array is an array with the
 * following contents:
 *   fieldname - Where data will be saved (EXIF or IPTC fields)
 *   label     - key to lookup in the $lang var, if not found printed as is
 *   fieldtype - 'text' or 'date'
 */
$tags = array(
    array('simple.title','img_title','text'),
    array('Date.EarliestTime','img_date','date'),
    array('File.Name','img_fname','text'),
    array(array('Iptc.Byline','Exif.TIFFArtist','Exif.Artist','Iptc.Credit'),'img_artist','text'),
    array(array('Iptc.CopyrightNotice','Exif.TIFFCopyright','Exif.Copyright'),'img_copyr','text'),
    array('File.Format','img_format','text'),
    array('File.NiceSize','img_fsize','text'),
    array('File.Width','img_width','text'),
    array('File.Height','img_height','text'),
    array('Simple.Camera','img_camera','text'),
    array(array('IPTC.Keywords','IPTC.Category','xmp.dc:subject'),'img_keywords','text')
);
