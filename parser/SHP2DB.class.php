<?php

 /**
 * SHP2DB Class, part of the code is from BytesFall ShapeFiles v0.0.1
 * @original author:<ovidio AT users.sourceforge.net>
 *
 * @description: Import the points coordinates and use according class to output the wkt string
 *               Used in read the SVG, SHP, E00 or MOF format
 *
 * @Task :Calculation for Points XY's Min&Max value
 * @Task :Output WKT format
 * @version $3.0$ 2007
 * @Author leelight
 * @Contact webmaster@easywms.com
 */
require_once '../models/setting.inc';
require_once 'Geometry2WKT.class.php';

function loadData($type, $data)
{
    if (!$data) return $data;
    $tmp = unpack($type, $data);
    return current($tmp);
}

class SHP2DB {
    private $database;
    private $data_encode;
    private $aid, $layerName, $SRSname;
    private $blnUseDbfFile = false, $blnUseShxFile = false;
    private $fileNameShp, $fileNameDbf, $fileNameShx;
    public $error="", $log="";
    public $recordgood = 0, $recordbad= 0, $recordNumber= 0;

    private $SHPFile, $SHXFile, $DBFFile;
    private $DBFHeader, $DBFHeaderArray = array();

     // ===================Appendix variable===============================
    private $appendix_params = array();

    public function SHP2DB($database, $aid, $data_encode="UTF-8",$layerName, $SRSname ,
        $blnUseDbfFile_, $blnUseShxFile_, $fileNameShp_, $fileNameDbf_, $fileNameShx_)
    {
        $this->database = $database;
        $this->aid = $aid;
        $this->data_encode = $data_encode;
        $this->layerName = $layerName;
        $this->SRSname = $SRSname;
        $this->blnUseDbfFile = $blnUseDbfFile_;
        $this->blnUseShxFile = $blnUseShxFile_;
        $this->fileNameShp = $fileNameShp_;
        $this->fileNameDbf = $fileNameDbf_;
        $this->fileNameShx = $fileNameShx_;

        $this->error = $this->database->databaseGetErrorMessage();
        if($this->error!="")
            $this->setError($this->error);
        else
		    $this->loadFromFile();
    }

    public function set_appendix_parameters($appendix_parameters){
		$this->appendix_params = $appendix_parameters;
	}

    public function loadFromFile()
    {
        if ($this->blnUseDbfFile) {
            if (($this->openSHPFile()) && ($this->openDBFFile())) {
                $this->loadHeaders();
                $this->loadRecords();
                $this->closeSHPFile();
                $this->closeDBFFile();

            } else {
                return false;
            }
        } else {
            if ($this->openSHPFile()) {
                $this->loadHeaders();
                $this->loadRecords();
                $this->closeSHPFile();
                $this->closeDBFFile();

            } else {
                return false;
            }
        }
    }

    function openSHPFile($toWrite = false)
    {
        if (file_exists($this->fileNameShp)) {
            $this->SHPFile = @fopen($this->fileNameShp, ($toWrite ? "wb+" : "rb"));
            if (!$this->SHPFile) {
                $this->setError(sprintf("It wasn't possible to open the Shape file '%s'", $this->SHPFileName));
                return false;
            }
        }
        else{
		     $this->setError(sprintf("The Shape file '%s' does not exist", $this->SHPFileName));
		     return false;
		}
        return true;
    }

    function closeSHPFile()
    {
        if ($this->SHPFile) {
            fclose($this->SHPFile);
            $this->SHPFile = null;
        }
    }

    function openSHXFile($toWrite = false)
    {
        if ($this->blnUseShxFile) {
            if (file_exists($this->fileNameShp)) {
                $this->SHXFile = @fopen($this->fileNameShx, ($toWrite ? "wb+" : "rb"));
                if (!$this->SHXFile) {
                    $this->setError(sprintf("It wasn't possible to open the SHX file '%s'", $this->fileNameShx));
                    return false;
                }
                return true;
            }
            else{
                $this->setError(sprintf("The SHX file '%s' does not exist", $this->fileNameShx));
                return false;
			}
        }
    }

    function closeSHXFile()
    {
        if ($this->SHXFile) {
            fclose($this->SHXFile);
            $this->SHXFile = null;
        }
    }

