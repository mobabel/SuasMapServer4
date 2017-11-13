<?php

/**
 * Colour Hex or ColorName to RGB value Class
 *
 * @version $3.0$ 2006
 * @Author leelight
 * @Contact leeglanz@hotmail.com
 */

//include("ColorConvert.class.php");

class RasterColor {
    /**
     *
     * @var string $array_R,$array_G,$array_B
     * @access public
     * @desc Output RGB value from HTML HEX color to RGB
     */
    public $array_R ;
    public $array_G ;
    public $array_B ;

    public $Geom_color;
    public $setRGB_R ;
    public $setRGB_G ;
    public $setRGB_B ;

    public function RasterColor($color)
    {
        if($color==""){
            $color="#000000";
		}
		if($color==-1){
            $color="#ffffff";
		}
        $color = strtolower($color);
        $color = str_replace('#', '', $color);

        $this->hex2rgb($color);
        $this->setRGB_R = $this->array_R;
        $this->setRGB_G = $this->array_G;
        $this->setRGB_B = $this->array_B;
        // echo $this->setRGB_R."|".$this->setRGB_G."|".$this->setRGB_B."\n";
    }

    /**
    *turn RGB hex value and alpha value to AABBGGRR format
    * $alpha is in percent format (0-100%)
    */
    public function ABGRColor($color,$alpha){
        if($color==""){
            $color="#000000";
		}
		if($color==-1){
            $color="#ffffff";
		}
        $color = strtolower($color);
        $color = str_replace('#', '', $color);

        $aa = dechex($alpha*255);
        $this->hex2rgb($color);
        $rr = dechex($this->array_R)=="0"?"00":dechex($this->array_R);
        $gg = dechex($this->array_G)=="0"?"00":dechex($this->array_G);
        $bb = dechex($this->array_B)=="0"?"00":dechex($this->array_B);

        //return $aa.$bb.$gg.$rr;
        return $aa.$rr.$gg.$bb;

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
// $color='RED';
// $color='#000000';
// $rastercolor=new RasterColor($color);
?>