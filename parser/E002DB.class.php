<?php

/**
* E002DB Class
*
* @original part of code comes from Image_GIS, author:<ovidio AT users.sourceforge.net>
* @description : Import the points coordinates and use according class to output the wkt string
*                  Used in read the SVG, SHP, E00 or MOF format
* @Task :Calculation for Points XY's Min&Max value
* @Task :Output WKT format
* @version $3.0$ 2007
* @Author leelight
* @Contact webmaster@easywms.com
*/

require_once '../models/setting.inc';
require_once 'Geometry2WKT.class.php';
require_once 'AttributeParser.class.php';

class E002DB {
    private $database;
    private $data_encode;
    private $aid, $layerName, $SRSname;
    private $fileName;
    public $error = "", $log = "";
    public $recordgood = 0, $recordbad = 0, $recordNumber = 0;
    private $parseFile;
    private $textRecid, $textx1, $textx2, $texty1, $texty2;
    private $textattribute;

    // ===================Appendix variable===============================
    private $appendix_params = array();

    public function E002DB($database, $aid, $data_encode="UTF-8", $layerName, $SRSname , $fileName)
    {
        $this->database = $database;
        $this->aid = $aid;
        $this->data_encode = $data_encode;
        $this->layerName = $layerName;
        $this->SRSname = $SRSname;
        $this->fileName = $fileName;

        $this->error = $this->database->databaseGetErrorMessage();
        if (!empty($this->error))
            $this->setError($this->error);
        else
            $this->loadFromFile();
    }

    public function set_appendix_parameters($appendix_parameters){
		$this->appendix_params = $appendix_parameters;
	}

    public function loadFromFile()
    {
        $this->openFile();
        $this->parseData();

        $this->recordgood = $this->database->recordgood;
        $this->recordbad = $this->database->recordbad;
        $this->log = $this->database->getLog4Database();
        $this->closeFile();
    }

    function openFile($toWrite = false)
    {
        if (file_exists($this->fileName)) {
            $this->parseFile = @fopen($this->fileName, ($toWrite ? "wb+" : "rb"));
            if (!$this->parseFile) {
                return $this->setError(sprintf("It is not possible to open the E00 file '%s'", $this->fileName));
            }
        } else {
            return $this->setError(sprintf("The E00 file '%s' does not exist", $this->fileName));
        }

        return true;
    }

    function closeFile()
    {
        if ($this->parseFile) {
            fclose($this->parseFile);
            $this->parseFile = null;
        }
    }

    function formatLine($string)
    {
        return strtoupper($string);
    }

