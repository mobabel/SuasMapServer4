<?php

require_once 'Geometry2WKT.class.php';
require_once 'AttributeParser.class.php';

class SVGToDB {
    // ==================Basic variable================================
    private $parser;
    private $content;
    private $value;
    private $bRead;
    private $attr_tag;

    private $database, $fileName, $SRSname, $aid, $layername;
    private $SVG_Use_Group_Name = false;
    private $data_encode;
    public $error = "";
    public $log = ""; //for using database->getLog4Database
    public $recordgood = 0;  //record number that inserted successfully
    public $recordbad = 0;
    // ===================Appendix variable===============================
    private $appendix_params = array();
	//appendix_params['use_custom_layername']
	//appendix_params['custom_layername']
	//appendix_params['use_groupname_as_layername']

    // ===================Internal variable===============================
    private $db_GSTYLE;
    private $db_GFILL;
    private $db_GSTROKE;
    private $db_GSTROKE_WIDTH;
    private $db_GTEXT_FONT_FAMILY;
    private $db_GTEXT_FONT_SIZE;

    private $xml_errorstring;
    // Point Geometry
    private $db_GeoCIRCLE_ID;
    private $db_GeoCIRCLE_CX;
    private $db_GeoCIRCLE_CY;
    private $db_GeoCIRCLE_R;
    private $db_GeoCIRCLE_FILL;
    private $db_GeoCIRCLE_STROKE;
    private $db_GeoCIRCLE_STROKE_WIDTH;
    private $db_GeoCIRCLE_STYLE;

    private $db_GeoELLIPSE_ID;
    private $db_GeoELLIPSE_CX;
    private $db_GeoELLIPSE_CY;
    private $db_GeoELLIPSE_RX;
    private $db_GeoELLIPSE_RY;
    private $db_GeoELLIPSE_FILL;
    private $db_GeoELLIPSE_STROKE;
    private $db_GeoELLIPSE_STROKE_WIDTH;
    private $db_GeoELLIPSE_STYLE;
    // LineString Geometry
    private $db_GeoLINE_ID;
    private $db_GeoLINE_X1;
    private $db_GeoLINE_Y1;
    private $db_GeoLINE_X2;
    private $db_GeoLINE_Y2;
    private $db_GeoLINE_FILL;
    private $db_GeoLINE_STROKE;
    private $db_GeoLINE_STROKE_WIDTH;
    private $db_GeoLINE_STYLE;

    private $db_GeoRECT_ID;
    private $db_GeoRECT_X;
    private $db_GeoRECT_Y;
    private $db_GeoRECT_WIDTH;
    private $db_GeoRECT_HEIGHT;
    private $db_GeoRECT_FILL;
    private $db_GeoRECT_STROKE;
    private $db_GeoRECT_STROKE_WIDTH;
    private $db_GeoRECT_STYLE;

    private $db_GeoIMAGE_ID;
    private $db_GeoIMAGE_X;
    private $db_GeoIMAGE_Y;
    private $db_GeoIMAGE_WIDTH;
    private $db_GeoIMAGE_HEIGHT;
    private $db_GeoIMAGE_XLINKHREF;

    private $db_GeoPATH_ID;
    private $db_GeoPATH_D;
    private $db_GeoPATH_FILL;
    private $db_GeoPATH_STROKE;
    private $db_GeoPATH_STYLE;
    private $db_GeoPATH_STROKE_WIDTH;

    private $db_GeoPOLYLINE_ID;
    private $db_GeoPOLYLINE_POINTS;
    private $db_GeoPOLYLINE_FILL;
    private $db_GeoPOLYLINE_STROKE;
    private $db_GeoPOLYLINE_STYLE;
    private $db_GeoPOLYLINE_STROKE_WIDTH;
    // Polygon Geometry
    private $db_GeoPOLYGON_ID;
    private $db_GeoPOLYGON_POINTS;
    private $db_GeoPOLYGON_FILL;
    private $db_GeoPOLYGON_STROKE;
    private $db_GeoPOLYGON_STYLE;
    private $db_GeoPOLYGON_STROKE_WIDTH;
    // Text Geometry
    private $text_tag;
    private $db_GeoTEXT_ID;
    private $db_GeoTEXT_X;
    private $db_GeoTEXT_Y;
    private $db_GeoTEXT_CONTENT;
    private $db_GeoTEXT_FILL;
    private $db_GeoTEXT_STROKE;
    private $db_GeoTEXT_FONT_FAMILY;
    private $db_GeoTEXT_FONT_SIZE;
    private $db_GeoTEXT_STYLE;

