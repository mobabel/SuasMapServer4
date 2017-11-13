<?php
/*
 * Created on 28.06.2009
 * 
 * Code was edited from project 'PHP GPX'
 * Author: cgarstin 
 * License : GNU General Public License (GPL)
 * http://sourceforge.net/projects/phpgpx/
 * PHP GPX is a PHP class that is based on the GPX data structure for GPS data. 
 * It aims to become a PHP class that can consume, manipulate, and export GPX data between different data sources.
 * 
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * GPX2DB.class.php
 * @version $Id$
 * @copyright (C) 2006-2009 LI Hui
 * @Description :
 * @contact webmaster@easywms.com
 *
 */
 
require_once 'Geometry2WKT.class.php';
require_once 'AttributeParser.class.php';

class GPX2DB extends GPXBaseClass{
	// ==================Basic variable================================
	private $recid;
	private $layerid = 0;
	
	private $database, $fileName, $SRSname, $aid, $layername;
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
	private $coordinates;
	public $name = NULL;
	public $version = NULL;
	public $creator = NULL;
	public $metadata = NULL;
	public $waypoints = NULL;
	public $routes = NULL;
	public $tracks = NULL;

	public $userID = NULL;
	public $public = true;
	
	public $objectType = "GPX";
	public $dbID = "UNDEFINED_GPX_ID";
	public $objectDBName = "go_gpx";
	
	// ==================================================
	function GPX2DB($database, $aid, $data_encode="UTF-8", $layername, $fileName, $SRSname)
		{
		
		$this->database = $database;
		$this->aid = $aid;
		$this->layername = $layername;
		$this->fileName = $fileName;
		$this->SRSname = empty($SRSname)?SRSNotDefined:$SRSname;
		$this->data_encode = $data_encode;
		
	}
	
	public function set_appendix_parameters($appendix_parameters){
		$this->appendix_params = $appendix_parameters;
	}
	
