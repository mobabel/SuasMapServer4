<?php

require_once 'Geometry2WKT.class.php';
require_once 'AttributeParser.class.php';

class KML2DB {
    // ==================Basic variable================================
    private $parser;
    private $content;
    private $value;
    private $attr_tag;
    private $tagname;
    private $recid;
    private $layerid = 0;

    private $database, $fileName, $SRSname, $aid, $layername;
    private $data_encode;
    public $error = "";
    public $log = ""; //for using database->getLog4Database
    public $recordgood = 0;  //record number that inserted successfully
    public $recordbad = 0;
    private $xml_errorstring;
    // ===================Appendix variable===============================
    private $appendix_params = array();
	//appendix_params['use_custom_layername']
	//appendix_params['custom_layername']
	//appendix_params['use_groupname_as_layername']
	//appendix_params['use_kml_string']
	//appendix_params['kml_string']
	private $kml_str;

    // ===================Internal variable===============================
    private $is_document;
    private $is_folder;
    private $is_name;
    private $is_open;
    private $is_placemark;
    private $is_description;
    private $is_styleUrl;
    private $is_style;

	private $is_LineString;
	private $is_tessellate;
	private $is_coordinates;

	private $is_Polygon;
	private $is_outerBoundaryIs;
	private $is_innerBoundaryIs;
	private $is_LinearRing;
	private $is_GeometryCollection;

	private $is_LookAt;
	private $is_longitude;
	private $is_latitude;
	private $is_altitude;
	private $is_range;
	private $is_tilt;
	private $is_heading;
	private $is_altitudeMode;
	private $is_Point;


	private $document_name;
	private $folder_name;
	private $placemark_name;
	private $placemark_attributes;
	private $coordinates;

