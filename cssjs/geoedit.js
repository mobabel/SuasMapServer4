/**
 * Part of source code come from the following listed website, thx! 
 * http://www.birdtheme.org/useful/googletool.html
 * http://code.google.com/articles/support/ezdigitizer.htm
 * http://groups.google.com/group/Google-Maps-API/browse_thread/thread/41aa4cd10e355531
 */

var map;
var a;
var gisMode_Hand = 0;
var gisMode_Point = 1;
var gisMode_Linestring = 2;
var gisMode_Polygon = 3;
var gisMode_Circle = 4;
var gisMode_Rectangle = 5;
var gisMode_CircleFill = 6;
var gisMode_RectangleFill = 7;

var gisMode = gisMode_Hand;
var gisModes = new Array();

var options = {};

var geomShapes = new Array();
var geomPoints = new Array();
var shapeIndex = 0;

//use to store the current coord points
var currentPoints = new Array();

var holePoints = new Array();

var polyShapeHole;
var shapehole = new Array();

var headfoot = true;
var mylistener;
var eventOnMoveListener;
var editlistener = null;
var holelistener = null;
var editing = false;
var holeediting = false;
var polygonDepth = "0";

var marker;
var geocoder = null;
var holemode = null;

var shapevalue1 = new Array();
var shapevalue2 = new Array();
var shapevalue3 = new Array();
var shapevalue4 = new Array();
var shapevalue5 = new Array();
var shapevalue6 = new Array();
var shapevalue7 = new Array();

//var colormode = null;

var fillColor = "#FF0000"; // red fill
var fillColorcur = "#FF0000";

var lineColor = "#FF0000";  // red line
var lineColorcur = "#FF0000";

var polygonlineColor = "#FF0000";
var polygonlineColorcur = "#FF0000";

var lineColor2 = "#000000"; // black line
var lineopacity = .4;
var lineopacitycur = .4;
var fillopacity = .4;
var fillopacitycur = .4;
var opacityhex = "66";
var opacityhexcur = "66";
var lineWeight = 3;
var lineWeightcur = 3;
var polygonLineWeight = 0.1;
var polygonLineWeightcur = 0.1;

var kmlFillColor = "660000FF";
var kmlFillColorcur = "660000FF";
var kmlLineColor = "660000FF";
var kmlLineColorcur = "660000FF";
var kmlpolygonLineColor = "660000FF";
var kmlpolygonLineColorcur = "660000FF";

var centerMarker = null;
var radiusMarker = null;
var rectangleMarkerTL = null;
var rectangleMarkerBR = null;
var btnShapeFill = false;

var opchoice = "polygon";

var firstpolygon = "";
var childwidth = 565;
var childheight = 310;
var childbordercolor = "#337EB7";
var childbackgroundcolor = "#ffffff";

function load() {
	if (GBrowserIsCompatible()) {
		GDraggableObject.setDraggableCursor('default');
		GDraggableObject.setDraggingCursor('pointer');
		map = new GMap2(document.getElementById("map"));
		//map = new GMap2(document.getElementById("map"), {draggableCursor: 'default', draggingCursor: 'pointer'});
		map.setCenter(new GLatLng(45.0,7.0), 3);
		map.addControl(new GLargeMapControl());
		map.addControl(new GMapTypeControl());
		//map.addMapType(G_PHYSICAL_MAP);
		//map.enableScrollWheelZoom();
		var pos = new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(10,40));
		map.addControl(new GScaleControl(),pos);
		mylistener = GEvent.addListener(map, 'click', mapClick);
		//TODO later use for drag rectangle
		//eventOnMoveListener= GEvent.addListener(map, 'mousemove', mapDragRectangle); 
		geocoder = new GClientGeocoder();
		GEvent.addListener(map, "mousemove", function(point){
			var LnglatStr6 = point.lng().toFixed(6) + ', ' + point.lat().toFixed(6);
			var latLngStr6 = point.lat().toFixed(6) + ', ' + point.lng().toFixed(6);
			$('#maplonlatvalue').html(LnglatStr6);
		});
		GEvent.addListener(map, "zoomend", map_Zoom);
		//GEvent.addListener(map, "moveend", mapcenter);
		
		//load kml directly
		//$serverwfs."?typename=".$layers."&maxfeatures=100&SERVICE=WFS&VERSION=1.1.1&REQUEST=GetFeature&OUTPUTFORMAT=text/xml
		//var kml = new GGeoXml("http://mydomain.com/myfile.kml");
		//map.addOverlay(kml)
	}
	else{
		growlError("Your browser does not support Google Maps");
	}
	select(gisMode_Hand);
}

//mapClick - Handles the event of a user clicking anywhere on the map
//Adds a new point to the map and draws either a new line from the last point
//or a new polygon section depending on the drawing mode.
/*function mapClick(marker, clickedPoint) {
    if (marker == null) {
        if (holemode) {
            // Push onto currentPoints of existing poly, building inner boundary
            holePoints.push(clickedPoint);
        }else{
            currentPoints.push(clickedPoint);
        }
        drawCoordinates();
    }
}
 */

function map_Zoom(){
	var mapZoom = map.getZoom();
	$("#mapzoom").html(mapZoom);
}

function map_Center(){
	var mapCenter = map.getCenter();
	var latLngStr6 = mapCenter.lat().toFixed(6) + ', ' + mapCenter.lng().toFixed(6);
	growlInfo("Map center is " + latLngStr6);
}

function mapClick(marker, clickedPoint) {
	var mapNormalProj = G_NORMAL_MAP.getProjection();
	var mapZoom = map.getZoom();
	var clickedPixel = mapNormalProj.fromLatLngToPixel(clickedPoint, mapZoom);
	var polyPixel = new GPoint(clickedPixel.x,clickedPixel.y);
	var polyPoint = mapNormalProj.fromPixelToLatLng(polyPixel,mapZoom);

	if(gisMode == gisMode_Point){
		if (clickedPoint) {
			geomPoints[shapeIndex]= clickedPoint;
			drawCoordinates();
		}
	}
	else if( gisMode == gisMode_Linestring ){
		if (polyPoint) {
			currentPoints.push(polyPoint);
			geomPoints[shapeIndex] = currentPoints;
			drawCoordinates();
		}
	}
	else if( gisMode == gisMode_Polygon ){
		if (polyPoint) {
			if (holemode) {
				// Push onto currentPoints of existing poly, building inner boundary
				holePoints.push(polyPoint);
			}else{
				currentPoints.push(polyPoint);
				geomPoints[shapeIndex] = currentPoints;
				//GLog.write(shapeIndex);
			}
			drawCoordinates();
		}
	}
}

/**
//click through the polygon
map.addOverlay(poly);
GEvent.addListener(map,"click",function(ov,point){if (point) alert(point.toUrlValue())});
GEvent.addListener(poly,"click",function(point){if (point) GEvent.trigger(map,"click",null,point)});
*/

function shapeObj(geomShape,index,id, attribute)
{
	this.geomShape=geomShape;
	this.index=id;
	this.id=id;
	this.attribute=attribute;
}


