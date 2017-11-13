<?php
/*
     - use CSV header or not: if yes, first line of the file will be recognized as CSV header, and all database columns will be called so, and this header line won't be imported in table content.
	    If not, the table columns will be calles as "layer,recid,geomtype,xmin,ymin,xmax,ymax,svggeom,svgxlink,srs,attributes,style"
     - separate char: character to separate fields, comma [,] is default
     - enclose char: character to enclose those values that contain separate char in text, quote ["] is default
     - escape char: character to escape special symbols like enclose char, back slash [\] is default
*/

class CSV2DB {
    private $database;
    private $data_encode;
    private $aid, $layername, $SRSname;

    private $file_name; //where to import from
    private $use_csv_header = true; //use first line of file as column names
    private $text_csv_terminated = ";"; //character to separate fields
    private $text_csv_enclosed = "\""; //character to enclose fields, which contain separator char into content
    private $text_csv_escaped = "\\"; //char to escape special symbols
    public $error; //error message
    public $log;
    public $recordgood, $recordbad;
    private $arr_csv_columns = array(); //array of columns
    private $table_exists = true; //flag: does table for import exist

    // ===================Appendix variable===============================
    private $appendix_params = array();

    function CSV2DB($database, $aid, $data_encode="UTF-8", $layername, $file_name, $SRSname, 
		$use_csv_header, $text_csv_terminated, $text_csv_enclosed, $text_csv_escaped, $table_exists)
    {
    	$this->database = $database;
    	$this->aid = $aid;
    	$this->data_encode = $data_encode;
    	$this->layername = $layername;
    	$this->SRSname = $SRSname;
        $this->file_name = $file_name;

        $this->use_csv_header = $use_csv_header;
        $this->text_csv_terminated = $text_csv_terminated;
        $this->text_csv_enclosed = $text_csv_enclosed;
        $this->text_csv_escaped = $text_csv_escaped;
        $this->table_exists = $table_exists;

        $this->arr_csv_columns = array();

        $this->error = $this->database->databaseGetErrorMessage();
    }

    public function set_appendix_parameters($appendix_parameters){
		$this->appendix_params = $appendix_parameters;
	}

    function import(){	
        // if(empty($this->arr_csv_columns))
        $this->get_csv_header_fields();
        if ($this->table_exists) {
            if ($this->database->databaseGetErrorMessage() == "") {
                $this->database->inputCSV2Database($this->aid, $this->data_encode, $this->layername, $this->file_name,
                	$this->SRSname, $this->appendix_params['cvs_use_default_srs'],
					$this->text_csv_terminated, $this->text_csv_enclosed,
                    $this->text_csv_escaped, $this->use_csv_header, $this->arr_csv_columns);
            }
            $this->error = $this->database->databaseGetErrorMessage();
            $this->recordgood = $this->database->recordgood;
            $this->recordbad = $this->database->recordbad;
            $this->log = $this->database->log;
        }
    }
    
    /**
     * returns array of CSV file columns
     */ 
    function get_csv_header_fields()
    {
        $this->arr_csv_columns = array();
        $this->arr_csv_columns_noh = array();
        if (file_exists($this->file_name)) {
            $handle = fopen($this->file_name, "r");

            if ($handle) {
            	//$this->data_encode
            	//setlocale(LC_ALL, 'de_DE.UTF8');
                $arr = fgetcsv($handle, 10 * 1024, $this->text_csv_terminated);
                if (is_array($arr) && !empty($arr)) {
                    if ($this->use_csv_header) {
                        foreach($arr as $val) {
                            if (trim($val) != "") {
                                $this->arr_csv_columns[] = $val;
                            }
                            // echo $val."|\n";
                        }
                    } else {
                        $this->arr_csv_columns_noh = array(id, aid, recid, layer, geomtype, srs, xmin, ymin, xmax, ymax, geom, xlink, attributes);
                        $i = 0;
                        foreach($arr as $val) {
                            $this->arr_csv_columns[] = $this->arr_csv_columns_noh[$i];
                            //echo $this->arr_csv_columns_noh[$i]."-\n";
                            $i++;
                        }
                    }
                    //print_r($this->arr_csv_columns);
                }
                unset($arr);
                fclose($handle);
            } else {
                return $this->error = "File cannot be opened: " . ("" == $this->file_name ? "[empty]" : $this->database->getMysql_escape_string($this->file_name));
            }
        }
        else{
            return $this->error = "The CSV file $this->file_name does not exist";
		}
        // print_r($this->arr_csv_columns);
        return $this->arr_csv_columns;
    }
}


/*$filename = "D:\ProgramFiles\xampp\htdocs\suas\files\user\1\data\csv\data-2-csv.txt";
$parser = new CSV2DB(null, $aid, $layername, $filename, $use_csv_header,
        	$text_csv_terminated, $text_csv_enclosed, $text_csv_escaped, $table_exists);
$parser->import();*/

// $csv2db->use_csv_header = false;
// $csv2db->text_csv_terminated = ";";
// $csv2db->text_csv_enclosed = "";
// $csv2db->text_csv_escaped = "";

?>