    function init()
    {
        $this->roottag = "";
        $this->curtag = &$this->roottag;
    }
    // ==================================================
    function SVGToDB($database, $aid, $data_encode="UTF-8", $layername, $fileName, $SRSname)
    {

        $this->database = $database;
        $this->aid = $aid;
        $this->data_encode = $data_encode;
        $this->layername = $layername;
        $this->fileName = $fileName;
        $this->SRSname = empty($SRSname)?SRSNotDefined:$SRSname;
        //if not given layername, user svg group id as layer
		if(empty($this->layername)){
			$this->SVG_Use_Group_Name = true;
		}
		
        $this->init();

        $this->parser = xml_parser_create($this->data_encode);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");

        if (file_exists($this->fileName)) {
        //begin to parse
            $xml_file = @fopen($this->fileName, 'r');
            if($xml_file){
                while (($data = fread($xml_file, 8192))) {
                    $this->parse($data);
                }
                fclose($xml_file);
            }
            else{
			    return $this->error = "It is not possible to open the SVG file".$this->file_name;
			}
        }
        else{
            return $this->error = "The SVG file $this->file_name does not exist";
		}
    }

    public function set_appendix_parameters($appendix_parameters){
		$this->appendix_params = $appendix_parameters;
	}

    function parse($data)
    {
        @xml_parse($this->parser, $data) or
        die(
           $this->printerror()
            );

      $this->recordgood = $this->database->recordgood;
      $this->recordbad = $this->database->recordbad;
      $this->log = $this->database->getLog4Database();
    }

    function printerror(){
	    setSessionMessage(t('Failure<br>XML Error: %s at line %d, please check the SVG file!',
		array('%s' => xml_error_string(xml_get_error_code($this->parser)),'%d' => xml_get_current_line_number($this->parser))), SITE_MESSAGE_ERROR);
		displayMessage(true);
		echo "
		<script type=\"text/javascript\" >
		var targelem = parent.parent.$('loader_container');
		targelem.style.display='none';
		targelem.style.visibility='hidden';
		</script>";
	}

