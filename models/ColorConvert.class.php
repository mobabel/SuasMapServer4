<?php
/**
 * Class for converting colortypes
 *
 * The class includes the following colors formats and types:
 *
 *   - RGB
 *   - HEX Codes for HTML
 */
class ColorConvert {
    /**
     *
     * @var array $rgb
     * @access private
     * @desc array for RGB colors
     */
    private $rgb = array("red" => 0, "green" => 0, "blue" => 0);

    /**
     *
     * @var array $hexbase
     * @access private
     * @desc array for HEX code chars
     */
    private $hexbase = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");

    /**
     *
     * @var array $hex_trip
     * @access private
     * @desc array for HEX codes
     */
    private $hex_trip = array();

    /**
     *
     * @var string $hex
     * @access private
     * @desc variable for HTML HEX color
     */
    private $hex = "";

    /**
     *
     * @var string $array_R,$array_G,$array_B
     * @access public
     * @desc Output RGB value from HTML HEX color to RGB
     */
    public $array_R ;
    public $array_G ;
    public $array_B ;

    /**
     * Constructor of class color
     *
     * @access public
     * @desc Constructor of class color
     */
    function ColorConvert()
    {
        $value = 0;
        for ($x = 0; $x < 16; $x++) {
            for ($y = 0; $y < 16; $y++) {
                $this->hex_trip[$value] = $this->hexbase[$x] . $this->hexbase[$y];
                $value++;
            }
        }
    }

    /**
     * Sets the RGB values
     *
     * @param int $red number from 0-255 for blue color value
     * @param int $green number from 0-255 for green color value
     * @param int $blue number from 0-255 for blue color value
     * @access public
     * @desc Sets the RGB values
     */
    function set_rgb($red, $green, $blue)
    {
        $this->rgb['red'] = $red;
        $this->rgb['green'] = $green;
        $this->rgb['blue'] = $blue;

        $this->convert_rgb_to_cmyk();
        $this->convert_rgb_to_hex();
    }

    /**
     * Sets the HEX HTML color value
     *
     * @param int $red number from 0-255 for blue color value
     * @access public
     * @desc Sets the HEX HTML color value like ffff00
     */
    function set_hex($hex)
    {
        $hex = strtolower($hex);
        $this->hex = $hex;

        $this->convert_hex_to_rgb();
        // $this->convert_rgb_to_cmyk();
    }

    /**
     * Returns the RGB values of a set color
     *
     * @return array $rgb color values of red ($rgb['red']), green ($rgb['green') and blue ($rgb['blue'])
     * @access public
     * @desc Returns the RGB values of a set color
     */
    function get_rgb()
    {
        return $this->rgb;
    }

    /**
     * Returns the HEX HTML color value of a set color
     *
     * @return string $hex HEX HTML color value
     * @access public
     * @desc Returns the HEX HTML color value of a set color
     */
    function get_hex()
    {
        return $this->hex;
    }

    /**
     * Converts the RGB colors to HEX HTML colors
     *
     * @access private
     * @desc Converts the RGB colors to HEX HTML colors
     */
    function convert_rgb_to_hex()
    {
        $this->hex = $this->hex_trip[$this->rgb['red']] . $this->hex_trip[$this->rgb['green']] . $this->hex_trip[$this->rgb['blue']];
    }

    /**
     * Converts the HTML HEX colors to RGB colors
     *
     * @access private
     * @desc Converts the HTML HEX colors to RGB colors
     */
    function convert_hex_to_rgb()
    {
        $red = substr($this->hex, 0, 2);
        $green = substr($this->hex, 2, 2);
        $blue = substr($this->hex, 4, 2);

        $found = false;
        for($i = 0;$i < count($this->hex_trip) && !$found;$i++) {
            if ($this->hex_trip[$i] == $red) {
                $this->rgb['red'] = $i;
                $found = true;
            }
        }

        $found = false;
        for($i = 0;$i < count($this->hex_trip) && !$found;$i++) {
            if ($this->hex_trip[$i] == $green) {
                $this->rgb['green'] = $i;
                $found = true;
            }
        }

        $found = false;
        for($i = 0;$i < count($this->hex_trip) && !$found;$i++) {
            if ($this->hex_trip[$i] == $blue) {
                $this->rgb['blue'] = $i;
                $found = true;
            }
        }
    }

    /**
     * Convert a hex colour string into an rgb array.
     *
     * Handles colour string in the following formats:
     *
     *      o #44FF55
     *      o 4FF55
     *      o #4F5
     *      o 4F5
     *
     * @return array
     * @param string $hex
     * @access public
     */
    function hex2rgb($hex)
    {
        // Regular Expression
        // $hex = @preg _replace('/^#/', '', $hex);
        if (strlen($hex) == 3) {
            $v = explode(':', chunk_split($hex, 1, ':'));
            // return array(16 * hexdec($v[0]) + hexdec($v[0]), 16 * hexdec($v[1]) + hexdec($v[1]), 16 * hexdec($v[2]) + hexdec($v[2]));
            $this->array_R = 16 * hexdec($v[0]) + hexdec($v[0]);
            $this->array_G = 16 * hexdec($v[1]) + hexdec($v[1]);
            $this->array_B = 16 * hexdec($v[2]) + hexdec($v[2]);
        } else {
            $v = explode(':', chunk_split($hex, 2, ':'));
            // return array(hexdec($v[0]), hexdec($v[1]), hexdec($v[2]));
            $this->array_R = hexdec($v[0]);
            $this->array_G = hexdec($v[1]);
            $this->array_B = hexdec($v[2]);
        }
    }
}

?>