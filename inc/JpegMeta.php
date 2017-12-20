<?php
/**
 * JPEG metadata reader/writer
 *
 * @license    BSD <http://www.opensource.org/licenses/bsd-license.php>
 * @link       http://github.com/sd/jpeg-php
 * @author     Sebastian Delmont <sdelmont@zonageek.com>
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Hakan Sandell <hakan.sandell@mydata.se>
 * @todo       Add support for Maker Notes, Extend for GIF and PNG metadata
 */

// Original copyright notice:
//
// Copyright (c) 2003 Sebastian Delmont <sdelmont@zonageek.com>
// All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions
// are met:
// 1. Redistributions of source code must retain the above copyright
//    notice, this list of conditions and the following disclaimer.
// 2. Redistributions in binary form must reproduce the above copyright
//    notice, this list of conditions and the following disclaimer in the
//    documentation and/or other materials provided with the distribution.
// 3. Neither the name of the author nor the names of its contributors
//    may be used to endorse or promote products derived from this software
//    without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
// IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
// TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
// PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
// HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
// TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
// PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
// SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE

class JpegMeta {
    var $_fileName;
    var $_fp = null;
    var $_fpout = null;
    var $_type = 'unknown';

    var $_markers;
    var $_info;


    /**
     * Constructor
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param $fileName
     */
    function __construct($fileName) {

        $this->_fileName = $fileName;

        $this->_fp = null;
        $this->_type = 'unknown';

        unset($this->_info);
        unset($this->_markers);
    }

    /**
     * Returns all gathered info as multidim array
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     */
    function & getRawInfo() {
        $this->_parseAll();

        if ($this->_markers == null) {
            return false;
        }

        return $this->_info;
    }

    /**
     * Returns basic image info
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     */
    function & getBasicInfo() {
        $this->_parseAll();

        $info = array();

        if ($this->_markers == null) {
            return false;
        }

        $info['Name'] = $this->_info['file']['Name'];
        if (isset($this->_info['file']['Url'])) {
            $info['Url'] = $this->_info['file']['Url'];
            $info['NiceSize'] = "???KB";
        } else {
            $info['Size'] = $this->_info['file']['Size'];
            $info['NiceSize'] = $this->_info['file']['NiceSize'];
        }

        if (@isset($this->_info['sof']['Format'])) {
            $info['Format'] = $this->_info['sof']['Format'] . " JPEG";
        } else {
            $info['Format'] = $this->_info['sof']['Format'] . " JPEG";
        }

        if (@isset($this->_info['sof']['ColorChannels'])) {
            $info['ColorMode'] = ($this->_info['sof']['ColorChannels'] > 1) ? "Color" : "B&W";
        }

        $info['Width'] = $this->getWidth();
        $info['Height'] = $this->getHeight();
        $info['DimStr'] = $this->getDimStr();

        $dates = $this->getDates();

        $info['DateTime'] = $dates['EarliestTime'];
        $info['DateTimeStr'] = $dates['EarliestTimeStr'];

        $info['HasThumbnail'] = $this->hasThumbnail();

        return $info;
    }


    /**
     * Convinience function to access nearly all available Data
     * through one function
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param array|string $fields field name or array with field names
     * @return bool|string
     */
    function getField($fields) {
        if(!is_array($fields)) $fields = array($fields);
        $info = false;
        foreach($fields as $field){
            if(strtolower(substr($field,0,5)) == 'iptc.'){
                $info = $this->getIPTCField(substr($field,5));
            }elseif(strtolower(substr($field,0,5)) == 'exif.'){
                $info = $this->getExifField(substr($field,5));
            }elseif(strtolower(substr($field,0,4)) == 'xmp.'){
                $info = $this->getXmpField(substr($field,4));
            }elseif(strtolower(substr($field,0,5)) == 'file.'){
                $info = $this->getFileField(substr($field,5));
            }elseif(strtolower(substr($field,0,5)) == 'date.'){
                $info = $this->getDateField(substr($field,5));
            }elseif(strtolower($field) == 'simple.camera'){
                $info = $this->getCamera();
            }elseif(strtolower($field) == 'simple.raw'){
                return $this->getRawInfo();
            }elseif(strtolower($field) == 'simple.title'){
                $info = $this->getTitle();
            }elseif(strtolower($field) == 'simple.shutterspeed'){
                $info = $this->getShutterSpeed();
            }else{
                $info = $this->getExifField($field);
            }
            if($info != false) break;
        }

        if($info === false)  $info = '';
        if(is_array($info)){
            if(isset($info['val'])){
                $info = $info['val'];
            }else{
                $info = join(', ',$info);
            }
        }
        return trim($info);
    }

    /**
     * Convinience function to set nearly all available Data
     * through one function
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $field field name
     * @param string $value
     * @return bool success or fail
     */
    function setField($field, $value) {
        if(strtolower(substr($field,0,5)) == 'iptc.'){
            return $this->setIPTCField(substr($field,5),$value);
        }elseif(strtolower(substr($field,0,5)) == 'exif.'){
            return $this->setExifField(substr($field,5),$value);
        }else{
            return $this->setExifField($field,$value);
        }
    }

    /**
     * Convinience function to delete nearly all available Data
     * through one function
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $field field name
     * @return bool
     */
    function deleteField($field) {
        if(strtolower(substr($field,0,5)) == 'iptc.'){
            return $this->deleteIPTCField(substr($field,5));
        }elseif(strtolower(substr($field,0,5)) == 'exif.'){
            return $this->deleteExifField(substr($field,5));
        }else{
            return $this->deleteExifField($field);
        }
    }

    /**
     * Return a date field
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $field
     * @return false|string
     */
    function getDateField($field) {
        if (!isset($this->_info['dates'])) {
            $this->_info['dates'] = $this->getDates();
        }

        if (isset($this->_info['dates'][$field])) {
            return $this->_info['dates'][$field];
        }

        return false;
    }

    /**
     * Return a file info field
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $field field name
     * @return false|string
     */
    function getFileField($field) {
        if (!isset($this->_info['file'])) {
            $this->_parseFileInfo();
        }

        if (isset($this->_info['file'][$field])) {
            return $this->_info['file'][$field];
        }

        return false;
    }

    /**
     * Return the camera info (Maker and Model)
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @todo   handle makernotes
     *
     * @return false|string
     */
    function getCamera(){
        $make  = $this->getField(array('Exif.Make','Exif.TIFFMake'));
        $model = $this->getField(array('Exif.Model','Exif.TIFFModel'));
        $cam = trim("$make $model");
        if(empty($cam)) return false;
        return $cam;
    }

    /**
     * Return shutter speed as a ratio
     *
     * @author Joe Lapp <joe.lapp@pobox.com>
     *
     * @return string
     */
    function getShutterSpeed() {
        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }
        if(!isset($this->_info['exif']['ExposureTime'])){
            return '';
        }

