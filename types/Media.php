<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\ValidationException;

class Media extends AbstractBaseType {

    protected $config = array(
        'mime' => 'image/',
        'width' => 90,
        'height' => 90,
        'agg_width' => '',
        'agg_height' => ''
    );

    /**
     * Checks against the allowed mime types
     *
     * @param string $rawvalue
     * @return int|string
     */
    public function validate($rawvalue) {
        $rawvalue = parent::validate($rawvalue);

        if(!trim($this->config['mime'])) return $rawvalue;
        $allows = explode(',', $this->config['mime']);
        $allows = array_map('trim', $allows);
        $allows = array_filter($allows);

        list(, $mime,) = mimetype($rawvalue, false);
        foreach($allows as $allow) {
            if(strpos($mime, $allow) === 0) return $rawvalue;
        }

        throw new ValidationException('Media mime type', $mime, $this->config['mime']);
    }

    /**
     * Output the stored data
     *
     * If outputted in an aggregation we collect the images into a gallery.
     *
     * @param string|int $value the value stored in the database
     * @param \Doku_Renderer $R the renderer currently used to render the data
     * @param string $mode The mode the output is rendered in (eg. XHTML)
     * @return bool true if $mode could be satisfied
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        // get width and height from config
        $width = null;
        $height = null;
        if($this->config['width']) $width = $this->config['width'];
        if($this->config['height']) $height = $this->config['height'];
        if(!empty($R->info['struct_table_hash'])) {
            // this is an aggregation, check for special values
            if($this->config['agg_width']) $width = $this->config['agg_width'];
            if($this->config['agg_height']) $height = $this->config['agg_height'];
        }

        // depending on renderer type directly output or get value from it
        $returnLink = null;
        $html = '';
        if(!media_isexternal($value)) {
            if(is_a($R, '\Doku_Renderer_xhtml')) {
                /** @var \Doku_Renderer_xhtml $R */
                $html = $R->internalmedia($value, null, null, $width, $height, null, 'direct', true);
            } else {
                $R->internalmedia($value, null, null, $width, $height, null, 'direct');
            }
        } else {
            if(is_a($R, '\Doku_Renderer_xhtml')) {
                /** @var \Doku_Renderer_xhtml $R */
                $html = $R->externalmedia($value, null, null, $width, $height, null, 'direct', true);
            } else {
                $R->externalmedia($value, null, null, $width, $height, null, 'direct');
            }
        }

        // add gallery meta data in XHTML
        if($mode == 'xhtml') {
            list(, $mime,) = mimetype($value, false);
            if(substr($mime, 0, 6) == 'image/') {
                $hash = !empty($R->info['struct_table_hash']) ? "[gal-" . $R->info['struct_table_hash'] . "]" : '';
                $html = str_replace('href', "rel=\"lightbox$hash\" href", $html);
            }
            $R->doc .= $html;
        }

        return true;
    }

    /**
     * Return the editor to edit a single value
     *
     * @param string $name     the form name where this has to be stored
     * @param string $rawvalue the current value
     *
     * @param string $htmlID
     *
     * @return string html
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        static $count = 0;
        $count++;

        $id = $htmlID ?: 'struct__' . md5($name.$count);

        $params = array(
            'name' => $name,
            'value' => $rawvalue,
            'class' => 'struct_media',
            'id' => $id
        );
        $attributes = buildAttributes($params, true);
        $html = "<input $attributes />";
        $html .= "<button type=\"button\" class=\"struct_media\">";
        $html .= "<img src=\"" . DOKU_BASE . "lib/images/toolbar/image.png\" height=\"16\" width=\"16\">";
        $html .= "</button>";
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function renderTagCloudLink($value, \Doku_Renderer $R, $mode, $page, $filter, $weight) {
        $media = $this->displayValue($value);
        if ($mode == 'xhtml' && $this->getConfig()['mime'] == 'image/') {
            $url = wl($page, $filter);
            $image = ml($media, ['h' => $weight, 'w' => $weight]);
            $media_escaped = hsc($media);
            $R->doc .= "<div style=\"height:{$weight}px; width:{$weight}px\">";
            $R->doc .= "<a href='$url' class='struct_image' style='background-image:url(\"$image\")' title='$media_escaped'>";
            $R->doc .= "<span class='a11y'>$media_escaped</span>";
            $R->doc .= "</a>";
            $R->doc .= "</div>";
            return;
        }
        $R->internallink("$page?$filter", $media);
    }


}
