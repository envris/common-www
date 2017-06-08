/* National Heritage List Google Map application    */
/*  Global vars and settings */

var map;                    // The GMap object;
var ovmap;                  // The GOverview map object

var jsonData;               // The marker data in JSON format
var gmarkers = [];          // The place markers

_mPreferMetric=true;        // Display the metric scale on top

// Initial map center and zoom level

var initLAT = -40;
var initLON = 118;
var initZOOM = 3;

// Min Zoom level for default maptypes

var minZOOM = 2;

/* Map the upper-case state abbreviations (as stored in the input JSON object)
   to their abbreviations (for the gicons array) and to their full names 
   for the pick list.
*/

var States = [];
States['ACT'] = { 'abbr': 'act', 'name': 'Australian Capital Territory'};
States['ANTA'] = { 'name': 'Antarctica'};
States['EXT'] = { 'name': 'External Territories'};
States['NSW'] = { 'abbr': 'nsw', 'name': 'New South Wales'};
States['NT'] = { 'abbr': 'nt', 'name': 'Northern Territory'};
States['QLD'] = { 'abbr': 'qld', 'name': 'Queensland'};
States['SA'] = { 'abbr': 'sa', 'name': 'South Australia'};
States['TAS'] = { 'abbr': 'tas', 'name': 'Tasmania'};
States['VIC'] = { 'abbr': 'vic', 'name': 'Victoria'};
States['WA'] = { 'abbr': 'wa', 'name': 'Western Australia'};

/* Create an associative array of GIcons() for the markers
   Note that not all the 'state' values in the database are listed
   separately; any not defined below are given the 'other' marker.
*/

/* 19/7/2007: Use the same icon for ALL places

var gicons = [];
gicons["act"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["nsw"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/nsw.png");
gicons["nt"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/nt.png");
gicons["qld"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/qld.png");
gicons["sa"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/sa.png");
gicons["tas"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/tas.png");
gicons["vic"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/vic.png");
gicons["wa"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/wa.png");
gicons["other"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/other.png");

*/

