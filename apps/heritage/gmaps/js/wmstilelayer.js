/*
 *   wmsTileLayer Class
 *
 * This class lets you add a tile layer to a Google map from a web map service.
 *
 * Copyright 2007 Australian Department of the Environment and Water Resources
 *
 * The GetTileUrl code was originally developed by John Deck, UC Berkeley
 * (http://chignik.berkeley.edu/google/wmstest236.html) 
 * with the following further attributions:
 * Inspiration & Code from:
 *      Mike Williams http://www.econym.demon.co.uk/googlemaps2/ V2 Reference & custommap code
 *      Brian Flood http://www.spatialdatalogic.com/cs/blogs/brian_flood/archive/2005/07/11/39.aspx V1 WMS code
 *      Kyle Mulka http://blog.kylemulka.com/?p=287  V1 WMS code modifications
 *      http://search.cpan.org/src/RRWO/GPS-Lowrance-0.31/lib/Geo/Coordinates/MercatorMeters.pm
 * 		Modified by Chris Holmes, TOPP to work by default with GeoServer.
 *		Guilhem Vellut <guilhem.vellut@gmail.com> for more accurate Javascript Mercator Fxn
 *
 * See also
 *  http://cfis.savagexi.com/articles/2006/05/03/google-maps-deconstructed
 *
 * There are variations on getTileUrl function in:
 * wms-gs.js from http://docs.codehaus.org/display/GEOSDOC/Google+Maps
 * wms_live.js from http://www.online-archaeology.co.uk/GoogleMap/
 *  (This one shifts the Audit Atlas' tiles badly.)
 *
*/

/*
 * Sample Usage:
 *
 * Construct the new GTileLayer:
 *
 *  var WHAtiles = new wmsTileLayer(map, {
 *                  { baseUrl: 'http://www.anra.gov.au/wmsconnector/com.esri.wms.Esrimap/Map_maker?',
 *                    layers: 'World Heritage Areas - Australia',
 *                    copyright: 'Commonwealth of Australia'
 *                  },
 *                  { minResolution: 2,
 *                    maxResolution: 17,
 *                    mapOpacity: 0.5,
 *                    satelliteOpacity: 0.3
 *                  }
 *                 });
 *
 * Add to all map types in your Gmap2 instance (map)
 *
 *  WHAtiles.addToMap();
 *
 * OR, Add it to a single map type
 *
 *  WHAtiles.addToMapType(G_NORMAL_MAP);
 *
 * OR, Add as a new map type (with an existing map type as base)
 *
 *  WHAtiles.addMapType( {
 *            baseMapType: G_NORMAL_MAP,
 *            name:        "WMS",
 *            alt:         "Show street map and WMS" });  
 *
 * OR, Add as an overlay on top of an existing map
 *     (NOTE: the following code must be AFTER map.setCenter() !)
 *
 * var overlay = new GTileLayerOverlay(WHAtiles);
 * map.addOverlay(overlay);
 *  
*/

/**
 * @constructor wmsTileLayer which returns a GTileLayer and accepts
 * two option hashes to set the WMS and GTileLayer parameters.
 *
 * @param {GMap2} gmap2 (required) GMap2 object to add layer to
 *
 * @param {opts_wms} named arguments:
 *  opts_wms.baseUrl   {url}    (required) Base URL for the WMS service
 *  opts_wms.layers    {string} (required) Name(s) of the WMS service's layer(s)
 *  opts_wms.copyright {string} (required) Copyright notice to display
 *  opts_wms.format    {string} (optional) Image format; default 'image/png'
 *  opts_wms.styles    {string} (optional) WMS service STYLES option
 *
 * @param {opts_other} named arguments:
 *  opts_other.minResolution {number}    (optional) Minimum zoom level; 0
 *  opts_other.maxResolution {number}    (optional) Maximum zoom level; 22
 *  opts_other.mapOpacity    {number}    (optional) Opacity value 0-1; 0.5
 *  opts_other.satelliteOpacity {number} (optional) Opacity value 0-1; 0.3
 *
 * @return  {GTileLayer}
 * 
*/

