<?php
/**
 * Getmap Geometry XML Parser Class
 *
 * @version $2.0$ 2006
 * @Author leelight
 * @Contact leeglanz@hotmail.com
 * @version $3.0$ 2006
 * @Author leelight
 * @Contact leeglanz@hotmail.com
 */

class SVGParser {
    private $parser = null;
    private $current = '';
    private $content;
    private $value;
    private $recordset = array();
    private $field;

    public $attr_tag;

    public $attr_ID;
    public $attr_D;
    public $attr_CX;
    public $attr_CY;
    public $attr_R;
    public $attr_RX;
    public $attr_RY;
    public $attr_FILL;
    public $attr_STROKE;
    public $attr_STROKE_WIDTH;

    public $path_ID;
    public $path_D;
    public $path_FILL;
    public $path_STROKE;
    public $path_STROKE_WIDTH;

    public $circle_ID;
    public $circle_CX;
    public $circle_CY;
    public $circle_R;
    public $circle_FILL;
    public $circle_STROKE;
    public $circle_STROKE_WIDTH;

    public $ellipse_ID;
    public $ellipse_CX;
    public $ellipse_CY;
    public $ellipse_RX;
    public $ellipse_RY;
    public $ellipse_FILL;
    public $ellipse_STROKE;
    public $ellipse_STROKE_WIDTH;

    public $rectangle_ID;
    public $rectangle_X; //maybe x and y is null, means from 0,0
    public $rectangle_Y;
    public $rectangle_WIDTH;
    public $rectangle_HEIGHT;
    public $rectangle_FILL;
    public $rectangle_STROKE;
    public $rectangle_STROKE_WIDTH;

    public $polygon_ID;
    public $polygon_POINTS;
    public $polygon_FILL;
    public $polygon_STROKE;
    public $polygon_STROKE_WIDTH;

    public $polyline_ID;
    public $polyline_POINTS;
    public $polyline_FILL;
    public $polyline_STROKE;
    public $polyline_STROKE_WIDTH;

    public $style_FONT_SIZE;
    public $style_FILL;
    public $style_STROKE;
    public $style_STROKE_WIDTH;

    public $text_tag;
    public $text_ID;
    public $text_X;
    public $text_Y;
    public $text_CONTENT;
    public $text_FILL;
    public $text_STROKE;
    public $text_FONT_SIZE;

    public function SVGParser($xml)
    {
        $xml = '<GEO>' . $xml . '</GEO>';
        $this->parser = xml_parser_create();
        xml_set_element_handler($this->parser, array($this, 'tag_open'), array($this, 'tag_close'));
        xml_set_character_data_handler($this->parser, array($this, 'cdata'));
        // xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
        // xml_set_element_handler($this->parser, "tag_open", "tag_close");
        // xml_set_character_data_handler($this->parser, "cdata");
        xml_parse($this->parser, $xml);
        // if (!xml_parse($this->parser, $xml))
        // die(sprintf('XML error: %s at line %d',
        // xml_error_string(xml_get_error_code($this->parser)),
        // xml_get_current_line_number($this->parser)));
    }

