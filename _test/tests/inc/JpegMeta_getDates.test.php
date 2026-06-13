<?php

/**
 * Tests for JpegMeta::getDates() with multi-valued EXIF date tags
 */
class JpegMeta_getDates_test extends DokuWikiTest {

    /**
     * When an EXIF date tag occurs more than once it is stored as an array.
     * getDates() must still return a string date instead of passing the array
     * to strtotime(). See #4628.
     */
    function test_array_datetime() {
        $jpeg = new class('') extends JpegMeta {
            function __construct($file) {
                parent::__construct($file);
                $this->_markers = array(array('marker' => 0xE1, 'data' => ''));
                $this->_info = array(
                    'file' => array('UnixTime' => 1107431129),
                    'jfif' => array(),
                    'jpeg' => array(),
                    'xmp'  => array(),
                    'adobe' => array(),
                    'exif' => array(
                        'ByteAlign' => 'Little Endian',
                        'DateTime'  => array('2005:02:03 11:25:29', '2005:02:04 09:44:46'),
                    ),
                );
            }
        };

        $dates = $jpeg->getDates();
        $this->assertEquals('2005-02-03 11:25:29', $dates['EarliestTimeStr']);
        $this->assertEquals('ExifDateTime', $dates['EarliestTimeSource']);
    }
}
