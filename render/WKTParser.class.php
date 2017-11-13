<?php
/**
 * WKT Parser Class
 *
 * @version $3.0$ 2006
 * @Author  leelight
 * @Contact leeglanz@hotmail.com
 */

class WKTParser {
    public $wktPointX;
    public $wktPointY;
    public $wktPointNr;

    public $wktMPointX;
    public $wktMPointY;
    public $wktMLineNr;
    public $wktMPointNr;//be array

    public $wktGeomType;

    public function parse($stringwkt){
        if (substr($stringwkt,0,5)=="POINT") {
            $this->wktGeomType = "POINT";
	        $stringwkt = str_replace('POINT(', '',$stringwkt);
	        $stringwkt = str_replace(')', '',$stringwkt);
            $stringwktDevided = explode(" ", $stringwkt);
            $this->wktPointX[0] = $stringwktDevided[0];
            $this->wktPointY[0] = $stringwktDevided[1];
            $this->wktPointNr = 1;
		}
        if (substr($stringwkt,0,10)=="LINESTRING") {
            $this->wktGeomType = "LINESTRING";
	        $stringwkt = str_replace('LINESTRING(', '',$stringwkt);
	        $stringwkt = str_replace(')', '',$stringwkt);
            $stringwktDevided = explode(",", $stringwkt);
            $this->wktPointNr = count($stringwktDevided);
            for($i=0;$i<$this->wktPointNr ;$i++){
                $stringwktTmp = explode(" ", $stringwktDevided[$i]);
                $this->wktPointX[$i] = $stringwktTmp[0];
                $this->wktPointY[$i] = $stringwktTmp[1];
			}
			//print_r($this->wktPointX);
            //echo $this->wktPointNr;
		}
		//The polygon maybe contain the inner rings, it has the same form as the multipolygon.
		//But as normally, the polygon doesnt have the inner rings
		if (substr($stringwkt,0,7)=="POLYGON") {
		    $this->wktGeomType = "POLYGON";
	        $stringwkt = str_replace('POLYGON((', '',$stringwkt);
	        $stringwkt = str_replace('))', '',$stringwkt);
            $stringwktDevided = explode("),(", $stringwkt);

            $this->wktMLineNr=count($stringwktDevided);
            for($i=0;$i<$this->wktMLineNr ;$i++){
                $stringwktMTmp[$i] = explode(",", $stringwktDevided[$i]);
                $this->wktMPointNr[$i]= count($stringwktMTmp[$i]);
                //print_r($stringwktMTmp[$i]);
                for($j=0;$j<$this->wktMPointNr[$i] ;$j++){
				    $stringwktTmp[$j] = explode(" ", $stringwktMTmp[$i][$j]);
				    $this->wktMPointX[$i][$j] = $stringwktTmp[$j][0];
                    $this->wktMPointY[$i][$j] = $stringwktTmp[$j][1];
				}
            }
/*
            $this->wktPointNr = count($stringwktDevided);
            for($i=0;$i<$this->wktPointNr ;$i++){
                $stringwktTmp = explode(" ", $stringwktDevided[$i]);
                $this->wktPointX[$i] = $stringwktTmp[0];
                $this->wktPointY[$i] = $stringwktTmp[1];
			}
*/
			//print_r($this->wktMPointX);
            //print_r($this->wktMPointNr);
		}
		if (substr($stringwkt,0,10)=="MULTIPOINT") {
		    $this->wktGeomType = "MULTIPOINT";
	        $stringwkt = str_replace('MULTIPOINT((', '',$stringwkt);
	        $stringwkt = str_replace('))', '',$stringwkt);
            $stringwktDevided = explode("),(", $stringwkt);
            $this->wktPointNr = count($stringwktDevided);
            for($i=0;$i<$this->wktPointNr ;$i++){
                $stringwktTmp = explode(" ", $stringwktDevided[$i]);
                $this->wktPointX[$i] = $stringwktTmp[0];
                $this->wktPointY[$i] = $stringwktTmp[1];
			}
			//print_r($this->wktPointX);
            //echo $this->wktPointNr;
		}
	    if (substr($stringwkt,0,15)=="MULTILINESTRING") {
	        $this->wktGeomType = "MULTILINESTRING";
	        $stringwkt = str_replace('MULTILINESTRING((', '',$stringwkt);
	        $stringwkt = str_replace('))', '',$stringwkt);
            $stringwktDevided = explode("),(", $stringwkt);
            #print_r($stringwktDevided);
            $this->wktMLineNr=count($stringwktDevided);
            for($i=0;$i<$this->wktMLineNr ;$i++){
                $stringwktMTmp[$i] = explode(",", $stringwktDevided[$i]);
                $this->wktMPointNr[$i]= count($stringwktMTmp[$i]);
                //print_r($stringwktMTmp[$i]);
                for($j=0;$j<$this->wktMPointNr[$i] ;$j++){
				    $stringwktTmp[$j] = explode(" ", $stringwktMTmp[$i][$j]);
				    $this->wktMPointX[$i][$j] = $stringwktTmp[$j][0];
                    $this->wktMPointY[$i][$j] = $stringwktTmp[$j][1];
				}
            }
            #print_r($this->wktMPointX);
            #print_r($this->wktMPointNr);
		}
		if (substr($stringwkt,0,12)=="MULTIPOLYGON") {
		    $this->wktGeomType = "MULTIPOLYGON";
	        $stringwkt = str_replace('MULTIPOLYGON(((', '',$stringwkt);
	        $stringwkt = str_replace(')))', '',$stringwkt);

            $stringwktDevided = explode(")),((", $stringwkt);
            $this->wktMLineNr=count($stringwktDevided);
            for($i=0;$i<$this->wktMLineNr ;$i++){
                $stringwktMTmp[$i] = explode(",", $stringwktDevided[$i]);
                $this->wktMPointNr[$i]= count($stringwktMTmp[$i]);
                //print_r($stringwktMTmp[$i]);
                for($j=0;$j<$this->wktMPointNr[$i] ;$j++){
				    $stringwktTmp[$j] = explode(" ", $stringwktMTmp[$i][$j]);
				    $this->wktMPointX[$i][$j] = $stringwktTmp[$j][0];
                    $this->wktMPointY[$i][$j] = $stringwktTmp[$j][1];
				}
            }
            #print_r($this->wktMPointX);
            #print_r($this->wktMPointNr);
		}
		//if (strstr($stringwkt,'GEOMETRYCOLLECTION')) {

		//}



	}

}