    function tag_open($parser, $tag, $attributes)
    {
        $parser;
        // echo "s2"."\n";
        if (!is_object($this->curtag)) {
            $null = 0;
            // $this->curtag = new XMLTag($null);
            // $this->curtag->set_name( $tag );
            // $this->curtag->set_attributes( $attributes );
        } else { // otherwise, add it to the tag list and move curtag
            // $this->curtag->add_subtag( $tag, $attributes );
            // $this->curtag = &$this->curtag->curtag;
        }
        switch ($tag) {
            case 'SVG': break;
            case 'SYMBOL': break;
            case 'G': { // Group
                    if($this->SVG_Use_Group_Name){
                    	$this->layername = $attributes['ID'];
                    	$this->layername = iconv($this->data_encode, "UTF-8//IGNORE", $this->layername);
                    }
                    $this->db_GSTYLE = $attributes['STYLE'];
                    $this->db_GFILL = $attributes['FILL'];
                    $this->db_GSTROKE = $attributes['STROKE'];
                    $this->db_GSTROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GTEXT_FONT_FAMILY = $attributes['FONT-FAMILY'];
                    $this->db_GTEXT_FONT_SIZE = $attributes['FONT-SIZE'];
                    $this->bRead = true;
                }
                break; //in <G>
            // /////////////////////////////////////////////////////////////////
            case 'CIRCLE': {
                    $this->db_GeoCIRCLE_ID = $attributes['ID'];
                    $this->db_GeoCIRCLE_CX = $attributes['CX'];
                    $this->db_GeoCIRCLE_CY = $attributes['CY'];
                    $this->db_GeoCIRCLE_R = $attributes['R'];
                    $this->db_GeoCIRCLE_FILL = $attributes['FILL'];
                    $this->db_GeoCIRCLE_STROKE = $attributes['STROKE'];
                    $this->db_GeoCIRCLE_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoCIRCLE_STYLE = $attributes['STYLE'];
                }
                break;
            case 'PATH': {
                    $this->db_GeoPATH_ID = $attributes['ID'];
                    $this->db_GeoPATH_D = $attributes['D'];
                    $this->db_GeoPATH_FILL = $attributes['FILL'];
                    $this->db_GeoPATH_STROKE = $attributes['STROKE'];
                    $this->db_GeoPATH_STYLE = $attributes['STYLE'];
                    $this->db_GeoPATH_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    // These are some spaces in the path, but the number is unclear, they must be delete
                    $iSpace = iSpace;
                    // $iSpace=strlen($pathString);
                    for($iSpace;$iSpace > 0;$iSpace--) {
                        $strChars = " ";
                        for($iaddS = 0;$iaddS < $iSpace;$iaddS++) {
                            $strChars = $strChars . " ";
                        }
                        $this->db_GeoPATH_D = str_replace($strChars, ' ', $this->db_GeoPATH_D);
                    }
                }
                break;
            case 'TEXT': {
                    $this->db_GeoTEXT_ID = $attributes['ID'];
                    $this->db_GeoTEXT_X = $attributes['X'];
                    $this->db_GeoTEXT_Y = $attributes['Y'];
                    $this->db_GeoTEXT_FILL = $attributes['FILL'];
                    $this->db_GeoTEXT_STROKE = $attributes['STROKE'];
                    $this->db_GeoTEXT_FONT_FAMILY = $attributes['FONT-FAMILY'];
                    $this->db_GeoTEXT_FONT_SIZE = $attributes['FONT-SIZE'];
                    $this->db_GeoTEXT_STYLE = $attributes['STYLE'];
                    $this->db_GeoTEXT_CONTENT = (isset($attributes['CONTENT'])) ? $attributes['CONTENT'] : '';
                }
                break;
            case 'RECT': {
                    $this->db_GeoRECT_ID = $attributes['ID'];
                    $this->db_GeoRECT_X = $attributes['X'];
                    $this->db_GeoRECT_Y = $attributes['Y'];
                    $this->db_GeoRECT_WIDTH = $attributes['WIDTH'];
                    $this->db_GeoRECT_HEIGHT = $attributes['HEIGHT'];
                    $this->db_GeoRECT_FILL = $attributes['FILL'];
                    $this->db_GeoRECT_STROKE = $attributes['STROKE'];
                    $this->db_GeoRECT_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoRECT_STYLE = $attributes['STYLE'];
                }
                break;
            case 'IMAGE': {
                    $this->db_GeoIMAGE_ID = $attributes['ID'];
                    $this->db_GeoIMAGE_X = $attributes['X'];
                    $this->db_GeoIMAGE_Y = $attributes['Y'];
                    $this->db_GeoIMAGE_WIDTH = $attributes['WIDTH'];
                    $this->db_GeoIMAGE_HEIGHT = $attributes['HEIGHT'];
                    $this->db_GeoIMAGE_XLINKHREF = $attributes['XLINK:HREF'];
                }
                break;
            case 'ELLIPSE': {
                    $this->db_GeoELLIPSE_ID = $attributes['ID'];
                    $this->db_GeoELLIPSE_CX = $attributes['CX'];
                    $this->db_GeoELLIPSE_CY = $attributes['CY'];
                    $this->db_GeoELLIPSE_RX = $attributes['RX'];
                    $this->db_GeoELLIPSE_RY = $attributes['RY'];
                    $this->db_GeoELLIPSE_FILL = $attributes['FILL'];
                    $this->db_GeoELLIPSE_STROKE = $attributes['STROKE'];
                    $this->db_GeoELLIPSE_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoELLIPSE_STYLE = $attributes['STYLE'];
                }
                break;
            case 'LINE': {
                    $this->db_GeoLINE_ID = $attributes['ID'];
                    $this->db_GeoLINE_X1 = $attributes['X1'];
                    $this->db_GeoLINE_Y1 = $attributes['Y1'];
                    $this->db_GeoLINE_X2 = $attributes['X2'];
                    $this->db_GeoLINE_Y2 = $attributes['Y2'];
                    $this->db_GeoLINE_FILL = $attributes['FILL'];
                    $this->db_GeoLINE_STROKE = $attributes['STROKE'];
                    $this->db_GeoLINE_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoLINE_STYLE = $attributes['STYLE'];
                }
                break;
            case 'POLYGON': {
                    $this->db_GeoPOLYGON_ID = $attributes['ID'];
                    $this->db_GeoPOLYGON_POINTS = $attributes['POINTS'];
                    $this->db_GeoPOLYGON_FILL = $attributes['FILL'];
                    $this->db_GeoPOLYGON_STROKE = $attributes['STROKE'];
                    $this->db_GeoPOLYGON_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoPOLYGON_STYLE = $attributes['STYLE'];
                }
                break;
            case 'POLYLINE': {
                    $this->db_GeoPOLYLINE_ID = $attributes['ID'];
                    $this->db_GeoPOLYLINE_POINTS = $attributes['POINTS'];
                    $this->db_GeoPOLYLINE_FILL = $attributes['FILL'];
                    $this->db_GeoPOLYLINE_STROKE = $attributes['STROKE'];
                    $this->db_GeoPOLYLINE_STROKE_WIDTH = $attributes['STROKE-WIDTH'];
                    $this->db_GeoPOLYLINE_STYLE = $attributes['STYLE'];
                }
        }
    }

