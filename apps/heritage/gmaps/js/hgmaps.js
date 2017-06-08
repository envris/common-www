/* Heritage Google Map Generation script.
 * Feb/2008
 * 
 * Accepts these parameters from the enclosing HTML page:
 *  GParamList:    optional, 'National' | 'World'
 *                  specifies the word used in the default pick list option.
 *  GParamMapLayer: optional, 'nhl' | 'whl' | 'both';
 *                  controls which additional map type is added, if any.
 *                  (NHL and/or WHL)
 *  GParamState:   optional, one of the state abbreviations in States[]
 *                  if not present, places in all states are shown.
 *                  If present, the initial map is zoomed in to the state
 *                  specified. The generation of markers and the pick list 
 *                  are controlled by GParamDisplay.
 *  GParamDisplay: optional, 'all' | 'state', default 'state'
 *                  only used if GParamState also defined.
 *                  If 'all', markers and pick list options re generated 
 *                  for all places. If 'state', only those for the state
 *                  specified.
 *                  It is not currrently possible to specify the marker
 *                  and pick list generation independently.
*/

/*  Global vars and settings */

/* Map the upper-case state abbreviations (as stored in the database)
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
   However, all use the same marker.
*/
var gicons = [];
gicons["act"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["nsw"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["nt"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["qld"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["sa"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["tas"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["vic"]  = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["wa"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");
gicons["other"] = new GIcon(G_DEFAULT_ICON, "/apps/heritage/gmaps/images/marker.png");

// Define the print images for the markers
for (state in gicons) {
  gicons[state].printImage = "/apps/heritage/gmaps/images/marker.gif";
  gicons[state].mozPrintImage = "/apps/heritage/gmaps/images/marker.gif";
  gicons[state].shadow = null;
  gicons[state].printShadow = null;
}
// Check user parameters, if any.

if (typeof GParamList == 'undefined') {
  var GParamList = '';
}
// try { console.log("GParamList=",GParamList); } catch(e) {};

if (typeof GParamMapLayer == 'undefined') {
  var GParamMapLayer = '';
}
// try { console.log("GParamMapLayer=",GParamMapLayer); } catch(e) {};

if (typeof GParamState == 'undefined') {
  var GParamState = '';
}
// try { console.log("GParamState=",GParamState); } catch(e) {};

if (typeof GParamDisplay == 'undefined') {
  var GParamDisplay = '';
  if (GParamState) {
    GParamDisplay = 'state';
  }
}
// try { console.log("GParamDisplay=",GParamDisplay); } catch(e) {};

/* Global vars and map settings */

var map;                    // The GMap object;
var overlayControl;         // The GOverview map control
var gmarkers = [];          // The place markers

_mPreferMetric=true;        // Display the metric scale on top

// Initial map center and zoom level

var initLAT = -28;
var initLON = 133;
var initZOOM = 4;

// Min Zoom level for default maptypes

var minZOOM = 2;

/* Load the data into the jsonData structure.
   (A separate js file contains the data.)
*/

var jsonData = eval(JSONObject);

// Set the marker icon for each entry by mapping to the state.

for (var i=0; i<jsonData.places.length; i++) {
  var place = jsonData.places[i];
  if (States[place.state].abbr) { place.icon = States[place.state].abbr; }
  else { place.icon = "other" }
}

/* Some miscellaneous globals for the build functions */

var marker_cntr = 0;        // A counter as we build the gmarkers array

/* The 'place_list' pick list's options:
   has to be built completely here because IE6 strips out the leading
   text up to a '>' otherwise!
*/
var select_html
    = '<select onchange="handleSelected(this);">' + "\n"
    + '<option value="" selected="selected">-- Zoom to a '
    + GParamList
    + ' Heritage place on the map --</option>' + "\n";

/* The pick list will contain optgroup elements around the options
   for a state. The addOption() function needs to know the current state.
*/
var cur_state = '';

/* Functions */

/* A function to zoom in on current point
*/
function myzoom(mapObj, a) {
  mapObj.setZoom(mapObj.getZoom() + a);
}

/* Function to handle selection from the pick list.
   We have a 'dummy' first entry in the list.
   Note that we identify the marker in the gmarker array by its index
   in the option set for the pick list (and vice-versa).
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

/* Function to zoom to the selected set by state.
*/
function displayset(state)  {

  // close any info window

  map.closeInfoWindow();

  var bounds = new GLatLngBounds(); // start with empty GLatLngBounds object

  /* Select the markers from the places and gmarkers arrays
     selecting only those required.
     If all_states is selected, reset to initial map
  */

  var active_cnt = 0;
  for (var i=0; i<jsonData.places.length; i++) {
    var place = jsonData.places[i];

    if (state && state == place.state) {
      active_cnt++;
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
      map.setZoom(map.getBoundsZoomLevel(bounds));
      map.setCenter(bounds.getCenter());
    }
    break;
  }
  /* But if no state was defined, reset to initial map
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
  if (f) { f.selectedIndex = 0; }    // Fails silently in IE6 and nothing else works
  return true;
}

/* A function to create the marker and set up the event window for a place.
*/
function createMarker(place) {

  var name = place.name;
  var id = place.id;

  /* Build the html which will be displayed in the popup info window
  */

  var html = '<div id="markerwindow">';
  html += '<p>';
  html += name;                      // Name of Heritage Listed Place
  html += '</p>';

  // Place the image on the right
  if (place.img_url) {                // Image of place (if available)
    html += '<div id="place_img"><img src="'
         + place.img_url + '"'
         + ' alt="Picture of ' + name + '"'
         + ' width="' + place.img_width + '"'
         + ' height="' + place.img_height + '"'
         + ' /></div>';
  }

  /*
  if (place.img_url) {                // Image of place (if available)
    html += '<div id="place_img"><img src="'
         + place.img_url + '"'
         + ' alt="Picture of ' + name + '"'
         + ' width="85"'
         + ' height="85"'
         + ' /></div>';
  }
  */

  /* Place the links on the left (if present):
   * (1) about URL  (required)
   * (2) virtual tour URL
   * (3) gallery URL
   * (4) AHDB URL
   * (5) WHL URL (only present for NHL places)
  */

  html += '<ul>';
  html += '<li><a href="'
         + place.about_url + '"'
         + ' target="New">About this place</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
         + '</li>';

  if (place.tour_url) {                 // Link to virtual tour
    html += '<li><a href="'
         +  place.tour_url + '"'
         +  ' target="New">Virtual tour</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
         + '</li>';
  }

  if (place.gallery_url) {              // Link to image gallery
    html += '<li><a href="'
         +  place.gallery_url + '"'
         +  ' target="New">More images</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
         + '</li>';
  }

  if ('whl_url' in place) {             // Also WH listed?
    if (place.whl_url) {
      html += '<li><a href="'
           +  place.whl_url + '"'
           +  ' target="New">World Heritage listing</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
           +  '</li>';
    }
  }

  if (place.ahdb_url) {                 // Link to AHDB entry
    html += '<li><a href="'
         +  place.ahdb_url + '"'
         +  ' target="New">Place Details (Australian Heritage Database)</a><span class="popup" title="Opens in a new browser window">&nbsp;</span>'
         + '</li>';
  }

  html += '</ul>';

  // Add the zoom 'buttons'

  html += '<div id="zoomright" class="clear"><p><a href="javascript:myzoom(map,-3)">Zoom out</a></p></div>'
       +  '<div id="zoomleft"><p><a href="javascript:myzoom(map,3)">Zoom in</a></p></div>';

  html += '</div>';                 // End of markerwindow div

  var point = new GLatLng(place.lat,place.lon);

  // Create the marker

  var marker = new GMarker(point, {title:name, icon:gicons[place.icon]});

  GEvent.addListener(marker, "click", function() {
    marker.openInfoWindowHtml(html);
  });
  gmarkers[marker_cntr] = marker;

  marker_cntr++;
  return marker;
}

/* Function to add an option to the 'place_list' pick list.
   We group the places by state.
   IF GParamState is specified, make the places not in the state invisible.
   This works nicely in FF2 but not in IE6.
*/
function addOption(name, state, param_state)   {
  var style = '';

/*      Nice idea; pity IE6 doesn't support it
  if (param_state) {
    if (state != param_state) {
      style = ' class="invisible"';
    }
  }
*/

  // Don't use the optgroup tag if a single state specified
/* This comment added by J.Longworth to undo sorting the pick list so is ungrouped by State
  if (! param_state) {
    if (cur_state && (cur_state != state)) {
      select_html += '</optgroup>' + "\n";
    }
    if (cur_state != state) {
      cur_state = state;
      select_html += '<optgroup label="' + States[state].name + '">' + "\n";
    }
  }
*/
  select_html += '<option' + style + '>' + name + '</option>' + "\n";
}

/* Function to create all the markers, and
   build the 'place_list' pick list's set of options.
*/
function createMarkersAndPickList(param_state, param_display) {

  for (var i=0; i<jsonData.places.length; i++) {
    var place = jsonData.places[i];

    // If param_state specified, continue or not depending on param_display
    if (param_state) {
      if (place.state != param_state) {
        if (param_display = 'state') { continue; }
      }
    }

    // create the marker and its popup window
    var marker = createMarker(place);
    map.addOverlay(marker);

    // Add to 'place_list' pick list
    addOption(place.name, place.state, param_state);
  }

  map.setCenter(new GLatLng(initLAT,initLON), initZOOM);

  // To Do: fix this if GParamState
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
  if ('undefined' == typeof jsonData) { return true; }  // No data to map

  if (GBrowserIsCompatible()) {

    // create the map

    map = new GMap2(document.getElementById("map"));

    /* ----------------------------------------------------------------- */

    // Set up controls

    map.enableDoubleClickZoom();  // Left button zooms in; Right out

    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT,new GSize(5,5)));
    map.addControl(new GScaleControl(), new GControlPosition(G_ANCHOR_BOTTOM_LEFT,new GSize(80,20)));

    // Add DragZoom control

    map.addControl(new DragZoomControl(
            {
              fillColor:'#000', opacity:.4, border:'1px solid black'
            },
            {
              buttonHTML:'<img title="Click and drag to zoom in" src="/apps/heritage/images/zoom.png" />',
              buttonZoomingHTML:'<img src="/apps/heritage/images/zoom-activated.png" />',
              buttonStartingStyle:{width:'24px', height:'24px'}
            },
            {
            }),
            new GControlPosition(G_ANCHOR_TOP_LEFT,new GSize(24,280)));

    /*  Don't set the map's center earlier; seems to lose the custom
        map type otherwise.
    */
    map.setCenter(new GLatLng(initLAT,initLON), initZOOM);

    /* Add the markers for all places.
       Build the 'place_list' pick list.
    */

    createMarkersAndPickList(GParamState, GParamDisplay);

    /* Add Overview map:
       Force it to always use the street map.
       BUT: this only works if WMS is added as an overlay
       or as a custom maptype. If added to a base maptype
       then it will also appear on the overview.
       NOTE: we have to do this somewhat clumsily:
       See http://econym.googlepages.com/modularized.html
    */

    overlayControl = new GOverviewMapControl(new GSize(100,100));
    map.addControl(overlayControl);

    // Wait for the control_api module to be loaded
    setTimeout("checkOverview()",100);

    /* ----------------------------------------------------------------- */

    /* If GParamState is specified, zoom to that state
    */
    if (GParamState) {
      displayset(GParamState);
    }
  }

  else {
    alert("Sorry, something has gone wrong! Perhaps the Google Maps API is not compatible with this browser");
  }
  return true;
}

// After the create_map() onload event, all control methods are accessible

function checkOverview()    {
  overmap = overlayControl.getOverviewMap();
  if (overmap) {
    // overmap.setMapType(G_NORMAL_MAP);
    GEvent.addListener(overmap, "maptypechanged",
                       function() { overmap.setMapType(G_NORMAL_MAP); });
  }
  else {  // control_api module hasn't yet loaded, wait again
    setTimeout("checkOverview()",100);
  }
}
