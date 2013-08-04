<?php

class ResizableImageFile extends MediaFile {
    /** @var int cache setting for this file */
    protected $cache = -1;

    /** @var int requested width */
    protected $width = 0;
    /** @var int requested height */
    protected $height = 0;

    /**
     * Initialize the file
     *
     * @param string $id
     * @param string $cache Cache behavior for external files (cache|nocache|recache)
     */
    public function __construct($id, $cache='cache', $w=0, $h=0) {
        global $conf;

        parent::__construct($id);

        if(strtolower($cache) == 'nocache') $this->cache = 0; //never cache
        if(strtolower($cache) == 'recache') $this->cache = $conf['cachetime']; //use standard cache
        $this->cache = -1; //cache endless

        $this->width = $w;
        $this->height = $h;
    }

    /**
     * Check if the current file is of the right type to resize
     *
     * @return bool
     */
    public function is_resizable() {
        if($this->getMimeType() == 'image/png') return true;
        if($this->getMimeType() == 'image/jpeg') return true;
        if($this->getMimeType() == 'image/gif') return true;
        return false;
    }

    /**
     * Download external image files
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function download(){
        global $conf;

        if(!$this->is_resizable()) return false;

        $http = new DokuHTTPClient();
        $http->keep_alive = false; // we do single ops here, no need for keep-alive

        $http->max_bodysize = $conf['fetchsize'];
        $http->timeout = 25; //max. 25 sec
        $http->header_regexp = '!\r\nContent-Type: image/(jpe?g|gif|png)!i';

        $data = $http->get($this->id);
        if(!$data) return false;

        $fileexists = $this->exists();
        $fp = @fopen($this->file,"w");
        if(!$fp) return false;
        fwrite($fp,$data);
        fclose($fp);
        if(!$fileexists and $conf['fperm']) chmod($this->file, $conf['fperm']);

        // check if it is really an image
        $info = @getimagesize($this->file);
        if(!$info){
            @unlink($this->file);
            return false;
        }

        return true;
    }

    /**
     * Return the locally stored file in original size
     *
     * May return false for external images that couldn't be cached
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @author  Pavel Vitis <Pavel.Vitis@seznam.cz>
     */
    public function getOriginalFile(){
        global $conf;
        // internal files are fine
        if(!$this->isExternal()) return $this->file;
        // if no cache or fetchsize no download
        if ($this->cache==0)           return false;
        if (!$conf['fetchsize']) return false;


        $mtime = @filemtime($this->file); // 0 if not exists

        //decide if download needed:
        if( ($mtime == 0) ||                           // cache does not exist
            ($this->cache != -1 && $mtime < time()-$this->cache)   // 'recache' and cache has expired
        ){
            if($this->download()){
                return $this->file;
            }else{
                return false;
            }
        }

        //if cache exists use it else
        if($mtime) return $this->file;

        //else return false
        return false;
    }

    /**
     * Return the local location of the media file
     *
     * This will return the unresized image when resizing failed or false if
     * the image to resize is not available locally
     *
     * @return bool|string path to file or false if it doesn't exist
     */
    public function getFile(){
        $local = $this->getOriginalFile();
        if(!$local) return false;
        if(!$this->width) return $local;

        if($this->height){
            return $this->crop_image($local, $this->getExtension(), $this->width, $this->height);
        }else{
            return $this->resize_image($local, $this->getExtension(), $this->width);
        }
    }