	public function begin(){
		if (file_exists($this->fileName)) {
			$gpxDocument = new XMLReader;
			$gpxDocument->open($this->fileName);
			
			$gpxDocument->read();
			if ($gpxDocument->name != "gpx") {
				$this->error = "The top node of the GPX document must be GPX";
				return false;
			}
			
			$this->version = $gpxDocument->getAttribute("version");
			$this->creator = $gpxDocument->getAttribute("creator");
			$this->readToNextOpen($gpxDocument);
			

			if ($gpxDocument->name == "metadata") {
				$this->metadata = new GPXMetadata();
				$this->metadata->XMLin($gpxDocument);
				//$this->readToNextOpen($gpxDocument);
			}
			
			//these nodes could be top nodes, ignore
			if ($gpxDocument->name == "time" ) {
				$this->readToNextOpen($gpxDocument);
			}	
			if ($gpxDocument->name == "bounds" ) {
				$this->readToNextOpen($gpxDocument);
			}
			
			if($this->appendix_parameters['use_groupname_as_layername']){
				$this->layername = $this->metadata->name;
			}
			if($this->layername==NULL || empty($this->layername)){
				$this->layername = LayerNotDefined;
			}
			//echo $this->layername;
			
			//echo $gpxDocument->name;
			//point
			if ($gpxDocument->name == "wpt") {
				$wptCount = 0;
				do {
					$this->waypoints[$wptCount] = new GPXWaypoint();
					$this->waypoints[$wptCount]->XMLin($gpxDocument);
					$attributes = AttributeParser::getAttributeFromArray($this->waypoints[$wptCount]->attribute);
					//echo $attributes;

					$pointparser = new PointParser(false);
                    $resultarray = $pointparser->parser($this->waypoints[$wptCount]->longitude, $this->waypoints[$wptCount]->latitude);
                    //print_r($resultarray);

                    $this->database->databaseInsertGeometry($this->aid, $this->layername, "wpt".$this->recid, GeometryTypePoint, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);
					$wptCount++;
					$this->recid++;
				} while ($gpxDocument->name == "wpt");
			}
			//polygon
			if ($gpxDocument->name == "rte") {
				$rteCount = 0;
				do {
					$this->routes[$rteCount] = new GPXRoute();
					$this->routes[$rteCount]->XMLin($gpxDocument);
					$attributes = AttributeParser::getAttributeFromArray($this->routes[$rteCount]->attribute);
					//echo $attributes;
					if ($this->routes[$rteCount]->routepoints != NULL) {
						foreach ($this->routes[$rteCount]->routepoints as $routepoint) { 
							$coordinates .= $routepoint->longitude." ".$routepoint->latitude." ";
						}
					}
					
					$PolygonParser = new PolygonParser(false, false);
                    $resultarray = $PolygonParser->parser(trim($coordinates));
                    //print_r($resultarray);

                    if($this->appendix_parameters['use_groupname_as_layername']){
	                    $this->layername = $this->routes[$rteCount]->name;
                    }
                    if( $this->layername==NULL || empty($this->layername) ){
	                    $this->layername = LayerNotDefined;
                    }
                    $this->database->databaseInsertGeometry($this->aid, $this->layername, "rte".$this->recid, GeometryTypePolygon, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);
	
					$rteCount++;
					$this->recid++;
				} while ($gpxDocument->name == "rte");
			}
			//linestring
			if ($gpxDocument->name == "trk") {
				$trkCount = 0;
				do {
					$this->tracks[$trkCount] = new GPXTrack();
					$this->tracks[$trkCount]->XMLin($gpxDocument);
					$attributes = AttributeParser::getAttributeFromArray($this->tracks[$trkCount]->attribute);
					//echo $attributes;
					if ($this->tracks[$trkCount]->segments != NULL) {
						foreach ($this->tracks[$trkCount]->segments as $segments) { 
							
							if ($segments->trkpts != NULL) {
								foreach ($segments->trkpts as $trackpoint) { 
									$coordinates .= $trackpoint->longitude." ".$trackpoint->latitude." ";
								}
							}		
						}
					}
					//TODO should use multilinestring
                    $PolylineParser = new PolylineParser(false, 1);
                    $resultarray = $PolylineParser->parser(trim($coordinates));
                    //print_r($resultarray);

                    if($this->appendix_parameters['use_groupname_as_layername']){
	                    $this->layername = $this->tracks[$trkCount]->name;
                    }
                    if( $this->layername==NULL || empty($this->layername) ){
	                    $this->layername = LayerNotDefined;
                    }
                    $this->database->databaseInsertGeometry($this->aid, $this->layername, "trk".$this->recid, GeometryTypeLineString, $resultarray[0], $resultarray[1], $resultarray[2], $resultarray[3], $resultarray[4], "", $this->SRSname, $attributes);
					
					$trkCount++;
					$this->recid++;
				} while ($gpxDocument->name == "trk");
			}
				
			$this->recordgood = $this->database->recordgood;
			$this->recordbad = $this->database->recordbad;
			$this->log = $this->database->getLog4Database();
		}
		else{
			return $this->error = "The GPX file $this->file_name does not exist";
		}
	}
	
