<?php


namespace splitbrain\slika;

/**
 * Image Processing Adapter for ImageMagick's command line utility `convert`
 */
class ImageMagickAdapter extends Adapter
{
    /** @var array the CLI arguments to run imagemagick */
    protected $args = [];

    /** @inheritDoc */
    public function __construct($imagepath, $options = [])
    {
        parent::__construct($imagepath, $options);

        if (!is_executable($this->options['imconvert'])) {
            throw new Exception('Can not find or run ' . $this->options['imconvert']);
        }

        $this->args[] = $this->options['imconvert'];
        $this->args[] = $imagepath;
    }

    /** @inheritDoc */
    public function autorotate()
    {
        $this->args[] = '-auto-orient';
        return $this;
    }

    /** @inheritDoc */
    public function rotate($orientation)
    {
        $orientation = (int)$orientation;
        if ($orientation < 0 || $orientation > 8) {
            throw new Exception('Unknown rotation given');
        }

        // rotate
        $this->args[] = '-rotate';
        if (in_array($orientation, [3, 4])) {
            $this->args[] = '180';
        } elseif (in_array($orientation, [5, 6])) {
            $this->args[] = '90';
        } elseif (in_array($orientation, [7, 8])) {
            $this->args[] = '270';
        }

        // additionally flip
        if (in_array($orientation, [2, 5, 7, 4])) {
            $this->args[] = '-flop';
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function resize($width, $height)
    {
        if ($width == 0 && $height == 0) {
            throw new Exception('You can not resize to 0x0');
        }
        if ($width == 0) $width = '';
        if ($height == 0) $height = '';

        $size = $width . 'x' . $height;

        $this->args[] = '-resize';
        $this->args[] = $size;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function crop($width, $height)
    {
        if ($width == 0 && $height == 0) {
            throw new Exception('You can not crop to 0x0');
        }

        if ($width == 0) $width = $height;
        if ($height == 0) $height = $width;

        $size = $width . 'x' . $height;

        $this->args[] = '-resize';
        $this->args[] = "$size^";
        $this->args[] = '-gravity';
        $this->args[] = 'center';
        $this->args[] = '-crop';
        $this->args[] = "$size+0+0";
        $this->args[] = '+repage';
        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function save($path, $extension = '')
    {
        if ($extension === 'jpg') {
            $extension = 'jpeg';
        }

        $this->args[] = '-quality';
        $this->args[] = $this->options['quality'];

        if ($extension !== '') $path = $extension . ':' . $path;
        $this->args[] = $path;

        $args = array_map('escapeshellarg', $this->args);

        $cmd = join(' ', $args);
        $output = [];
        $return = 0;
        exec($cmd, $output, $return);

        if ($return !== 0) {
            throw new Exception('ImageMagick returned non-zero exit code for ' . $cmd);
        }
    }
}