    /**
     * Resizes the given image to the given size
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    public static function resize_image($file, $ext, $w, $h=0){
        global $conf;

        $info = @getimagesize($file); //get original size
        if($info == false) return $file; // that's no image - it's a spaceship!

        if(!$h) $h = round(($w * $info[1]) / $info[0]);

        // we wont scale up to infinity
        if($w > 2000 || $h > 2000) return $file;

        // resize necessary? - (w,h) = native dimensions
        if(($w == $info[0]) && ($h == $info[1])) return $file;

        //cache
        $local = getCacheName($file,'.media.'.$w.'x'.$h.'.'.$ext);
        $mtime = @filemtime($local); // 0 if not exists

        if( $mtime > filemtime($file) ||
            ResizableImageFile::resize_imageIM($ext,$file,$info[0],$info[1],$local,$w,$h) ||
            ResizableImageFile::resize_imageGD($ext,$file,$info[0],$info[1],$local,$w,$h) ){
            if($conf['fperm']) chmod($local, $conf['fperm']);
            return $local;
        }
        //still here? resizing failed
        return $file;
    }

    /**
     * Crops the given image to the wanted ratio, then calls media_resize_image to scale it
     * to the wanted size
     *
     * Crops are centered horizontally but prefer the upper third of an vertical
     * image because most pics are more interesting in that area (rule of thirds)
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    public static function crop_image($file, $ext, $w, $h=0){
        global $conf;

        if(!$h) $h = $w;
        $info = @getimagesize($file); //get original size
        if($info == false) return $file; // that's no image - it's a spaceship!

        // calculate crop size
        $fr = $info[0]/$info[1];
        $tr = $w/$h;

        // check if the crop can be handled completely by resize,
        // i.e. the specified width & height match the aspect ratio of the source image
        if ($w == round($h*$fr)) {
            return ResizableImageFile::resize_image($file, $ext, $w);
        }

        if($tr >= 1){
            if($tr > $fr){
                $cw = $info[0];
                $ch = (int) ($info[0]/$tr);
            }else{
                $cw = (int) ($info[1]*$tr);
                $ch = $info[1];
            }
        }else{
            if($tr < $fr){
                $cw = (int) ($info[1]*$tr);
                $ch = $info[1];
            }else{
                $cw = $info[0];
                $ch = (int) ($info[0]/$tr);
            }
        }
        // calculate crop offset
        $cx = (int) (($info[0]-$cw)/2);
        $cy = (int) (($info[1]-$ch)/3);

        //cache
        $local = getCacheName($file,'.media.'.$cw.'x'.$ch.'.crop.'.$ext);
        $mtime = @filemtime($local); // 0 if not exists

        if( $mtime > @filemtime($file) ||
                ResizableImageFile::crop_imageIM($ext,$file,$info[0],$info[1],$local,$cw,$ch,$cx,$cy) ||
                ResizableImageFile::resize_imageGD($ext,$file,$cw,$ch,$local,$cw,$ch,$cx,$cy) ){
            if($conf['fperm']) chmod($local, $conf['fperm']);
            return ResizableImageFile::resize_image($local,$ext, $w, $h);
        }

        //still here? cropping failed
        return ResizableImageFile::resize_image($file,$ext, $w, $h);
    }

    /**
     * resize images using external ImageMagick convert program
     *
     * @author Pavel Vitis <Pavel.Vitis@seznam.cz>
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected static function resize_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
        global $conf;

        // check if convert is configured
        if(!$conf['im_convert']) return false;

        // prepare command
        $cmd  = $conf['im_convert'];
        $cmd .= ' -resize '.$to_w.'x'.$to_h.'!';
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $cmd .= ' -quality '.$conf['jpg_quality'];
        }
        $cmd .= " $from $to";

        @exec($cmd,$out,$retval);
        if ($retval == 0) return true;
        return false;
    }

    /**
     * crop images using external ImageMagick convert program
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected static function crop_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h,$ofs_x,$ofs_y){
        global $conf;

        // check if convert is configured
        if(!$conf['im_convert']) return false;

        // prepare command
        $cmd  = $conf['im_convert'];
        $cmd .= ' -crop '.$to_w.'x'.$to_h.'+'.$ofs_x.'+'.$ofs_y;
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $cmd .= ' -quality '.$conf['jpg_quality'];
        }
        $cmd .= " $from $to";

        @exec($cmd,$out,$retval);
        if ($retval == 0) return true;
        return false;
    }

    /**
     * resize or crop images using PHP's libGD support
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     * @author Sebastian Wienecke <s_wienecke@web.de>
     */
    protected static function resize_imageGD($ext,$from,$from_w,$from_h,$to,$to_w,$to_h,$ofs_x=0,$ofs_y=0){
        global $conf;

        if($conf['gdlib'] < 1) return false; //no GDlib available or wanted

        // check available memory
        if(!is_mem_available(($from_w * $from_h * 4) + ($to_w * $to_h * 4))){
            return false;
        }

        // create an image of the given filetype
        if ($ext == 'jpg' || $ext == 'jpeg'){
            if(!function_exists("imagecreatefromjpeg")) return false;
            $image = @imagecreatefromjpeg($from);
        }elseif($ext == 'png') {
            if(!function_exists("imagecreatefrompng")) return false;
            $image = @imagecreatefrompng($from);

        }elseif($ext == 'gif') {
            if(!function_exists("imagecreatefromgif")) return false;
            $image = @imagecreatefromgif($from);
        }
        if(!$image) return false;

        if(($conf['gdlib']>1) && function_exists("imagecreatetruecolor") && $ext != 'gif'){
            $newimg = @imagecreatetruecolor ($to_w, $to_h);
        }
        if(!$newimg) $newimg = @imagecreate($to_w, $to_h);
        if(!$newimg){
            imagedestroy($image);
            return false;
        }

        //keep png alpha channel if possible
        if($ext == 'png' && $conf['gdlib']>1 && function_exists('imagesavealpha')){
            imagealphablending($newimg, false);
            imagesavealpha($newimg,true);
        }

        //keep gif transparent color if possible
        if($ext == 'gif' && function_exists('imagefill') && function_exists('imagecolorallocate')) {
            if(function_exists('imagecolorsforindex') && function_exists('imagecolortransparent')) {
                $transcolorindex = @imagecolortransparent($image);
                if($transcolorindex >= 0 ) { //transparent color exists
                    $transcolor = @imagecolorsforindex($image, $transcolorindex);
                    $transcolorindex = @imagecolorallocate($newimg, $transcolor['red'], $transcolor['green'], $transcolor['blue']);
                    @imagefill($newimg, 0, 0, $transcolorindex);
                    @imagecolortransparent($newimg, $transcolorindex);
                }else{ //filling with white
                    $whitecolorindex = @imagecolorallocate($newimg, 255, 255, 255);
                    @imagefill($newimg, 0, 0, $whitecolorindex);
                }
            }else{ //filling with white
                $whitecolorindex = @imagecolorallocate($newimg, 255, 255, 255);
                @imagefill($newimg, 0, 0, $whitecolorindex);
            }
        }

        //try resampling first
        if(function_exists("imagecopyresampled")){
            if(!@imagecopyresampled($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h)) {
                imagecopyresized($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h);
            }
        }else{
            imagecopyresized($newimg, $image, 0, 0, $ofs_x, $ofs_y, $to_w, $to_h, $from_w, $from_h);
        }

        $okay = false;
        if ($ext == 'jpg' || $ext == 'jpeg'){
            if(!function_exists('imagejpeg')){
                $okay = false;
            }else{
                $okay = imagejpeg($newimg, $to, $conf['jpg_quality']);
            }
        }elseif($ext == 'png') {
            if(!function_exists('imagepng')){
                $okay = false;
            }else{
                $okay =  imagepng($newimg, $to);
            }
        }elseif($ext == 'gif') {
            if(!function_exists('imagegif')){
                $okay = false;
            }else{
                $okay = imagegif($newimg, $to);
            }
        }

        // destroy GD image ressources
        if($image) imagedestroy($image);
        if($newimg) imagedestroy($newimg);

        return $okay;
    }

}