    function cdata($parser, $cdata)
    {
        $parser;
        switch ($this->content) {
            case 'base64': {
                    $this->value = base64_decode($cdata);
                }
                break;
            default: {
                    $this->value = $cdata;
                }
                $this->db_GeoTEXT_CONTENT = $this->value;
        }
    }

    function tag_close($parser, $tag)
    {
        $parser;
        // echo "s3"."\n";
        switch ($tag) {
            case 'SVG': break;
            case 'SYMBOL': break;
            case 'G': {
                    $this->bRead = false;
                }
                break;
            // -----------------------CIRCLE------------------------------------------------
            case 'CIRCLE': {
                    if ($this->bRead == true) {
                        //if svg group name is empty, use default name
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $pointparser = new PointParser(true);
                        $resultarray = $pointparser->parser($this->db_GeoCIRCLE_CX, $this->db_GeoCIRCLE_CY);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoCIRCLE_ID, GeometryTypePoint, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");
						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------ELLIPSE------------------------------------------------
            case 'ELLIPSE': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $pointparser = new PointParser(true);
                        $resultarray = $pointparser->parser($this->db_GeoELLIPSE_CX, $this->db_GeoELLIPSE_CY);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoELLIPSE_ID, GeometryTypePoint, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------LINE------------------------------------------------
            case 'LINE': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $lineparser = new LineParser(true);
                        $resultarray = $lineparser->parser($this->db_GeoLINE_X1, $this->db_GeoLINE_Y1, $this->db_GeoLINE_X2, $this->db_GeoLINE_Y2);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoLINE_ID, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------RECTANGE------------------------------------------------
            case 'RECT': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $ractangeparser = new RectangeParser(true);
                        $resultarray = $ractangeparser->parser($this->db_GeoRECT_X, $this->db_GeoRECT_Y, $this->db_GeoRECT_WIDTH, $this->db_GeoRECT_HEIGHT);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoRECT_ID, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------RECTANGE------------------------------------------------
            case 'IMAGE': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $ractangeparser = new RectangeParser(true);
                        $resultarray = $ractangeparser->parser($this->db_GeoIMAGE_X, $this->db_GeoIMAGE_Y, $this->db_GeoIMAGE_WIDTH, $this->db_GeoIMAGE_HEIGHT);
                        if ($this->db_GeoIMAGE_XLINKHREF != "") {
                            $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoIMAGE_ID, GeometryTypeImage, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], $this->db_GeoIMAGE_XLINKHREF, $this->SRSname, "");
                        }

	                    if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------PATH------------------------------------------------
            /*
* M = moveto
* L = lineto
* H = horizontal lineto
* V = vertical lineto
* C = curveto
* S = smooth curveto
* Q = quadratic Belzier curve
* T = smooth quadratic Belzier curveto
* A = elliptical Arc
* Z = closepath
*/
            case 'PATH': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        // $db_GeoPATH_D_uppercase is $db_Gpdu
                        $db_Gpdu = strtoupper($this->db_GeoPATH_D);
                        // if path(s) is close path(s), the other commands such as H,V.. are not considered here
                        if (strstr($db_Gpdu, 'M') AND strstr($db_Gpdu, 'L') AND !strstr($db_Gpdu, 'H') AND !strstr($db_Gpdu, 'V') AND !strstr($db_Gpdu, 'C') AND !strstr($db_Gpdu, 'S') AND !strstr($db_Gpdu, 'Q') AND !strstr($db_Gpdu, 'T') AND !strstr($db_Gpdu, 'A')) {
                            $path_mlzparser = new Path_MLZParser(true);
                            $resultarray = $path_mlzparser->parser($this->db_GeoPATH_D);
                        }
                        // The other commands such as H,V..
                        // The XY max min here is not accurate, need update later
                        elseif (strstr($db_Gpdu, 'H') OR strstr($db_Gpdu, 'V') OR strstr($db_Gpdu, 'C') OR strstr($db_Gpdu, 'S') OR strstr($db_Gpdu, 'Q') OR strstr($db_Gpdu, 'T') OR strstr($db_Gpdu, 'A')) {
                            break;
                        } //
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoPATH_ID, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------POLYLINE------------------------------------------------
            case 'POLYLINE': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $PolylineParser = new PolylineParser(true,0);
                        $resultarray = $PolylineParser->parser($this->db_GeoPOLYLINE_POINTS);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoPOLYLINE_ID, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

					    if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------POLYGON------------------------------------------------
            case 'POLYGON': {
                    if ($this->bRead == true) {
                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}

                        $PolygonParser = new PolygonParser(true);
                        $resultarray = $PolygonParser->parser($this->db_GeoPOLYGON_POINTS);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoPOLYGON_ID, GeometryTypePolygon, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

						if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
					}
                }
                break;
            // -----------------------TEXT------------------------------------------------
            case 'TEXT': {
                    if ($this->bRead == true) {
                        $this->db_GeoTEXT_CONTENT = str_replace('\'', '\'\'', $this->db_GeoTEXT_CONTENT);
                        $attributes = AttributeParser::writeTextAngleAttribute($this->db_GeoTEXT_CONTENT,0);

                        if($this->layername == ""){
                            $this->layername = LayerNotDefined;
						}
                        //delete the px mark in element!
                        $this->db_GeoTEXT_X = trim(str_replace('PX','',strtoupper($this->db_GeoTEXT_X)));
                        $this->db_GeoTEXT_Y = trim(str_replace('PX','',strtoupper($this->db_GeoTEXT_Y)));

						$pointparser = new PointParser(true);
                        $resultarray = $pointparser->parser($this->db_GeoTEXT_X, $this->db_GeoTEXT_Y);
                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->db_GeoTEXT_ID, GeometryTypeText, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);

	                    if ($this->database->databaseGetErrorMessage() != "") {
	                        $this->error = $this->database->databaseGetErrorMessage();
	                        break;
	                    }
                    }
                }
                // -----------------------ALL SVG SHAPES------------------------------------------------
        } //switch tag
    }

}
?>