//drawCoordinates
function drawCoordinates(){
	if (currentPoints.length > 0 || geomPoints[shapeIndex]){
		//process for help-marker overlay
		if( gisMode == gisMode_Rectangle || gisMode == gisMode_RectangleFill){
			if(rectangleMarkerTL && !rectangleMarkerBR){
				return;
			}else{
				map.removeOverlay(centerMarker); 
			}
		}
		else{
			map.clearOverlays();
		}
		
		if (gisMode == gisMode_Point){
			//var color = getColor(true);
			geomShapes[shapeIndex] = new GMarker(geomPoints[shapeIndex], {/*icon: getIcon(color),*/ draggable: true});
		}
		else if(gisMode == gisMode_Linestring 
				|| (gisMode == gisMode_Circle) 
				|| (gisMode == gisMode_Rectangle)){
			geomShapes[shapeIndex] = new GPolyline(currentPoints,lineColorcur,lineWeightcur,lineopacitycur);
		}
		else if (gisMode == gisMode_Polygon 
				|| (gisMode == gisMode_CircleFill) 
				|| (gisMode == gisMode_RectangleFill)) {
			geomShapes[shapeIndex] = new GPolygon(currentPoints,polygonlineColorcur,polygonLineWeightcur, lineopacitycur,fillColorcur,fillopacitycur);
		} 
		
		/**
		 * ( (gisMode == gisMode_Hand || gisMode == gisMode_Point 
				|| gisMode == gisMode_Circle || gisMode == gisMode_Rectangle
				|| gisMode == gisMode_CircleFill || gisMode == gisMode_RectangleFill)
				?geomShapes.length:geomShapes.length-1)
		 */
		//GLog.write('geomShapes length: '+  geomShapes.length);
		for(var i=0;i<geomShapes.length ;i++){
			if(gisModes[i] == gisMode_Point){
				map.addOverlay(geomShapes[i]);
				GEvent.addListener(geomShapes[i], "dragend", function() {
					//updateMarker(marker, cells);
				});
				/*GEvent.addListener(geomShapes[i], "click", function() {
				});*/
				geomShapes[i].bindInfoWindowHtml('id: '+shapeIndex);
			}
			else if(gisModes[i] == gisMode_Linestring || gisModes[i] == gisMode_Circle || gisModes[i] == gisMode_Rectangle){
				map.addOverlay(geomShapes[i]);
				geomShapes[i].enableEditing({onEvent: "mouseover"});
				geomShapes[i].disableEditing({onEvent: "mouseout"});
				GEvent.addListener(geomShapes[i], 'lineupdated', function() {
					//updateCoordinates(geomShapes[i], i);
				});
				GEvent.addListener(geomShapes[i], "click", function(latlng) {
					//GLog.write('id: '+  latlng);
				});
			}
			else if(gisModes[i] == gisMode_Polygon || gisModes[i] == gisMode_CircleFill || gisModes[i] == gisMode_RectangleFill){
				map.addOverlay(geomShapes[i]);
				geomShapes[i].enableEditing({onEvent: "mouseover"});
				geomShapes[i].disableEditing({onEvent: "mouseout"});
				GEvent.addListener(geomShapes[i], 'lineupdated', function() {
					//updateCoordinates(currentPoints, i);
				});
				GEvent.addListener(geomShapes[i], "click", function(latlng) {
					//GLog.write('id: '+  latlng);
				});
			}
		}
		
		
		if(gisMode == gisMode_Point){
			var shape = geomShapes[shapeIndex];
			//var shapeobj = new shapeObj(shape,shapeIndex , 0 , "");
			GEvent.addListener(shape, "dragend", function() {
				//updateMarker(marker, cells);
			});
			shape.bindInfoWindowHtml('id: '+shapeIndex); 
			map.addOverlay(shape);
			
			gisModes[shapeIndex] = gisMode_Point;
			shapeIndex++;
			select(gisMode_Hand);
		}
		else if(gisMode == gisMode_Linestring){	
			/*marker = new GMarker(currentPoints[0]); // marker always at startpoint
			map.addOverlay(marker);
			*/
			map.addOverlay(geomShapes[shapeIndex]);
			//geomShapes[shapeIndex].enableDrawing(options);
			geomShapes[shapeIndex].enableEditing({onEvent: "mouseover"});
			geomShapes[shapeIndex].disableEditing({onEvent: "mouseout"});
			GEvent.addListener(geomShapes[shapeIndex], 'lineupdated', function() {
				//updateCoordinates(geomShapes[shapeIndex], i);
			});
			GEvent.addListener(geomShapes[shapeIndex], "click", function(latlng) {
				//GLog.write('latlng: '+  latlng);
			});
			
			gisModes[shapeIndex] = gisMode_Linestring;			
			logCoordinates();
		}
		else if(gisMode == gisMode_Polygon){			
			/*marker = new GMarker(currentPoints[0]); // marker always at startpoint
			map.addOverlay(marker);
			if (holePoints.length > 0){
				marker = new GMarker(holePoints[0]); // marker always at startpoint
				map.addOverlay(marker);
			}*/

			map.addOverlay(geomShapes[shapeIndex]);
			//geomShapes[shapeIndex].enableDrawing(options);
			geomShapes[shapeIndex].enableEditing({onEvent: "mouseover"});
			geomShapes[shapeIndex].disableEditing({onEvent: "mouseout"});
			GEvent.addListener(geomShapes[shapeIndex], 'lineupdated', function() {
				//updateCoordinates(currentPoints, i);
			});
			GEvent.addListener(geomShapes[shapeIndex], "click", function(latlng) {
				//GLog.write('id: '+  latlng);
			});
			
			gisModes[shapeIndex] = gisMode_Polygon;
			logCoordinates();
		}
		else if(gisMode == gisMode_Circle || gisMode == gisMode_CircleFill){	
			map.addOverlay(geomShapes[shapeIndex]);
			//geomShapes[shapeIndex].enableDrawing(options);
			geomShapes[shapeIndex].enableEditing({onEvent: "mouseover"});
			geomShapes[shapeIndex].disableEditing({onEvent: "mouseout"});
			GEvent.addListener(geomShapes[shapeIndex], 'lineupdated', function() {
				//updateCoordinates(currentPoints, i);
			});
			GEvent.addListener(geomShapes[shapeIndex], "click", function(latlng) {
				//GLog.write('id: '+  latlng);
			});
			
			gisModes[shapeIndex] = gisMode;	
			logCoordinates();
			nextshape();
		}
		else if(gisMode == gisMode_Rectangle || gisMode == gisMode_RectangleFill){
			map.addOverlay(geomShapes[shapeIndex]);
			
			logCoordinates();
			nextshape();
		}
		
	}
}

/**
 * update the coordinates after editing
 * @return
 */
function updateCoordinates(geomShape, index){
	GLog.write('updateCoordinates '+ geomShape);
	if( geomShape != undefined ){
		var j = geomShape.getVertexCount(); // get the amount of points 
		GLog.write('j '+ j);
		for (var i = 0; i<j; i++) {
			geomPoints[index][i] = geomShape.getVertex(i);
		}
		
		drawCoordinates();
		logCoordinates();
	}
}

/**
 * not use now
 * @return
 */
function stopEditing(){
	select(gisMode_Hand);
	GEvent.removeListener(editlistener);
	//polyShape.disableEditing();
	editing = false;
	if (polyShapeHole != null){
		GEvent.removeListener(holelistener);
		//polyShapeHole.disableEditing();
		holeediting = false;
	}
}