        $field = $this->_info['exif']['ExposureTime'];
        if($field['den'] == 1) return $field['num'];
        return $field['num'].'/'.$field['den'];
    }

    /**
     * Return an EXIF field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return false|string
     */
    function getExifField($field) {
        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['exif'][$field])) {
            return $this->_info['exif'][$field];
        }

        return false;
    }

    /**
     * Return an XMP field
     *
     * @author Hakan Sandell <hakan.sandell@mydata.se>
     *
     * @param string $field field name
     * @return false|string
     */
    function getXmpField($field) {
        if (!isset($this->_info['xmp'])) {
            $this->_parseMarkerXmp();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['xmp'][$field])) {
            return $this->_info['xmp'][$field];
        }

        return false;
    }

    /**
     * Return an Adobe Field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return false|string
     */
    function getAdobeField($field) {
        if (!isset($this->_info['adobe'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['adobe'][$field])) {
            return $this->_info['adobe'][$field];
        }

        return false;
    }

    /**
     * Return an IPTC field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return false|string
     */
    function getIPTCField($field) {
        if (!isset($this->_info['iptc'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['iptc'][$field])) {
            return $this->_info['iptc'][$field];
        }

        return false;
    }

    /**
     * Set an EXIF field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     * @author Joe Lapp <joe.lapp@pobox.com>
     *
     * @param string $field field name
     * @param string $value
     * @return bool
     */
    function setExifField($field, $value) {
        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['exif'] == false) {
            $this->_info['exif'] = array();
        }

        // make sure datetimes are in correct format
        if(strlen($field) >= 8 && strtolower(substr($field, 0, 8)) == 'datetime') {
            if(strlen($value) < 8 || $value{4} != ':' || $value{7} != ':') {
                $value = date('Y:m:d H:i:s', strtotime($value));
            }
        }

        $this->_info['exif'][$field] = $value;

        return true;
    }

    /**
     * Set an Adobe Field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @param string $value
     * @return bool
     */
    function setAdobeField($field, $value) {
        if (!isset($this->_info['adobe'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['adobe'] == false) {
            $this->_info['adobe'] = array();
        }

        $this->_info['adobe'][$field] = $value;

        return true;
    }

    /**
     * Calculates the multiplier needed to resize the image to the given
     * dimensions
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param int $maxwidth
     * @param int $maxheight
     * @return float|int
     */
    function getResizeRatio($maxwidth,$maxheight=0){
        if(!$maxheight) $maxheight = $maxwidth;

        $w = $this->getField('File.Width');
        $h = $this->getField('File.Height');

        $ratio = 1;
        if($w >= $h){
            if($w >= $maxwidth){
                $ratio = $maxwidth/$w;
            }elseif($h > $maxheight){
                $ratio = $maxheight/$h;
            }
        }else{
            if($h >= $maxheight){
                $ratio = $maxheight/$h;
            }elseif($w > $maxwidth){
                $ratio = $maxwidth/$w;
            }
        }
        return $ratio;
    }


    /**
     * Set an IPTC field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @param string $value
     * @return bool
     */
    function setIPTCField($field, $value) {
        if (!isset($this->_info['iptc'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['iptc'] == false) {
            $this->_info['iptc'] = array();
        }

        $this->_info['iptc'][$field] = $value;

        return true;
    }

    /**
     * Delete an EXIF field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return bool
     */
    function deleteExifField($field) {
        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['exif'] != false) {
            unset($this->_info['exif'][$field]);
        }

        return true;
    }

    /**
     * Delete an Adobe field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return bool
     */
    function deleteAdobeField($field) {
        if (!isset($this->_info['adobe'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['adobe'] != false) {
            unset($this->_info['adobe'][$field]);
        }

        return true;
    }

    /**
     * Delete an IPTC field
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $field field name
     * @return bool
     */
    function deleteIPTCField($field) {
        if (!isset($this->_info['iptc'])) {
            $this->_parseMarkerAdobe();
        }

        if ($this->_markers == null) {
            return false;
        }

        if ($this->_info['iptc'] != false) {
            unset($this->_info['iptc'][$field]);
        }

        return true;
    }

    /**
     * Get the image's title, tries various fields
     *
     * @param int $max maximum number chars (keeps words)
     * @return false|string
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function getTitle($max=80){
        // try various fields
        $cap = $this->getField(array('Iptc.Headline',
                    'Iptc.Caption',
                    'Xmp.dc:title',
                    'Exif.UserComment',
                    'Exif.TIFFUserComment',
                    'Exif.TIFFImageDescription',
                    'File.Name'));
        if (empty($cap)) return false;

        if(!$max) return $cap;
        // Shorten to 80 chars (keeping words)
        $new = preg_replace('/\n.+$/','',wordwrap($cap, $max));
        if($new != $cap) $new .= '...';

        return $new;
    }

    /**
     * Gather various date fields
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @return array|bool
     */
    function getDates() {
        $this->_parseAll();
        if ($this->_markers == null) {
            if (@isset($this->_info['file']['UnixTime'])) {
                $dates = array();
                $dates['FileModified'] = $this->_info['file']['UnixTime'];
                $dates['Time'] = $this->_info['file']['UnixTime'];
                $dates['TimeSource'] = 'FileModified';
                $dates['TimeStr'] = date("Y-m-d H:i:s", $this->_info['file']['UnixTime']);
                $dates['EarliestTime'] = $this->_info['file']['UnixTime'];
                $dates['EarliestTimeSource'] = 'FileModified';
                $dates['EarliestTimeStr'] = date("Y-m-d H:i:s", $this->_info['file']['UnixTime']);
                $dates['LatestTime'] = $this->_info['file']['UnixTime'];
                $dates['LatestTimeSource'] = 'FileModified';
                $dates['LatestTimeStr'] = date("Y-m-d H:i:s", $this->_info['file']['UnixTime']);
                return $dates;
            }
            return false;
        }

        $dates = array();

        $latestTime = 0;
        $latestTimeSource = "";
        $earliestTime = time();
        $earliestTimeSource = "";

        if (@isset($this->_info['exif']['DateTime'])) {
            $dates['ExifDateTime'] = $this->_info['exif']['DateTime'];

            $aux = $this->_info['exif']['DateTime'];
            $aux{4} = "-";
            $aux{7} = "-";
            $t = strtotime($aux);

            if ($t && $t > $latestTime) {
                $latestTime = $t;
                $latestTimeSource = "ExifDateTime";
            }

            if ($t && $t < $earliestTime) {
                $earliestTime = $t;
                $earliestTimeSource = "ExifDateTime";
            }
        }

        if (@isset($this->_info['exif']['DateTimeOriginal'])) {
            $dates['ExifDateTimeOriginal'] = $this->_info['exif']['DateTime'];

            $aux = $this->_info['exif']['DateTimeOriginal'];
            $aux{4} = "-";
            $aux{7} = "-";
            $t = strtotime($aux);

            if ($t && $t > $latestTime) {
                $latestTime = $t;
                $latestTimeSource = "ExifDateTimeOriginal";
            }

            if ($t && $t < $earliestTime) {
                $earliestTime = $t;
                $earliestTimeSource = "ExifDateTimeOriginal";
            }
        }

        if (@isset($this->_info['exif']['DateTimeDigitized'])) {
            $dates['ExifDateTimeDigitized'] = $this->_info['exif']['DateTime'];

            $aux = $this->_info['exif']['DateTimeDigitized'];
            $aux{4} = "-";
            $aux{7} = "-";
            $t = strtotime($aux);

            if ($t && $t > $latestTime) {
                $latestTime = $t;
                $latestTimeSource = "ExifDateTimeDigitized";
            }

            if ($t && $t < $earliestTime) {
                $earliestTime = $t;
                $earliestTimeSource = "ExifDateTimeDigitized";
            }
        }

        if (@isset($this->_info['iptc']['DateCreated'])) {
            $dates['IPTCDateCreated'] = $this->_info['iptc']['DateCreated'];

            $aux = $this->_info['iptc']['DateCreated'];
            $aux = substr($aux, 0, 4) . "-" . substr($aux, 4, 2) . "-" . substr($aux, 6, 2);
            $t = strtotime($aux);

            if ($t && $t > $latestTime) {
                $latestTime = $t;
                $latestTimeSource = "IPTCDateCreated";
            }

            if ($t && $t < $earliestTime) {
                $earliestTime = $t;
                $earliestTimeSource = "IPTCDateCreated";
            }
        }

        if (@isset($this->_info['file']['UnixTime'])) {
            $dates['FileModified'] = $this->_info['file']['UnixTime'];

            $t = $this->_info['file']['UnixTime'];

            if ($t && $t > $latestTime) {
                $latestTime = $t;
                $latestTimeSource = "FileModified";
            }

            if ($t && $t < $earliestTime) {
                $earliestTime = $t;
                $earliestTimeSource = "FileModified";
            }
        }

        $dates['Time'] = $earliestTime;
        $dates['TimeSource'] = $earliestTimeSource;
        $dates['TimeStr'] = date("Y-m-d H:i:s", $earliestTime);
        $dates['EarliestTime'] = $earliestTime;
        $dates['EarliestTimeSource'] = $earliestTimeSource;
        $dates['EarliestTimeStr'] = date("Y-m-d H:i:s", $earliestTime);
        $dates['LatestTime'] = $latestTime;
        $dates['LatestTimeSource'] = $latestTimeSource;
        $dates['LatestTimeStr'] = date("Y-m-d H:i:s", $latestTime);

        return $dates;
    }

    /**
     * Get the image width, tries various fields
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @return false|string
     */
    function getWidth() {
        if (!isset($this->_info['sof'])) {
            $this->_parseMarkerSOF();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['sof']['ImageWidth'])) {
            return $this->_info['sof']['ImageWidth'];
        }

        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }

        if (isset($this->_info['exif']['PixelXDimension'])) {
            return $this->_info['exif']['PixelXDimension'];
        }

        return false;
    }

    /**
     * Get the image height, tries various fields
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @return false|string
     */
    function getHeight() {
        if (!isset($this->_info['sof'])) {
            $this->_parseMarkerSOF();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (isset($this->_info['sof']['ImageHeight'])) {
            return $this->_info['sof']['ImageHeight'];
        }

        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }

        if (isset($this->_info['exif']['PixelYDimension'])) {
            return $this->_info['exif']['PixelYDimension'];
        }

        return false;
    }

    /**
     * Get an dimension string for use in img tag
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @return false|string
     */
    function getDimStr() {
        if ($this->_markers == null) {
            return false;
        }

        $w = $this->getWidth();
        $h = $this->getHeight();

        return "width='" . $w . "' height='" . $h . "'";
    }

    /**
     * Checks for an embedded thumbnail
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $which possible values: 'any', 'exif' or 'adobe'
     * @return false|string
     */
    function hasThumbnail($which = 'any') {
        if (($which == 'any') || ($which == 'exif')) {
            if (!isset($this->_info['exif'])) {
                $this->_parseMarkerExif();
            }

            if ($this->_markers == null) {
                return false;
            }

            if (isset($this->_info['exif']) && is_array($this->_info['exif'])) {
                if (isset($this->_info['exif']['JFIFThumbnail'])) {
                    return 'exif';
                }
            }
        }

        if ($which == 'adobe') {
            if (!isset($this->_info['adobe'])) {
                $this->_parseMarkerAdobe();
            }

            if ($this->_markers == null) {
                return false;
            }

            if (isset($this->_info['adobe']) && is_array($this->_info['adobe'])) {
                if (isset($this->_info['adobe']['ThumbnailData'])) {
                    return 'exif';
                }
            }
        }

        return false;
    }

    /**
     * Send embedded thumbnail to browser
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     *
     * @param string $which possible values: 'any', 'exif' or 'adobe'
     * @return bool
     */
    function sendThumbnail($which = 'any') {
        $data = null;

        if (($which == 'any') || ($which == 'exif')) {
            if (!isset($this->_info['exif'])) {
                $this->_parseMarkerExif();
            }

            if ($this->_markers == null) {
                return false;
            }

            if (isset($this->_info['exif']) && is_array($this->_info['exif'])) {
                if (isset($this->_info['exif']['JFIFThumbnail'])) {
                    $data =& $this->_info['exif']['JFIFThumbnail'];
                }
            }
        }

        if (($which == 'adobe') || ($data == null)){
            if (!isset($this->_info['adobe'])) {
                $this->_parseMarkerAdobe();
            }

            if ($this->_markers == null) {
                return false;
            }

            if (isset($this->_info['adobe']) && is_array($this->_info['adobe'])) {
                if (isset($this->_info['adobe']['ThumbnailData'])) {
                    $data =& $this->_info['adobe']['ThumbnailData'];
                }
            }
        }

        if ($data != null) {
            header("Content-type: image/jpeg");
            echo $data;
            return true;
        }

        return false;
    }

    /**
     * Save changed Metadata
     *
     * @author Sebastian Delmont <sdelmont@zonageek.com>
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $fileName file name or empty string for a random name
     * @return bool
     */
    function save($fileName = "") {
        if ($fileName == "") {
            $tmpName = tempnam(dirname($this->_fileName),'_metatemp_');
            $this->_writeJPEG($tmpName);
            if (file_exists($tmpName)) {
                return io_rename($tmpName, $this->_fileName);
            }
        } else {
            return $this->_writeJPEG($fileName);
        }
        return false;
    }

    /*************************************************************/
    /* PRIVATE FUNCTIONS (Internal Use Only!)                    */
    /*************************************************************/

    /*************************************************************/
    function _dispose($fileName = "") {
        $this->_fileName = $fileName;

        $this->_fp = null;
        $this->_type = 'unknown';

        unset($this->_markers);
        unset($this->_info);
    }

    /*************************************************************/
    function _readJPEG() {
        unset($this->_markers);
        //unset($this->_info);
        $this->_markers = array();
        //$this->_info = array();

        $this->_fp = @fopen($this->_fileName, 'rb');
        if ($this->_fp) {
            if (file_exists($this->_fileName)) {
                $this->_type = 'file';
            }
            else {
                $this->_type = 'url';
            }
        } else {
            $this->_fp = null;
            return false;  // ERROR: Can't open file
        }

        // Check for the JPEG signature
        $c1 = ord(fgetc($this->_fp));
        $c2 = ord(fgetc($this->_fp));

        if ($c1 != 0xFF || $c2 != 0xD8) {   // (0xFF + SOI)
            $this->_markers = null;
            return false;  // ERROR: File is not a JPEG
        }

        $count = 0;

        $done = false;
        $ok = true;

        while (!$done) {
            $capture = false;

            // First, skip any non 0xFF bytes
            $discarded = 0;
            $c = ord(fgetc($this->_fp));
            while (!feof($this->_fp) && ($c != 0xFF)) {
                $discarded++;
                $c = ord(fgetc($this->_fp));
            }
            // Then skip all 0xFF until the marker byte
            do {
                $marker = ord(fgetc($this->_fp));
            } while (!feof($this->_fp) && ($marker == 0xFF));

            if (feof($this->_fp)) {
                return false; // ERROR: Unexpected EOF
            }
            if ($discarded != 0) {
                return false; // ERROR: Extraneous data
            }

            $length = ord(fgetc($this->_fp)) * 256 + ord(fgetc($this->_fp));
            if (feof($this->_fp)) {
                return false; // ERROR: Unexpected EOF
            }
            if ($length < 2) {
                return false; // ERROR: Extraneous data
            }
            $length = $length - 2; // The length we got counts itself

            switch ($marker) {
                case 0xC0:    // SOF0
                case 0xC1:    // SOF1
                case 0xC2:    // SOF2
                case 0xC9:    // SOF9
                case 0xE0:    // APP0: JFIF data
                case 0xE1:    // APP1: EXIF or XMP data
                case 0xED:    // APP13: IPTC / Photoshop data
                    $capture = true;
                    break;
                case 0xDA:    // SOS: Start of scan... the image itself and the last block on the file
                    $capture = false;
                    $length = -1;  // This field has no length... it includes all data until EOF
                    $done = true;
                    break;
                default:
                    $capture = true;//false;
                    break;
            }

            $this->_markers[$count] = array();
            $this->_markers[$count]['marker'] = $marker;
            $this->_markers[$count]['length'] = $length;

            if ($capture) {
                if ($length)
                    $this->_markers[$count]['data'] = fread($this->_fp, $length);
                else
                    $this->_markers[$count]['data'] = "";
            }
            elseif (!$done) {
                $result = @fseek($this->_fp, $length, SEEK_CUR);
                // fseek doesn't seem to like HTTP 'files', but fgetc has no problem
                if (!($result === 0)) {
                    for ($i = 0; $i < $length; $i++) {
                        fgetc($this->_fp);
                    }
                }
            }
            $count++;
        }

        if ($this->_fp) {
            fclose($this->_fp);
            $this->_fp = null;
        }

        return $ok;
    }

    /*************************************************************/
    function _parseAll() {
        if (!isset($this->_info['file'])) {
            $this->_parseFileInfo();
        }
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        if (!isset($this->_info['jfif'])) {
            $this->_parseMarkerJFIF();
        }
        if (!isset($this->_info['jpeg'])) {
            $this->_parseMarkerSOF();
        }
        if (!isset($this->_info['exif'])) {
            $this->_parseMarkerExif();
        }
        if (!isset($this->_info['xmp'])) {
            $this->_parseMarkerXmp();
        }
        if (!isset($this->_info['adobe'])) {
            $this->_parseMarkerAdobe();
        }
    }

    /*************************************************************/

    /**
     * @param string $outputName
     *
     * @return bool
     */
    function _writeJPEG($outputName) {
        $this->_parseAll();

        $wroteEXIF = false;
        $wroteAdobe = false;

        $this->_fp = @fopen($this->_fileName, 'r');
        if ($this->_fp) {
            if (file_exists($this->_fileName)) {
                $this->_type = 'file';
            }
            else {
                $this->_type = 'url';
            }
        } else {
            $this->_fp = null;
            return false;  // ERROR: Can't open file
        }

        $this->_fpout = fopen($outputName, 'wb');
        if (!$this->_fpout) {
            $this->_fpout = null;
            fclose($this->_fp);
            $this->_fp = null;
            return false;  // ERROR: Can't open output file
        }

        // Check for the JPEG signature
        $c1 = ord(fgetc($this->_fp));
        $c2 = ord(fgetc($this->_fp));

        if ($c1 != 0xFF || $c2 != 0xD8) {   // (0xFF + SOI)
            return false;  // ERROR: File is not a JPEG
        }

        fputs($this->_fpout, chr(0xFF), 1);
        fputs($this->_fpout, chr(0xD8), 1); // (0xFF + SOI)

        $count = 0;

        $done = false;
        $ok = true;

        while (!$done) {
            // First, skip any non 0xFF bytes
            $discarded = 0;
            $c = ord(fgetc($this->_fp));
            while (!feof($this->_fp) && ($c != 0xFF)) {
                $discarded++;
                $c = ord(fgetc($this->_fp));
            }
            // Then skip all 0xFF until the marker byte
            do {
                $marker = ord(fgetc($this->_fp));
            } while (!feof($this->_fp) && ($marker == 0xFF));

            if (feof($this->_fp)) {
                $ok = false;
                break; // ERROR: Unexpected EOF
            }
            if ($discarded != 0) {
                $ok = false;
                break; // ERROR: Extraneous data
            }

            $length = ord(fgetc($this->_fp)) * 256 + ord(fgetc($this->_fp));
            if (feof($this->_fp)) {
                $ok = false;
                break; // ERROR: Unexpected EOF
            }
            if ($length < 2) {
                $ok = false;
                break; // ERROR: Extraneous data
            }
            $length = $length - 2; // The length we got counts itself

            unset($data);
            if ($marker == 0xE1) { // APP1: EXIF data
                $data =& $this->_createMarkerEXIF();
                $wroteEXIF = true;
            }
            elseif ($marker == 0xED) { // APP13: IPTC / Photoshop data
                $data =& $this->_createMarkerAdobe();
                $wroteAdobe = true;
            }
            elseif ($marker == 0xDA) { // SOS: Start of scan... the image itself and the last block on the file
                $done = true;
            }

            if (!$wroteEXIF && (($marker < 0xE0) || ($marker > 0xEF))) {
                if (isset($this->_info['exif']) && is_array($this->_info['exif'])) {
                    $exif =& $this->_createMarkerEXIF();
                    $this->_writeJPEGMarker(0xE1, strlen($exif), $exif, 0);
                    unset($exif);
                }
                $wroteEXIF = true;
            }

            if (!$wroteAdobe && (($marker < 0xE0) || ($marker > 0xEF))) {
                if ((isset($this->_info['adobe']) && is_array($this->_info['adobe']))
                        || (isset($this->_info['iptc']) && is_array($this->_info['iptc']))) {
                    $adobe =& $this->_createMarkerAdobe();
                    $this->_writeJPEGMarker(0xED, strlen($adobe), $adobe, 0);
                    unset($adobe);
                }
                $wroteAdobe = true;
            }

            $origLength = $length;
            if (isset($data)) {
                $length = strlen($data);
            }

            if ($marker != -1) {
                $this->_writeJPEGMarker($marker, $length, $data, $origLength);
            }
        }

        if ($this->_fp) {
            fclose($this->_fp);
            $this->_fp = null;
        }

        if ($this->_fpout) {
            fclose($this->_fpout);
            $this->_fpout = null;
        }

        return $ok;
    }

    /*************************************************************/

    /**
     * @param integer $marker
     * @param integer $length
     * @param string $data
     * @param integer $origLength
     *
     * @return bool
     */
    function _writeJPEGMarker($marker, $length, &$data, $origLength) {
        if ($length <= 0) {
            return false;
        }

        fputs($this->_fpout, chr(0xFF), 1);
        fputs($this->_fpout, chr($marker), 1);
        fputs($this->_fpout, chr((($length + 2) & 0x0000FF00) >> 8), 1);
        fputs($this->_fpout, chr((($length + 2) & 0x000000FF) >> 0), 1);

        if (isset($data)) {
            // Copy the generated data
            fputs($this->_fpout, $data, $length);

            if ($origLength > 0) {   // Skip the original data
                $result = @fseek($this->_fp, $origLength, SEEK_CUR);
                // fseek doesn't seem to like HTTP 'files', but fgetc has no problem
                if ($result != 0) {
                    for ($i = 0; $i < $origLength; $i++) {
                        fgetc($this->_fp);
                    }
                }
            }
        } else {
            if ($marker == 0xDA) {  // Copy until EOF
                while (!feof($this->_fp)) {
                    $data = fread($this->_fp, 1024 * 16);
                    fputs($this->_fpout, $data, strlen($data));
                }
            } else { // Copy only $length bytes
                $data = @fread($this->_fp, $length);
                fputs($this->_fpout, $data, $length);
            }
        }

        return true;
    }

    /**
     * Gets basic info from the file - should work with non-JPEGs
     *
     * @author  Sebastian Delmont <sdelmont@zonageek.com>
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function _parseFileInfo() {
        if (file_exists($this->_fileName) && is_file($this->_fileName)) {
            $this->_info['file'] = array();
            $this->_info['file']['Name'] = utf8_decodeFN(utf8_basename($this->_fileName));
            $this->_info['file']['Path'] = fullpath($this->_fileName);
            $this->_info['file']['Size'] = filesize($this->_fileName);
            if ($this->_info['file']['Size'] < 1024) {
                $this->_info['file']['NiceSize'] = $this->_info['file']['Size'] . 'B';
            } elseif ($this->_info['file']['Size'] < (1024 * 1024)) {
                $this->_info['file']['NiceSize'] = round($this->_info['file']['Size'] / 1024) . 'KB';
            } elseif ($this->_info['file']['Size'] < (1024 * 1024 * 1024)) {
                $this->_info['file']['NiceSize'] = round($this->_info['file']['Size'] / (1024*1024)) . 'MB';
            } else {
                $this->_info['file']['NiceSize'] = $this->_info['file']['Size'] . 'B';
            }
            $this->_info['file']['UnixTime'] = filemtime($this->_fileName);

            // get image size directly from file
            $size = getimagesize($this->_fileName);
            $this->_info['file']['Width']  = $size[0];
            $this->_info['file']['Height'] = $size[1];
            // set mime types and formats
            // http://php.net/manual/en/function.getimagesize.php
            // http://php.net/manual/en/function.image-type-to-mime-type.php
            switch ($size[2]){
                case 1:
                    $this->_info['file']['Mime']   = 'image/gif';
                    $this->_info['file']['Format'] = 'GIF';
                    break;
                case 2:
                    $this->_info['file']['Mime']   = 'image/jpeg';
                    $this->_info['file']['Format'] = 'JPEG';
                    break;
                case 3:
                    $this->_info['file']['Mime']   = 'image/png';
                    $this->_info['file']['Format'] = 'PNG';
                    break;
                case 4:
                    $this->_info['file']['Mime']   = 'application/x-shockwave-flash';
                    $this->_info['file']['Format'] = 'SWF';
                    break;
                case 5:
                    $this->_info['file']['Mime']   = 'image/psd';
                    $this->_info['file']['Format'] = 'PSD';
                    break;
                case 6:
                    $this->_info['file']['Mime']   = 'image/bmp';
                    $this->_info['file']['Format'] = 'BMP';
                    break;
                case 7:
                    $this->_info['file']['Mime']   = 'image/tiff';
                    $this->_info['file']['Format'] = 'TIFF (Intel)';
                    break;
                case 8:
                    $this->_info['file']['Mime']   = 'image/tiff';
                    $this->_info['file']['Format'] = 'TIFF (Motorola)';
                    break;
                case 9:
                    $this->_info['file']['Mime']   = 'application/octet-stream';
                    $this->_info['file']['Format'] = 'JPC';
                    break;
                case 10:
                    $this->_info['file']['Mime']   = 'image/jp2';
                    $this->_info['file']['Format'] = 'JP2';
                    break;
                case 11:
                    $this->_info['file']['Mime']   = 'application/octet-stream';
                    $this->_info['file']['Format'] = 'JPX';
                    break;
                case 12:
                    $this->_info['file']['Mime']   = 'application/octet-stream';
                    $this->_info['file']['Format'] = 'JB2';
                    break;
                case 13:
                    $this->_info['file']['Mime']   = 'application/x-shockwave-flash';
                    $this->_info['file']['Format'] = 'SWC';
                    break;
                case 14:
                    $this->_info['file']['Mime']   = 'image/iff';
                    $this->_info['file']['Format'] = 'IFF';
                    break;
                case 15:
                    $this->_info['file']['Mime']   = 'image/vnd.wap.wbmp';
                    $this->_info['file']['Format'] = 'WBMP';
                    break;
                case 16:
                    $this->_info['file']['Mime']   = 'image/xbm';
                    $this->_info['file']['Format'] = 'XBM';
                    break;
                default:
                    $this->_info['file']['Mime']   = 'image/unknown';
            }
        } else {
            $this->_info['file'] = array();
            $this->_info['file']['Name'] = utf8_basename($this->_fileName);
            $this->_info['file']['Url'] = $this->_fileName;
        }

        return true;
    }

    /*************************************************************/
    function _parseMarkerJFIF() {
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_markers[$i]['marker'] == 0xE0) {
                $signature = $this->_getFixedString($this->_markers[$i]['data'], 0, 4);
                if ($signature == 'JFIF') {
                    $data =& $this->_markers[$i]['data'];
                    break;
                }
            }
        }

        if ($data == null) {
            $this->_info['jfif'] = false;
            return false;
        }

        $this->_info['jfif'] = array();

        $vmaj = $this->_getByte($data, 5);
        $vmin = $this->_getByte($data, 6);

        $this->_info['jfif']['Version'] = sprintf('%d.%02d', $vmaj, $vmin);

        $units = $this->_getByte($data, 7);
        switch ($units) {
            case 0:
                $this->_info['jfif']['Units'] = 'pixels';
                break;
            case 1:
                $this->_info['jfif']['Units'] = 'dpi';
                break;
            case 2:
                $this->_info['jfif']['Units'] = 'dpcm';
                break;
            default:
                $this->_info['jfif']['Units'] = 'unknown';
                break;
        }

        $xdens = $this->_getShort($data, 8);
        $ydens = $this->_getShort($data, 10);

        $this->_info['jfif']['XDensity'] = $xdens;
        $this->_info['jfif']['YDensity'] = $ydens;

        $thumbx = $this->_getByte($data, 12);
        $thumby = $this->_getByte($data, 13);

        $this->_info['jfif']['ThumbnailWidth'] = $thumbx;
        $this->_info['jfif']['ThumbnailHeight'] = $thumby;

        return true;
    }

    /*************************************************************/
    function _parseMarkerSOF() {
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            switch ($this->_markers[$i]['marker']) {
                case 0xC0: // SOF0
                case 0xC1: // SOF1
                case 0xC2: // SOF2
                case 0xC9: // SOF9
                    $data =& $this->_markers[$i]['data'];
                    $marker = $this->_markers[$i]['marker'];
                    break;
            }
        }

        if ($data == null) {
            $this->_info['sof'] = false;
            return false;
        }

        $pos = 0;
        $this->_info['sof'] = array();

        switch ($marker) {
            case 0xC0: // SOF0
                $format = 'Baseline';
                break;
            case 0xC1: // SOF1
                $format = 'Progessive';
                break;
            case 0xC2: // SOF2
                $format = 'Non-baseline';
                break;
            case 0xC9: // SOF9
                $format = 'Arithmetic';
                break;
            default:
                return false;
        }

        $this->_info['sof']['Format']          = $format;
        $this->_info['sof']['SamplePrecision'] = $this->_getByte($data, $pos + 0);
        $this->_info['sof']['ImageHeight']     = $this->_getShort($data, $pos + 1);
        $this->_info['sof']['ImageWidth']      = $this->_getShort($data, $pos + 3);
        $this->_info['sof']['ColorChannels']   = $this->_getByte($data, $pos + 5);

        return true;
    }

    /**
     * Parses the XMP data
     *
     * @author  Hakan Sandell <hakan.sandell@mydata.se>
     */
    function _parseMarkerXmp() {
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_markers[$i]['marker'] == 0xE1) {
                $signature = $this->_getFixedString($this->_markers[$i]['data'], 0, 29);
                if ($signature == "http://ns.adobe.com/xap/1.0/\0") {
                    $data = substr($this->_markers[$i]['data'], 29);
                    break;
                }
            }
        }

        if ($data == null) {
            $this->_info['xmp'] = false;
            return false;
        }

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        $result = xml_parse_into_struct($parser, $data, $values, $tags);
        xml_parser_free($parser);

        if ($result == 0) {
            $this->_info['xmp'] = false;
            return false;
        }

        $this->_info['xmp'] = array();
        $count = count($values);
        for ($i = 0; $i < $count; $i++) {
            if ($values[$i]['tag'] == 'rdf:Description' && $values[$i]['type'] == 'open') {

                while ((++$i < $count) && ($values[$i]['tag'] != 'rdf:Description')) {
                    $this->_parseXmpNode($values, $i, $this->_info['xmp'][$values[$i]['tag']], $count);
                }
            }
        }
        return true;
    }

    /**
     * Parses XMP nodes by recursion
     *
     * @author  Hakan Sandell <hakan.sandell@mydata.se>
     *
     * @param array $values
     * @param int $i
     * @param mixed $meta
     * @param integer $count
     */
    function _parseXmpNode($values, &$i, &$meta, $count) {
        if ($values[$i]['type'] == 'close') return;

        if ($values[$i]['type'] == 'complete') {
            // Simple Type property
            $meta = $values[$i]['value'];
            return;
        }

        $i++;
        if ($i >= $count) return;

        if ($values[$i]['tag'] == 'rdf:Bag' || $values[$i]['tag'] == 'rdf:Seq') {
            // Array property
            $meta = array();
            while ($values[++$i]['tag'] == 'rdf:li') {
                $this->_parseXmpNode($values, $i, $meta[], $count);
            }
            $i++; // skip closing Bag/Seq tag

        } elseif ($values[$i]['tag'] == 'rdf:Alt') {
            // Language Alternative property, only the first (default) value is used
            if ($values[$i]['type'] == 'open') {
                $i++;
                $this->_parseXmpNode($values, $i, $meta, $count);
                while ((++$i < $count) && ($values[$i]['tag'] != 'rdf:Alt'));
                $i++; // skip closing Alt tag
            }

        } else {
            // Structure property
            $meta = array();
            $startTag = $values[$i-1]['tag'];
            do {
                $this->_parseXmpNode($values, $i, $meta[$values[$i]['tag']], $count);
            } while ((++$i < $count) && ($values[$i]['tag'] != $startTag));
        }
    }

    /*************************************************************/
    function _parseMarkerExif() {
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_markers[$i]['marker'] == 0xE1) {
                $signature = $this->_getFixedString($this->_markers[$i]['data'], 0, 6);
                if ($signature == "Exif\0\0") {
                    $data =& $this->_markers[$i]['data'];
                    break;
                }
            }
        }

        if ($data == null) {
            $this->_info['exif'] = false;
            return false;
        }
        $pos = 6;
        $this->_info['exif'] = array();

        // We don't increment $pos after this because Exif uses offsets relative to this point

        $byteAlign = $this->_getShort($data, $pos + 0);

        if ($byteAlign == 0x4949) { // "II"
            $isBigEndian = false;
        } elseif ($byteAlign == 0x4D4D) { // "MM"
            $isBigEndian = true;
        } else {
            return false; // Unexpected data
        }

        $alignCheck = $this->_getShort($data, $pos + 2, $isBigEndian);
        if ($alignCheck != 0x002A) // That's the expected value
            return false; // Unexpected data

        if ($isBigEndian) {
            $this->_info['exif']['ByteAlign'] = "Big Endian";
        } else {
            $this->_info['exif']['ByteAlign'] = "Little Endian";
        }

        $offsetIFD0 = $this->_getLong($data, $pos + 4, $isBigEndian);
        if ($offsetIFD0 < 8)
            return false; // Unexpected data

        $offsetIFD1 = $this->_readIFD($data, $pos, $offsetIFD0, $isBigEndian, 'ifd0');
        if ($offsetIFD1 != 0)
            $this->_readIFD($data, $pos, $offsetIFD1, $isBigEndian, 'ifd1');

        return true;
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $base
     * @param integer $offset
     * @param boolean $isBigEndian
     * @param string $mode
     *
     * @return int
     */
    function _readIFD($data, $base, $offset, $isBigEndian, $mode) {
        $EXIFTags = $this->_exifTagNames($mode);

        $numEntries = $this->_getShort($data, $base + $offset, $isBigEndian);
        $offset += 2;

        $exifTIFFOffset = 0;
        $exifTIFFLength = 0;
        $exifThumbnailOffset = 0;
        $exifThumbnailLength = 0;

        for ($i = 0; $i < $numEntries; $i++) {
            $tag = $this->_getShort($data, $base + $offset, $isBigEndian);
            $offset += 2;
            $type = $this->_getShort($data, $base + $offset, $isBigEndian);
            $offset += 2;
            $count = $this->_getLong($data, $base + $offset, $isBigEndian);
            $offset += 4;

            if (($type < 1) || ($type > 12))
                return false; // Unexpected Type

            $typeLengths = array( -1, 1, 1, 2, 4, 8, 1, 1, 2, 4, 8, 4, 8 );

            $dataLength = $typeLengths[$type] * $count;
            if ($dataLength > 4) {
                $dataOffset = $this->_getLong($data, $base + $offset, $isBigEndian);
                $rawValue = $this->_getFixedString($data, $base + $dataOffset, $dataLength);
            } else {
                $rawValue = $this->_getFixedString($data, $base + $offset, $dataLength);
            }
            $offset += 4;

            switch ($type) {
                case 1:    // UBYTE
                    if ($count == 1) {
                        $value = $this->_getByte($rawValue, 0);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getByte($rawValue, $j);
                    }
                    break;
                case 2:    // ASCII
                    $value = $rawValue;
                    break;
                case 3:    // USHORT
                    if ($count == 1) {
                        $value = $this->_getShort($rawValue, 0, $isBigEndian);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getShort($rawValue, $j * 2, $isBigEndian);
                    }
                    break;
                case 4:    // ULONG
                    if ($count == 1) {
                        $value = $this->_getLong($rawValue, 0, $isBigEndian);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getLong($rawValue, $j * 4, $isBigEndian);
                    }
                    break;
                case 5:    // URATIONAL
                    if ($count == 1) {
                        $a = $this->_getLong($rawValue, 0, $isBigEndian);
                        $b = $this->_getLong($rawValue, 4, $isBigEndian);
                        $value = array();
                        $value['val'] = 0;
                        $value['num'] = $a;
                        $value['den'] = $b;
                        if (($a != 0) && ($b != 0)) {
                            $value['val'] = $a / $b;
                        }
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++) {
                            $a = $this->_getLong($rawValue, $j * 8, $isBigEndian);
                            $b = $this->_getLong($rawValue, ($j * 8) + 4, $isBigEndian);
                            $value = array();
                            $value[$j]['val'] = 0;
                            $value[$j]['num'] = $a;
                            $value[$j]['den'] = $b;
                            if (($a != 0) && ($b != 0))
                                $value[$j]['val'] = $a / $b;
                        }
                    }
                    break;
                case 6:    // SBYTE
                    if ($count == 1) {
                        $value = $this->_getByte($rawValue, 0);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getByte($rawValue, $j);
                    }
                    break;
                case 7:    // UNDEFINED
                    $value = $rawValue;
                    break;
                case 8:    // SSHORT
                    if ($count == 1) {
                        $value = $this->_getShort($rawValue, 0, $isBigEndian);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getShort($rawValue, $j * 2, $isBigEndian);
                    }
                    break;
                case 9:    // SLONG
                    if ($count == 1) {
                        $value = $this->_getLong($rawValue, 0, $isBigEndian);
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++)
                            $value[$j] = $this->_getLong($rawValue, $j * 4, $isBigEndian);
                    }
                    break;
                case 10:   // SRATIONAL
                    if ($count == 1) {
                        $a = $this->_getLong($rawValue, 0, $isBigEndian);
                        $b = $this->_getLong($rawValue, 4, $isBigEndian);
                        $value = array();
                        $value['val'] = 0;
                        $value['num'] = $a;
                        $value['den'] = $b;
                        if (($a != 0) && ($b != 0))
                            $value['val'] = $a / $b;
                    } else {
                        $value = array();
                        for ($j = 0; $j < $count; $j++) {
                            $a = $this->_getLong($rawValue, $j * 8, $isBigEndian);
                            $b = $this->_getLong($rawValue, ($j * 8) + 4, $isBigEndian);
                            $value = array();
                            $value[$j]['val'] = 0;
                            $value[$j]['num'] = $a;
                            $value[$j]['den'] = $b;
                            if (($a != 0) && ($b != 0))
                                $value[$j]['val'] = $a / $b;
                        }
                    }
                    break;
                case 11:   // FLOAT
                    $value = $rawValue;
                    break;

                case 12:   // DFLOAT
                    $value = $rawValue;
                    break;
                default:
                    return false; // Unexpected Type
            }

            $tagName = '';
            if (($mode == 'ifd0') && ($tag == 0x8769)) {  // ExifIFDOffset
                $this->_readIFD($data, $base, $value, $isBigEndian, 'exif');
            } elseif (($mode == 'ifd0') && ($tag == 0x8825)) {  // GPSIFDOffset
                $this->_readIFD($data, $base, $value, $isBigEndian, 'gps');
            } elseif (($mode == 'ifd1') && ($tag == 0x0111)) {  // TIFFStripOffsets
                $exifTIFFOffset = $value;
            } elseif (($mode == 'ifd1') && ($tag == 0x0117)) {  // TIFFStripByteCounts
                $exifTIFFLength = $value;
            } elseif (($mode == 'ifd1') && ($tag == 0x0201)) {  // TIFFJFIFOffset
                $exifThumbnailOffset = $value;
            } elseif (($mode == 'ifd1') && ($tag == 0x0202)) {  // TIFFJFIFLength
                $exifThumbnailLength = $value;
            } elseif (($mode == 'exif') && ($tag == 0xA005)) {  // InteropIFDOffset
                $this->_readIFD($data, $base, $value, $isBigEndian, 'interop');
            }
            // elseif (($mode == 'exif') && ($tag == 0x927C)) {  // MakerNote
            // }
            else {
                if (isset($EXIFTags[$tag])) {
                    $tagName = $EXIFTags[$tag];
                    if (isset($this->_info['exif'][$tagName])) {
                        if (!is_array($this->_info['exif'][$tagName])) {
                            $aux = array();
                            $aux[0] = $this->_info['exif'][$tagName];
                            $this->_info['exif'][$tagName] = $aux;
                        }

                        $this->_info['exif'][$tagName][count($this->_info['exif'][$tagName])] = $value;
                    } else {
                        $this->_info['exif'][$tagName] = $value;
                    }
                }
                /*
                 else {
                    echo sprintf("<h1>Unknown tag %02x (t: %d l: %d) %s in %s</h1>", $tag, $type, $count, $mode, $this->_fileName);
                    // Unknown Tags will be ignored!!!
                    // That's because the tag might be a pointer (like the Exif tag)
                    // and saving it without saving the data it points to might
                    // create an invalid file.
                }
                */
            }
        }

        if (($exifThumbnailOffset > 0) && ($exifThumbnailLength > 0)) {
            $this->_info['exif']['JFIFThumbnail'] = $this->_getFixedString($data, $base + $exifThumbnailOffset, $exifThumbnailLength);
        }

        if (($exifTIFFOffset > 0) && ($exifTIFFLength > 0)) {
            $this->_info['exif']['TIFFStrips'] = $this->_getFixedString($data, $base + $exifTIFFOffset, $exifTIFFLength);
        }

        $nextOffset = $this->_getLong($data, $base + $offset, $isBigEndian);
        return $nextOffset;
    }

    /*************************************************************/
    function & _createMarkerExif() {
        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_markers[$i]['marker'] == 0xE1) {
                $signature = $this->_getFixedString($this->_markers[$i]['data'], 0, 6);
                if ($signature == "Exif\0\0") {
                    $data =& $this->_markers[$i]['data'];
                    break;
                }
            }
        }

        if (!isset($this->_info['exif'])) {
            return false;
        }

        $data = "Exif\0\0";
        $pos = 6;
        $offsetBase = 6;

        if (isset($this->_info['exif']['ByteAlign']) && ($this->_info['exif']['ByteAlign'] == "Big Endian")) {
            $isBigEndian = true;
            $aux = "MM";
            $pos = $this->_putString($data, $pos, $aux);
        } else {
            $isBigEndian = false;
            $aux = "II";
            $pos = $this->_putString($data, $pos, $aux);
        }
        $pos = $this->_putShort($data, $pos, 0x002A, $isBigEndian);
        $pos = $this->_putLong($data, $pos, 0x00000008, $isBigEndian); // IFD0 Offset is always 8

        $ifd0 =& $this->_getIFDEntries($isBigEndian, 'ifd0');
        $ifd1 =& $this->_getIFDEntries($isBigEndian, 'ifd1');

        $pos = $this->_writeIFD($data, $pos, $offsetBase, $ifd0, $isBigEndian, true);
        $pos = $this->_writeIFD($data, $pos, $offsetBase, $ifd1, $isBigEndian, false);

        return $data;
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     * @param integer $offsetBase
     * @param array $entries
     * @param boolean $isBigEndian
     * @param boolean $hasNext
     *
     * @return mixed
     */
    function _writeIFD(&$data, $pos, $offsetBase, &$entries, $isBigEndian, $hasNext) {
        $tiffData = null;
        $tiffDataOffsetPos = -1;

        $entryCount = count($entries);

        $dataPos = $pos + 2 + ($entryCount * 12) + 4;
        $pos = $this->_putShort($data, $pos, $entryCount, $isBigEndian);

        for ($i = 0; $i < $entryCount; $i++) {
            $tag = $entries[$i]['tag'];
            $type = $entries[$i]['type'];

            if ($type == -99) { // SubIFD
                $pos = $this->_putShort($data, $pos, $tag, $isBigEndian);
                $pos = $this->_putShort($data, $pos, 0x04, $isBigEndian); // LONG
                $pos = $this->_putLong($data, $pos, 0x01, $isBigEndian); // Count = 1
                $pos = $this->_putLong($data, $pos, $dataPos - $offsetBase, $isBigEndian);

                $dataPos = $this->_writeIFD($data, $dataPos, $offsetBase, $entries[$i]['value'], $isBigEndian, false);
            } elseif ($type == -98) { // TIFF Data
                $pos = $this->_putShort($data, $pos, $tag, $isBigEndian);
                $pos = $this->_putShort($data, $pos, 0x04, $isBigEndian); // LONG
                $pos = $this->_putLong($data, $pos, 0x01, $isBigEndian); // Count = 1
                $tiffDataOffsetPos = $pos;
                $pos = $this->_putLong($data, $pos, 0x00, $isBigEndian); // For Now
                $tiffData =& $entries[$i]['value'] ;
            } else { // Regular Entry
                $pos = $this->_putShort($data, $pos, $tag, $isBigEndian);
                $pos = $this->_putShort($data, $pos, $type, $isBigEndian);
                $pos = $this->_putLong($data, $pos, $entries[$i]['count'], $isBigEndian);
                if (strlen($entries[$i]['value']) > 4) {
                    $pos = $this->_putLong($data, $pos, $dataPos - $offsetBase, $isBigEndian);
                    $dataPos = $this->_putString($data, $dataPos, $entries[$i]['value']);
                } else {
                    $val = str_pad($entries[$i]['value'], 4, "\0");
                    $pos = $this->_putString($data, $pos, $val);
                }
            }
        }

        if ($tiffData != null) {
            $this->_putLong($data, $tiffDataOffsetPos, $dataPos - $offsetBase, $isBigEndian);
            $dataPos = $this->_putString($data, $dataPos, $tiffData);
        }

        if ($hasNext) {
            $pos = $this->_putLong($data, $pos, $dataPos - $offsetBase, $isBigEndian);
        } else {
            $pos = $this->_putLong($data, $pos, 0, $isBigEndian);
        }

        return $dataPos;
    }

    /*************************************************************/

    /**
     * @param boolean $isBigEndian
     * @param string $mode
     *
     * @return array
     */
    function & _getIFDEntries($isBigEndian, $mode) {
        $EXIFNames = $this->_exifTagNames($mode);
        $EXIFTags = $this->_exifNameTags($mode);
        $EXIFTypeInfo = $this->_exifTagTypes($mode);

        $ifdEntries = array();
        $entryCount = 0;

        foreach($EXIFNames as $tag => $name) {
            $type = $EXIFTypeInfo[$tag][0];
            $count = $EXIFTypeInfo[$tag][1];
            $value = null;

            if (($mode == 'ifd0') && ($tag == 0x8769)) {  // ExifIFDOffset
                if (isset($this->_info['exif']['EXIFVersion'])) {
                    $value =& $this->_getIFDEntries($isBigEndian, "exif");
                    $type = -99;
                }
                else {
                    $value = null;
                }
            } elseif (($mode == 'ifd0') && ($tag == 0x8825)) {  // GPSIFDOffset
                if (isset($this->_info['exif']['GPSVersionID'])) {
                    $value =& $this->_getIFDEntries($isBigEndian, "gps");
                    $type = -99;
                } else {
                    $value = null;
                }
            } elseif (($mode == 'ifd1') && ($tag == 0x0111)) {  // TIFFStripOffsets
                if (isset($this->_info['exif']['TIFFStrips'])) {
                    $value =& $this->_info['exif']['TIFFStrips'];
                    $type = -98;
                } else {
                    $value = null;
                }
            } elseif (($mode == 'ifd1') && ($tag == 0x0117)) {  // TIFFStripByteCounts
                if (isset($this->_info['exif']['TIFFStrips'])) {
                    $value = strlen($this->_info['exif']['TIFFStrips']);
                } else {
                    $value = null;
                }
            } elseif (($mode == 'ifd1') && ($tag == 0x0201)) {  // TIFFJFIFOffset
                if (isset($this->_info['exif']['JFIFThumbnail'])) {
                    $value =& $this->_info['exif']['JFIFThumbnail'];
                    $type = -98;
                } else {
                    $value = null;
                }
            } elseif (($mode == 'ifd1') && ($tag == 0x0202)) {  // TIFFJFIFLength
                if (isset($this->_info['exif']['JFIFThumbnail'])) {
                    $value = strlen($this->_info['exif']['JFIFThumbnail']);
                } else {
                    $value = null;
                }
            } elseif (($mode == 'exif') && ($tag == 0xA005)) {  // InteropIFDOffset
                if (isset($this->_info['exif']['InteroperabilityIndex'])) {
                    $value =& $this->_getIFDEntries($isBigEndian, "interop");
                    $type = -99;
                } else {
                    $value = null;
                }
            } elseif (isset($this->_info['exif'][$name])) {
                $origValue =& $this->_info['exif'][$name];

                // This makes it easier to process variable size elements
                if (!is_array($origValue) || isset($origValue['val'])) {
                    unset($origValue); // Break the reference
                    $origValue = array($this->_info['exif'][$name]);
                }
                $origCount = count($origValue);

                if ($origCount == 0 ) {
                    $type = -1;  // To ignore this field
                }

                $value = " ";

                switch ($type) {
                    case 1:    // UBYTE
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {

                            $this->_putByte($value, $j, $origValue[$j]);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putByte($value, $j, 0);
                            $j++;
                        }
                        break;
                    case 2:    // ASCII
                        $v = strval($origValue[0]);
                        if (($count != 0) && (strlen($v) > $count)) {
                            $v = substr($v, 0, $count);
                        }
                        elseif (($count > 0) && (strlen($v) < $count)) {
                            $v = str_pad($v, $count, "\0");
                        }

                        $count = strlen($v);

                        $this->_putString($value, 0, $v);
                        break;
                    case 3:    // USHORT
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $this->_putShort($value, $j * 2, $origValue[$j], $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putShort($value, $j * 2, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 4:    // ULONG
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $this->_putLong($value, $j * 4, $origValue[$j], $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putLong($value, $j * 4, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 5:    // URATIONAL
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $v = $origValue[$j];
                            if (is_array($v)) {
                                $a = $v['num'];
                                $b = $v['den'];
                            }
                            else {
                                $a = 0;
                                $b = 0;
                                // TODO: Allow other types and convert them
                            }
                            $this->_putLong($value, $j * 8, $a, $isBigEndian);
                            $this->_putLong($value, ($j * 8) + 4, $b, $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putLong($value, $j * 8, 0, $isBigEndian);
                            $this->_putLong($value, ($j * 8) + 4, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 6:    // SBYTE
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $this->_putByte($value, $j, $origValue[$j]);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putByte($value, $j, 0);
                            $j++;
                        }
                        break;
                    case 7:    // UNDEFINED
                        $v = strval($origValue[0]);
                        if (($count != 0) && (strlen($v) > $count)) {
                            $v = substr($v, 0, $count);
                        }
                        elseif (($count > 0) && (strlen($v) < $count)) {
                            $v = str_pad($v, $count, "\0");
                        }

                        $count = strlen($v);

                        $this->_putString($value, 0, $v);
                        break;
                    case 8:    // SSHORT
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $this->_putShort($value, $j * 2, $origValue[$j], $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putShort($value, $j * 2, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 9:    // SLONG
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $this->_putLong($value, $j * 4, $origValue[$j], $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putLong($value, $j * 4, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 10:   // SRATIONAL
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $v = $origValue[$j];
                            if (is_array($v)) {
                                $a = $v['num'];
                                $b = $v['den'];
                            }
                            else {
                                $a = 0;
                                $b = 0;
                                // TODO: Allow other types and convert them
                            }

                            $this->_putLong($value, $j * 8, $a, $isBigEndian);
                            $this->_putLong($value, ($j * 8) + 4, $b, $isBigEndian);
                            $j++;
                        }

                        while ($j < $count) {
                            $this->_putLong($value, $j * 8, 0, $isBigEndian);
                            $this->_putLong($value, ($j * 8) + 4, 0, $isBigEndian);
                            $j++;
                        }
                        break;
                    case 11:   // FLOAT
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $v = strval($origValue[$j]);
                            if (strlen($v) > 4) {
                                $v = substr($v, 0, 4);
                            }
                            elseif (strlen($v) < 4) {
                                $v = str_pad($v, 4, "\0");
                            }
                            $this->_putString($value, $j * 4, $v);
                            $j++;
                        }

                        while ($j < $count) {
                            $v = "\0\0\0\0";
                            $this->_putString($value, $j * 4, $v);
                            $j++;
                        }
                        break;
                    case 12:   // DFLOAT
                        if ($count == 0) {
                            $count = $origCount;
                        }

                        $j = 0;
                        while (($j < $count) && ($j < $origCount)) {
                            $v = strval($origValue[$j]);
                            if (strlen($v) > 8) {
                                $v = substr($v, 0, 8);
                            }
                            elseif (strlen($v) < 8) {
                                $v = str_pad($v, 8, "\0");
                            }
                            $this->_putString($value, $j * 8, $v);
                            $j++;
                        }

                        while ($j < $count) {
                            $v = "\0\0\0\0\0\0\0\0";
                            $this->_putString($value, $j * 8, $v);
                            $j++;
                        }
                        break;
                    default:
                        $value = null;
                        break;
                }
            }

            if ($value != null) {
                $ifdEntries[$entryCount] = array();
                $ifdEntries[$entryCount]['tag'] = $tag;
                $ifdEntries[$entryCount]['type'] = $type;
                $ifdEntries[$entryCount]['count'] = $count;
                $ifdEntries[$entryCount]['value'] = $value;

                $entryCount++;
            }
        }

        return $ifdEntries;
    }

    /*************************************************************/
    function _parseMarkerAdobe() {
        if (!isset($this->_markers)) {
            $this->_readJPEG();
        }

        if ($this->_markers == null) {
            return false;
        }

        $data = null;
        $count = count($this->_markers);
        for ($i = 0; $i < $count; $i++) {
            if ($this->_markers[$i]['marker'] == 0xED) {
                $signature = $this->_getFixedString($this->_markers[$i]['data'], 0, 14);
                if ($signature == "Photoshop 3.0\0") {
                    $data =& $this->_markers[$i]['data'];
                    break;
                }
            }
        }

        if ($data == null) {
            $this->_info['adobe'] = false;
            $this->_info['iptc'] = false;
            return false;
        }
        $pos = 14;
        $this->_info['adobe'] = array();
        $this->_info['adobe']['raw'] = array();
        $this->_info['iptc'] = array();

        $datasize = strlen($data);

        while ($pos < $datasize) {
            $signature = $this->_getFixedString($data, $pos, 4);
            if ($signature != '8BIM')
                return false;
            $pos += 4;

            $type = $this->_getShort($data, $pos);
            $pos += 2;

            $strlen = $this->_getByte($data, $pos);
            $pos += 1;
            $header = '';
            for ($i = 0; $i < $strlen; $i++) {
                $header .= $data{$pos + $i};
            }
            $pos += $strlen + 1 - ($strlen % 2);  // The string is padded to even length, counting the length byte itself

            $length = $this->_getLong($data, $pos);
            $pos += 4;

            $basePos = $pos;

            switch ($type) {
                case 0x0404: // Caption (IPTC Data)
                    $pos = $this->_readIPTC($data, $pos);
                    if ($pos == false)
                        return false;
                    break;
                case 0x040A: // CopyrightFlag
                    $this->_info['adobe']['CopyrightFlag'] = $this->_getByte($data, $pos);
                    $pos += $length;
                    break;
                case 0x040B: // ImageURL
                    $this->_info['adobe']['ImageURL'] = $this->_getFixedString($data, $pos, $length);
                    $pos += $length;
                    break;
                case 0x040C: // Thumbnail
                    $aux = $this->_getLong($data, $pos);
                    $pos += 4;
                    if ($aux == 1) {
                        $this->_info['adobe']['ThumbnailWidth'] = $this->_getLong($data, $pos);
                        $pos += 4;
                        $this->_info['adobe']['ThumbnailHeight'] = $this->_getLong($data, $pos);
                        $pos += 4;

                        $pos += 16; // Skip some data

                        $this->_info['adobe']['ThumbnailData'] = $this->_getFixedString($data, $pos, $length - 28);
                        $pos += $length - 28;
                    }
                    break;
                default:
                    break;
            }

            // We save all blocks, even those we recognized
            $label = sprintf('8BIM_0x%04x', $type);
            $this->_info['adobe']['raw'][$label] = array();
            $this->_info['adobe']['raw'][$label]['type'] = $type;
            $this->_info['adobe']['raw'][$label]['header'] = $header;
            $this->_info['adobe']['raw'][$label]['data'] =& $this->_getFixedString($data, $basePos, $length);

            $pos = $basePos + $length + ($length % 2); // Even padding
        }

    }

    /*************************************************************/
    function _readIPTC(&$data, $pos = 0) {
        $totalLength = strlen($data);

        $IPTCTags = $this->_iptcTagNames();

        while ($pos < ($totalLength - 5)) {
            $signature = $this->_getShort($data, $pos);
            if ($signature != 0x1C02)
                return $pos;
            $pos += 2;

            $type = $this->_getByte($data, $pos);
            $pos += 1;
            $length = $this->_getShort($data, $pos);
            $pos += 2;

            $basePos = $pos;
            $label = '';

            if (isset($IPTCTags[$type])) {
                $label = $IPTCTags[$type];
            } else {
                $label = sprintf('IPTC_0x%02x', $type);
            }

            if ($label != '') {
                if (isset($this->_info['iptc'][$label])) {
                    if (!is_array($this->_info['iptc'][$label])) {
                        $aux = array();
                        $aux[0] = $this->_info['iptc'][$label];
                        $this->_info['iptc'][$label] = $aux;
                    }
                    $this->_info['iptc'][$label][ count($this->_info['iptc'][$label]) ] = $this->_getFixedString($data, $pos, $length);
                } else {
                    $this->_info['iptc'][$label] = $this->_getFixedString($data, $pos, $length);
                }
            }

            $pos = $basePos + $length; // No padding
        }
        return $pos;
    }

    /*************************************************************/
    function & _createMarkerAdobe() {
        if (isset($this->_info['iptc'])) {
            if (!isset($this->_info['adobe'])) {
                $this->_info['adobe'] = array();
            }
            if (!isset($this->_info['adobe']['raw'])) {
                $this->_info['adobe']['raw'] = array();
            }
            if (!isset($this->_info['adobe']['raw']['8BIM_0x0404'])) {
                $this->_info['adobe']['raw']['8BIM_0x0404'] = array();
            }
            $this->_info['adobe']['raw']['8BIM_0x0404']['type'] = 0x0404;
            $this->_info['adobe']['raw']['8BIM_0x0404']['header'] = "Caption";
            $this->_info['adobe']['raw']['8BIM_0x0404']['data'] =& $this->_writeIPTC();
        }

        if (isset($this->_info['adobe']['raw']) && (count($this->_info['adobe']['raw']) > 0)) {
            $data = "Photoshop 3.0\0";
            $pos = 14;

            reset($this->_info['adobe']['raw']);
            foreach ($this->_info['adobe']['raw'] as $value){
                $pos = $this->_write8BIM(
                        $data,
                        $pos,
                        $value['type'],
                        $value['header'],
                        $value['data'] );
            }
        }

        return $data;
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     *
     * @param string $type
     * @param string $header
     * @param mixed $value
     *
     * @return int|mixed
     */
    function _write8BIM(&$data, $pos, $type, $header, &$value) {
        $signature = "8BIM";

        $pos = $this->_putString($data, $pos, $signature);
        $pos = $this->_putShort($data, $pos, $type);

        $len = strlen($header);

        $pos = $this->_putByte($data, $pos, $len);
        $pos = $this->_putString($data, $pos, $header);
        if (($len % 2) == 0) {  // Even padding, including the length byte
            $pos = $this->_putByte($data, $pos, 0);
        }

        $len = strlen($value);
        $pos = $this->_putLong($data, $pos, $len);
        $pos = $this->_putString($data, $pos, $value);
        if (($len % 2) != 0) {  // Even padding
            $pos = $this->_putByte($data, $pos, 0);
        }
        return $pos;
    }

    /*************************************************************/
    function & _writeIPTC() {
        $data = " ";
        $pos = 0;

        $IPTCNames =& $this->_iptcNameTags();

        foreach($this->_info['iptc'] as $label => $value) {
            $value =& $this->_info['iptc'][$label];
            $type = -1;

            if (isset($IPTCNames[$label])) {
                $type = $IPTCNames[$label];
            }
            elseif (substr($label, 0, 7) == "IPTC_0x") {
                $type = hexdec(substr($label, 7, 2));
            }

            if ($type != -1) {
                if (is_array($value)) {
                    $vcnt = count($value);
                    for ($i = 0; $i < $vcnt; $i++) {
                        $pos = $this->_writeIPTCEntry($data, $pos, $type, $value[$i]);
                    }
                }
                else {
                    $pos = $this->_writeIPTCEntry($data, $pos, $type, $value);
                }
            }
        }

        return $data;
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     *
     * @param string $type
     * @param mixed $value
     *
     * @return int|mixed
     */
    function _writeIPTCEntry(&$data, $pos, $type, &$value) {
        $pos = $this->_putShort($data, $pos, 0x1C02);
        $pos = $this->_putByte($data, $pos, $type);
        $pos = $this->_putShort($data, $pos, strlen($value));
        $pos = $this->_putString($data, $pos, $value);

        return $pos;
    }

    /*************************************************************/
    function _exifTagNames($mode) {
        $tags = array();

        if ($mode == 'ifd0') {
            $tags[0x010E] = 'ImageDescription';
            $tags[0x010F] = 'Make';
            $tags[0x0110] = 'Model';
            $tags[0x0112] = 'Orientation';
            $tags[0x011A] = 'XResolution';
            $tags[0x011B] = 'YResolution';
            $tags[0x0128] = 'ResolutionUnit';
            $tags[0x0131] = 'Software';
            $tags[0x0132] = 'DateTime';
            $tags[0x013B] = 'Artist';
            $tags[0x013E] = 'WhitePoint';
            $tags[0x013F] = 'PrimaryChromaticities';
            $tags[0x0211] = 'YCbCrCoefficients';
            $tags[0x0212] = 'YCbCrSubSampling';
            $tags[0x0213] = 'YCbCrPositioning';
            $tags[0x0214] = 'ReferenceBlackWhite';
            $tags[0x8298] = 'Copyright';
            $tags[0x8769] = 'ExifIFDOffset';
            $tags[0x8825] = 'GPSIFDOffset';
        }
        if ($mode == 'ifd1') {
            $tags[0x00FE] = 'TIFFNewSubfileType';
            $tags[0x00FF] = 'TIFFSubfileType';
            $tags[0x0100] = 'TIFFImageWidth';
            $tags[0x0101] = 'TIFFImageHeight';
            $tags[0x0102] = 'TIFFBitsPerSample';
            $tags[0x0103] = 'TIFFCompression';
            $tags[0x0106] = 'TIFFPhotometricInterpretation';
            $tags[0x0107] = 'TIFFThreshholding';
            $tags[0x0108] = 'TIFFCellWidth';
            $tags[0x0109] = 'TIFFCellLength';
            $tags[0x010A] = 'TIFFFillOrder';
            $tags[0x010E] = 'TIFFImageDescription';
            $tags[0x010F] = 'TIFFMake';
            $tags[0x0110] = 'TIFFModel';
            $tags[0x0111] = 'TIFFStripOffsets';
            $tags[0x0112] = 'TIFFOrientation';
            $tags[0x0115] = 'TIFFSamplesPerPixel';
            $tags[0x0116] = 'TIFFRowsPerStrip';
            $tags[0x0117] = 'TIFFStripByteCounts';
            $tags[0x0118] = 'TIFFMinSampleValue';
            $tags[0x0119] = 'TIFFMaxSampleValue';
            $tags[0x011A] = 'TIFFXResolution';
            $tags[0x011B] = 'TIFFYResolution';
            $tags[0x011C] = 'TIFFPlanarConfiguration';
            $tags[0x0122] = 'TIFFGrayResponseUnit';
            $tags[0x0123] = 'TIFFGrayResponseCurve';
            $tags[0x0128] = 'TIFFResolutionUnit';
            $tags[0x0131] = 'TIFFSoftware';
            $tags[0x0132] = 'TIFFDateTime';
            $tags[0x013B] = 'TIFFArtist';
            $tags[0x013C] = 'TIFFHostComputer';
            $tags[0x0140] = 'TIFFColorMap';
            $tags[0x0152] = 'TIFFExtraSamples';
            $tags[0x0201] = 'TIFFJFIFOffset';
            $tags[0x0202] = 'TIFFJFIFLength';
            $tags[0x0211] = 'TIFFYCbCrCoefficients';
            $tags[0x0212] = 'TIFFYCbCrSubSampling';
            $tags[0x0213] = 'TIFFYCbCrPositioning';
            $tags[0x0214] = 'TIFFReferenceBlackWhite';
            $tags[0x8298] = 'TIFFCopyright';
            $tags[0x9286] = 'TIFFUserComment';
        } elseif ($mode == 'exif') {
            $tags[0x829A] = 'ExposureTime';
            $tags[0x829D] = 'FNumber';
            $tags[0x8822] = 'ExposureProgram';
            $tags[0x8824] = 'SpectralSensitivity';
            $tags[0x8827] = 'ISOSpeedRatings';
            $tags[0x8828] = 'OECF';
            $tags[0x9000] = 'EXIFVersion';
            $tags[0x9003] = 'DatetimeOriginal';
            $tags[0x9004] = 'DatetimeDigitized';
            $tags[0x9101] = 'ComponentsConfiguration';
            $tags[0x9102] = 'CompressedBitsPerPixel';
            $tags[0x9201] = 'ShutterSpeedValue';
            $tags[0x9202] = 'ApertureValue';
            $tags[0x9203] = 'BrightnessValue';
            $tags[0x9204] = 'ExposureBiasValue';
            $tags[0x9205] = 'MaxApertureValue';
            $tags[0x9206] = 'SubjectDistance';
            $tags[0x9207] = 'MeteringMode';
            $tags[0x9208] = 'LightSource';
            $tags[0x9209] = 'Flash';
            $tags[0x920A] = 'FocalLength';
            $tags[0x927C] = 'MakerNote';
            $tags[0x9286] = 'UserComment';
            $tags[0x9290] = 'SubSecTime';
            $tags[0x9291] = 'SubSecTimeOriginal';
            $tags[0x9292] = 'SubSecTimeDigitized';
            $tags[0xA000] = 'FlashPixVersion';
            $tags[0xA001] = 'ColorSpace';
            $tags[0xA002] = 'PixelXDimension';
            $tags[0xA003] = 'PixelYDimension';
            $tags[0xA004] = 'RelatedSoundFile';
            $tags[0xA005] = 'InteropIFDOffset';
            $tags[0xA20B] = 'FlashEnergy';
            $tags[0xA20C] = 'SpatialFrequencyResponse';
            $tags[0xA20E] = 'FocalPlaneXResolution';
            $tags[0xA20F] = 'FocalPlaneYResolution';
            $tags[0xA210] = 'FocalPlaneResolutionUnit';
            $tags[0xA214] = 'SubjectLocation';
            $tags[0xA215] = 'ExposureIndex';
            $tags[0xA217] = 'SensingMethod';
            $tags[0xA300] = 'FileSource';
            $tags[0xA301] = 'SceneType';
            $tags[0xA302] = 'CFAPattern';
        } elseif ($mode == 'interop') {
            $tags[0x0001] = 'InteroperabilityIndex';
            $tags[0x0002] = 'InteroperabilityVersion';
            $tags[0x1000] = 'RelatedImageFileFormat';
            $tags[0x1001] = 'RelatedImageWidth';
            $tags[0x1002] = 'RelatedImageLength';
        } elseif ($mode == 'gps') {
            $tags[0x0000] = 'GPSVersionID';
            $tags[0x0001] = 'GPSLatitudeRef';
            $tags[0x0002] = 'GPSLatitude';
            $tags[0x0003] = 'GPSLongitudeRef';
            $tags[0x0004] = 'GPSLongitude';
            $tags[0x0005] = 'GPSAltitudeRef';
            $tags[0x0006] = 'GPSAltitude';
            $tags[0x0007] = 'GPSTimeStamp';
            $tags[0x0008] = 'GPSSatellites';
            $tags[0x0009] = 'GPSStatus';
            $tags[0x000A] = 'GPSMeasureMode';
            $tags[0x000B] = 'GPSDOP';
            $tags[0x000C] = 'GPSSpeedRef';
            $tags[0x000D] = 'GPSSpeed';
            $tags[0x000E] = 'GPSTrackRef';
            $tags[0x000F] = 'GPSTrack';
            $tags[0x0010] = 'GPSImgDirectionRef';
            $tags[0x0011] = 'GPSImgDirection';
            $tags[0x0012] = 'GPSMapDatum';
            $tags[0x0013] = 'GPSDestLatitudeRef';
            $tags[0x0014] = 'GPSDestLatitude';
            $tags[0x0015] = 'GPSDestLongitudeRef';
            $tags[0x0016] = 'GPSDestLongitude';
            $tags[0x0017] = 'GPSDestBearingRef';
            $tags[0x0018] = 'GPSDestBearing';
            $tags[0x0019] = 'GPSDestDistanceRef';
            $tags[0x001A] = 'GPSDestDistance';
        }

        return $tags;
    }

    /*************************************************************/
    function _exifTagTypes($mode) {
        $tags = array();

        if ($mode == 'ifd0') {
            $tags[0x010E] = array(2, 0); // ImageDescription -> ASCII, Any
            $tags[0x010F] = array(2, 0); // Make -> ASCII, Any
            $tags[0x0110] = array(2, 0); // Model -> ASCII, Any
            $tags[0x0112] = array(3, 1); // Orientation -> SHORT, 1
            $tags[0x011A] = array(5, 1); // XResolution -> RATIONAL, 1
            $tags[0x011B] = array(5, 1); // YResolution -> RATIONAL, 1
            $tags[0x0128] = array(3, 1); // ResolutionUnit -> SHORT
            $tags[0x0131] = array(2, 0); // Software -> ASCII, Any
            $tags[0x0132] = array(2, 20); // DateTime -> ASCII, 20
            $tags[0x013B] = array(2, 0); // Artist -> ASCII, Any
            $tags[0x013E] = array(5, 2); // WhitePoint -> RATIONAL, 2
            $tags[0x013F] = array(5, 6); // PrimaryChromaticities -> RATIONAL, 6
            $tags[0x0211] = array(5, 3); // YCbCrCoefficients -> RATIONAL, 3
            $tags[0x0212] = array(3, 2); // YCbCrSubSampling -> SHORT, 2
            $tags[0x0213] = array(3, 1); // YCbCrPositioning -> SHORT, 1
            $tags[0x0214] = array(5, 6); // ReferenceBlackWhite -> RATIONAL, 6
            $tags[0x8298] = array(2, 0); // Copyright -> ASCII, Any
            $tags[0x8769] = array(4, 1); // ExifIFDOffset -> LONG, 1
            $tags[0x8825] = array(4, 1); // GPSIFDOffset -> LONG, 1
        }
        if ($mode == 'ifd1') {
            $tags[0x00FE] = array(4, 1); // TIFFNewSubfileType -> LONG, 1
            $tags[0x00FF] = array(3, 1); // TIFFSubfileType -> SHORT, 1
            $tags[0x0100] = array(4, 1); // TIFFImageWidth -> LONG (or SHORT), 1
            $tags[0x0101] = array(4, 1); // TIFFImageHeight -> LONG (or SHORT), 1
            $tags[0x0102] = array(3, 3); // TIFFBitsPerSample -> SHORT, 3
            $tags[0x0103] = array(3, 1); // TIFFCompression -> SHORT, 1
            $tags[0x0106] = array(3, 1); // TIFFPhotometricInterpretation -> SHORT, 1
            $tags[0x0107] = array(3, 1); // TIFFThreshholding -> SHORT, 1
            $tags[0x0108] = array(3, 1); // TIFFCellWidth -> SHORT, 1
            $tags[0x0109] = array(3, 1); // TIFFCellLength -> SHORT, 1
            $tags[0x010A] = array(3, 1); // TIFFFillOrder -> SHORT, 1
            $tags[0x010E] = array(2, 0); // TIFFImageDescription -> ASCII, Any
            $tags[0x010F] = array(2, 0); // TIFFMake -> ASCII, Any
            $tags[0x0110] = array(2, 0); // TIFFModel -> ASCII, Any
            $tags[0x0111] = array(4, 0); // TIFFStripOffsets -> LONG (or SHORT), Any (one per strip)
            $tags[0x0112] = array(3, 1); // TIFFOrientation -> SHORT, 1
            $tags[0x0115] = array(3, 1); // TIFFSamplesPerPixel -> SHORT, 1
            $tags[0x0116] = array(4, 1); // TIFFRowsPerStrip -> LONG (or SHORT), 1
            $tags[0x0117] = array(4, 0); // TIFFStripByteCounts -> LONG (or SHORT), Any (one per strip)
            $tags[0x0118] = array(3, 0); // TIFFMinSampleValue -> SHORT, Any (SamplesPerPixel)
            $tags[0x0119] = array(3, 0); // TIFFMaxSampleValue -> SHORT, Any (SamplesPerPixel)
            $tags[0x011A] = array(5, 1); // TIFFXResolution -> RATIONAL, 1
            $tags[0x011B] = array(5, 1); // TIFFYResolution -> RATIONAL, 1
            $tags[0x011C] = array(3, 1); // TIFFPlanarConfiguration -> SHORT, 1
            $tags[0x0122] = array(3, 1); // TIFFGrayResponseUnit -> SHORT, 1
            $tags[0x0123] = array(3, 0); // TIFFGrayResponseCurve -> SHORT, Any (2^BitsPerSample)
            $tags[0x0128] = array(3, 1); // TIFFResolutionUnit -> SHORT, 1
            $tags[0x0131] = array(2, 0); // TIFFSoftware -> ASCII, Any
            $tags[0x0132] = array(2, 20); // TIFFDateTime -> ASCII, 20
            $tags[0x013B] = array(2, 0); // TIFFArtist -> ASCII, Any
            $tags[0x013C] = array(2, 0); // TIFFHostComputer -> ASCII, Any
            $tags[0x0140] = array(3, 0); // TIFFColorMap -> SHORT, Any (3 * 2^BitsPerSample)
            $tags[0x0152] = array(3, 0); // TIFFExtraSamples -> SHORT, Any (SamplesPerPixel - 3)
            $tags[0x0201] = array(4, 1); // TIFFJFIFOffset -> LONG, 1
            $tags[0x0202] = array(4, 1); // TIFFJFIFLength -> LONG, 1
            $tags[0x0211] = array(5, 3); // TIFFYCbCrCoefficients -> RATIONAL, 3
            $tags[0x0212] = array(3, 2); // TIFFYCbCrSubSampling -> SHORT, 2
            $tags[0x0213] = array(3, 1); // TIFFYCbCrPositioning -> SHORT, 1
            $tags[0x0214] = array(5, 6); // TIFFReferenceBlackWhite -> RATIONAL, 6
            $tags[0x8298] = array(2, 0); // TIFFCopyright -> ASCII, Any
            $tags[0x9286] = array(2, 0); // TIFFUserComment -> ASCII, Any
        } elseif ($mode == 'exif') {
            $tags[0x829A] = array(5, 1); // ExposureTime -> RATIONAL, 1
            $tags[0x829D] = array(5, 1); // FNumber -> RATIONAL, 1
            $tags[0x8822] = array(3, 1); // ExposureProgram -> SHORT, 1
            $tags[0x8824] = array(2, 0); // SpectralSensitivity -> ASCII, Any
            $tags[0x8827] = array(3, 0); // ISOSpeedRatings -> SHORT, Any
            $tags[0x8828] = array(7, 0); // OECF -> UNDEFINED, Any
            $tags[0x9000] = array(7, 4); // EXIFVersion -> UNDEFINED, 4
            $tags[0x9003] = array(2, 20); // DatetimeOriginal -> ASCII, 20
            $tags[0x9004] = array(2, 20); // DatetimeDigitized -> ASCII, 20
            $tags[0x9101] = array(7, 4); // ComponentsConfiguration -> UNDEFINED, 4
            $tags[0x9102] = array(5, 1); // CompressedBitsPerPixel -> RATIONAL, 1
            $tags[0x9201] = array(10, 1); // ShutterSpeedValue -> SRATIONAL, 1
            $tags[0x9202] = array(5, 1); // ApertureValue -> RATIONAL, 1
            $tags[0x9203] = array(10, 1); // BrightnessValue -> SRATIONAL, 1
            $tags[0x9204] = array(10, 1); // ExposureBiasValue -> SRATIONAL, 1
            $tags[0x9205] = array(5, 1); // MaxApertureValue -> RATIONAL, 1
            $tags[0x9206] = array(5, 1); // SubjectDistance -> RATIONAL, 1
            $tags[0x9207] = array(3, 1); // MeteringMode -> SHORT, 1
            $tags[0x9208] = array(3, 1); // LightSource -> SHORT, 1
            $tags[0x9209] = array(3, 1); // Flash -> SHORT, 1
            $tags[0x920A] = array(5, 1); // FocalLength -> RATIONAL, 1
            $tags[0x927C] = array(7, 0); // MakerNote -> UNDEFINED, Any
            $tags[0x9286] = array(7, 0); // UserComment -> UNDEFINED, Any
            $tags[0x9290] = array(2, 0); // SubSecTime -> ASCII, Any
            $tags[0x9291] = array(2, 0); // SubSecTimeOriginal -> ASCII, Any
            $tags[0x9292] = array(2, 0); // SubSecTimeDigitized -> ASCII, Any
            $tags[0xA000] = array(7, 4); // FlashPixVersion -> UNDEFINED, 4
            $tags[0xA001] = array(3, 1); // ColorSpace -> SHORT, 1
            $tags[0xA002] = array(4, 1); // PixelXDimension -> LONG (or SHORT), 1
            $tags[0xA003] = array(4, 1); // PixelYDimension -> LONG (or SHORT), 1
            $tags[0xA004] = array(2, 13); // RelatedSoundFile -> ASCII, 13
            $tags[0xA005] = array(4, 1); // InteropIFDOffset -> LONG, 1
            $tags[0xA20B] = array(5, 1); // FlashEnergy -> RATIONAL, 1
            $tags[0xA20C] = array(7, 0); // SpatialFrequencyResponse -> UNDEFINED, Any
            $tags[0xA20E] = array(5, 1); // FocalPlaneXResolution -> RATIONAL, 1
            $tags[0xA20F] = array(5, 1); // FocalPlaneYResolution -> RATIONAL, 1
            $tags[0xA210] = array(3, 1); // FocalPlaneResolutionUnit -> SHORT, 1
            $tags[0xA214] = array(3, 2); // SubjectLocation -> SHORT, 2
            $tags[0xA215] = array(5, 1); // ExposureIndex -> RATIONAL, 1
            $tags[0xA217] = array(3, 1); // SensingMethod -> SHORT, 1
            $tags[0xA300] = array(7, 1); // FileSource -> UNDEFINED, 1
            $tags[0xA301] = array(7, 1); // SceneType -> UNDEFINED, 1
            $tags[0xA302] = array(7, 0); // CFAPattern -> UNDEFINED, Any
        } elseif ($mode == 'interop') {
            $tags[0x0001] = array(2, 0); // InteroperabilityIndex -> ASCII, Any
            $tags[0x0002] = array(7, 4); // InteroperabilityVersion -> UNKNOWN, 4
            $tags[0x1000] = array(2, 0); // RelatedImageFileFormat -> ASCII, Any
            $tags[0x1001] = array(4, 1); // RelatedImageWidth -> LONG (or SHORT), 1
            $tags[0x1002] = array(4, 1); // RelatedImageLength -> LONG (or SHORT), 1
        } elseif ($mode == 'gps') {
            $tags[0x0000] = array(1, 4); // GPSVersionID -> BYTE, 4
            $tags[0x0001] = array(2, 2); // GPSLatitudeRef -> ASCII, 2
            $tags[0x0002] = array(5, 3); // GPSLatitude -> RATIONAL, 3
            $tags[0x0003] = array(2, 2); // GPSLongitudeRef -> ASCII, 2
            $tags[0x0004] = array(5, 3); // GPSLongitude -> RATIONAL, 3
            $tags[0x0005] = array(2, 2); // GPSAltitudeRef -> ASCII, 2
            $tags[0x0006] = array(5, 1); // GPSAltitude -> RATIONAL, 1
            $tags[0x0007] = array(5, 3); // GPSTimeStamp -> RATIONAL, 3
            $tags[0x0008] = array(2, 0); // GPSSatellites -> ASCII, Any
            $tags[0x0009] = array(2, 2); // GPSStatus -> ASCII, 2
            $tags[0x000A] = array(2, 2); // GPSMeasureMode -> ASCII, 2
            $tags[0x000B] = array(5, 1); // GPSDOP -> RATIONAL, 1
            $tags[0x000C] = array(2, 2); // GPSSpeedRef -> ASCII, 2
            $tags[0x000D] = array(5, 1); // GPSSpeed -> RATIONAL, 1
            $tags[0x000E] = array(2, 2); // GPSTrackRef -> ASCII, 2
            $tags[0x000F] = array(5, 1); // GPSTrack -> RATIONAL, 1
            $tags[0x0010] = array(2, 2); // GPSImgDirectionRef -> ASCII, 2
            $tags[0x0011] = array(5, 1); // GPSImgDirection -> RATIONAL, 1
            $tags[0x0012] = array(2, 0); // GPSMapDatum -> ASCII, Any
            $tags[0x0013] = array(2, 2); // GPSDestLatitudeRef -> ASCII, 2
            $tags[0x0014] = array(5, 3); // GPSDestLatitude -> RATIONAL, 3
            $tags[0x0015] = array(2, 2); // GPSDestLongitudeRef -> ASCII, 2
            $tags[0x0016] = array(5, 3); // GPSDestLongitude -> RATIONAL, 3
            $tags[0x0017] = array(2, 2); // GPSDestBearingRef -> ASCII, 2
            $tags[0x0018] = array(5, 1); // GPSDestBearing -> RATIONAL, 1
            $tags[0x0019] = array(2, 2); // GPSDestDistanceRef -> ASCII, 2
            $tags[0x001A] = array(5, 1); // GPSDestDistance -> RATIONAL, 1
        }

        return $tags;
    }

    /*************************************************************/
    function _exifNameTags($mode) {
        $tags = $this->_exifTagNames($mode);
        return $this->_names2Tags($tags);
    }

    /*************************************************************/
    function _iptcTagNames() {
        $tags = array();
        $tags[0x14] = 'SuplementalCategories';
        $tags[0x19] = 'Keywords';
        $tags[0x78] = 'Caption';
        $tags[0x7A] = 'CaptionWriter';
        $tags[0x69] = 'Headline';
        $tags[0x28] = 'SpecialInstructions';
        $tags[0x0F] = 'Category';
        $tags[0x50] = 'Byline';
        $tags[0x55] = 'BylineTitle';
        $tags[0x6E] = 'Credit';
        $tags[0x73] = 'Source';
        $tags[0x74] = 'CopyrightNotice';
        $tags[0x05] = 'ObjectName';
        $tags[0x5A] = 'City';
        $tags[0x5C] = 'Sublocation';
        $tags[0x5F] = 'ProvinceState';
        $tags[0x65] = 'CountryName';
        $tags[0x67] = 'OriginalTransmissionReference';
        $tags[0x37] = 'DateCreated';
        $tags[0x0A] = 'CopyrightFlag';

        return $tags;
    }

    /*************************************************************/
    function & _iptcNameTags() {
        $tags = $this->_iptcTagNames();
        return $this->_names2Tags($tags);
    }

    /*************************************************************/
    function _names2Tags($tags2Names) {
        $names2Tags = array();

        foreach($tags2Names as $tag => $name) {
            $names2Tags[$name] = $tag;
        }

        return $names2Tags;
    }

    /*************************************************************/

    /**
     * @param $data
     * @param integer $pos
     *
     * @return int
     */
    function _getByte(&$data, $pos) {
        return ord($data{$pos});
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     *
     * @param mixed $val
     *
     * @return int
     */
    function _putByte(&$data, $pos, $val) {
        $val = intval($val);

        $data{$pos} = chr($val);

        return $pos + 1;
    }

    /*************************************************************/
    function _getShort(&$data, $pos, $bigEndian = true) {
        if ($bigEndian) {
            return (ord($data{$pos}) << 8)
                + ord($data{$pos + 1});
        } else {
            return ord($data{$pos})
                + (ord($data{$pos + 1}) << 8);
        }
    }

    /*************************************************************/
    function _putShort(&$data, $pos = 0, $val = 0, $bigEndian = true) {
        $val = intval($val);

        if ($bigEndian) {
            $data{$pos + 0} = chr(($val & 0x0000FF00) >> 8);
            $data{$pos + 1} = chr(($val & 0x000000FF) >> 0);
        } else {
            $data{$pos + 0} = chr(($val & 0x00FF) >> 0);
            $data{$pos + 1} = chr(($val & 0xFF00) >> 8);
        }

        return $pos + 2;
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     *
     * @param bool $bigEndian
     *
     * @return int
     */
    function _getLong(&$data, $pos, $bigEndian = true) {
        if ($bigEndian) {
            return (ord($data{$pos}) << 24)
                + (ord($data{$pos + 1}) << 16)
                + (ord($data{$pos + 2}) << 8)
                + ord($data{$pos + 3});
        } else {
            return ord($data{$pos})
                + (ord($data{$pos + 1}) << 8)
                + (ord($data{$pos + 2}) << 16)
                + (ord($data{$pos + 3}) << 24);
        }
    }

    /*************************************************************/

    /**
     * @param mixed $data
     * @param integer $pos
     *
     * @param mixed $val
     * @param bool $bigEndian
     *
     * @return int
     */
    function _putLong(&$data, $pos, $val, $bigEndian = true) {
        $val = intval($val);

        if ($bigEndian) {
            $data{$pos + 0} = chr(($val & 0xFF000000) >> 24);
            $data{$pos + 1} = chr(($val & 0x00FF0000) >> 16);
            $data{$pos + 2} = chr(($val & 0x0000FF00) >> 8);
            $data{$pos + 3} = chr(($val & 0x000000FF) >> 0);
        } else {
            $data{$pos + 0} = chr(($val & 0x000000FF) >> 0);
            $data{$pos + 1} = chr(($val & 0x0000FF00) >> 8);
            $data{$pos + 2} = chr(($val & 0x00FF0000) >> 16);
            $data{$pos + 3} = chr(($val & 0xFF000000) >> 24);
        }

        return $pos + 4;
    }

    /*************************************************************/
    function & _getNullString(&$data, $pos) {
        $str = '';
        $max = strlen($data);

        while ($pos < $max) {
            if (ord($data{$pos}) == 0) {
                return $str;
            } else {
                $str .= $data{$pos};
            }
            $pos++;
        }

        return $str;
    }

    /*************************************************************/
    function & _getFixedString(&$data, $pos, $length = -1) {
        if ($length == -1) {
            $length = strlen($data) - $pos;
        }

        $rv = substr($data, $pos, $length);
        return $rv;
    }

    /*************************************************************/
    function _putString(&$data, $pos, &$str) {
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $data{$pos + $i} = $str{$i};
        }

        return $pos + $len;
    }

    /*************************************************************/
    function _hexDump(&$data, $start = 0, $length = -1) {
        if (($length == -1) || (($length + $start) > strlen($data))) {
            $end = strlen($data);
        } else {
            $end = $start + $length;
        }

        $ascii = '';
        $count = 0;

        echo "<tt>\n";

        while ($start < $end) {
            if (($count % 16) == 0) {
                echo sprintf('%04d', $count) . ': ';
            }

            $c = ord($data{$start});
            $count++;
            $start++;

            $aux = dechex($c);
            if (strlen($aux) == 1)
                echo '0';
            echo $aux . ' ';

            if ($c == 60)
                $ascii .= '&lt;';
            elseif ($c == 62)
                $ascii .= '&gt;';
            elseif ($c == 32)
                $ascii .= '&#160;';
            elseif ($c > 32)
                $ascii .= chr($c);
            else
                $ascii .= '.';

            if (($count % 4) == 0) {
                echo ' - ';
            }

            if (($count % 16) == 0) {
                echo ': ' . $ascii . "<br>\n";
                $ascii = '';
            }
        }

        if ($ascii != '') {
            while (($count % 16) != 0) {
                echo '-- ';
                $count++;
                if (($count % 4) == 0) {
                    echo ' - ';
                }
            }
            echo ': ' . $ascii . "<br>\n";
        }

        echo "</tt>\n";
    }

    /*****************************************************************/
}

/* vim: set expandtab tabstop=4 shiftwidth=4: */