    function openDBFFile($toWrite = false)
    {
        if ($this->blnUseDbfFile) {
            $checkFunction = $toWrite ? "is_writable" : "is_readable";
            if (($toWrite) && (!file_exists($this->fileNameDbf))) {
                if (!@dbase_create($this->fileNameDbf, $this->DBFHeader)) {
                    $this->setError(sprintf("It is not possible to create the DBase file '%s'", $this->fileNameDbf));
                    return false;
                }
            }
            if ($checkFunction($this->fileNameDbf)) {
                $this->DBFFile = dbase_open($this->fileNameDbf, ($toWrite ? 2 : 0));
                if (!$this->DBFFile) {
                    $this->setError(sprintf("It is not possible to open the DBase file '%s'", $this->fileNameDbf));
                    return false;
                }
            } else {
                $this->setError(sprintf("It is not possible to find the DBase file '%s'", $this->fileNameDbf));
                return false;
            }
            return true;
        }
    }

    function closeDBFFile()
    {
        if ($this->DBFFile) {
            dbase_close($this->DBFFile);
            $this->DBFFile = null;
        }
    }

    function getDBFHeader()
    {
        return $this->DBFHeader;
    }

    function getIndexFromDBFData($field, $value)
    {
        $result = -1;
        for ($i = 0;
            $i < (count($this->records) - 1);
            $i++) {
            if (isset($this->records[$i]->DBFData[$field]) && (strtoupper($this->records[$i]->DBFData[$field]) == strtoupper($value))) {
                $result = $i;
            }
        }

        return $result;
    }

    function loadDBFHeader()
    {
        $DBFFile = fopen($this->fileNameDbf, 'r');
        $result = array();
        $buff32 = array();
        $i = 1;
        $inHeader = true;
        while ($inHeader) {
            if (!feof($DBFFile)) {
                $buff32 = fread($DBFFile, 32);
                if ($i > 1) {
                    if (substr($buff32, 0, 1) == chr(13)) {
                        $inHeader = false;
                    } else {
                        $pos = strpos(substr($buff32, 0, 10), chr(0));
                        $pos = ($pos == 0 ? 10 : $pos);
                        $fieldName = substr($buff32, 0, $pos);
                        $fieldType = substr($buff32, 11, 1);
                        $fieldLen = ord(substr($buff32, 16, 1));
                        $fieldDec = ord(substr($buff32, 17, 1));
                        array_push($result, array($fieldName, $fieldType, $fieldLen, $fieldDec));
                    }
                }
                $i++;
            } else {
                $inHeader = false;
            }
        }

        fclose($DBFFile);
        return($result);
    }

    function loadHeaders()
    {
        fseek($this->SHPFile, 24, SEEK_SET);
        $this->fileLength = loadData("N", fread($this->SHPFile, 4));
        fseek($this->SHPFile, 32, SEEK_SET);
        $this->shapeType = loadData("V", fread($this->SHPFile, 4));
        // echo $shapeType;
        $boundingBox = array();
        $boundingBox["xmin"] = loadData("d", fread($this->SHPFile, 8));
        $boundingBox["ymin"] = loadData("d", fread($this->SHPFile, 8));
        $boundingBox["xmax"] = loadData("d", fread($this->SHPFile, 8));
        $boundingBox["ymax"] = loadData("d", fread($this->SHPFile, 8));
        // print_r($boundingBox);
        if ($this->blnUseDbfFile) {
            $this->DBFHeader = $this->loadDBFHeader();
            // analize the DBF file header
            // [0] => Array
            // (
            // [0] => Id
            // [1] => N
            // [2] => 6
            // [3] => 0
            // )
            foreach($this->DBFHeader as $key => $value) {
                for($i = 0; $i < count($value);$i++) {
                    // name="Id" type="N" length="6" dec="0"
                    $tempname = $value[0];
                    if ($i == 0) $this->DBFHeaderArray[$tempname] .= 'name=' . '"' . $value[0] . '" ';
                    if ($i == 1) $this->DBFHeaderArray[$tempname] .= 'type=' . '"' . $value[1] . '" ';
                    if ($i == 2) $this->DBFHeaderArray[$tempname] .= 'length=' . '"' . $value[2] . '" ';
                    if ($i == 3) $this->DBFHeaderArray[$tempname] .= 'dec=' . '"' . $value[3] . '"';
                }
            }
        }
    }

    function loadRecords()
    {
        fseek($this->SHPFile, 100);
        while (!feof($this->SHPFile)) {
            $bByte = ftell($this->SHPFile);
            $record = new ShapeRecord(-1, $this->data_encode);
            // set the parameters
            $record->blnUseDbfFile = $this->blnUseDbfFile;
            $record->DBFHeaderArray = $this->DBFHeaderArray;
            $record->database = $this->database;
            $record->aid = $this->aid;
            $record->layerName = $this->layerName;
            $record->SRSname = $this->SRSname;

            $record->loadFromFile($this->SHPFile, $this->DBFFile);

            //get the variant
            $this->setError($record->error);
            // add the error not only in inputting of database
            $this->setLog($record->log.$record->database->getLog4Database());
            $this->recordgood = $record->database->recordgood;
            $this->recordbad = $record->database->recordbad;
            $this->recordNumber = $record->recordNumber;
            //$records["shapetype"] = $record->shapeType;
            //$records["geom"] = $record->SHPData;

            if (isset($records)) {
                $arrGeometry[] = $records;
                unset($records);
            }

            $eByte = ftell($this->SHPFile);
            if ($eByte <= $bByte) {
                //error is set to null in the last step!!!!
                if($this->recordbad>0)
                    $this->setError("See logs:");

                return false;
            }
        }


    }