//logCoordinates - prints out coordinates of global currentPoints (and holePoints) array
//This version logs KML or only coordinates, but could be extended to log different types of output
function logCoordinates(){
	if (false)//document.getElementById("javascript").checked
	{
		showCode();
	} else {
		var j;
		var k;
		document.getElementById("coords").value =  ""; // erase content in coords window

		j = currentPoints.length; // get the amount of points
		if (holePoints.length > 0){
			k = holePoints.length; // get the amount of points
			polygonLineWeightcur = 0;
		}
		if (headfoot){
			// this is the upper part of the KML code
			var header = '<?xml version="1.0" encoding="UTF-8"?>\n' +
			'<kml xmlns="http://earth.google.com/kml/2.2">\n' +
			'<Document><name>kml-file with polygon for bird species range</name>\n' +
			'<description>Source various books and websites</description>\n' +

			'<Style id="rangecolour">\n' +
			'<LineStyle><color>'+kmlpolygonLineColorcur+'</color><width>'+polygonLineWeightcur+'</width></LineStyle>\n' +
			'<PolyStyle><color>'+kmlFillColorcur+'</color></PolyStyle>\n' +
			'</Style>\n' +

			'<Style id="linecolour">\n' +
			'<LineStyle><color>'+kmlLineColorcur+'</color><width>'+lineWeightcur+'</width></LineStyle>\n' +
			'</Style>\n';
		}
		// check mode
		if (gisMode == gisMode_Polygon){ // print polygon
			if (headfoot){
				header += '<Placemark><name>distribution</name>\n' +
				'<description></description>\n' +
				'<styleUrl>#rangecolour</styleUrl>\n' +
				'<Polygon>\n<tessellate>1</tessellate><altitudeMode>clampToGround</altitudeMode>\n' +
				'<outerBoundaryIs><LinearRing><coordinates>\n';
				var footer = '</Polygon>\n</Placemark>\n</Document>\n</kml>';
				// print coords header
				document.getElementById("coords").value =  header;
			}
			// loop to print coords within the outerBoundaryIs code
			// coordinates are printed with a maximum of 6 decimal places, function roundVal takes care of this
			for (var i = 0; i<j; i++) {
				var lat = currentPoints[i].lat();
				var longi = currentPoints[i].lng();
				document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth + "\n";
				//firstpolygon += "\n" + roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth;
			}
			if (headfoot){
				document.getElementById("coords").value +="</coordinates></LinearRing></outerBoundaryIs>\n";
			}
			if (holemode){
				if (headfoot){
					document.getElementById("coords").value +="<innerBoundaryIs><LinearRing><coordinates>\n";
				}
				// loop to print inner boundary coords
				if (holePoints.length > 0){
					for (var i = 0; i<k; i++) {
						var lat = holePoints[i].lat();
						var longi = holePoints[i].lng();
						//document.getElementById("coords").value += longi + "," + lat + "," + polygonDepth + "\n";
						document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth + "\n";
					}
				}
				if (headfoot){
					document.getElementById("coords").value +="</coordinates></LinearRing></innerBoundaryIs>\n";
				}
			}
		} else if(gisMode == gisMode_Linestring){ // print polyline(s)
			if (holemode){ // print a polygon with hole, on the map the shapes are shown as lines, but
				// the kml will be printed as a polygon with a hole, outerBoundaryIs with coordinates
				// and innerBoundaryIs with coordinates
				if (headfoot){
					header += '<Placemark><name>distribution</name>\n' +
					'<description></description>\n' +
					'<styleUrl>#rangecolour</styleUrl>\n' +
					'<Polygon>\n<tessellate>1</tessellate><altitudeMode>clampToGround</altitudeMode>\n' +
					'<outerBoundaryIs><LinearRing><coordinates>\n';
					var footer = "</Polygon>\n</Placemark>\n</Document>\n</kml>";
					// print coords header
					document.getElementById("coords").value =  header;
				}
				// loop to print outer boundary coords
				for (var i = 0; i<j; i++) {
					var lat = currentPoints[i].lat();
					var longi = currentPoints[i].lng();
					document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth + "\n";
				}
				if (headfoot){
					document.getElementById("coords").value +="</coordinates></LinearRing></outerBoundaryIs>\n" +
					"<innerBoundaryIs><LinearRing><coordinates>\n";
				}
				// loop to print inner boundary coords
				if (holePoints.length > 0){
					for (var i = 0; i<k; i++) {
						var lat = holePoints[i].lat();
						var longi = holePoints[i].lng();
						document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth + "\n";
					}
				}
				if (headfoot){
					document.getElementById("coords").value +="</coordinates></LinearRing></innerBoundaryIs>\n";
				}

			}else{ // print single polyline
				if (headfoot){
					header += '<Placemark><name>distribution</name>\n' +
					'<description></description>\n' +
					'<styleUrl>#linecolour</styleUrl>\n' +
					'<LineString>\n<tessellate>1</tessellate><altitudeMode>clampToGround</altitudeMode>\n<coordinates>\n';
					var footer = '</coordinates>\n</LineString>\n</Placemark>\n</Document>\n</kml>';
					// print coords header
					document.getElementById("coords").value =  header;
				}
				for (var i = 0; i<j; i++) {
					var lat = currentPoints[i].lat();
					var longi = currentPoints[i].lng();
					document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + ",0\n";
				}
			}
		}
		if (headfoot){
			document.getElementById("coords").value +=  footer;
			//document.getElementById("coords").value +=  firstpolygon;
		}
	}
}

function nextshape() {
	if(currentPoints.length==0){
		return;
	}

	shapevalue1[shapeIndex] = polygonlineColorcur;
	shapevalue2[shapeIndex] = polygonLineWeightcur;
	shapevalue3[shapeIndex] = lineopacitycur;
	shapevalue4[shapeIndex] = fillColorcur;
	shapevalue5[shapeIndex] = fillopacitycur;
	shapevalue6[shapeIndex] = lineColorcur;
	shapevalue7[shapeIndex] = lineWeightcur;

	if (gisMode == gisMode_Point) {
		gisModes[shapeIndex] = gisMode_Point;
	}
	else if (gisMode == gisMode_Linestring) {
		gisModes[shapeIndex] = gisMode_Linestring;
		geomPoints[shapeIndex] = currentPoints;
		shapeIndex++;
	}
	else if (gisMode == gisMode_Polygon) {
		gisModes[shapeIndex] = gisMode_Polygon;
		geomPoints[shapeIndex] = currentPoints;
		shapeIndex++;
	}
	else if (gisMode == gisMode_Circle || gisMode == gisMode_CircleFill) {
		GEvent.removeListener(mylistener);
		centerMarker = null;
		radiusMarker = null;
		mylistener = GEvent.addListener(map, 'click', mapClick);
	}
	else if (gisMode == gisMode_Rectangle || gisMode == gisMode_RectangleFill) {

	}
	currentPoints = [];
	btnShapeFill = false;
	select(gisMode_Hand);
}

/**
 * prepare the data in kml format and wait for uploading
 * @return
 */

