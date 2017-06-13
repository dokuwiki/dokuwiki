<?php
namespace dokuwiki\plugin\struct\types;

use dokuwiki\plugin\struct\meta\ValidationException;

class Color extends AbstractBaseType {

    protected $config = array(
        'default' => '#ffffff'
    );

    /**
     * @inheritDoc
     */
    public function validate($rawvalue) {
        $rawvalue = trim(strtolower($rawvalue));
        if(!preg_match('/^#[a-f0-9]{6}$/', $rawvalue)) {
            throw new ValidationException('bad color specification');
        }

        // ignore if default
        if($rawvalue == strtolower($this->config['default'])) {
            $rawvalue = '';
        }

        return $rawvalue;
    }

    /**
     * @inheritDoc
     */
    public function renderValue($value, \Doku_Renderer $R, $mode) {
        if($mode == 'xhtml') {
            $R->doc .= '<div title="' . hsc($value) . '" style="background-color:' . hsc($value) . ';" class="struct_color"></div>';
        } else {
            $R->cdata($value);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function renderMultiValue($values, \Doku_Renderer $R, $mode) {
        if($mode == 'xhtml') {
            foreach($values as $value) {
                $this->renderValue($value, $R, $mode);
            }
        } else {
            $R->cdata(join(', ', $values));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function valueEditor($name, $rawvalue, $htmlID) {
        if(!preg_match('/^#[a-f0-9]{6}$/', $rawvalue)) {
            // any non-color (eg. from a previous type) should default to the default
            $rawvalue = $this->config['default'];
        }

        $params = array(
            'name' => $name,
            'value' => $rawvalue,
            'class' => 'struct_color',
            'type' => 'color',
            'id' => $htmlID
        );
        $attributes = buildAttributes($params, true);
        return "<input $attributes />";
    }

    /**
     * @inheritDoc
     */
    public function renderTagCloudLink($value, \Doku_Renderer $R, $mode, $page, $filter, $weight) {
        $color = $this->displayValue($value);
        if ($mode == 'xhtml') {
            $url = wl($page, $filter);
            $style = "background-color:$color;";
            $R->doc .= "<a class='struct_color_tagcloud' href='$url' style='$style'><span class='a11y'>$color</span></a>";
            return;
        }
        $R->internallink("$page?$filter", $color);
    }


    /**
     * Sort by the hue of a color, not by its hex-representation
     */
    public function getSortString($value) {
        $hue = $this->getHue(parent::getSortString($value));
        return $hue;
    }

    /**
     * Calculate the hue of a color to use it for sorting so we can sort similar colors together.
     *
     * @param string $color the color as #RRGGBB
     * @return float|int
     */
    protected function getHue($color) {
        if (!preg_match('/^#[0-9A-F]{6}$/i', $color)) {
            return 0;
        }

        $red   = hexdec(substr($color, 1, 2));
        $green = hexdec(substr($color, 3, 2));
        $blue  = hexdec(substr($color, 5, 2));

        $min = min([$red, $green, $blue]);
        $max = max([$red, $green, $blue]);

        if ($max == $red) {
            $hue = ($green-$blue)/($max-$min);
        }
        if ($max == $green) {
            $hue = 2 + ($blue-$red)/($max-$min);
        }
        if ($max == $blue) {
            $hue = 4 + ($red-$green)/($max-$min);
        }
        $hue = $hue * 60;
        if ($hue < 0) {
            $hue += 360;
        }
        return $hue;
    }
}