var gicons = [];
gicons["act"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["nsw"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["nt"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["qld"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["sa"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["tas"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["vic"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["wa"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");
gicons["other"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/images/act.png");

// Define the print images for the markers

for (state in gicons) {
  gicons[state].printImage = "/apps/heritage/images/" + state + ".gif";
  gicons[state].mozPrintImage = "/apps/heritage/images/" + state + ".gif";
  gicons[state].shadow = null;
  gicons[state].printShadow = null;
}

/* Some miscellaneous globals for the build functions */

var marker_cntr = 0;        // A counter as we build the gmarkers array

/* The 'place_list' pick list's options:
   has to be built completely here because IE6 strips out the leading
   text up to a '>' otherwise!
*/
var select_html
    = '<select id="place_list" onchange="handleSelected(this);">' + "\n"
    + '<option value="" selected="selected">-- Select a National Heritage place --</option>' + "\n";

/* The pick list will contain optgroup elements around the options
   for a state. The addOption() function needs to know the current state.
*/
var cur_state = '';

/* Functions */

/* Function to handle selection from the pick list.
   We have a 'dummy' first entry in the list.
   Note that we identify the marker in the gmarker array by its index
   in the option set for the pick list which is the same as
   its index in the jsonData.places list.
*/
function handleSelected(opt)    {
  var i = opt.selectedIndex - 1;
  if (i > -1) {
    gmarkers[i].show();                 // In case it's off
    GEvent.trigger(gmarkers[i],"click");

    // Zoom to the selected place
    var place = jsonData.places[i];
    map.setCenter(new GLatLng(place.lat,place.lon), 7);
  }
  else {                // Dummy event selected; close any InfoWindow
    map.closeInfoWindow();
  }
  return true;
}

/* Function to turn on all or a subset of the points (by state).
   All we need to do is make the required ones visible and turn
   the others off. Then we zoom to the selected set.
*/
function displayset(state)  {

  /* hide the info window,
     otherwise it still stays open where the possibly hidden marker used to be
  */
  map.getInfoWindow().hide();

  var bounds = new GLatLngBounds(); // start with empty GLatLngBounds object

  /* Select the markers from the places and gmarkers arrays
     selecting only those required and turning off the others.
     If all_states is selected, ONLY mainland OZ is shown.
  */

  var active_cnt = 0;
  for (var i=0; i<jsonData.places.length; i++) {
    var place = jsonData.places[i];

    if (state && state != place.state) {
      gmarkers[i].hide();               // Turn if off if not wanted
    }
    else {
      active_cnt++;
      gmarkers[i].show();               // Otherwise, turn it on
      bounds.extend(new GLatLng(place.lat,place.lon));
    }
  }

  switch (active_cnt) {
  case 0:
    map.setCenter(new GLatLng(initLAT,initLON), initZOOM);
    break;
  case 1:
    map.setZoom(7);
    map.setCenter(bounds.getCenter());
    break;
  default:
    // Check size of bounds, just in case
    var width = bounds.toSpan().lat();
    var height = bounds.toSpan().lng();
    if ( bounds.isEmpty() || bounds.isFullLat() || bounds.isFullLng() ||
         (Math.abs(width) == 0.0 && Math.abs(height) == 0.0) ) {
      map.setZoom(7);
      map.setCenter(bounds.getCenter());
    }
    else {
      map.setZoom(map.getBoundsZoomLevel(bounds)-1);
      map.setCenter(bounds.getCenter());
    }
    break;
  }
  /* But if no state was defined, use fixed bounding box (mainland OZ)
     and reset the 'All States' button, just in case
  */
  if (! state) {
    map.setCenter(new GLatLng(initLAT,initLON), initZOOM);
    var f = document.getElementById('all_states');
    if (f) { f.checked = true; }
  }
  /* We might have been called via the onclick of the state's icon
     so turn on the state's radio button
  */
  else {
    var f = document.getElementById(state.toLowerCase());
    if (f) { f.checked = true; }
  }
  // Also reset the 'place_list' pick list
  var f = document.getElementById('place_list');
  if (f) { f.selectedIndex = 0; }    // Fails silently in IE6

  // if (f) {  // All of these fail in IE6; I give up!
    // alert("selected option: " + f.options[f.selectedIndex].text);
    // f.options[0].selected = true;
    // The following puts IE into an infinite loop!!!
    // while(true) try { f.options[0].selected = true; break; } catch(e) {};
    // var opt = f.options[0];             // Also fails
    // opt.setAttribute('selected',true);
  // }

  return true;
}

/* Function to add an option to the 'place_list' pick list.
   We group the places by state.
*/
function addOption(name, state)   {
  
  if (cur_state && (cur_state != state)) {
    select_html += '</optgroup>' + "\n";
  }
  if (cur_state != state) {
    cur_state = state;
    select_html += '<optgroup label="' + States[state].name + '">' + "\n";
  }
  select_html += '<option>' + name + '</option>' + "\n";
}

/* Function to zoom in on current point
*/
function myzoom(mapObj, a) {
  mapObj.setZoom(mapObj.getZoom() + a);
}

/* Function to create the marker and set up the event window.
*/
function createMarker(place) {

  var name = place.name;
  var id = place.id;

  // Build the html which will be displayed in the popup info window

  var html = '<div class="markerwindow"><p>';

  html += name;                       // Name of Heritage Listed Place

  if (place.img_url) {                // Image of place (if available)
    html += '<br />'
         + '<img src="'
         + place.img_url + '"'
         + ' alt="Picture of ' + name + '"'
         + ' width="' + place.img_width + '"'
         + ' height="' + place.img_height + '"'
         + ' />'
  }

  html += '</p><ul>';
                                      // More info about place (if available)
  if (place.url) {
    html += '<li><a href="'
         + place.url + '"'
         + ' target="New">More about this place</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
         + '</li>';
  }
                                      // Link to Heritage Database
  html += '<li><a href="http://www.environment.gov.au/cgi-bin/ahdb/search.pl?mode=place_detail;place_id=' + id + '"'
       + ' target="New">Place Details (Australian Heritage Database)</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
       + '</li>';
                                      // Link to more images
  html += '<li><a href="http://www.environment.gov.au/cgi-bin/heritage/photodb/imagesearch.pl?proc=search_results;placeid=' + id + '"'
       + ' target="New">More images</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
       + '</li>';

  if ('whl' in place) {               // Also WH listed?
    if (place.whl) {
      html += '<li><a href="'
           +  place.whl + '"'
           +  ' target="New">World Heritage listing</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
           +  '</li>';
    }
  }
  html += '</ul>';

  html += '<div id="zoomright"><p><a href="javascript:myzoom(map,-3)">Zoom out</a></p></div>'
       +  '<div id="zoomleft"><p><a href="javascript:myzoom(map,3)">Zoom in</a></p></div>';

  html += '</div>';

  var point = new GLatLng(place.lat,place.lon);

  var marker = new GMarker(point, {title:name, icon:gicons[place.icon]});

  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });
  gmarkers[marker_cntr] = marker;

  marker_cntr++;
  return marker;
}

/* Function to create and display all the markers, and
   build the 'place_list' pick list's set of options.
*/
function createMarkersAndPickList() {

  for (var i=0; i<jsonData.places.length; i++) {
    var place = jsonData.places[i];

    // Set the marker icon by mapping to the state
    if (States[place.state].abbr) { 
      place.icon = States[place.state].abbr; 
    }
    else { 
      place.icon = "other" 
    }

    // create the marker and its popup window
    var marker = createMarker(place);
    map.addOverlay(marker);
    
    // Add to 'place_list' pick list
    addOption(place.name, place.state);
  }

  map.setCenter(new GLatLng(initLAT,initLON), initZOOM);

  var f = document.getElementById('all_states');
  if (f) { f.checked = true; }

  // Add the options to the 'place_list' pick list

  var f = document.getElementById('place_list');
  if (f) {
    f.innerHTML = select_html 
                + '</optgroup>' + "\n"
                + '</select>' + "\n";
  }

  return true;
}

/*  Build the map
*/
function create_map() {
  if (GBrowserIsCompatible()) {

    // create the map

    map = new GMap2(document.getElementById("map"));

    /*
        Set the minimum zoom level for all default map types
        However, this also affects the overview map which then
        looks like crap.

    var maptypes = map.getMapTypes();
    for (var i = 0; i< maptypes.length; i++) {
      maptypes[i].getMinimumResolution = function() { return minZOOM; }
    }

    */

    /* ----------------------------------------------------------------- */

    // Add the World Heritage List places via WMS

    var tileWHL = new wmsTileLayer(map,
                    { baseUrl: 'http://audit.ea.gov.au:80/wmsconnector/com.esri.wms.Esrimap/Map_maker?ServiceName=audit.deh.gov.au-Map_maker',
                      layers: 'World Heritage Areas - Australia',
                      copyright: 'Commonwealth of Australia'
                    },
                    {
                      minResolution: minZOOM,
                      maxResolution: 17
                    }
                  );


    // Firebug output
    // console.dir(tileWHL);
    
    // Add the WHL layer to the street map (w/o a button)
    // ToDO: How to change the alt text????

    // tileWHL.addToMapType(G_NORMAL_MAP);

    // Add the WHL layer to all map types

    // tileWHL.addToMap();

    // Add as a custom map type (with the normal map as base)
    // so it will have a control button

    tileWHL.addMapType( { 
            baseMapType: G_NORMAL_MAP,
            name: "WHL",
            altText: "Show street map and World Heritage List places" });
    
    // To add as an overlay, must do so AFTER creating the markers

    /* ----------------------------------------------------------------- */

    /* ----------------------------------------------------------------- */

    // Add the National Heritage List places via WMS

    var tileNHL = new wmsTileLayer(map,
                    { baseUrl: 'http://audit.ea.gov.au:80/wmsconnector/com.esri.wms.Esrimap/Map_maker?ServiceName=audit.deh.gov.au-Map_maker',
                      layers: 'National Heritage List',
                      copyright: 'Commonwealth of Australia'
                    },
                    { 
                      minResolution: minZOOM,
                      maxResolution: 17
                    }
                  );

    // Add new layer to the street map (w/o a button)
    // ToDO: How to change the alt text????
    // 18/7/07: Don't think it's possible

    // tileNHL.addToMapType(G_NORMAL_MAP);

    // Add new layer to all map types

    // tileNHL.addToMap();

    // Add as a custom map type (with the normal map as base)
    // so it will have a control button

    tileNHL.addMapType( { 
            baseMapType: G_NORMAL_MAP,
            name: "NHL",
            altText: "Show street map and National Heritage List places" });

    /* ----------------------------------------------------------------- */

    // Set up controls

    map.enableDoubleClickZoom();  // Left button zooms in; Right out

    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT,new GSize(5,5)));
    map.addControl(new GScaleControl(), new GControlPosition(G_ANCHOR_BOTTOM_LEFT,new GSize(80,20)));

    // Add GZoom control
    map.addControl(new GZoomControl(
            {
              sColor:'#000', nOpacity:.4, sBorder:'1px solid black'
            },
            {
              sButtonHTML:'<img title="Click and drag to zoom in" src="/apps/heritage/images/zoom.png" />',
              sButtonZoomingHTML:'<img src="/apps/heritage/images/zoom-activated.png" />',
              oButtonStartingStyle:{width:'24px', height:'24px'}
            },
            {
            }),
            new GControlPosition(G_ANCHOR_TOP_LEFT,new GSize(24,280)));

    /* ----------------------------------------------------------------- */
    /*
        Add the WHL layer as an overlay (but copyright not handled properly)
        (Have to put it below the map!)
    */
    /*
    var overlayWHL = new GTileLayerOverlay(tileWHL);
    map.addOverlay(overlayWHL);

    // Hide the overlay at zoom levels greater than 10

    GEvent.addListener(map, "zoomend", 
            function(oldLevel, newLevel) { 
              if (newLevel > 10) { overlayWHL.hide(); } 
              else { overlayWHL.show(); } });
    */
    /* ----------------------------------------------------------------- */

    /*  Don't set the map's center earlier; seems to lose the custom
        map type otherwise.
    */
    map.setCenter(new GLatLng(initLAT,initLON), initZOOM);

    /* ----------------------------------------------------------------- */
    /* Add Overview map:
       Force it to always use the street map.
       BUT: this only works if WMS is added as an overlay
       or as a custom maptype. If added to a base maptype
       then it will also appear on the overview.
       
       Give it full scope for min/max zoom.
       BUT: this also changes the min/max zoom of the main map
       because we're updating the global map type 8-((
       Would have to create a custom map type ?
    */

    var ovcontrol = new GOverviewMapControl(new GSize(100,100));
    map.addControl(ovcontrol);
    ovmap = ovcontrol.getOverviewMap();

    /*
    var ovmaptypes = ovmap.getMapTypes();
    for (var i = 0; i < ovmaptypes.length; i++) {
      ovmaptypes[i].getMinimumResolution = function() { return 0; }
      // ovmaptypes[i].getMaximumResolution = function() { return 15; }
    }
    */

    setTimeout("ovmap.setMapType(G_NORMAL_MAP);",1);
    GEvent.addListener(map, "maptypechanged",
                       function() { ovmap.setMapType(G_NORMAL_MAP); });

    /* ----------------------------------------------------------------- */

    /* ----------------------------------------------------------------- */
    /* Retrieve the marker data from a JSON file,
       then create the markers and populate the pick list.
       NOTE that a relative url doesn't work in localhost mode;
       data file must be in same directory as HTML page.
    */

    process_marker_data = function(data, responseCode) {

      // Firebug output
      // console.log(document);
      try { console.log(responseCode); } catch(e) {};
    
      // In localhost mode, responseCode is 0 on success

      if (responseCode == 0 || responseCode == 200 || responseCode == 304) {

        // Load the data into the jsonData structure

        jsonData = eval('(' + data + ')');

        /* Add the markers and display them for all places.
           Add the places to the 'place_list' pick list.
        */

        createMarkersAndPickList();

        /* COULD also add a listener for the infoWindowclose event
           to pan the map to center on the closed marker's window.
           See GMaps API group: 'Show the map at the zoom/center from start
           after clicking a marker'
        */
        return true;
      }

      // Some sort of error occurred...
      else if (responseCode == -1) {
        alert("Data request timed out. Please try later.");
      }
      else {
        alert("Data file couln't be read!");
      }
    }

    // Retrieve the file

    try {
    /*
      GDownloadUrl('/apps/heritage/gmaps/data/nhl-gmap-data.json', 
                   process_marker_data);
    */
      GDownloadUrl('nhl-gmap-data.json', process_marker_data);
    }
    catch(e) {
      alert("Couldn't read data file!");
      try { console.log(e); } catch(e) {};
    }

    /* ----------------------------------------------------------------- */
  }

  else {
    alert("Sorry, something has gone wrong! Perhaps the Google Maps API is not compatible with this browser");
  }
  return true;
}