function prepareData(layername){
	$("#coords").val(''); // erase content in coords window
	var activePoints = new Array();
	var n = geomShapes.length;
	if(n==0)return;
	GLog.write(n + ' geometries will be saved.');
	
	if (headfoot){
		// this is the upper part of the KML code
		var header = '<?xml version="1.0" encoding="UTF-8"?>\n' +
		'<kml xmlns="http://earth.google.com/kml/2.2">\n' +
		'<Document><name>geoedit</name>\n' +
		'<description>SUAS geoedit upload kml data</description>\n' +

		'<Style id="rangecolour">\n' +
		'<LineStyle><color>'+kmlpolygonLineColorcur+'</color><width>'+polygonLineWeightcur+'</width></LineStyle>\n' +
		'<PolyStyle><color>'+kmlFillColorcur+'</color></PolyStyle>\n' +
		'</Style>\n' +

		'<Style id="linecolour">\n' +
		'<LineStyle><color>'+kmlLineColorcur+'</color><width>'+lineWeightcur+'</width></LineStyle>\n' +
		'</Style>\n';
		document.getElementById("coords").value +=  header;
	}

	document.getElementById("coords").value +=  '<Folder>';

	for(var m=0;m<n;m++){
		activePoints = geomPoints[m];
		
		var j = 0;
		// check mode
		if(gisModes[m] == gisMode_Point){ // print point
			var lat = activePoints.lat();
			var longi = activePoints.lng();

			if (headfoot){
				header = '<Placemark><name>'+layername+'</name>\n' +
				'<description></description>\n' +
				'<styleUrl>#linecolour</styleUrl>\n' +
				'<LookAt>\n' +
				'<longitude>'+roundVal(longi)+'</longitude>\n' +
				'<latitude>'+roundVal(lat)+'</latitude>\n' +
				'<altitude>0</altitude>\n' +
				//'<range>132.1940477027328</range>\n' +
				//'<tilt>0</tilt>\n' +
				//'<heading>0.0005668054794107561</heading>\n' +
				//'<altitudeMode>relativeToGround</altitudeMode>\n' +
				'</LookAt>'+
				'<styleUrl>#linecolour</styleUrl>\n' +
				'<Point>\n<coordinates>\n';

				var footer = '</coordinates>\n</Point>\n</Placemark>';
				// print coords header
				document.getElementById("coords").value +=  header;
			}
			document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + ",0\n";
			if (headfoot){
				document.getElementById("coords").value += footer;
			}
		}
		// print polyline(s)
		else if(gisModes[m] == gisMode_Linestring 
			|| gisModes[m] == gisMode_Circle  || gisModes[m] == gisMode_Rectangle){ 
			j = activePoints.length;
			if (headfoot){
				header = '<Placemark><name>'+layername+'</name>\n' +
				'<description></description>\n' +
				'<styleUrl>#linecolour</styleUrl>\n' +
				'<LineString>\n<tessellate>1</tessellate><altitudeMode>clampToGround</altitudeMode>\n<coordinates>\n';

				var footer = '</coordinates>\n</LineString>\n</Placemark>';
				// print coords header
				document.getElementById("coords").value +=  header;
			}
			for (var i = 0; i<j; i++) {
				var lat = activePoints[i].lat();
				var longi = activePoints[i].lng();
				document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + ",0\n";
			}
			if (headfoot){
				document.getElementById("coords").value += footer;
			}

		}
		// print polygon
		else if (gisModes[m] == gisMode_Polygon
			|| gisModes[m] == gisMode_CircleFill  || gisModes[m] == gisMode_RectangleFill){ 
			j = activePoints.length;
			GLog.write('points length: '+j);
			if (headfoot){
				header = '<Placemark><name>'+layername+'</name>\n' +
				'<description></description>\n' +
				'<styleUrl>#rangecolour</styleUrl>\n' +
				'<Polygon>\n<tessellate>1</tessellate><altitudeMode>clampToGround</altitudeMode>\n' +
				'<outerBoundaryIs><LinearRing><coordinates>\n';

				var footer = '</coordinates></LinearRing>\n</outerBoundaryIs>\n</Polygon>\n</Placemark>';
				// print coords header
				document.getElementById("coords").value +=  header;
			}
			// loop to print coords within the outerBoundaryIs code
			// coordinates are printed with a maximum of 6 decimal places, function roundVal takes care of this
			for (var i = 0; i<j; i++) {
				var lat = activePoints[i].lat();
				var longi = activePoints[i].lng();
				document.getElementById("coords").value += roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth + "\n";
				//firstpolygon += "\n" + roundVal(longi) + "," + roundVal(lat) + "," + polygonDepth;
			}
			if (headfoot){
				document.getElementById("coords").value += footer;
			}
		} 

	}
	document.getElementById("coords").value +=  '</Folder>';
	if (headfoot){
		document.getElementById("coords").value +=  '\n</Document>\n</kml>';
	}
}

//let start and end meet
function closePolyline() {
	if (gisMode != gisMode_Circle && gisMode != gisMode_CircleFill){ // in circlemode this has been done in function drawCircle
		// Push onto currentPoints of existing polyline
		if (holemode) {
			if (holePoints.length > 2){
				holePoints.push(holePoints[0]);
				drawCoordinates();
			}
		}else{
			// Push onto currentPoints of existing polyline/polygon
			if(gisMode == gisMode_Linestring){
				if (currentPoints.length > 2){
					currentPoints.push(currentPoints[0]);
					drawCoordinates();
				}
			}
		}
	}
}

//Clear current Map
function clearMap(){
	map.clearOverlays();
	polyShapeHole = null;
	centerMarker = null;
	radiusMarker = null;
	currentPoints = [];
	holePoints = [];
	geomPoints = [];
	geomShapes = [];
	btnShapeFill = false;

	shapeIndex = 0;
	//document.getElementById("nohole").checked = false;

	gisMode = gisMode_Hand;
	holemode = null;
}

function select(gisMode_) {
	gisMode = gisMode_;
	var buttonId = null;
	switch(gisMode_){
	case gisMode_Hand: buttonId = "hand_b";break;
	case gisMode_Point: buttonId = "point_b";break;
	case gisMode_Linestring: buttonId = "linestring_b";break;
	case gisMode_Polygon: buttonId = "polygon_b";break;
	case gisMode_Circle: buttonId = "circle_b";break;
	case gisMode_Rectangle: buttonId = "rectangle_b";break;
	case gisMode_CircleFill: buttonId = "circlef_b";break;
	case gisMode_RectangleFill: buttonId = "rectanglef_b";break;
	default: buttonId = "hand_b";
	}
	$("#hand_b").attr("class", "unselected");
	$("#point_b").attr("class", "unselected");
	$("#linestring_b").attr("class", "unselected");
	$("#polygon_b").attr("class", "unselected");
	$("#circle_b").attr("class", "unselected");
	$("#rectangle_b").attr("class", "unselected");
	$("#circlef_b").attr("class", "unselected");
	$("#rectanglef_b").attr("class", "unselected");

	$("#"+buttonId).attr("class", "selected");
}


var COLORS = [["red", "#ff0000"], ["orange", "#ff8800"], ["green","#008000"],
              ["blue", "#000080"], ["purple", "#800080"]];
var colorIndex_ = 0;

function getColor(named) {
	return COLORS[(colorIndex_++) % COLORS.length][named ? 0 : 1];
}

function getIcon(color) {
	var icon = new GIcon();
	icon.image = "http://google.com/mapfiles/ms/micons/" + color + ".png";
	icon.iconSize = new GSize(32, 32);
	icon.iconAnchor = new GPoint(15, 32);
	return icon;
}


function drawPoint() {
	select(gisMode_Point);
	/*var listener = GEvent.addListener(map, "click", function(overlay, latlng) {
		if (latlng) {
			select(gisMode_Hand);
			GEvent.removeListener(listener);
			var color = getColor(true);
			var marker = new GMarker(latlng, {icon: getIcon(color), draggable: true});
			map.addOverlay(marker);
			//var cells = addFeatureEntry("Placemark " + (++markerCounter_), color);
			//updateMarker(marker, cells);
			GEvent.addListener(marker, "dragend", function() {
				//updateMarker(marker, cells);
			});
			GEvent.addListener(marker, "click", function() {
				//updateMarker(marker, cells, true);
			});		
		}
	});*/
}

function drawLinestring() {
	select(gisMode_Linestring);
	var color = getColor(false);
	var line = new GPolyline([], color);
	/*	startDrawing(line, "Line " + (++lineCounter_), function() {
		var cell = this;
		var len = line.getLength();
		cell.innerHTML = (Math.round(len / 10) / 100) + "km";
	}, color);*/
	if (currentPoints.length > 0){
		drawCoordinates();
	}
}

function drawPolygon() {
	select(gisMode_Polygon);
	var color = getColor(false);
	var polygon = new GPolygon([], color, 2, 0.7, color, 0.2);
	/*	startDrawing(polygon, "Polygon " + (++shapeCounter_), function() {
		var cell = this;
		var area = polygon.getArea();
		cell.innerHTML = (Math.round(area / 10000) / 100) + "km<sup>2</sup>";
	}, color);*/
	if (currentPoints.length > 0){
		drawCoordinates();
	}
}