    function setError($error_)
    {
        $this->error = $error_;
    }
    public function getError(){
	    return $this->error;
	}
    function setLog($log_){
	    $this->log = $log_;
	}
	public function getLog(){
		return $this->log;
	}
}

class ShapeRecord {
    public $SHPFile = null;
    public $DBFFile = null;
    public $blnUseDbfFile = false;
    public $recordNumber = 0;
    public $shapeType = null;
    public $SHPData = array();
    public $DBFData = array();
    public $DBFHeaderArray;
    public $database, $aid, $layerName, $SRSname;
    public $error,$log;
    public $recordgood, $recordbad;
    public $attributes = "";
    public $data_encode = "UTF-8";

    function ShapeRecord($shapeType_, $data_encode)
    {
        $this->shapeType = $shapeType_;
        $this->data_encode = $data_encode;
    }

    public function loadFromFile(&$SHPFile, &$DBFFile)
    {
        $this->SHPFile = $SHPFile;
        $this->DBFFile = $DBFFile;
        $this->loadStoreHeaders();
        if ($this->blnUseDbfFile)
            $this->loadDBFData();

        switch ($this->shapeType) {
            case 0:
                $this->loadNullRecord();
                break;
            case 1:
                $this->loadPointRecord();
                break;
            case 3:
                $this->loadPolyLineRecord();
                break;
            case 5:
                $this->loadPolygonRecord();
                break;
            case 8:
                $this->loadMultiPointRecord();
                break;
            default:
                $this->setError(sprintf("The Shape Type '%s' is not supported.", $this->shapeType));
                break;
        }
    }

    function loadStoreHeaders()
    {
        $this->recordNumber = loadData("N", fread($this->SHPFile, 4));
        $tmp = loadData("N", fread($this->SHPFile, 4)); //We read the length of the record
        $this->shapeType = loadData("V", fread($this->SHPFile, 4));
    }

    function loadDBFData()
    {
        $this->DBFData = @dbase_get_record_with_names($this->DBFFile, $this->recordNumber);
        // <attribute name='Id' type='N' length='6' dec='0'>11613</attribute>
        foreach($this->DBFHeaderArray as $key => $value) {
            $this->attributes .= '<attribute ';
            $this->attributes .= $value . '>';
            $this->attributes .= $this->DBFData[$key] . '</attribute>';
        }
        $this->attributes = str_replace('\'', '\'\'', $this->attributes);
        $this->attributes = '<attributes>' . $this->attributes . '</attributes>';
        $this->attributes = iconv($this->data_encode, "UTF-8//IGNORE", $this->attributes);
        //TODO use php serilized string
        
        
        //echo $this->attributes;
        // unset($this->DBFData["deleted"]);
    }

    function loadPoint()
    {
        $data = array();
        $x1 = loadData("d", fread($this->SHPFile, 8));
        $y1 = loadData("d", fread($this->SHPFile, 8));
        $data["pointString"] = "$x1 $y1 ";//echo $data["pointString"]."a\n";
        return $data;
    }
    function loadNullRecord()
    {
        $this->SHPData = array();
    }

