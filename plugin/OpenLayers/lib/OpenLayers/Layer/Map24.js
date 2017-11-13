/* Copyright (c) 2007 MetaCarta, Inc., published under a modified BSD license.
 * See http://svn.openlayers.org/trunk/openlayers/repository-license.txt 
 * for the full text of the license. */


/**
 * @requires OpenLayers/Layer/EventPane.js
 * @requires OpenLayers/Layer/FixedZoomLevels.js
 * 
 * Class: OpenLayers.Layer.Map24
 * 
 * Inherits:
 *  - <OpenLayers.Layer.EventPane>
 *  - <OpenLayers.Layer.FixedZoomLevels>
 */
OpenLayers.Layer.Map24 = OpenLayers.Class(OpenLayers.Layer.EventPane, 
                                           OpenLayers.Layer.FixedZoomLevels, {
    
    /** 
     * Constant: MIN_ZOOM_LEVEL
     * {Integer} 0 
     */
    MIN_ZOOM_LEVEL: 0,
    
    /** 
     * Constant: MAX_ZOOM_LEVEL
     * {Integer} 20
     */
    MAX_ZOOM_LEVEL: 20,

    /** 
     * Constant: RESOLUTIONS
     * {Array(Float)} Hardcode these resolutions so that they are directly
     *                bound to the MAP24 percentage zoom values 
     *                from 100 to 0 in 21 steps
     *                DO NOT CHANGE
     */
    RESOLUTIONS: [
        0.2516667654320989,
        0.15323582929439156,
        0.09330282184548229,
        0.05681058147050751,
        0.0345910456209149,
        0.021061929066319887,
        0.012824268478501,
        0.007808489976906512,
        0.004754463447304297,
        0.0028949160130327775,
        0.0017626676102148072,
        0.001073259842466182,
        0.0006534905859591841,
        0.00039789986454349393,
        0.00024227480181883274,
        0.00014751721432148916,
        0.00008982084953864508,
        0.00005469046476338868,
        0.00003330014079581003,
        0.000020275917965186535,
        0.000012345679012346915
    ],

    /**
     * APIProperty: type
     */
    type: null,
    
    /**
     * Current center
     * (Map24.Coordinate)
     */
    center: null,
    
    /**
     * Current zoom
     * (Integer)
     */
    zoom: 0,
    
    /**
     * Current bounds.
     * (Map24.Rectangle)
     */
    clipRect: null,
    
    /**
     * Current canvas width
     */
    canvasWidth: null,
    
    /**
     * Current canvas height
     */
    canvasHeight: null,
    
    /** 
     * Constructor: OpenLayers.Layer.Map24
     * 
     * Parameters:
     * name - {String}
     * options - {Object}
     */
    initialize: function(name, options) {
        options = options || {};
        
        if (options["numZoomLevels"] == null) {
            // default to all zoom levels instead of 16
            options["numZoomLevels"] = this.MAX_ZOOM_LEVEL + 1;  
        }

        // The center of these bounds will not stray outside
        // of the viewport extent during panning.  In addition, if
        // <displayOutsideMaxExtent> is set to false, data will not be
        // requested that falls completely outside of these bounds.
        this.maxExtent = new OpenLayers.Bounds(-180, -90, 180, 90);

        OpenLayers.Layer.EventPane.prototype.initialize.apply(this, arguments);
        OpenLayers.Layer.FixedZoomLevels.prototype.initialize.apply(this, 
                                                                    arguments);
    },
    
    /** 
     * Method: loadMapObject
     * Load the Map24 API and register a MapViewChanged event listener.
     */
    loadMapObject:function() {
        
        try {
            // create Map24
           var me = this;
           Map24.loadApi( ["core_api", "wrapper_api"] , function() { 
                
                //Initialize mapping client and show map.
                me.options["NodeName"] = me.div.id;
                
                if (me.options["MapType"] == null) {
                    // default to "Static" map instead of "Auto"
                    me.options["MapType"] = "Static"; 
                }
            
                // Check if a "start map view" is available 
                if (me.options["StartMapView"] != null) { 
                    Map24.MapApplication.setStartMapView( 
                        me.options["StartMapView"] 
                    ); 
                } 

                Map24.MapApplication.init( me.options );

                // Do not register a listener if this is the non-static 
                // (or applet) version of map24. Note that you can't use 
                // OpenLayers controls or other layers if you are using the 
                // applet version, so it doesn't really make sense to use a 
                // non-static map24 map with openlayers, but I kept this line 
                // anyway so that the applet version still works.
                if (me.options["MapType"] == "Static") {
                    
                    Map24.MapApplication.Map.addListener( 
                        "Map24.Event.MapViewChanged", function(e) {
                            me.mapViewChanged(e);
                        }
                    );
                }
                
                // Call the optional callback function to notify about
                // map initialization
                if (me.options["InitCallback"] != null) {
                    me.options["InitCallback"](); // call the function
                }
            });
            this.mapObject = "Map24"; //placeholder object
        } catch (e) {
            // do not crash
        }
               
    },
    
    /**
     * Method: mapViewChanged
     */
    mapViewChanged:function(e) {

        var firstCall = (this.clipRect == null);
        
        if (this.canvasWidth != e.Canvas.NodeWidth ||
            this.canvasHeight != e.Canvas.NodeHeight)
        {
            // canvas size has changed. must remove the toolbar
            e.Canvas.ViewportNode.removeChild(e.MapClient._Toolbar.Node);        	
        }
    
        this.clipRect = e.ClipRect.clone();
        this.center = e.ClipRect.Center.clone();
        this.canvasWidth = e.Canvas.NodeWidth;
        this.canvasHeight = e.Canvas.NodeHeight;
    
        if (firstCall) {
            // this is the first time this is called meaning
            // the map has just been made visible with zoom level 0.
            // this is the place where we can save the maximum extent of the map.
            this.maxExtent.left = e.ClipRect.TopLeft.Longitude/60;
            this.maxExtent.bottom = e.ClipRect.LowerRight.Latitude/60;
            this.maxExtent.right = e.ClipRect.LowerRight.Longitude/60;
            this.maxExtent.top = e.ClipRect.TopLeft.Latitude/60;
            
            // calculate the current zoom level from the initial bounds
            this.zoom = this.getZoomForExtent(this.maxExtent);
        
            // must redraw all layers including center
            var center = new OpenLayers.LonLat(e.ClipRect.Center.Longitude/60, 
                                               e.ClipRect.Center.Latitude/60);
            this.map.setCenter(center, this.zoom, false, true);
        } else {
            // must redraw all layers!
            this.map.setCenter(null, this.zoom, false, true);
        }
    },
    
    /**
     * APIMethod: onMapResize
     * 
     * Parameters:
     * evt - {Event}
     */
    onMapResize: function() {
        // Not necessary to do anything here. mapViewChanged will get called.
    },

    /**
     * APIMethod: getZoomForExtent
     * 
     * Parameters:
     * bounds - {<OpenLayers.Bounds>}
     *  
     * Return:
     * {Integer} Corresponding zoom level for a specified Bounds. 
     *           If mapObject is not loaded or not centered, returns null
     *
     */
    getZoomForExtent: function (bounds) {
        var res = Math.max(
            Math.abs(bounds.right - bounds.left) / this.canvasWidth,
            Math.abs(bounds.top - bounds.bottom) / this.canvasHeight
        );
        
        // find the corresponding zoom level
        var i = this.MIN_ZOOM_LEVEL;
        while (i <= this.MAX_ZOOM_LEVEL && this.RESOLUTIONS[i] > res) {
            i++;
        }
        if (this.RESOLUTIONS[i] > res) {
            return this.MAX_ZOOM_LEVEL;
        }
        if (i == this.MIN_ZOOM_LEVEL) {
            return this.MIN_ZOOM_LEVEL;
        }
        return i-1; // return the highest zoom level that is larger than res
    },
    
  //
  // TRANSLATION: MapObject Bounds <-> OpenLayers.Bounds
  //

    /**
     * APIMethod: getOLBoundsFromMapObjectBounds
     * 
     * Parameters:
     * moBounds - {Map24.Rectangle}
     * 
     * Return:
     * {<OpenLayers.Bounds>} An <OpenLayers.Bounds>, translated from the 
     *                       passed-in MapObject Bounds.
     *                       Returns null if null value is passed in.
     */
    getOLBoundsFromMapObjectBounds: function(moBounds) {
        var olBounds = null;
        if (moBounds != null) {
            var se = moBounds.LowerRight;
            var nw = moBounds.TopLeft;
            olBounds = new OpenLayers.Bounds(nw.Longitude/60, 
                                             se.Latitude/60, 
                                             se.Longitude/60, 
                                             nw.Latitude/60);
        }
        return olBounds;
    },

    /**
     * APIMethod: getMapObjectBoundsFromOLBounds
     * 
     * Parameters:
     * olBounds - {<OpenLayers.Bounds>}
     * 
     * Return:
     * {Map24.Rectangle} A MapObject Bounds, translated from olBounds
     *          Returns null if null value is passed in
     */
    getMapObjectBoundsFromOLBounds: function(olBounds) {
        var moBounds = null;
        if (olBounds != null) {
        
            var nw = new Map24.Coordinate(olBounds.left*60, 
                                          olBounds.top*60);

            var se = new Map24.Coordinate(olBounds.right*60, 
                                          obBounds.bottom*60);
            
            moBounds = new Map24.Rectangle();
            moBounds.setTopLeft(nw);
            moBounds.setLowerRight(se);
        }
        return moBounds;
    },
    
    
    /** 
     * APIMethod: getWarningHTML
     * 
     * Return: 
     * {String} String with information on why layer is broken, how to get
     *          it working.
     */
    getWarningHTML:function() {

        var html = "";
        html += "The Map24 Layer was unable to load correctly.<br>";
        html += "<br>";
        html += "To get rid of this message, select a new BaseLayer "
        html += "in the layer switcher in the upper-right corner.<br>";
        html += "<br>";
        html += "Most likely, this is because the Map24 Maps library";
        html += " script was either not included, or does not contain the";
        html += " correct API key for your site.<br>";
        html += "<br>";
        html += "Developers: For help getting this working correctly, ";
        html += "<a href='http://trac.openlayers.org/wiki/Map24' "
        html +=  "target='_blank'>";
        html +=     "click here";
        html += "</a>";
        
        return html;
    },


    /************************************
     *                                  *
     *   MapObject Interface Controls   *
     *                                  *
     ************************************/


  // Get&Set Center, Zoom

    /** 
     * APIMethod: setMapObjectCenter
     * Set the mapObject to the specified center and zoom
     * 
     * Parameters:
     * center - {Object} MapObject LonLat format
     * zoom - {int} MapObject zoom format
     */
    setMapObjectCenter: function(center, zoom) {
    
        if (center != null) {

            // Immediately update the clipRect values for fast panning
            var xdiff = center.Longitude - this.center.Longitude;
            var ydiff = center.Latitude - this.center.Latitude;
            this.clipRect.TopLeft.Longitude += xdiff;
            this.clipRect.TopLeft.Latitude += ydiff;
            this.clipRect.LowerRight.Longitude += xdiff;
            this.clipRect.LowerRight.Latitude += ydiff;
        
            this.center = center;
            Map24.MapApplication.center({ 
                'Coordinate': center 
            });
        }
        
        if (zoom != null) {
            this.zoom = zoom;    
            Map24.MapApplication.zoom( 100 - this.zoom*5 );    
        }    
    },
   
    /**
     * APIMethod: getMapObjectCenter
     * 
     * Return: 
     * {Object} The mapObject's current center in Map Object format
     */
    getMapObjectCenter: function() {
        return this.center;
    },

    /** 
     * APIMethod: getMapObjectZoom
     * 
     * Return:
     * {Object} The mapObject's current zoom, in Map Object format
     */
    getMapObjectZoom: function() {
        return this.zoom;
    },


  // LonLat - Pixel Translation
  
    /**
     * APIMethod: getMapObjectLonLatFromMapObjectPixel
     * 
     * Parameters:
     * moPixel - {Map24.Point} MapObject Pixel format
     * 
     * Return:
     * {Map24.Coordinate} MapObject LonLat translated from MapObject Pixel
     */
    getMapObjectLonLatFromMapObjectPixel: function(moPixel) {
        
        var tl = this.clipRect.TopLeft;
        var lr = this.clipRect.LowerRight;

        var x = tl.Longitude + 
            moPixel.X * (lr.Longitude - tl.Longitude) / (this.canvasWidth);

        var y = tl.Latitude + 
            moPixel.Y * (lr.Latitude - tl.Latitude) / (this.canvasHeight);

        return new Map24.Coordinate(x,y);
    },

    /**
     * APIMethod: getMapObjectPixelFromMapObjectLonLat
     * 
     * Parameters:
     * moLonLat - {Object} MapObject LonLat format
     * 
     * Return:
     * {Object} MapObject Pixel transtlated from MapObject LonLat
     */
    getMapObjectPixelFromMapObjectLonLat: function(moLonLat) {

        var tl = this.clipRect.TopLeft;
        var lr = this.clipRect.LowerRight;
        
        var x = (moLonLat.Longitude - tl.Longitude) * (this.canvasWidth) / 
            (lr.Longitude - tl.Longitude);

        var y = (moLonLat.Latitude - tl.Latitude) * (this.canvasHeight) / 
            (lr.Latitude - tl.Latitude);

        return new Map24.Point(x,y);
    },

    /************************************
     *                                  *
     *       MapObject Primitives       *
     *                                  *
     ************************************/


  // LonLat
    
    /**
     * APIMethod: getLongitudeFromMapObjectLonLat
     * 
     * Parameters:
     * moLonLat - {Object} MapObject LonLat format
     * 
     * Return:
     * {Float} Longitude of the given MapObject LonLat
     */
    getLongitudeFromMapObjectLonLat: function(moLonLat) {
        return moLonLat.Longitude/60;  
    },

    /**
     * APIMethod: getLatitudeFromMapObjectLonLat
     * 
     * Parameters:
     * moLonLat - {Object} MapObject LonLat format
     * 
     * Return:
     * {Float} Latitude of the given MapObject LonLat
     */
    getLatitudeFromMapObjectLonLat: function(moLonLat) {
        return moLonLat.Latitude/60;  
    },
    
    /**
     * APIMethod: getMapObjectLonLatFromLonLat
     * 
     * Parameters:
     * lon - {Float}
     * lat - {Float}
     * 
     * Return:
     * {Object} MapObject LonLat built from lon and lat params
     */
    getMapObjectLonLatFromLonLat: function(lon, lat) {
        return new Map24.Coordinate(lon*60, lat*60);
    },

  // Pixel
    
    /**
     * APIMethod: getXFromMapObjectPixel
     * 
     * Parameters:
     * moPixel - {Object} MapObject Pixel format
     * 
     * Return:
     * {Integer} X value of the MapObject Pixel
     */
    getXFromMapObjectPixel: function(moPixel) {
        return moPixel.X;
    },

    /**
     * APIMethod: getYFromMapObjectPixel
     * 
     * Parameters:
     * moPixel - {Object} MapObject Pixel format
     * 
     * Return:
     * {Integer} Y value of the MapObject Pixel
     */
    getYFromMapObjectPixel: function(moPixel) {
        return moPixel.Y;
    },

    /**
     * APIMethod: getMapObjectPixelFromXY
     * 
     * Parameters:
     * x - {Integer}
     * y - {Integer}
     * 
     * Return:
     * {Object} MapObject Pixel from x and y parameters
     */
    getMapObjectPixelFromXY: function(x, y) {
        return new Map24.Point(x, y);
    },

    CLASS_NAME: "OpenLayers.Layer.Map24"
});