function startDrawing(poly, name, onUpdate, color) {
	map.addOverlay(poly);
	poly.enableDrawing(options);
	poly.enableEditing({onEvent: "mouseover"});
	poly.disableEditing({onEvent: "mouseout"});
	GEvent.addListener(poly, "endline", function() {
		select("hand_b");
		var cells = addFeatureEntry(name, color);
		GEvent.bind(poly, "lineupdated", cells.desc, onUpdate);
		GEvent.addListener(poly, "click", function(latlng, index) {
			if (typeof index == "number") {
				poly.deleteVertex(index);
			} else {
				var newColor = getColor(false);
				cells.color.style.backgroundColor = newColor
				poly.setStrokeStyle({color: newColor, weight: 4});
			}
		});
	});
}



/**
 * 
 * not use now
 * @param marker
 * @param cells
 * @param opt_changeColor
 * @return
 */
function updateMarker(marker, cells, opt_changeColor) {
	if (opt_changeColor) {
		var color = getColor(true);
		marker.setImage(getIcon(color).image);
		cells.color.style.backgroundColor = color;
	}
	var latlng = marker.getPoint();
	cells.desc.innerHTML = "(" + Math.round(latlng.y * 100) / 100 + ", " +
	Math.round(latlng.x * 100) / 100 + ")";
}


/**
 * 
 * @param fill, fill with color?
 * @return
 */
function drawRectange(fill){
	btnShapeFill = fill;
	rectangleMarkerTL = null;
	rectangleMarkerBR = null;
	centerMarker = null;
	if(fill){
		select(gisMode_RectangleFill);
	}else{
		select(gisMode_Rectangle);	
	}
	GEvent.removeListener(mylistener);
	
	if (gisMode == gisMode_Rectangle || gisMode == gisMode_RectangleFill){
		mylistener = GEvent.addListener(map, "click", mapClickRectangle)
	}else{
		mylistener = GEvent.addListener(map, 'click', mapClick);
	}
}

function mapClickRectangle(overlay,Point){
    if (!rectangleMarkerTL){   
    	// First click
    	rectangleMarkerTL = Point; // first corner
		centerMarker = new GMarker(Point,{title:"Start"});
		map.addOverlay(centerMarker);
		drawCoordinates();
    }
    else{
        if(!btnShapeFill){
    		gisModes[shapeIndex] = gisMode_Rectangle;
	}else{
    		gisModes[shapeIndex] = gisMode_RectangleFill;
	}
    	// Second click

        rectangleMarkerBR = Point; //second corner
        
        currentPoints.push( rectangleMarkerTL );
        currentPoints.push(new GPoint( rectangleMarkerTL.x, rectangleMarkerBR.y ));
        currentPoints.push( rectangleMarkerBR );
        currentPoints.push(new GPoint( rectangleMarkerBR.x, rectangleMarkerTL.y ));
        if(!btnShapeFill){
        	currentPoints.push( rectangleMarkerTL );
        }
        
        geomPoints[shapeIndex] = currentPoints;
        drawCoordinates();
		shapeIndex++;
        
        GEvent.removeListener(mylistener);
        mylistener = GEvent.addListener(map, 'click', mapClick);
        select(gisMode_Hand);
    }
} 

/**
 * can not use, because rectangleMarkerTL has been set to null
 * @param point
 * @return
 */
function mapDragRectangle(point)
{
    if (gisMode ==  gisMode_Hand)
    {
    	rectangleMarkerBR = Point; //second corner
    	
        currentPoints.push( rectangleMarkerTL );
        currentPoints.push(new GPoint( rectangleMarkerTL.x, rectangleMarkerBR.y ));
        currentPoints.push( rectangleMarkerBR );
        currentPoints.push(new GPoint( rectangleMarkerBR.x, rectangleMarkerTL.y ));
        currentPoints.push( rectangleMarkerTL );
        
        geomPoints[shapeIndex] = currentPoints;
        
        drawCoordinates();
    }

} 

/**
 * 
 * @param fill, fill with color
 * @return
 */
function drawCircle(fill){
	btnShapeFill = fill;
	if(fill){
		select(gisMode_CircleFill);
	}else{
		select(gisMode_Circle);	
	}
	GEvent.removeListener(mylistener);
	centerMarker = null;
	radiusMarker = null;

	if (gisMode == gisMode_Circle || gisMode == gisMode_CircleFill){
		mylistener = GEvent.addListener(map, 'click', mapClickCircle);
		if (holemode){
			drawCoordinates();
		}
	}else{
		mylistener = GEvent.addListener(map, 'click', mapClick);
		if (holemode){
			drawCoordinates();
		}
	}
}


function mapClickCircle(marker, point) {
	if (!centerMarker) {
		centerMarker = new GMarker(point,{title:"Start"});
		map.addOverlay(centerMarker);
	}
	else if (!radiusMarker){
		radiusMarker = point;
		processDrawCircle(); // fill the currentPoints array with all the points needed to draw a circle
		drawCoordinates();
		shapeIndex++;
	}
}

function processDrawCircle(){
	var zoom = map.getZoom();
	var normalProj = G_NORMAL_MAP.getProjection();
	var centerPt = normalProj.fromLatLngToPixel(centerMarker.getPoint(), zoom);
	var radiusPt = normalProj.fromLatLngToPixel(radiusMarker, zoom);
	with (Math) {
		var radius = floor(sqrt(pow((centerPt.x-radiusPt.x),2) + pow((centerPt.y-radiusPt.y),2)));
		for (var i = 0 ; i < 361 ; i+=10 ) {
			var aRad = i*(PI/180);
			y = centerPt.y + radius * sin(aRad)
			x = centerPt.x + radius * cos(aRad)
			var p = new GPoint(x,y);
			if (holemode){
				holePoints.push(normalProj.fromPixelToLatLng(p, zoom));
			}else{
				currentPoints.push(normalProj.fromPixelToLatLng(p, zoom));
			}
		}
		
		geomPoints[shapeIndex] = currentPoints;
		if(btnShapeFill){
			gisMode[shapeIndex] = gisMode_CircleFill;		
		}else{
			gisMode[shapeIndex] = gisMode_Circle;
		}
	}
}

//Delete last Point
//This function removes the last point from the Polyline/Polygon and redraws
//map.
function deleteLastPoint(){
	if (gisMode != gisMode_Circle && gisMode != gisMode_Rectangle
		&& gisMode != gisMode_CircleFill && gisMode != gisMode_RectangleFill){ // do not allow delete last point in a circle
		if (!holemode){
			// pop last element of currentPoints array
			currentPoints.pop();
			drawCoordinates();
		}else{
			map.removeOverlay(polyShapeHole);
			// pop last element of holePoints array
			holePoints.pop();
			drawCoordinates();
		}
	}
}

function loacateAddress(address) {
	if (geocoder) {
		geocoder.getLatLng(address,
				function(point) {
			if (!point) {
				growlError("'"+address + "' can not be found");
			} else {
				var mapZoom = map.getZoom();
				map.setCenter(point, mapZoom);
				// Create our "tiny" marker icon
				var tinyIcon = new GIcon();
				tinyIcon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
				tinyIcon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
				tinyIcon.iconSize = new GSize(12, 20);
				tinyIcon.shadowSize = new GSize(22, 20);
				tinyIcon.iconAnchor = new GPoint(6, 20);
				tinyIcon.infoWindowAnchor = new GPoint(5, 1);
				// Set up our GMarkerOptions object literal
				markerOptions = { icon:tinyIcon };
				var centerpoint = new GMarker(point, markerOptions);
				map.addOverlay(centerpoint);
			}
		}
		);
	}
}

//the copy part may not work with all web browsers
function copyTextarea(){
	document.getElementById("coords").focus();
	document.getElementById("coords").select();
	copiedTxt = document.selection.createRange();
	copiedTxt.execCommand("Copy");
}