    function loadPointRecord()
    {
        $data = $this->loadPoint();
        $tmp = explode(" ", $data["pointString"]);
        // $this->SHPData["xmin"] = $this->SHPData["xmax"] = $tmp[0];
        // $this->SHPData["ymin"] = $this->SHPData["ymax"] = $tmp[1];
        // $this->SHPData["numparts"] = 1;
        // $this->SHPData["numpoints"] = 1;
        // $this->SHPData["parts"][0]["pointString"] = $data["pointString"];
        $pointparser = new PointParser(false);
        $resultarray = $pointparser->parser($tmp[0], $tmp[1]);
        $this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypePoint,
            $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $this->attributes);
        if ($this->database->databaseGetErrorMessage() != "") {
            $this->error = $this->database->databaseGetErrorMessage();
        }
    }

    function loadMultiPointRecord()
    {
        $this->SHPData = array();
        $this->SHPData["xmin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["xmax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["numpoints"] = loadData("V", fread($this->SHPFile, 4));
        for ($i = 0;
            $i <= $this->SHPData["numpoints"];
            $i++) {
            $data = $this->loadPoint();
            $this->SHPData["pointString"] .= $data["pointString"];
        }

        $multipointparser = new MultiPointParser(false);
        $resultarray = $multipointparser->parser($this->SHPData["pointString"]);
        $this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypePoint,
            $this->SHPData["xmin"], $this->SHPData["ymin"], $this->SHPData["xmax"], $this->SHPData["ymax"], $resultarray[4], "", $this->SRSname, $this->attributes);
        if ($this->database->databaseGetErrorMessage() != "") {
            $this->error = $this->database->databaseGetErrorMessage();
        }
    }

    function loadPolyLineRecord()
    {
        $this->SHPData = array();
        $this->SHPData["xmin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["xmax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["numparts"] = loadData("V", fread($this->SHPFile, 4));
        $this->SHPData["numpoints"] = loadData("V", fread($this->SHPFile, 4));
        for ($i = 0;
            $i < $this->SHPData["numparts"];
            $i++) {
            $this->SHPData["parts"][$i] = loadData("V", fread($this->SHPFile, 4));
        }

        $firstIndex = ftell($this->SHPFile);
        $readPoints = 0;
        while (list($partIndex, $partData) = @each($this->SHPData["parts"])) {
            if (!isset($this->SHPData["parts"][$partIndex]["pointString"]) || !is_array($this->SHPData["parts"][$partIndex]["pointString"])) {
                $this->SHPData["parts"][$partIndex] = array();
                // $this->SHPData["parts"][$partIndex]["pointString"] = array();
            } while (!in_array($readPoints, $this->SHPData["parts"]) && ($readPoints < ($this->SHPData["numpoints"])) && !feof($this->SHPFile)) {
                $data = $this->loadPoint();
                $this->SHPData["parts"][$partIndex]["pointString"] .= $data["pointString"];
                $readPoints++;
            }
        }

        fseek($this->SHPFile, $firstIndex + ($readPoints * 16));
        $multilinestringparser = new MultiLineStringParser(false);
        $resultarray = $multilinestringparser->parser($this->SHPData["parts"]);
        //echo $resultarray[4];
        $this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypeLineString,
            $this->SHPData["xmin"], $this->SHPData["ymin"], $this->SHPData["xmax"], $this->SHPData["ymax"], $resultarray[4], "", $this->SRSname, $this->attributes);
        if ($this->database->databaseGetErrorMessage() != "") {
            $this->error = $this->database->databaseGetErrorMessage();
        }
    }

    function loadPolygonRecord()
    {
        $this->SHPData = array();
        $this->SHPData["xmin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymin"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["xmax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["ymax"] = loadData("d", fread($this->SHPFile, 8));
        $this->SHPData["numparts"] = loadData("V", fread($this->SHPFile, 4));
        $this->SHPData["numpoints"] = loadData("V", fread($this->SHPFile, 4));
        for ($i = 0;
            $i < $this->SHPData["numparts"];
            $i++) {
            $this->SHPData["parts"][$i] = loadData("V", fread($this->SHPFile, 4));
        }

        $firstIndex = ftell($this->SHPFile);
        $readPoints = 0;
        while (list($partIndex, $partData) = @each($this->SHPData["parts"])) {
            if (!isset($this->SHPData["parts"][$partIndex]["pointString"]) || !is_array($this->SHPData["parts"][$partIndex]["pointString"])) {
                $this->SHPData["parts"][$partIndex] = array();
                // $this->SHPData["parts"][$partIndex]["pointString"] = array();
            } while (!in_array($readPoints, $this->SHPData["parts"]) && ($readPoints < ($this->SHPData["numpoints"])) && !feof($this->SHPFile)) {
                $data = $this->loadPoint();
                $this->SHPData["parts"][$partIndex]["pointString"] .= $data["pointString"];
                $readPoints++;
            }
        }

        fseek($this->SHPFile, $firstIndex + ($readPoints * 16));
        $multipolygonparser = new MultiPolygonParser(false);
        $resultarray = $multipolygonparser->parser($this->SHPData["parts"]);
        //echo $resultarray[4];
        $this->database->databaseInsertGeometry($this->aid, $this->layerName, $this->layerName . "_" . $this->recordNumber, GeometryTypePolygon,
            $this->SHPData["xmin"], $this->SHPData["ymin"], $this->SHPData["xmax"], $this->SHPData["ymax"], $resultarray[4], "", $this->SRSname, $this->attributes);
        if ($this->database->databaseGetErrorMessage() != "") {
            $this->error = $this->database->databaseGetErrorMessage();
        }
    }

    function setError($error)
    {
      //record the non database error
      $this->recordbad++;
      $this->log .= "Error  $this->recordbad :\n" . $error . "\n";
    }

}

?>