function wmsTileLayer(gmap2, opts_wms, opts_other) {

  // Set up the global data structure

  this.globals = {
    map: gmap2
  };

  // Set up some defaults: WMS

  this.globals.wms = {
    format: 'image/png'
  };

  var wms = this.globals.wms;
  for (var s in opts_wms) {
    wms[s] = opts_wms[s];
  }

  // The others

  this.globals.other = {
    minResolution: 0,
    maxResolution: 22,
    mapOpacity: 0.5,
    satelliteOpacity: 0.3
  };

  var other = this.globals.other;
  if (opts_other != null) {
    for (var s in opts_other) {
      other[s] = opts_other[s];
    }
  }

  // Set up the copyright

  /* Can't figure out how to use this...
  var copyright = new GCopyright(1,
                        new GLatLngBounds(new GLatLng(-90,-180),
                                          new GLatLng(90,180)),
                        this.globals.other.minResolution,
                        this.globals.wms.copyright);
  this.copyrightCollection = new GCopyrightCollection('');
  this.copyrightCollection.addCopyright(copyright);
  */

  // Firebug output
  // try { console.dir(this); } catch(e) {};
}

// Constructor

wmsTileLayer.prototype = new GTileLayer(new GCopyrightCollection(''));

// wmsTileLayer.prototype = new GTileLayer(new GCopyrightCollection(''),0,20);
// wmsTileLayer.prototype = new GTileLayer(this.copyrightCollection,0,20);

/** 
 *  Overrides the GTileLayer getCopyright method
 *
*/

wmsTileLayer.prototype.getCopyright = function(bounds, zoom) {
  var G = this.globals;
  return { prefix: '', copyrightTexts:[G.wms.copyright]};
}

/**
 *  Overrides the GTileLayer minResolution method
 *
*/

wmsTileLayer.prototype.minResolution = function() {
  return this.globals.other.minResolution;
}

/**
 *  Overrides the GTileLayer maxResolution method
 *
*/

wmsTileLayer.prototype.maxResolution = function() {
  return this.globals.other.maxResolution;
}

/**
 *  Overrides the GTileLayer isPng method
 *  (Set to false for IE6)
*/

wmsTileLayer.prototype.isPng = function() {
  return false;
}

/**
 *  Overrides the GTileLayer getOpacity method
 *
*/

wmsTileLayer.prototype.getOpacity = function()  {
  var G = this.globals;
  if ( (G.map.getCurrentMapType() == G_SATELLITE_MAP) ||
       (G.map.getCurrentMapType() == G_HYBRID_MAP) ) {
    return G.other.satelliteOpacity;
  }
  else {
    return G.other.mapOpacity;
  }
}

/**
 *  Overrides the GTileLayer getTileUrl method
 *
 * @param {GPoint} bound (required) x,y values of the tile (not lat/lon!)
 * @param {number} zoom  (required) Current zoom level
 *
 * @return  {string}    WMS' URL for the required tile
 *
*/