    function init()
    {
        $this->roottag = "";
        $this->curtag = &$this->roottag;
    }
    // ==================================================
    function KML2DB($database, $aid, $data_encode="UTF-8", $layername, $fileName, $SRSname)
    {

        $this->database = $database;
        $this->aid = $aid;
        $this->layername = $layername;
        $this->fileName = $fileName;
        $this->SRSname = empty($SRSname)?SRSNotDefined:$SRSname;
		$this->data_encode = $data_encode;

        $this->init();

        $this->parser = xml_parser_create($this->data_encode);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "tag_open", "tag_close");
        xml_set_character_data_handler($this->parser, "cdata");

    }
    
    public function begin(){
    	if($this->appendix_params['use_kml_string']){
    		$this->kml_str = $this->appendix_params['kml_string'];
    		$this->parse($this->kml_str);
    	}else{
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
				    return $this->error = "It is not possible to open the KML file".$this->file_name;
				}
	        }
	        else{
	            return $this->error = "The KML file $this->file_name does not exist";
			}
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
    	if($this->appendix_params['use_kml_string']){
    		echo $this->error = t('err:Failure<br>XML Error: %s at line %d, please check the KML file!',
				array('%s' => xml_error_string(xml_get_error_code($this->parser)),'%d' => xml_get_current_line_number($this->parser)));
    	}else{
		    setSessionMessage(t('Failure<br>XML Error: %s at line %d, please check the KML file!',
				array('%s' => xml_error_string(xml_get_error_code($this->parser)),'%d' => xml_get_current_line_number($this->parser))), SITE_MESSAGE_ERROR);
			displayMessage(true);
			echo "
			<script type=\"text/javascript\" >
			var targelem = parent.parent.$('loader_container');
			targelem.style.display='none';
			targelem.style.visibility='hidden';
			</script>";
    	}
	}

    function tag_open($parser, $tag, $attributes)
    {
        $parser;
        $this->tagname = $tag;
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
            case 'KML': break;
            case 'DOCUMENT': {
					$this->is_document = true;
				}break;
            case 'NAME': {
            		//if there is no Folder, use the file
					$this->is_name = true;
				}break;
			case 'DESCRIPTION': {
            		//if there is no Folder, use the file
					$this->description = true;
				}break;
            case 'STYLE': break;//ignore the style
            case 'STYLEURL': break;
			//=========end of kml description========
			// Group
            case 'FOLDER': {
                    //if not given layername, user svg group id as layer
                    $this->is_folder = true;
                    $this->layerid = 0;
                }
                break;
            case 'OPEN': {
					$this->is_open = true;
				}break;
           //=========end of Folder description========
            case 'PLACEMARK': {
            		$this->layerid ++;
                    $this->is_placemark = true;
                }
                break;
            case 'LINESTRING': {
                    $this->is_LineString = true;
                }
                break;
            case 'TESSELLATE': {
                    $this->is_tessellate = true;
                }
                break;
            case 'COORDINATES': {
            		$this->coordinates = "";
                    $this->is_coordinates = true;
                }
                break;
            case 'POLYGON': {
                    $this->is_Polygon = true;
                }
                break;
            case 'OUTERBOUNDARYIS': {
                    $this->is_outerBoundaryIs = true;
                }
                break;
            case 'INNERBOUNDARYIS': {
                    $this->is_innerBoundaryIs = true;
                }
                break;
            case 'LINEARRING': {
                    $this->is_LinearRing = true;
                }
                break;
            case 'POINT': {
                    $this->is_Point = true;
                }
                break;
            case 'LOOKAT': break;//ignore
			case 'LONGITUDE': break;//ignore
			case 'LATITUDE': break;//ignore
			case 'ALTITUDE': break;//ignore
			case 'RANGE': break;//ignore
			case 'TILT': break;//ignore
			case 'HEADING': break;//ignore
			case 'ALTITUDEMODE': break;//ignore
        }
    }

	//<![CDATA[ ........  ]]> will be filtered automatically
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
        }
		if($this->is_coordinates){
			$this->coordinates .= $this->value;
			//echo $this->value;
		}

    }

    function tag_close($parser, $tag)
    {
        $parser;
        switch ($tag) {
            case 'KML': break;
            case 'DOCUMENT': {
					$this->is_document = false;
				}break;
            case 'NAME': {
            		//if there is no Folder, use the file
            		if($this->is_placemark){
						$this->placemark_name = $this->value;
					}elseif($this->is_folder){
						$this->folder_name = $this->value;
					}elseif($this->is_document){
						$this->document_name = $this->value;
					}

					$this->is_name = false;
				}break;
            case 'DESCRIPTION': {
            		//only catch the attributes for placemark(geometry)
            		//<![CDATA[ ........  ]]>
					if($this->is_placemark){
						$this->placemark_attributes = $this->value;
					}

					$this->description = false;
				}break;
            case 'STYLE': break;//ignore the style
            case 'STYLEURL': break;
			//=========end of kml description========
			// Group
            case 'FOLDER': {
                    //if not given layername, user svg group id as layer
                    $this->is_folder = false;
                }
                break;
            case 'OPEN': {
					$this->is_open = false;
				}break;
           //=========end of Folder description========
            case 'PLACEMARK': {
                    $this->is_placemark = false;
                }
                break;
            case 'LINESTRING': {
                    $this->is_LineString = false;
                }
                break;
            case 'TESSELLATE': {
                    $this->is_tessellate = false;
                }
                break;
            case 'COORDINATES': {

					if($this->appendix_parameters['use_groupname_as_layername']){
						$this->layername = $this->folder_name;
						if(empty($this->layername)){
                			$this->layername = $this->document_name;
						}
					}/*elseif($this->appendix_params['use_custom_layername']){
						$this->layername = $this->appendix_params['custom_layername'];
					}*/
            		if(empty($this->layername)){
                		$this->layername = LayerNotDefined;
					}

					$this->placemark_attributes = iconv($this->data_encode, "UTF-8//IGNORE", $this->placemark_attributes);
					//echo $this->placemark_attributes;
					$attributeArray = AttributeParser::getArrayFromXml($this->placemark_attributes);
					if(is_array($attributeArray)){
						//if placemark_attributes is not xml, return empty array
						if(count($attributeArray) == 0){
							$attributeArray['description'] = $this->placemark_attributes;
						}
						$attributeArray['name'] = $this->placemark_name;
					}
					//print_r($attributeArray);
					$attributes = AttributeParser::getAttributeFromArray($attributeArray);

					//echo $attributes;
					if(empty($attributes)){
						$attributes = 	"";
					}

					$this->recid = (empty($this->placemark_name)?$this->folder_name:$this->placemark_name).$this->layerid;
					$this->coordinates = $this->format_kml_coordinates($this->coordinates);

					//==============POINT================
					if ($this->is_placemark && $this->is_Point ) {
						$points = explode(" ", $this->coordinates);
						$pointparser = new PointParser(false);
                        $resultarray = $pointparser->parser($points[0], $points[1]);//print_r($resultarray);

                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->recid, GeometryTypePoint, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);
					}
					//==============LineString================
					elseif ($this->is_placemark && $this->is_LineString ) {
                        $PolylineParser = new PolylineParser(false, 1);
                        $resultarray = $PolylineParser->parser($this->coordinates);//print_r($resultarray);

                        $this->database->databaseInsertGeometry($this->aid, $this->layername, $this->recid, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);

					}
					//==============Polygon================
					elseif ($this->is_placemark && $this->is_Polygon ) {
						//TODO how to control it??
						if($this->is_outerBoundaryIs){

						}
						elseif($this->is_innerBoundaryIs){

						}
						$PolygonParser = new PolygonParser(false, false);
                        $resultarray = $PolygonParser->parser($this->coordinates);//print_r($resultarray);

                       	$this->database->databaseInsertGeometry($this->aid, $this->layername, $this->recid, GeometryTypePolygon, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);
					}

					if ($this->database->databaseGetErrorMessage()) {
	                    $this->error = $this->database->databaseGetErrorMessage();
	                    break;
	                }

                    $this->is_coordinates = false;
                }
                break;
            case 'POLYGON': {
                    $this->is_Polygon = false;
                }
                break;
            case 'OUTERBOUNDARYIS': {
                    $this->is_outerBoundaryIs = false;
                }
                break;
            case 'INNERBOUNDARYIS': {
                    $this->is_innerBoundaryIs = false;
                }
                break;
            case 'LINEARRING': {
                    $this->is_LinearRing = false;
                }
                break;
            case 'POINT': {
                    $this->is_Point = false;
                }
                break;
            case 'LOOKAT': break;//ignore
			case 'LONGITUDE': break;//ignore
			case 'LATITUDE': break;//ignore
			case 'ALTITUDE': break;//ignore
			case 'RANGE': break;//ignore
			case 'TILT': break;//ignore
			case 'HEADING': break;//ignore
			case 'ALTITUDEMODE': break;//ignore
            // -----------------------ALL KML SHAPES------------------------------------------------
        }
    }

    /**
    * delete the altitude infomation and return the lat long with space
    *
    * 9.181544743046366,48.77711168370395,0 9.181541786465026,48.77699582640013,0
    * => 9.181544743046366,48.77711168370395,0 9.181541786465026,48.77699582640013,0
    * @params: $org
    */
    function format_kml_coordinates($org){
    	$last = "";
    	$array_temp = "";
    	$org = trim($org);
    	if(strpos($org, "\r\n")){
			$array_corrds = explode("\r\n", $org);
		}
		elseif(strpos($org, "\n")){
			$array_corrds = explode("\n", $org);
		}
		else{
			$array_corrds = explode(" ", $org);
		}

		for($i=0,$j=count($array_corrds);$i<$j;$i++){
			$array_temp = explode(",", $array_corrds[$i]);
			$last .= $array_temp[0]." ".$array_temp[1]." ";
		}
		$array_temp = null;
		return $last;
	}

}


/*include_once '../config.php';
include_once '../models/common.inc';
include_once 'AttributeParser.class.php';
switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername ,$dbpassword, $dbname, $dbprefix);
$database->databaseConnect();
$KML2DB =  new KML2DB($database, 34, "UTF-8", "network", "../files/user/1/data/Germany Railway Net.kml", "EPSG:4326", "UTF-8");//Temporary Places.kml   Germany Railway Net
$KML2DB->set_appendix_parameters(array());
echo $KML2DB->error;*/

?>