//$stringwkt='MULTILINESTRING((-1360 2156,-1335 2194,-1178 2439,-1131 2508,-1114 2535,-1081 2586,-1009 2697),(-1260 -736,-1240 -732),(1766 -1439,1846 -1443,1907 -1446,1949 -1448,2040 -1453,2072 -1457,2140 -1455,2152 -1455))';
//$stringwkt='LINESTRING(-1360 2156,-1335 2194,-1178 2439,-1131 2508,-1114 2535,-1081 2586,-1009 2697)';
//$stringwkt='MULTIPOINT((-1360 2156),(-1335 2194),(-1178 2439),(-1131 2508),(-1114 2535),(-1081 2586),(-1009 2697))';
//$stringwkt='MULTIPOLYGON((-1360 2156,-1335 2194,-1178 2439,-1131 2508,-1114 2535,-1081 2586,-1009 2697),(-1260 -736,-1240 -732),(1766 -1439,1846 -1443,1907 -1446,1949 -1448,2040 -1453,2072 -1457,2140 -1455,2152 -1455))';
//$stringwkt='POLYGON((-1360 2156,-1335 2194,-1178 2439,-1131 2508,-1114 2535,-1081 2586,-1009 2697))';

//$wktparser=new WKTParser();
//$wktparser->parse($stringwkt);
?>