function roundVal(val){
	if (val.toString().length < 9){
		return val;
	}else{
		var dec = 6;
		var result = Math.round(val*Math.pow(10,dec))/Math.pow(10,dec);
		return result;
	}
}

function managechildcreation()
{
	var wc = new getchildwindowposition();
	createchild(wc.left,wc.top);
}

function getchildwindowposition()
{
	var info = new getparentwindowsize();
	xleft = ((info.clientWidth - childwidth)/2) + " ";
	xleft = parseInt(xleft);
	ytop =  ((info.clientHeight - childheight)/2) + " ";
	ytop = parseInt(ytop);
	this.left = xleft;
	this.top = ytop;
}

function getparentwindowsize() {
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	this.clientHeight = myHeight;
	this.clientWidth = myWidth;
}

function createchild(left,top)
{
	objId="child";
	var newdiv = document.createElement('div');
	newdiv.id = objId;
	document.body.appendChild(newdiv);
	childwindowstyle(objId,left,top);
}

function childwindowstyle(objId, left, top)
{
	var obj = document.getElementById(objId);
	obj.style.position = "absolute";
	obj.style.left = left +"px";
	obj.style.top = top +"px";
	obj.style.width = childwidth +"px";
	obj.style.height = childheight + "px";
	obj.style.border = "2px solid " + childbordercolor;
	obj.style.background = childbackgroundcolor;
}

function color_html2kml(color)
{
	var newcolor ="ffffff";
	if(color.length == 7) {
		newcolor = color.substring(5,7)+color.substring(3,5)+color.substring(1,3);
	}
	return newcolor
}

function colortotext(bgColor)
{
	bgColor = bgColor.toUpperCase();
	var childhtmlcolor = document.infotextform.htmlc.value = ""+bgColor;
	var childkmlcolor = document.infotextform.kmlc.value = opacityhexcur + color_html2kml(""+bgColor);
	if (document.getElementById("childpolygonmaster").checked)
	{
		if (document.getElementById("childpolygonfield").checked)
		{
			fillColorcur = childhtmlcolor;
			kmlFillColorcur = childkmlcolor;
		} else {
			polygonlineColorcur = childhtmlcolor;
			kmlpolygonLineColorcur = childkmlcolor;
		}
	} else {
		lineColorcur = childhtmlcolor;
		kmlLineColorcur = childkmlcolor;
	}
	if (currentPoints.length > 0){
		drawCoordinates();
	}
}

//this function handles clicks on colours in child window (style manager window)
function setcellcolor()
{
	var obj = document.getElementById("currentcolor");
	var childcolorname = this.id;
	document.getElementById("currentcolorname").innerHTML = childcolorname;
	obj.style.backgroundColor = this.bgColor;
	colortotext(this.bgColor);
}

function addcellcolor(row,color,name)
{
	cell = document.createElement("td");
	cell.id = name;
	cell.setAttribute("Width",10);
	cell.setAttribute("Height",10);
	cell.setAttribute("bgColor",color);
	cell.onclick = setcellcolor;
	row.appendChild(cell);
}

function addrow(myTable,height)
{
	lastRow = myTable.rows.length;
	row = myTable.insertRow(lastRow);
	row.setAttribute("height",height);
	return row
}

function updateopathick()
{
	var opacityhexcurold = opacityhexcur;
	var lineWeightcurold = lineWeightcur;
	var lineopa = document.getElementById("kmlcinput").value;
	var fillopa = document.getElementById("kmlfillinput").value;
	var linethick = document.getElementById("htmlcinput").value;
	if (document.getElementById("childpolygonmaster").checked)
	{
		if (lineopa != lineopacitycur)
		{
			lineopacitycur = lineopa;
			opacityhexcur = getopacityhex(lineopa);
			kmlpolygonLineColorcur = opacityhexcur + color_html2kml(""+polygonlineColorcur);
			document.infotextform.kmlc.value = kmlpolygonLineColorcur;
		}
		if (fillopa != fillopacitycur)
		{
			fillopacitycur = fillopa;
			opacityhexcur = getopacityhex(fillopa);
			kmlFillColorcur = opacityhexcur + color_html2kml(""+fillColorcur);
			document.infotextform.kmlc.value = kmlFillColorcur;
		}
		if (linethick != polygonLineWeightcur)
		{
			polygonLineWeightcur = linethick;
		}
	} else {
		if (lineopa != lineopacitycur)
		{
			lineopacitycur = lineopa;
			opacityhexcur = getopacityhex(lineopa);
			kmlLineColorcur = opacityhexcur + color_html2kml(""+lineColorcur);
			document.infotextform.kmlc.value = kmlLineColorcur;
		}
		if (linethick != lineWeightcur)
		{
			lineWeightcur = linethick;
		}
	}
	if (currentPoints.length > 0){
		drawCoordinates();
	}
}

function getopacityhex(opa)
{
	var hexopa = opacityhexcur;
	if (opa == 0)
	{
		hexopa = "00";
	}
	if (opa == .0)
	{
		hexopa = "00";
	}
	if (opa == .1)
	{
		hexopa = "1a";
	}
	if (opa == .2)
	{
		hexopa = "33";
	}
	if (opa == .3)
	{
		hexopa = "4d";
	}
	if (opa == .4)
	{
		hexopa = "66";
	}
	if (opa == .5)
	{
		hexopa = "80";
	}
	if (opa == .6)
	{
		hexopa = "9a";
	}
	if (opa == .7)
	{
		hexopa = "b3";
	}
	if (opa == .8)
	{
		hexopa = "cd";
	}
	if (opa == .9)
	{
		hexopa = "e6";
	}
	if (opa == 1.0)
	{
		hexopa = "ff";
	}
	if (opa == 1)
	{
		hexopa = "ff";
	}
	return hexopa
}

function togglecolorMode()
{
	if (document.getElementById("childpolygonmaster").checked)
	{
		var obj = document.getElementById("currentcolor");
		document.getElementById("currentcolorname").innerHTML = "";
		if (document.getElementById("childpolygonfield").checked)
		{
			obj.style.backgroundColor = fillColorcur;
			document.infotextform.kmlc.value = kmlFillColorcur;
			document.infotextform.htmlc.value = fillColorcur;
		} else {
			obj.style.backgroundColor = polygonlineColorcur;
			document.infotextform.kmlc.value = kmlpolygonLineColorcur;
			document.infotextform.htmlc.value = polygonlineColorcur;
		}
	} else {
		document.getElementById("childpolygonfield").checked = false;
		document.getElementById("childpolylinefield").checked = false;
	}
}

function polylinetopolygon()
{
	var obj = document.getElementById("currentcolor");
	document.getElementById("currentcolorname").innerHTML = "";
	document.getElementById("childfield").checked = false;
	document.getElementById("childpolylinemaster").checked = false;
	document.getElementById("childpolygonfield").checked = true;
	document.getElementById("childpolylinefield").checked = false;
	document.getElementById("opandweight").innerHTML = "polygon";
	document.getElementById("kmlcinput").value = lineopacitycur;
	document.getElementById("htmlcinput").value = polygonLineWeightcur;
	document.getElementById("kmlfillinput").value = fillopacitycur;
	document.opacityandlinethicknessform.kmlfillinput.disabled = false;
	document.infotextform.kmlc.value = kmlFillColorcur;
	document.infotextform.htmlc.value = fillColorcur;
	obj.style.backgroundColor = fillColorcur;
}