	function printerror(){
		setSessionMessage(t('Failure<br>XML Error: %s at line %d, please check the GPX file!',
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

abstract class GPXBaseClass {
	
	public $debugMode = false;
	
	// Function to read to the next opening tag
	public function readToNextOpen($input) {
		do {
			if (!$input->read()) { return false; }	
		} while ($input->nodeType != 1);
		return true;
	}
	
	public function skipExtensions($gpxDocument) {
		// Skip extensions
		while ($gpxDocument->name == "extensions" 
			|| $gpxDocument->name == "gpxx:WaypointExtension"
			|| $gpxDocument->name == "gpxx:DisplayMode"
			|| $gpxDocument->name == "gpxx:RouteExtension"
			|| $gpxDocument->name == "gpxx:TrackExtension"
			|| $gpxDocument->name == "gpxx:IsAutoNamed"
			|| $gpxDocument->name == "gpxx:RoutePointExtension"  
			|| $gpxDocument->name == "gpxx:Subclass"
			|| $gpxDocument->name == "gpxx:DisplayColor"
			|| $gpxDocument->name == "gpxx:Categories" 
			|| $gpxDocument->name == "gpxx:Category") {
			$this->readToNextOpen($gpxDocument);
		}
	}
	
	public function debug($input) {
		if ($this->debugMode) { echo "{ " . $input . " } <br/>"; }
	}
}

class GPXRoute extends GPXBaseClass {
	public $name = NULL;
	public $cmt = NULL;
	public $desc = NULL;
	public $src = NULL;
	public $links = NULL;
	public $number = NULL;
	public $type = NULL;
	public $routepoints = NULL;
	
	public $attribute = array();
	
	public $objectDBName = "go_gpx_route";
	public $objectType = "ROUTE";
	public $dbID = "UNDEFINED_ROUTE_ID";

	public function GPXRoute() {
		$this->debug("GPXRoute");
	}	

	public function XMLin($gpxDocument) {
		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "name") {
			$gpxDocument->read();
			$this->name = $gpxDocument->value;
			$this->attribute['name'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "cmt") {
			$gpxDocument->read();
			$this->comment = $gpxDocument->value;
			$this->attribute['cmt'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "desc") {
			$gpxDocument->read();
			$this->description = $gpxDocument->value;
			$this->attribute['desc'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "src") {
			$gpxDocument->read();
			$this->source = $gpxDocument->value;
			$this->attribute['src'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "link") {
			$linkCount = 0;
			do {
				$this->links[$linkCount] = new GPXLink();
				$this->links[$linkCount]->XMLin($gpxDocument);
				$linkCount++;
			} while ($gpxDocument->name == "link");
		}
		if ($gpxDocument->name == "number") {
			$gpxDocument->read();
			$this->number = $gpxDocument->value;
			$this->attribute['number'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "type") {
			$gpxDocument->read();
			$this->type = $gpxDocument->value;
			$this->attribute['type'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		$this->skipExtensions($gpxDocument);

		if ($gpxDocument->name == "rtept") {
			$rteptCount = 0;
			do {
				$this->routepoints[$rteptCount] = new GPXWaypoint();
				$this->routepoints[$rteptCount]->XMLin($gpxDocument);
				$rteptCount++;
			} while ($gpxDocument->name == "rtept");
		}
	}
	
	
	// Bounds calculation methods //////////////////////////////////////////////
	public function maxLat() {
		$maxLat = NULL;
		foreach ($this->routepoints as $routepoint) {
			if ($maxLat == NULL || $routepoint->latitude > $maxLat) { $maxLat = $routepoint->latitude; } 
		}
		return $maxLat;
	}
	public function maxLon() {
		$maxLon = NULL;
		foreach ($this->routepoints as $routepoint) {
			if ($maxLon == NULL || $routepoint->longitude > $maxLon) { $maxLon = $routepoint->longitude; } 
		}
		return $maxLon;
	}
	public function minLat() {
		$minLat = NULL;
		foreach ($this->routepoints as $routepoint) {
			if ($minLat == NULL || $routepoint->latitude < $minLat) { $minLat = $routepoint->latitude; } 
		}
		return $minLat;
	}
	public function minLon() {
		$minLon = NULL;
		foreach ($this->routepoints as $routepoint) {
			if ($minLon == NULL || $routepoint->longitude < $minLon) { $minLon = $routepoint->longitude; } 
		}
		return $minLon;
	}
	public function midLat() {
		return $this->minLat() + ($this->maxLat() - $this->minLat()) / 2;
	}
	public function midLon() {
		return $this->minLon() + ($this->maxLon() - $this->minLon()) / 2;
	}
}

class GPXTrack extends GPXBaseClass {
	public $name = NULL;
	public $cmt = NULL;
	public $desc = NULL;
	public $src = NULL;
	public $links = NULL;
	public $number = NULL;
	public $type = NULL;
	public $segments = NULL;
	
	public $attribute = array();
	
	public $objectDBName = "go_gpx_track";
	public $objectType = "TRACK";
	public $dbID = "UNDEFINED_TRACK_ID";
	public $created = NULL;

	public function GPXTrack() {
		$this->debug("GPXTrack");
	}
	
	public function XMLin($gpxDocument) {
		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "name") {
			$gpxDocument->read();
			$this->name = $gpxDocument->value;
			$this->attribute['name'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "cmt") {
			$gpxDocument->read();
			$this->cmt = $gpxDocument->value;
			$this->attribute['cmt'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "desc") {
			$gpxDocument->read();
			$this->desc = $gpxDocument->value;
			$this->attribute['desc'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "src") {
			$gpxDocument->read();
			$this->source = $gpxDocument->value;
			$this->attribute['src'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "link") {
			$linkCount = 0;
			do {
				$this->links[$linkCount] = new GPXLink();
				$this->links[$linkCount]->XMLin($gpxDocument);
				$linkCount++;
			} while ($gpxDocument->name == "link");
		}
		if ($gpxDocument->name == "number") {
			$gpxDocument->read();
			$this->number = $gpxDocument->value;
			$this->attribute['number'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "type") {
			$gpxDocument->read();
			$this->type = $gpxDocument->value;
			$this->attribute['type'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		$this->skipExtensions($gpxDocument);
		
		if ($gpxDocument->name == "trkseg") {
			$trksegCount = 0;
			do {
				$this->segments[$trksegCount] = new GPXTrackSegment();
				$this->segments[$trksegCount]->XMLin($gpxDocument);
				$trksegCount++;
			} while ($gpxDocument->name == "trkseg");
		}
	}

	
	// Bounds calculation methods //////////////////////////////////////////////////////////////////
	public function maxLat() {
		$maxLat = NULL;
		foreach ($this->segments as $segment) {
			foreach ($segment->trkpts as $trkpoint) {
				if ($maxLat == NULL || $trkpoint->latitude > $maxLat) { $maxLat = $trkpoint->latitude; } 
			}
		}
		return $maxLat;
	}
	public function maxLon() {
		$maxLon = NULL;
		foreach ($this->segments as $segment) {
			foreach ($segment->trkpts as $trkpoint) {
				if ($maxLon == NULL || $trkpoint->longitude > $maxLon) { $maxLon = $trkpoint->longitude; } 
			}
		}
		return $maxLon;
	}
	public function minLat() {
		$minLat = NULL;
		foreach ($this->segments as $segment) {
			foreach ($segment->trkpts as $trkpoint) {
				if ($minLat == NULL || $trkpoint->latitude < $minLat) { $minLat = $trkpoint->latitude; } 
			}
		}
		return $minLat;
	}
	public function minLon() {
		$minLon = NULL;
		foreach ($this->segments as $segment) {
			foreach ($segment->trkpts as $trkpoint) {
				if ($minLon == NULL || $trkpoint->longitude < $minLon) { $minLon = $trkpoint->longitude; } 
			}
		}
		return $minLon;
	}
	public function midLat() {
		return $this->minLat() + ($this->maxLat() - $this->minLat()) / 2;
	}
	public function midLon() {
		return $this->minLon() + ($this->maxLon() - $this->minLon()) / 2;
	}
}

class GPXTrackSegment extends GPXBaseClass {
	public $trkpts = NULL;

	public $objectType = "TRACK_SEGMENT";
	public $dbID = "UNDEFINED_TRACK_SEGMENT_ID";
	public $objectDBName = "go_gpx_track_segment";
	
	public function GPXTrackSegment() {
		$this->debug("GPXTrackSegment");
	}
	
	public function XMLin($gpxDocument) {

		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "trkpt") {
			$trkPtCount = 0;
			do {
				$this->trkpts[$trkPtCount] = new GPXWaypoint();
				$this->trkpts[$trkPtCount]->XMLin($gpxDocument);
				$trkPtCount++;
			} while ($gpxDocument->name == "trkpt");
		}
		$this->skipExtensions($gpxDocument);		
	}
	
}

class GPXWaypoint extends GPXBaseClass {
	public $latitude = NULL;
	public $longitude = NULL;	
	public $elevation = NULL;
	public $time = NULL;
	public $magvar = NULL;
	public $geoIDHeight = NULL;
	public $name = NULL;
	public $comment = NULL;
	public $description = NULL;
	public $source = NULL;
	public $links = NULL;
	public $symbol = NULL;
	public $type = NULL;
	public $fix = NULL;
	public $satellites = NULL;
	public $hdop = NULL;
	public $vdop = NULL;
	public $pdop = NULL;
	public $ageofdgpsdata = NULL;
	public $dgpsid = NULL;
	
	public $attribute = array();
	
	public $objectDBName = "go_gpx_waypoint";
	public $objectType = "WAYPOINT";
	public $dbID = "UNDEFINED_WAYPOINT_ID";
	
	public function GPXWaypoint() {
		$this->debug("GPXWaypoint");
	}
	
	public function XMLin($gpxDocument) {
		$this->latitude = $gpxDocument->getAttribute("lat");
		$this->longitude = $gpxDocument->getAttribute("lon");
		//$this->debug($this->latitude);

		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "ele") {
			$gpxDocument->read();
			$this->elevation = $gpxDocument->value;
			$this->attribute['ele'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "time") {
			$gpxDocument->read();
			$this->time = $gpxDocument->value;
			$this->attribute['time'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "magvar") {
			$gpxDocument->read();
			$this->magvar = $gpxDocument->value;
			$this->attribute['magvar'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "geoidheight") {
			$gpxDocument->read();
			$this->geoIDHeight = $gpxDocument->value;
			$this->attribute['geoidheight'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "name") {
			$gpxDocument->read();
			$this->name = $gpxDocument->value;
			$this->attribute['name'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "cmt") {
			$gpxDocument->read();
			$this->comment = $gpxDocument->value;
			$this->attribute['cmt'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "desc") {
			$gpxDocument->read();
			$this->description = $gpxDocument->value;
			$this->attribute['desc'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "src") {
			$gpxDocument->read();
			$this->source = $gpxDocument->value;
			$this->attribute['src'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "link") {
			$linkCount = 0;
			do {
				$this->links[$linkCount] = new GPXLink();
				$this->links[$linkCount]->XMLin($gpxDocument);
				$linkCount++;
			} while ($gpxDocument->name == "link");
		}
		if ($gpxDocument->name == "sym") {
			$gpxDocument->read();
			$this->symbol = $gpxDocument->value;
			$this->attribute['sym'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "type") {
			$gpxDocument->read();
			$this->type = $gpxDocument->value;
			$this->attribute['type'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "fix") {
			$gpxDocument->read();
			$this->fix = $gpxDocument->value;
			$this->attribute['fix'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "sat") {
			$gpxDocument->read();
			$this->satellites = $gpxDocument->value;
			$this->attribute['sat'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "hdop") {
			$gpxDocument->read();
			$this->hdop = $gpxDocument->value;
			$this->attribute['hdop'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "vdop") {
			$gpxDocument->read();
			$this->vdop = $gpxDocument->value;
			$this->attribute['vdop'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "pdop") {
			$gpxDocument->read();
			$this->pdop = $gpxDocument->value;
			$this->attribute['pdop'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "ageofdgpsdata") {
			$gpxDocument->read();
			$this->ageofdgpsdata = $gpxDocument->value;
			$this->attribute['ageofdgpsdata'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "dgpsid") {
			$gpxDocument->read();
			$this->dgpsid = $gpxDocument->value;
			$this->attribute['dgpsid'] = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		$this->skipExtensions($gpxDocument);
	}
}

class GPXCopyright extends GPXBaseClass {
	public $year;
	public $license;
	public $author;
	
	private $objectDBName = "go_gpx_copyright";
	private $objectType = "COPYRIGHT";
	private $dbID = "UNDEFINED_COPYRIGHT_ID";

	public function GPXCopyright() {
		$this->debug("GPXCopyright");
	}

	public function XMLin($gpxDocument) {
		$this->author = $gpxDocument->getAttribute("author");
		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "year") {
			$gpxDocument->read();
			$this->year = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		$this->readToNextOpen($gpxDocument);
		if ($gpxDocument->name == "license") {
			$gpxDocument->read();
			$this->license = $gpxDocument->value;
		}
	}
}

class GPXBounds extends GPXBaseClass {
	public $min_latitude; // float
	public $min_longitude; // float
	public $max_latitude; // float
	public $max_longitude; // float
	
	private $objectDBName = "go_gpx_bounds";
	private $dbID = "UNDEFINED_BOUNDS_ID";

	public function GPXBounds() {
		$this->debug("GPXBounds");
	}
	
	public function XMLin($gpxDocument){
		$this->min_latitude = $gpxDocument->getAttribute("minlat");
		$this->min_longitude = $gpxDocument->getAttribute("minlon");
		$this->max_latitude = $gpxDocument->getAttribute("maxlat");
		$this->max_longitude = $gpxDocument->getAttribute("maxlon");
	}
}

class GPXEmail extends GPXBaseClass {
	public $email_id;
	public $domain;
	
	private $objectDBName = "go_gpx_email";
	private $objectType = "EMAIL";
	private $dbID = "UNDEFINED_EMAIL_ID";

	public function GPXEmail() {
		$this->debug("GPXEmail");
	}

	public function XMLin($gpxDocument) {
		$this->email_id = $gpxDocument->getAttribute("id");
		$this->domain = $gpxDocument->getAttribute("domain");
	}
	
}
class GPXLink extends GPXBaseClass {
	public $href;
	public $text;
	public $type;
	
	private $objectType = "GPXLink";
	private $objectDBName = "go_gpx_link";
	private $dbID = "UNDEFINED_LINK_ID";

	public function GPXLink() {
		$this->debug("GPXLink");
	}

	public function XMLin($gpxDocument){
		$this->href = $gpxDocument->getAttribute("href");
		$this->readToNextOpen($gpxDocument);
	
		if ($gpxDocument->name == "text"){
			$gpxDocument->read();
			$this->text = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "type"){
			$gpxDocument->read();
			$this->type = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
	}
	
}

class GPXMetadata extends GPXBaseClass {
	public $name = NULL;
	public $description = NULL;
	public $author = NULL;
	public $copyright = NULL;
	public $links = NULL;
	public $time = NULL;
	public $keywords = NULL;
	public $bounds = NULL;
	
	private $objectDBName = "go_gpx_metadata";
	private $objectType = "METADATA";
	private $dbID = "UNDEFINED_METADATA_ID";

	public function GPXMetadata() {
		$this->debug("GPXMetaData");
	}
	
	public function XMLin ($gpxDocument) {
		$this->readToNextOpen($gpxDocument);

		if ($gpxDocument->name == "name") {
			$gpxDocument->read();
			$this->name = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "description") {
			$gpxDocument->read();
			$this->description = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "author") {
			$this->author = new GPXPerson();
			$this->author->XMLin($gpxDocument);
		}
		if ($gpxDocument->name == "copyright") {
			$this->copyright = new GPXCopyright();
			$this->copyright->XMLin($gpxDocument);
	}
		if ($gpxDocument->name == "link") {
			$linkCount = 0;
			do {
				$this->links[$linkCount] = new GPXLink();
				$this->links[$linkCount]->XMLin($gpxDocument);
				$linkCount++;
			} while ($gpxDocument->name == "link");
		}
		if ($gpxDocument->name == "time") {
			$gpxDocument->read();
			$this->time = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "keywords") {
			$gpxDocument->read();
			$this->keywords = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "bounds") {
			$this->bounds = new GPXBounds($gpxDocument);
		}

	}
	
}

class GPXPerson extends GPXBaseClass {

	public $name;
	public $email; // GPXAuthor
	public $links; // GPXLinka

	private $objectDBName = "go_gpx_person";
	private $objectType = "PERSON";
	private $dbID = "UNDEFINED_PERSON_ID";

	public function GPXPerson() {
		$this->debug("GPXPerson");
	}

	public function XMLin($gpxDocument) {
		$this->readToNextOpen($gpxDocument);

		if ($gpxDocument->name == "name") {
			$gpxDocument->read();
			$this->name = $gpxDocument->value;
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "email") {
			$this->email = new GPXEmail($gpxDocument);
			$this->readToNextOpen($gpxDocument);
		}
		if ($gpxDocument->name == "link") {
			$linkCount = 0;
			do {
				$this->links[$linkCount] = new GPXLink();
				$this->links[$linkCount]->XMLin($gpxDocument);
				$linkCount++;
			} while ($gpxDocument->name == "link");
		}
	}
}

/*include_once '../config.php';
include_once '../models/common.inc';
include_once 'AttributeParser.class.php';
switchDatabase($dbtype);
$database = new Database();
$database->databaseConfig($dbserver, $dbusername ,$dbpassword, $dbname, $dbprefix);
$database->databaseConnect();
//fells_loop.gpx  2007-01-22_15_50.gpx   tracktreks.gpx
$GPX2DB =  new GPX2DB($database, 34, "UTF-8", "network", "../files/user/1/data/gpx/tracktreks.gpx", "EPSG:4326", "UTF-8");
$GPX2DB->set_appendix_parameters(array());
echo $GPX2DB->error;*/

?>