    function parseData(){
        $arrTemp = array();
        $in_data = false;

        $numRecords = 0;
        $ln = 0;

		$this->recordNumber = 0;
		/**
		* The subsequent lines of a set are the coordinates with two x-y pairs per line,
		* if the coverage is single-precision. If there are an odd number of coordinates,
		* the last line will have only one x-y pair.
		* Double-precision puts one coordinate pair on each line.
        */
		while (!feof($this->parseFile)) {
            $ln ++;
            $line = fgets($this->parseFile, 1024);

            if (substr($this->formatLine($line), 0, 3) == "ARC") {
                $in_data = true;
            }
            if ($in_data) {
            	//reload one new geometry array, get the new numRecords, and reset the geometry array;
            	if ($numRecords == 0 &&
                    preg_match("#^\s+([0-9]+)\s+([-0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#", $line, $a)) {
					$numRecords = $a[7];
                    //echo $numRecords. '<br />';
                    //$a[0] is the whole content of line

					//$arrGeometry is not null, import into database
					if(!empty($arrTemp)){
						$arrGeomInfo = $this->GetPointString($arrTemp);
						$PolylineParser = new PolylineParser(false,1);
                		$resultarray = $PolylineParser->parser($arrGeomInfo["pointString"]);
                		//echo $arrGeomInfo["pointString"]."\n<br>";
                		//echo $resultarray[4]."<br>";
                		$this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypeLineString,
						$resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

			    		if ($this->database->databaseGetErrorMessage() != "") {
                    		$this->error = $this->database->databaseGetErrorMessage();
                		}
					}

                    $arrGeometry = array();
                    $arrTemp = array();
                    //read the recordnumber this line
                    $this->recordNumber = $a[1];
                }
				//This work only for single-precision, the 4 coordinates(two point)
                else if ($numRecords &&
                         preg_match("#^([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})#", $line, $a)) {
						//echo $a[1] . '<br />';
						//echo $a[2] . '<br />';

					$arrTemp[$numRecords]['x'] = $a[1];
                    $arrTemp[$numRecords]['y'] = $a[2];

                    $numRecords--;

		    		$arrTemp[$numRecords]['x'] = $a[3];
                    $arrTemp[$numRecords]['y'] = $a[4];
                    //$lineSet->addLine($a[1], $a[2], $a[3], $a[4]);


                    $numRecords--;
                }
				//this part works for Double-precision, the first 2 coordinates(one point)
                else if ($numRecords &&
                         preg_match("#^([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})([ -][0-9]\.[0-9]{7,14}E[-+][0-9]{2})#", $line, $a)) {
						//echo $a[1] . '<br />';
						//echo $a[2] . '<br />';

                    $arrTemp[$numRecords]['x'] = $a[1];
                    $arrTemp[$numRecords]['y'] = $a[2];


                    $numRecords--;
                }
				//find this string and stop the parsing
				//        -1         0         0         0         0         0         0
                else if ($numRecords == 0 &&
						 preg_match("#^\s+(-1)\s+([-0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)#", $line, $a)) {
						 //echo "ARC end here. line number ".$ln;
						 //echo $line . '<br />';
						 //import the last data
					if(!empty($arrTemp)){
						$arrGeomInfo = $this->GetPointString($arrTemp);
						$PolylineParser = new PolylineParser(false,1);
                		$resultarray = $PolylineParser->parser($arrGeomInfo["pointString"]);
                		//echo $arrGeomInfo["pointString"]."\n<br>";
                		//echo $resultarray[4]."<br>";
                		$this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypeLineString,
						$resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, "");

			    		if ($this->database->databaseGetErrorMessage() != "") {
                    		$this->error = $this->database->databaseGetErrorMessage();
                		}
					}

                    $arrGeometry = array();
                    $arrTemp = array();

					break;
				}
                else if ($ln > 2) {
                    printf(
                        'Died at: %s<br />',
                        $ln
                    );
                    break;
                }
            }
        }
    }

    function GetPointString($arrGeometry){
		$arrGeomInfoTemp = array();
		$arrGeomInfoTemp["xmin"]    = 9999999;
		$arrGeomInfoTemp["ymin"]     = 9999999;
		$arrGeomInfoTemp["xmax"]    = -9999999;
		$arrGeomInfoTemp["ymax"]     = -9999999;
		$arrGeomInfoTemp["pointString"] = "";
		$arrGeomInfoTemp["pointCount"] = count($arrGeometry);

		for($i=$arrGeomInfoTemp["pointCount"]+1;$i>0;$i--){
			$array = $arrGeometry[$i];
			$x = $array['x'];
			$y  = $array['y'];

			$arrGeomInfoTemp["xmin"]  = min($x,$arrGeomInfoTemp["xmin"]);
			$arrGeomInfoTemp["ymin"]   = min($y ,$arrGeomInfoTemp["ymin"]);
			$arrGeomInfoTemp["xmax"]  = max($x,$arrGeomInfoTemp["xmax"]);
			$arrGeomInfoTemp["ymax"]   = max($y ,$arrGeomInfoTemp["ymax"]);
			if(!empty($arrGeomInfoTemp["pointString"]))$arrGeomInfoTemp["pointString"] .= " ";
			$arrGeomInfoTemp["pointString"] .= "$x $y";
		}

		return $arrGeomInfoTemp;
	}

    function setError($error_)
    {
        $this->error = $error_;
    }
    public function getError()
    {
        return $this->error;
    }
    function setLog($log_)
    {
        $this->log .= $log_;
    }
    public function getLog()
    {
        return $this->log;
    }
}
?>