    function parse($data)
    {
        xml_parse($this->parser, $data) or
        die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->parser)),
                xml_get_current_line_number($this->parser)));
    }

    function tag_open($parser, $tag, $attributes)
    {
        $parser;
        // echo $tag;
        switch ($tag) {
            case 'GEO': break;
            case 'PATH': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v."\n";
                            switch ($k) {
                                case 'ID': {
                                        $this->path_ID = $v;
                                    }
                                    break;
                                case 'D': {
                                        $this->path_D = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->path_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->path_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->path_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'CIRCLE': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'ID': {
                                        $this->circle_ID = $v;
                                    }
                                    break;
                                case 'CX': {
                                        $this->circle_CX = $v;
                                    }
                                    break;
                                case 'CY': {
                                        $this->circle_CY = $v;
                                    }
                                    break;
                                case 'R': {
                                        $this->circle_R = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->circle_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->circle_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->circle_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'ELLIPSE': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'ID': {
                                        $this->ellipse_ID = $v;
                                    }
                                    break;
                                case 'CX': {
                                        $this->ellipse_CX = $v;
                                    }
                                    break;
                                case 'CY': {
                                        $this->ellipse_CY = $v;
                                    }
                                    break;
                                case 'RX': {
                                        $this->ellipse_RX = $v;
                                    }
                                    break;
                                case 'RY': {
                                        $this->ellipse_RY = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->ellipse_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->ellipse_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->ellipse_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'RECT': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'ID': {
                                        $this->rectangle_ID = $v;
                                    }
                                    break;
                                case 'X': {
                                        $this->rectangle_X = $v;
                                    }
                                    break;
                                case 'Y': {
                                        $this->rectangle_Y = $v;
                                    }
                                    break;
                                case 'WIDTH': {
                                        $this->rectangle_WIDTH = $v;
                                    }
                                    break;
                                case 'HEIGHT': {
                                        $this->rectangle_HEIGHT = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->rectangle_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->rectangle_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->rectangle_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'POLYGON': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'ID': {
                                        $this->polygon_ID = $v;
                                    }
                                    break;
                                case 'POINTS': {
                                        $this->polygon_POINTS = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->polygon_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->polygon_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->polygon_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'POLYLINE': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'ID': {
                                        $this->polyline_ID = $v;
                                    }
                                    break;
                                case 'POINTS': {
                                        $this->polyline_POINTS = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->polyline_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->polyline_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->polyline_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'STYLE': {
                    $this->attr_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $k."|".$v;
                            switch ($k) {
                                case 'FONT-SIZE': {
                                        $this->style_FONT_SIZE = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->style_FILL = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->style_STROKE = $v;
                                    }
                                    break;
                                case 'STROKE-WIDTH': {
                                        $this->style_STROKE_WIDTH = $v;
                                    }
                                    break;
                            }
                        }
                    }
                }
                break;
            case 'TEXT': {
                    // echo "text "."\n";
                    $this->text_tag = $tag;
                    if (count($attributes)) {
                        foreach ($attributes as $k => $v) {
                            // echo $tk."|".$tv."\n";
                            switch ($k) {
                                case 'ID': {
                                        $this->ellipse_ID = $v;
                                    }
                                    break;
                                case 'X': {
                                        $this->text_X = $v;
                                    }
                                    break;
                                case 'Y': {
                                        $this->text_Y = $v;
                                    }
                                    break;
                                case 'STROKE': {
                                        $this->text_STROKE = $v;
                                    }
                                    break;
                                case 'FILL': {
                                        $this->text_FILL = $v;
                                    }
                                    break;
                                case 'FONT-SIZE': {
                                        $this->text_FONT_SIZE = $v;
                                    }
                                    break;
                            }
                        }
                    }

                    $this->text_CONTENT = (isset($attributes['CONTENT'])) ? $attributes['CONTENT'] : '';
                    $this->recordset = array();
                }
        }
    }

    function cdata($parser, $cdata)
    {
        $parser;
        if (isset($cdata)) {
            switch ($this->content) {
                case 'base64': {
                        $this->value = base64_decode($cdata);
                    }
                    break;
                default: {
                        $this->value = $cdata;
                    }
            }
            $this->text_CONTENT = $this->value;
            // echo $this->text_CONTENT."\n";
        } //if
    }

    function tag_close($parser, $tag)
    {
        $parser;
        // if ($tag = "text") {
        // $this->recordset[$this->field] = $this->value;
        // foreach ($this->recordset as $tcaption => $tvalue){
        // $this->text_CONTENT = $tvalue;
        // echo $this->text_CONTENT." is text_CONTENT"."\n";
        // }
        // }
    }
}
// $source='
// <path stroke="red" id="Buildings_4"  d="M3462060.66 -5423517.8 l 1.73 -5.73 "/>
// <text  x="1" y="2">dd</text>
// ';
// $source='<style fill="none" stroke="#ff8040" stroke-width="20" />';
// $source='<circle cx="3578220.12" cy="-5501918.54" r = "1046.83" />';
// $xml=new SVGParser($source);
?>