function polygontopolyline()
{
	var obj = document.getElementById("currentcolor");
	document.getElementById("currentcolorname").innerHTML = "";
	document.getElementById("childfield").checked = true;
	document.getElementById("childpolygonmaster").checked = false;
	document.getElementById("childpolygonfield").checked = false;
	document.getElementById("childpolylinefield").checked = false;
	document.getElementById("opandweight").innerHTML = "polyline";
	document.getElementById("kmlcinput").value = lineopacitycur;
	document.getElementById("htmlcinput").value = lineWeightcur;
	document.getElementById("kmlfillinput").value = fillopacitycur;
	document.opacityandlinethicknessform.kmlfillinput.disabled = true;
	document.infotextform.kmlc.value = kmlLineColorcur;
	document.infotextform.htmlc.value = lineColorcur;
	obj.style.backgroundColor = lineColorcur;
}

function closechild()
{
	var obj = document.getElementById("child");
	document.body.removeChild(obj);
}

function restoredefaults()
{
	if (currentPoints.length > 0){
		fillColorcur = fillColor;
		lineColorcur = lineColor;
		polygonlineColorcur = polygonlineColor;
		lineopacitycur = lineopacity;
		fillopacitycur = fillopacity;
		opacityhexcur = opacityhex;
		lineWeightcur = lineWeight;
		polygonLineWeightcur = polygonLineWeight;
		kmlFillColorcur = kmlFillColor;
		kmlLineColorcur = kmlLineColor;
		kmlpolygonLineColorcur = kmlpolygonLineColor;
		opchoice = "polygon";
		drawCoordinates();
	}
}

//arrive here from Style Options button click
function createchildwindow()
{
	managechildcreation();

	var title = "<b>&nbsp; Style manager<b>";
	var childtitlecolor = "#337EB7";

	var obj = document.getElementById("child");

	var childwindow ='<div id = "titlefield" style='
		+ '"position: relative; background-color:'+childtitlecolor+'; color:white; left:2px; top:2px; width:'+(childwidth-4)+'px; height:18px;"/>'
		+ title +'</div>'
		+ '<div style="position:absolute; left:0px; top:24px; width:260px; height:200px; font-family:Arial,Helvetica,sans-serif; font-size:9pt; overflow-y:auto; overflow-x:hidden;"/>'
		+ '<div id="colorcells" style="position:absolute; left:2px; top:1px; width:'+(childwidth-4)+'px; height:'+(childheight-111)+'px;"/>'
		+ '<TABLE id="TblColors" cellSpacing=0 cellPadding=2 border=1 width=240 height=190 style="cursor:hand;" >'
		+ '</TABLE></div></div>'
		+ '<div id="currentcolor" style="position:absolute; left:10px; top:235px; width:20px; height:20px; background-color:'+fillColorcur+'; border: 1px solid;"/></div>'
		+ '<div id="currentcolorname" style="position:absolute; left:40px; top:235px; width:150px; height:35px;"/></div>'
		+ '<form name="infotextform">'
		+ '<div style="position:absolute; left:270px; top:24px; width:270px; height:55px; border:2px solid; border-color:' + childbordercolor +';"/>'
		+ '<table><tr><td colspan="2">'
		+ 'Polygon: <input type="checkbox" name="childPolyMode" id="childpolygonmaster" value="polygonmaster" onclick="polylinetopolygon();" checked="checked"/></td>'
		+ '</tr><tr>'
		+ '<td>Fill colour: <input type="radio" name="colorMode" id="childpolygonfield" value="childpolygon" onclick="togglecolorMode();" checked="checked"/></td>'
		+ '<td>Line colour: <input type="radio" name="colorMode" id="childpolylinefield" value="childpolyline" onclick="togglecolorMode();"/></td></tr>'
		+ '</table>'
		+ '</div>'
		+ '<div style="position:absolute; left:270px; top:85px; width:270px; height:24px;"/>'
		+ '<table><tr><td>HTML: <input type="text" name="htmlc" value="'+fillColorcur+'" style="width:80px" readonly /></td>'
		+ '<td>KML: <input type="text" name="kmlc" value="'+kmlFillColorcur+'" style="width:80px" readonly /></td></tr>'
		+ '</table>'
		+ '</div>'
		+ '<div style="position:absolute; left:270px; top:117px; width:270px; height:24px; border:2px solid; border-color:' + childbordercolor +';"/>'
		+ '<table><tr><td>'
		+ 'Polyline: <input type="checkbox" name="childPolyMode" id="childpolylinemaster" value="polylinemaster" onclick="polygontopolyline();"/></td>'
		+ '<td>Line colour: <input type="radio" name="colorMode" id="childfield" value="childpolyline"/></td></tr>'
		+ '</table>'
		+ '</div></form>'
		+ '<div style="position:absolute; left:270px; top:175px; width:270px; height:80px; border:2px solid; border-color:' + childbordercolor +'";/>'
		+ '<form name="opacityandlinethicknessform">'
		+ '<table><tr><td colspan="2">You may enter new values for <span id="opandweight">'+opchoice+'</span></td></tr>'
		+ '<tr><td>Line opacity: <input type="text" name="kmlcinput" value="'+lineopacitycur+'" style="width:25px" /></td>'
		+ '<td>Line thicknes: <input type="text" name="htmlcinput" value="'+polygonLineWeightcur+'" style="width:25px" /></td></tr>'
		+ '<tr><td>Fill opacity: <input type="text" name="kmlfillinput" value="'+fillopacitycur+'" style="width:25px" /></td>'
		+ '<td align="center"><input type="button" name="opathick" value="Apply" onClick="updateopathick();"/></td></tr>'
		+ '</table>'
		+ '</form></div>'
		+ '<div style="position:absolute; left:170px; top:275px; width:460px; height:35px;"/>'
		+ '<form name="buttonform">'
		+ '<input type="button" name="default" value="Restore defaults" onClick="restoredefaults();closechild();"/>'
		+ '&nbsp;&nbsp;<input type="button" name="close" value="Finished" onClick="closechild();"/>'
		+ '</form></div>';
	obj.innerHTML = childwindow;

	var colortable = document.getElementById("TblColors");
	var cellcolor = ['#000000','#FFFFFF','#FFFFF0','#C0C0C0',
	                 '#A9A9A9','#808080','#696969',
	                 '#2F4F4F','#8B0000','#DC143C',
	                 '#FF0000','#FF4500','#FF6347',
	                 '#FF7F50','#FF8040','#FF8C00',
	                 '#F5DEB3','#FFFF00','#FFD700',
	                 '#ADFF2F','#98FB98','#7CFC00',
	                 '#7FFF00','#00FF00','#00FF7F',
	                 '#32CD32','#228B22','#008000',
	                 '#006400','#556B2F','#808000',
	                 '#C71585','#DDA0DD','#CD5C5C',
	                 '#800000','#804000','#D2691E',
	                 '#B8860B','#CD853F','#F4A460',
	                 '#F0E68C','#BC8F8F','#EEE8AA',
	                 '#000080','#191970','#0000A0',
	                 '#0000CD','#0000FF','#6A5ACD',
	                 '#6495ED','#87CEFA','#ADD8E6',
	                 '#E0FFFF','#00FFFF','#FF69B4',
	                 '#FF00FF','#FF0080','#9932CC',
	                 '#8B008B','#800080','#4B0082',
	                 '#8D38C9','#9400D3'];
	var colorname = ['Black','White','Ivory','Silver',
	                 'Dark Grey','Grey','Dim Grey',
	                 'Dark Slate Gray','Dark Red','Crimson',
	                 'Red','Orange Red','Tomato',
	                 'Coral','Orange','Dark Orange',
	                 'Wheat','Yellow','Gold',
	                 'Green Yellow','Pale Green','Lawn Green',
	                 'Chartreuse','Lime','Spring Green',
	                 'Lime Green','Forest Green','Green',
	                 'Dark Green','Dark Olive Green','Olive',
	                 'Medium Violet Red','Plum','Indian Red',
	                 'Maroon','Brown','Chocolate',
	                 'Dark Goldenrod','Peru','Sandy Brown',
	                 'Khaki','Rosy Brown','Pale Goldenrod',
	                 'Navy','Midnight Blue','Dark Blue',
	                 'Medium Blue','Blue','Slate Blue',
	                 'Cornflower Blue','Light Sky Blue','Light Blue',
	                 'Light Cyan','Cyan','Hot Pink',
	                 'Fuchsia','Light Purple','Dark Orchid',
	                 'Dark Magenta','Purple','Indigo',
	                 'Violet','Dark Violet'];

	var cellarraylength = cellcolor.length;
	var row = addrow(colortable,"10");
	for(i=0; i<cellarraylength; i++) {
		if (i==9 || i==18 || i==27 || i==36 || i==45 || i==54)
		{
			row = addrow(colortable,"10");
		}
		addcellcolor(row,cellcolor[i],colorname[i]);
	}
}