wmsTileLayer.prototype.getTileUrl = function(bound, zoom) {

  // Some basic functions needed to specify the tiles to be returned

  var _MAGIC_NUMBER=6356752.3142;
  var _WGS84_SEMI_MAJOR_AXIS = 6378137.0;
  var _WGS84_ECCENTRICITY = 0.0818191913108718138;

  var _DEG2RAD=0.0174532922519943;
  var _PI=3.14159267;

  function dd2MercMetersLng(p_lng) {
	return _WGS84_SEMI_MAJOR_AXIS * (p_lng*_DEG2RAD);
  }

  function dd2MercMetersLat(p_lat) {
	var lat_rad = p_lat * _DEG2RAD;
	return _WGS84_SEMI_MAJOR_AXIS * Math.log(Math.tan((lat_rad + _PI / 2) / 2) * Math.pow( ((1 - _WGS84_ECCENTRICITY * Math.sin(lat_rad)) / (1 + _WGS84_ECCENTRICITY * Math.sin(lat_rad))), (_WGS84_ECCENTRICITY/2)));
  }

  var G = this.globals;
                                    // Each GMap tile is 256 pixels square
  var lULP = new GPoint(bound.x*256,(bound.y+1)*256);
  var lLRP = new GPoint((bound.x+1)*256,bound.y*256);

  var lUL = G_NORMAL_MAP.getProjection().fromPixelToLatLng(lULP,zoom);
  var lLR = G_NORMAL_MAP.getProjection().fromPixelToLatLng(lLRP,zoom);

  /* For now, hardwire the BBOX calculation and the SRS setting
     as required for the DEW audit WMS.
  */

  var lBbox=dd2MercMetersLng(lUL.x)+","+dd2MercMetersLat(lUL.y)+","+dd2MercMetersLng(lLR.x)+","+dd2MercMetersLat(lLR.y);

  var lSRS="EPSG:54004";

  var wmsURL = G.wms.baseUrl;

  wmsURL += "&REQUEST=GetMap";
  wmsURL += "&SERVICE=WMS";
  wmsURL +=	"&VERSION=1.1.1";
  wmsURL += "&LAYERS=" + G.wms.layers;
  wmsURL += "&STYLE=" + G.wms.styles;
  wmsURL += "&FORMAT=" + G.wms.format;
  wmsURL += "&BGCOLOR=0xFFFFFF";
  wmsURL += "&TRANSPARENT=TRUE";
  wmsURL += "&WIDTH=256";
  wmsURL += "&HEIGHT=256";
  wmsURL += "&SRS="+lSRS;
  wmsURL += "&BBOX="+lBbox;
  wmsURL += "&reaspect=false";

  wmsURL += '&EXCEPTIONS=application/vnd.ogc_se_xml';  // To get error messages

  // Firebug
  // try { console.log("wmsURL=",wmsURL); } catch(e) {};

  return wmsURL;
}

/**
 *  addToMap method adds the layer to ALL map types of the GMap2 object
 *
*/

wmsTileLayer.prototype.addToMap = function()    {

  var G = this.globals;
  var types = G.map.getMapTypes();
  for ( var i = 0; i < types.length; i++) {
    this.addToMapType(types[i]);
  }
}

/**
 *  addToMapType method adds the layer to the specified map type
 *  and sets the min and max zoom levels from the wmsTileLayer.
 *
*/

wmsTileLayer.prototype.addToMapType = function(mapType)    {
  
  var G = this.globals;
  mapType.getTileLayers().splice(1,0,this);
  mapType.getMinimumResolution = function() { return G.other.minResolution; }
  mapType.getMaximumResolution = function() { return G.other.maxResolution; }

}

/**
 *  addMapType method creates a new GmapType from the layer;
 *  (This is just a simplified front-end to GMapType.)
 *
 * @param {opts} named arguments:
 *  opts.baseMapType    {GMapType}  (required) Use baseMapType
 *  opts.name           {string}    (required) Text used as the button label 
 *                                             in the GMapTypeControl
 *  opts.altText        {string}    (required) alt text for the button label
 *
 * @return  {GMapType}
 *
*/

wmsTileLayer.prototype.addMapType = function(opts)  {

  var G = this.globals;

  var layers = [opts.baseMapType.getTileLayers()[0], this];
  var projection = opts.baseMapType.getProjection();

  var maptype = new GMapType(layers, projection, opts.name,
                           { errorMessage:  "",
                             minResolution: G.other.minResolution, 
                             maxResolution: G.other.maxResolution,
                             alt:           opts.altText });

  maptype.getMinimumResolution = function() { return G.other.minResolution; }
  maptype.getMaximumResolution = function() { return G.other.maxResolution; }

  G.map.addMapType(maptype);

  return maptype;
}
