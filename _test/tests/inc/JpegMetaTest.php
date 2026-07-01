<?php

class JpegMetaTest extends DokuWikiTest
{
    public function testGetDatesHandlesExifDateTimeArrays()
    {
        $meta = new JpegMeta('');
        $meta->_markers = array(array('marker' => 0));
        $meta->_info = array(
            'file' => array(),
            'jfif' => false,
            'jpeg' => false,
            'exif' => array(
                'DateTime' => array(
                    '2005:02:03 11:25:29',
                    '2005:02:04 09:44:46',
                ),
            ),
            'xmp' => false,
            'adobe' => false,
        );

        $dates = $meta->getDates();

        $this->assertSame(strtotime('2005-02-03 11:25:29'), $dates['EarliestTime']);
        $this->assertSame('ExifDateTime', $dates['EarliestTimeSource']);
        $this->assertSame(strtotime('2005-02-04 09:44:46'), $dates['LatestTime']);
        $this->assertSame('ExifDateTime', $dates['LatestTimeSource']);
    }
}