function ExpandAllLayers(){
	for(var i=0;i<20;i++){
		if($('#srs_'+i)){
			$.tree_reference('panellayer').open_branch($('#srs_'+i));
		}else{
			return;
		}
	}
}

function CollapseAllLayers(){
	for(var i=0;i<20;i++){
		if($('#srs_'+i)){
			$.tree_reference('panellayer').close_branch($('#srs_'+i));
		}else{
			return;
		}
	}	
}

function validateData(){
	prepareData('');
	var kml = document.getElementById("coords").value;
	//invalid kml
	if(kml.indexOf('</kml>')<0){
		growlError('No geometries or invalid kml data');
	}else{
		growlInfo('Valid kml data');
	}
}

function saveLayer(aid){
	try{
		var id = "saveLayer";
		
		if($("#dialog"+id).html() == null || $("#dialog"+id).html() == ''){
			var $dialog = $('<div id="dialog'+id+'" title="Save Layer" style="font-size:10px">'+
					'<form>'+
					'<fieldset>'+
					'<label for="srs">SRS:</label>'+
					'<input type="text" size="20" class="smallInput" value="EPSG:4326" disabled/>'+
					'<label for="layername">Layer name:</label>'+
					'<input type="text" size="20" class="smallInput" id="map_savelayer_layername" name="map_savelayer_layername" value="" />'+
					'</fieldset>'+
					'</form>'+
			'</div>');
			$('body').append($dialog);
		}

		$(function() {
			$("#dialog"+id).dialog({
				bgiframe: true,
				modal: true,
				resizable: false,
				buttons: {
				OK: function() {
				var layername = $('#map_savelayer_layername').val();
				if(layername.length==0){
					growlError('Layer name can not be empty!');
					return;
				}
				prepareData(layername);
				var kml = document.getElementById("coords").value;
				//invalid kml
				if(kml.indexOf('</kml>')<0){
					$(this).dialog('close');
					growlError('No geometries or invalid kml, can not save it.');
					return;
				}
				
				$(this).dialog('close');
				var arrayPageSize = getPageSize();
				setBackgroudOverlay(arrayPageSize);
				createContainer();
				$.ajax({
					type: "POST",
					url: "upload_geo.php?",
					data: "aid="+aid+'&srs=EPSG:4326&layername='+layername+"&kmlstr="+kml,
					async: true,
					dataType: 'text',
					success: function(msg) {
					var flag = msg.substring(0, 3);
					var message = msg.substring(4, msg.length);
					if(flag == 'suc'){
						growlInfo(message);	
						map.clearOverlays();
					}else{
						growlError(message);
					}
					$('#loader_container').hide();
					$('#overlay').hide();
				}
				});

			},
			Cancel: function() {
				$(this).dialog('close');
			}
			}

			});

			$('#dialog'+id).dialog('open');
		});
		
	}catch(e){
		growlError('Can not upload layer: '+e);
		$('#map_loading').hide();
	}
}

function loadLayer(TREE_OBJ, wfsurl){
	try{
		if(TREE_OBJ.selected.children("A").attr('ref') == 'epsg4326'){
			map.clearOverlays();
			$('#map_loading').show();
			var layername = TREE_OBJ.selected.children("A").text();
			$.ajax({
				type: "GET",
				url: wfsurl+"?SERVICE=WFS&VERSION=1.1.1&TYPENAME="+layername+"&REQUEST=GetFeature&OUTPUTFORMAT=text%2Fxml&MAXFEATURES=100",
				dataType: "xml",
				success: function(xml) {
					//go to the center of map
					jQuery('gml\\:boundedBy',xml).each(function(i) {
						if(i == 0){
							c = jQuery(this).find('gml\\:coordinates').text().split(',');
							var centerPoint = new GLatLng( (parseFloat(c[1])+ parseFloat(c[3]))/2, (parseFloat(c[0])+ parseFloat(c[2]))/2);
							var mapZoom = map.getZoom();
							map.setCenter(centerPoint, mapZoom);
						}
				    });
					//load point
					// /adds/add
					jQuery('gml\\:featureMember',xml).find('myns\\:'+layername).each(function(i) {
						   var temp = jQuery(this).find('myns\\:msGeometry').find('gml\\:Point').find('gml\\:coordinates').text().split(',');
						   var shape = new GMarker(new GLatLng(parseFloat(temp[1]), parseFloat(temp[0])), { draggable: false});

						   map.addOverlay(shape);
						   
						   //TODO how to list the unknown tagName
						  /* jQuery(this).find('myns\\:msGeometry').next().each(function() {
							   tagname = jQuery(this)[0].tagName;
							   GLog.write(tagname);
							   if(tagname.indexOf('myns:')==0 && tagname != 'myns:msGeometry'){
								   //GLog.write(jQuery(this).text());
							   }
							   
						   });*/
					});
					//load linestring
					jQuery('myns\\:msGeometry',xml).find('gml\\:LineString').each(function(i) {
					   var points = jQuery(this).find('gml\\:coordinates').text().split(' ');
					   pointlength = points.length;
					   var gpoints = new Array();
					   var temp = new Array();
					   for(var m = 0;m<pointlength;m++){
						   temp = points[m].split(',');
						   gpoints.push(new GLatLng(parseFloat(temp[1]), parseFloat(temp[0])));
					   }
					   var shape = new GPolyline(gpoints,lineColorcur,lineWeightcur,lineopacitycur);
					   //var stepDistance = shape.getLength();
					   map.addOverlay(shape);
					});
					//load polygon
					jQuery('myns\\:msGeometry',xml).find('gml\\:Polygon').find('gml\\:LinearRing').each(function(i) {
						var points = jQuery(this).find('gml\\:coordinates').text().split(' ');
						pointlength = points.length;
						var gpoints = new Array();
						var temp = new Array();
						for(var m = 0;m<pointlength;m++){
							temp = points[m].split(',');
							gpoints.push(new GLatLng(parseFloat(temp[1]), parseFloat(temp[0])));
						}
						var shape = new GPolygon(gpoints,polygonlineColorcur,polygonLineWeightcur, lineopacitycur,fillColorcur,fillopacitycur);
						//var stepDistance = shape.getLength();
						//GLog.write(stepDistance);
						map.addOverlay(shape);
					});
					/*
					$(xml).find('site').each(function(){
						var id = $(this).attr('id');
						var title = $(this).find('title').text();
						var url = $(this).find('url').text();
						$('<div class="items" id="link_'+id+'"></div>').html('<a href="'+url+'">'+title+'</a>').appendTo('#page-wrap');
						$(this).find('desc').each(function(){
							var brief = $(this).find('brief').text();
							var long = $(this).find('long').text();
							$('<div class="brief"></div>').html(brief).appendTo('#link_'+id);
							$('<div class="long"></div>').html(long).appendTo('#link_'+id);
						});
					});
					*/
					$('#map_loading').hide();
				}
			});

		}
	}catch(e){
		growlError('Can not load layer: '+e);
		$('#map_loading').hide();
	}
}
