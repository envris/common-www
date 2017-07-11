<?php
  /*
   * 2006 09 14 - Alex Fahey 0419 47 98 98
   * This file has been built to provide the search results for all sections of the DEH webiste.
   * It expects to be passed the following parameters via the URL
   *    query --> This is what the user is searching for
   *    theme --> This is used indicate where the user would like to search
   *    type --> This is used indicate if the search is for any specific type of document (NB: only a value of "publications" has any effect at this time)
   *
   * It also expects the following variable to be set in the page that is including this script
   *   $siteArea --> This indicates where the search page is located and sets the default search areas
   *   Below is example code to show how to include the search code and $siteArea into a web page
   *    <?php $siteArea = "atmosphere"; ?>
   *    <?php include '../includes/php/funnelback.php'; ?>
   *
   *
   */

  //Setup some inital constants
  // define constants
  define("COLLECTION", "deh");  // tells funnelback to use the DEH federated collection
  define("RESULTS_PER_PAGE", 20);
  define("PAGING_BUFFER", 5);   // how many pages either side of the current page to display
  define("DISPLAY_SPELLING_SUGGESTION", true);  // when keywords are spealt incorrectly, should the form suggest an alternative?
//  define("SEARCH_URL", "http://bureau2-query.funnelback.com/search/xml.cgi");
  define("SEARCH_URL", "http://bureau2-query.funnelback.com/search/xml_v7.cgi");
  define("SEARCH_URL_AUST_GOV", "http://agencysearch.australia.gov.au/search/xml.cgi");

  // define globals
  $search = new CSearch;  // CSearch object containing our results data
  $is_result_sum = null;  // are we in the results summary heirarchy?
  $result_sum_tag = ""; // which tag are we within the results summary?

  $is_spelling = null;    // are we in the suggested spelling heirarchy?
  $spelling_tag = "";   // which tag are we within the suggested spelling?

  $is_suggest = null;    // are we in the suggested spelling heirarchy?
  $suggest_tag = "";   // which tag are we within the suggested spelling?
  $wordToReplace = "";   //Counder for the number of word replacemtns

  $result = null;     // temporary CResult object
  $is_result = null;    // are we in the results heirarchy?
  $result_tag = "";     // which tag are we within the results?

//Workout if we are showing debug data
  $isDebuging = "False";
  //$isDebuging = "True";

  if (isset($_GET["debug"]))
  {
    $isDebuging = "True";
  }

//Work out if we are doing a publication search
$isPublicationsSearch = "False";
if (isset($_GET["type"]))
{
  if($_GET["type"] == "publications")
  {
    $isPublicationsSearch = "True";
  }
}

//Work out which site we are searching
if (isset($_GET["site"])) $search->site = $_GET["site"];

//Write the page title
if ($isPublicationsSearch == "True")
{
  print "<h1><a name=\"top\" id=\"top\">Search results (publications only)</a></h1>";
} else {
  print "<h1><a name=\"top\" id=\"top\">Search results</a></h1>";
}

//Set the inital search scope and set up radio button text based on the SiteArea
$RadioText = "";
$RadioText2 = "";
$RadioTheme2 = "";
switch($search->site)
{

  case "cwg.gov.au":
    define("THEME", "deh");
    $search->theme = "deh";
    $search->cur_site_area_theme = "deh";
    $search->url_limit_string = "communitywatergrants.gov.au";
    $search->show_pub_search_again = "no";
    $siteArea = "waterrating";
    $RadioText = "Community Water Grants";
  break;

  case "heritage.gov.au":
    define("THEME", "heritage");
    $search->theme = "heritage";
    $search->cur_site_area_theme = "heritage";
    $search->show_pub_search_again = "no";
    $siteArea = "heritage";
    $RadioText = "heritage.gov.au";
  break;

  case "npi.gov.au":
    define("THEME", "deh");
    $search->theme = "deh";
    $search->cur_site_area_theme = "deh";
    $search->url_limit_string = "npi.gov.au";
    $search->show_pub_search_again = "no";
    $siteArea = "npi";
    $RadioText = "npi.gov.au";
  break;

  case "apfp":
    define("THEME", "heritage");
    $search->theme = "heritage";
    $search->cur_site_area_theme = "heritage";
    $search->url_limit_string = "/apfp/";
    $search->show_pub_search_again = "yes";
    $siteArea = "heritage";
    $RadioText = "Asia-Pacific Focal Point";
  break;

  case "orer.gov.au":
    define("THEME", "atmosphere");
    $search->theme = "atmosphere";
    $search->cur_site_area_theme = "atmosphere";
    $search->url_limit_string = "orer.gov.au";
    $search->show_pub_search_again = "no";
    $siteArea = "orer";
    $RadioText = "orer.gov.au";
  break;

  case "waterrating.gov.au":
    define("THEME", "deh");
    $search->theme = "deh";
    $search->cur_site_area_theme = "deh";
    $search->url_limit_string = "waterrating.gov.au";
    $search->show_pub_search_again = "no";
    $siteArea = "waterrating";
    $RadioText = "waterrating.gov.au";
  break;

  case "weeds.gov.au":
    define("THEME", "deh");
    $search->theme = "deh";
    $search->cur_site_area_theme = "deh";
    $search->url_limit_string = "weeds.gov.au";
    $search->show_pub_search_again = "no";
    $siteArea = "weeds";
    $RadioText = "weeds.gov.au";
  break;

}

  // you do not need to change anything else below, anything you change might break functionality
  // ********* end customisable area *********


  $a_href = "";
  $search->query = "";
  if (isset($_GET["query"])) $search->query = urldecode ( $_GET["query"]);
  $search->altquery = $search->query;

//Update the theme if a different radio button has been checked by the user
  if (isset($_GET["theme"])) $search->theme = $_GET["theme"];

  class CSearch {
    var $theme;
    var $cur_site_area_theme;
    var $url_limit_string;
    var $query;
    var $altquery;
    var $fully_matching;
    var $partially_matching;
    var $total_matching;
    var $num_ranks;
    var $currstart;
    var $currend;
    var $nextstart;
    var $results;
    var $page;
    var $spelling;
    var $show_pub_search_again;

    function CSearch() {
      $this->theme = THEME;
      $this->cur_site_area_theme = "";
      $this->url_limit_string = "";
      $this->query = "";
      $this->altquery = "";
      $this->fully_matching = 0;
      $this->partially_matching = 0;
      $this->total_matching = 0;
      $this->num_ranks = 0;
      $this->currstart = 0;
      $this->currend = 0;
      $this->nextstart = 0;
      $this->results = array();
      $this->page = 0;
      $this->spelling = null;
      $this->show_pub_search_again = "no";
    }
  }

  class CResult {
    var $rank;
    var $score;
    var $title;
    var $collection;
    var $live_url;
    var $summary;
    var $cache_url;
    var $date;
    var $filesize;
    var $filetype;
    var $tier;
    var $docnum;

    function CResult() {
      // a very nice constructor
    }
  }

  function startElement($parser, $name, $attrs)
  {
    global $is_result_sum, $result_sum_tag;
    global $is_spelling, $spelling_tag;
    global $is_suggest, $suggest_tag, $wordToReplace;
    global $is_result, $result, $result_tag;

    // results summary
    if ($is_result_sum)
      $result_sum_tag = $name;
    elseif ($name == "RESULTS_SUMMARY")
      $is_result_sum = true;

    // suggested spelling
    if ($is_spelling)
      $spelling_tag = $name;
    elseif ($name == "SPELL")
      $is_spelling = true;

    // suggested spelling
    if ($is_suggest)
      $suggest_tag = $name;
    elseif ($name == "MAP")
      $is_suggest = true;

    // results
    if ($is_result)
      $result_tag = $name;
    elseif ($name == "RESULT") {
      $is_result = true;
      $result = new CResult();
    }
  }

  function characterData($parser, $data)
  {
    global $is_result_sum, $result_sum_tag, $search;
    global $is_spelling, $spelling_tag;
    global $is_suggest, $suggest_tag, $wordToReplace;
    global $is_result, $result_tag, $result;

    // results summary
    if ($is_result_sum) {
      if ($data = trim($data)) {
        switch($result_sum_tag) {
          case "FULLY_MATCHING":
            $search->fully_matching = (int)$data;
            break;
          case "PARTIALLY_MATCHING":
            $search->partially_matching = (int)$data;
            break;
          case "TOTAL_MATCHING":
            $search->total_matching = (int)$data;
            break;
          case "NUM_RANKS":
            $search->num_ranks = (int)$data;
            break;
          case "CURRSTART":
            $search->currstart = (int)$data;
            break;
          case "CURREND":
            $search->currend = (int)$data;
            break;
        }
      }
    }

    // suggested spelling
    if ($is_spelling) {
      if ($data = trim($data)) {
        switch($spelling_tag) {
          case "TEXT":
            $search->spelling .= $data;
            break;
        }
      }
    }

    // suggested word replacements
    if ($is_suggest) {
      if ($data = trim($data)) {
        switch($suggest_tag) {
          case "TERM":
            $wordToReplace = $data;
            break;
          case "SUGGEST":
            $search->altquery = str_replace($wordToReplace, $data, $search->altquery);
            break;
        }
      }
    }

    // results
    if ($is_result) {
      if ($data = trim($data)) {
        switch($result_tag) {
          case "RANK":
            $result->rank .= $data;
            break;
          case "SCORE":
            $result->score .= $data;
            break;
          case "TITLE":
            $result->title .= $data;
            break;
          case "COLLECTION":
            $result->collection .= $data;
            break;
          case "LIVE_URL":
            $result->live_url .= $data;
            break;
          case "SUMMARY":
            $result->summary .= $data;
            break;
          case "CACHE_URL":
            $result->cache_url .= $data;
            break;
          case "DATE":
            $result->date .= $data;
            break;
          case "FILESIZE":
            $result->filesize .= $data;
            break;
          case "FILETYPE":
            $result->filetype .= $data;
            break;
          case "TIER":
            $result->tier .= $data;
            break;
          case "DOCNUM":
            $result->docnum .= $data;
            break;
        }
      }
    }
  }

  function endElement($parser, $name)
  {
    global $is_result_sum, $result_sum_tag;
    global $is_spelling, $spelling_tag;
    global $is_result, $result_tag, $result, $search;

    // results summary
    if ($name == "RESULTS_SUMMARY") {
      $result_sum_tag = "";
      $is_result_sum = false;
    }

    // suggested spelling
    if ($name == "SPELL") {
      $spelling_tag = "";
      $is_spelling = false;

      // if the altquery, which has had all selling sugestions replaced, is the same as the query, then there were no changes
      if ($search->altquery == $search->query) $search->altquery = null;
    }

    // results
    if ($name == "RESULT") {
      $result_tag = "";
      $is_result = false;
      array_push($search->results, $result);
      $result = null;
    }
  }

  /*
   * retrieveResults determines the HTTP request, sends it off to SEARCH_URL, retrieves the XML reply and parses it
   * into the CSearch structure defined above.
   *
   * pre: a search query has been made
   * post: returns true if the process was successful having populated the global search object with the reply
   */
  function retrieveResults() {
    global $search, $a_href, $isPublicationsSearch, $siteArea;
    global $isDebuging;

    $content = "";

    if (isset($_GET["p"])) $search->page = (int)$_GET["p"] - 1;
  if (isset($_GET["s"]))
  {
    $scope = ((bool)$_GET["s"]) ? "on" : "off";
  } else {
    $scope = "off";
  }

    //If the theme is "allgov" then we need to search the Australia.gov index, otherwise just search the DEH collection
    if($search->theme == "allgov")
    {
        $location = SEARCH_URL_AUST_GOV."?collection=gov&query=" . urlencode(str_replace('\"','"',$search->query)) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE) . "&fqp=1&gscope1=0";

    } else {
        $location = SEARCH_URL . "?collection=" . urlencode(COLLECTION) . "&query=" . urlencode($search->query) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE);

        //Sort out the theme for the search and page links
  if($search->theme == "all")
  {
    $search->url_limit_string = "";
    if($isPublicationsSearch == "True") {$search->url_limit_string = "/publications/"; }
    if($search->url_limit_string != "") {$location = $location . "&scope=" . urlencode($search->url_limit_string); };
  } else {
    $location = $location . "&meta_x_sand=" . urlencode($search->theme);
    if($isPublicationsSearch == "True") {$search->url_limit_string = $search->url_limit_string . "publications/"; }
    if($search->url_limit_string != "") {$location = $location . "&scope=" . urlencode($search->url_limit_string); };
  }
    }

  //print "<h1>" . $search->url_limit_string . "</h1>";

  if ($isDebuging == "True")
  {
    print "Request sent to FunnelBack: $location";
  }

  //Set up the page and Next links
  //$a_href = $_SERVER["PHP_SELF"] . "?query=" . urlencode($search->query);
  $a_href = "/search.php?query=" . urlencode($search->query);
  if (isset($_GET["s"]))
  {
    $a_href = $a_href . "&amp;s=" . urlencode($_GET["s"]);
  }

    //If it is a Publications search then set the theme to pubs
    if ( $search->theme ) { $a_href = $a_href . "&amp;theme=" . urlencode($search->theme); }
    if($isPublicationsSearch == "True")
    {
       $a_href = $a_href . "&amp;type=publications";
    }

    // retrieve xml feed
    $myWebContext = $_SERVER['MY_SERVER_LOCATION'];

    if ($myWebContext == "DMZ") {
      if (!($fp = fopen($location, "r"))) {
        return false;
      }
      while ($filechunk = fread($fp, 4096)) {
        $content .= $filechunk;
      }
      fclose($fp);
    }
    else {
      $proxyHost = 'proxy.deh.gov.au';
      $proxyPort = '8080';

      $proxySock = fsockopen($proxyHost,$proxyPort);
      if ($proxySock) {
        fputs($proxySock, "GET $location HTTP/1.0\r\n");
        fputs($proxySock, "Host: $proxyHost\r\n\r\n");
        while (!feof($proxySock)) {
          $content .= fread($proxySock,4096);
        }
        fclose($proxySock);
        $content = substr($content, strpos($content,"\r\n\r\n")+4);
      }
      else {
        return false;
      }
    }



    // add CDATA to summary tags
    // this will prevent the <b>...</b> tags from being interpreted as another XML tag
//    $content = str_replace("<summary>", "<summary><![CDATA[", $content);
//    $content = str_replace("</summary>", "]]></summary>", $content);

    $xmlParser = xml_parser_create();
    xml_parser_set_option($xmlParser, XML_OPTION_SKIP_WHITE, 1);
    xml_set_element_handler($xmlParser, "startElement", "endElement");
    xml_set_character_data_handler($xmlParser, "characterData");
    xml_parse($xmlParser, $content);
    xml_parser_free($xmlParser);

    // uncomment the following if you wish to view the results of the feed
    // leave this commented when in production
  //Show the raw data
  if ($isDebuging == "True")
  {
    echo "<pre>";
    //var_dump($location);
    var_dump($myWebContext);
    var_dump($search);
    //phpinfo();
    echo "</pre>";
  }


    return true;
  }

  /*
   * showPageNav creates the paging navigation shown at the bottom of search results.
   *
   * pre: retrieveResults previously called to populate the global search object with data
   * post: none
   */
  function showPageNav() {
    global $search, $a_href;

    $total_pages = $search->total_matching / RESULTS_PER_PAGE;
    // check if there is a remainder
    if (is_int($total_pages))
      $total_pages = (int)$total_pages;
    else
      $total_pages = (int)($total_pages + 1);

    // make sure current page is less than $total_pages
    if ($search->page > $total_pages) $search->page = $total_pages - 1;

    // find our max previous page
    if ($search->page < PAGING_BUFFER)
      $start = 0;
    else
      $start = $search->page - PAGING_BUFFER;

    echo "<div id=\"agencysearchpagenav\">Pages ($total_pages): ";
    if ($search->page != 0) echo "<a href=\"" . $a_href . "&amp;p=1" . "\"><span>&#171; first</span></a> " .
      "<a href=\"" . $a_href . "&amp;p=" . urlencode($search->page) . "\"><span>&#8249; prev</span></a> ";
    if ($search->page - PAGING_BUFFER > 0) echo " ... ";
    for ($i = $start; $i < $total_pages; $i++) {
      if ($i == $search->page)
        echo "<strong>";    // highlight the current page
      else
        echo "<a href=\"" . $a_href . "&amp;p=" . urlencode($i + 1) . "\">";

      echo ($i + 1);

      if ($i == $search->page)
        echo "</strong> ";    // finish highlighting the current page
      else
        echo "</a> ";

      // break out of loop if we have reached our paging limit
      if ($i + 1 == $search->page + PAGING_BUFFER) break;

      // display link separator
      if ($i != $total_pages - 1) echo "- ";
    }
    if ($search->page + PAGING_BUFFER < $total_pages - 1) echo " ... ";
    if ($search->page != $total_pages - 1) echo "<a href=\"" . $a_href . "&amp;p=" . urlencode($search->page + 2) . "\"><span>next &#8250;</span></a> " .
      "<a href=\"" . $a_href . "&amp;p=" . urlencode($total_pages) . "\"><span>last &#187;</span></a>";
    echo "</div>\n";
  }

  /*
   * showSearchForm does what it suggests
   *
   * pre: this function requires a passed string via $msg.  This string is then displayed at the top of the form.
   * post: the form outputted to the screen.
   */
function showSearchForm($msg)
{
  global $search;
  if (DISPLAY_SPELLING_SUGGESTION)
  {
    if ($search->altquery)
    {
      if (isset($_GET["s"]))
      {
        $msg .= " <strong>Did you mean: <a href=\"?s=" . $_GET["s"] . "&amp;query=" . urlencode(str_replace("\\", "", $search->altquery)) . "\">" . str_replace("\\", "", $search->altquery) . "</a>?</strong>";
      } else {
        $msg .= " <strong>Did you mean: <a href=\"?query=" . urlencode(str_replace("\\", "", $search->altquery)) . "\">" . str_replace("\\", "", $search->altquery) . "</a>?</strong>";
      }
    }
  }
?>
<div id="agencysearchform">
<p><?php echo $msg ?></p>
<?php
}

  /*
   * This function displays the search form and if a previous search has been made, also displays the results.
   *
   * pre: none
   * post: the search form outputted to the screen and any results if a previous query has been made.
   */
  function searchPanoptics() {
    global $search, $siteArea, $isPublicationsSearch, $RadioText, $RadioText2, $RadioTheme2, $PublicationType;

    if ($search->query != "") {
      // display search results
      if (retrieveResults()) {
        showSearchForm("Search summary: <strong>" . $search->fully_matching . "</strong> fully matching plus <strong>" . $search->partially_matching . "</strong> partially matching documents found.");

  echo "<fieldset>\n<legend>Search again</legend>\n<form action=\"search.php\" method=\"get\">\n";
  echo "<label for=\"query\">Search:</label>&nbsp;<input type=\"text\" name=\"query\" value=\"".htmlspecialchars(str_replace('\"',"&quot;",str_replace("\'","'",$search->query)))."\" id=\"query\" size=\"25\" />\n";
  echo "<hr align=\"center\" width=\"80%\" />\n";
  echo "<p class=\"searchoptions\">\n";

  //Write the current site area radio button
  if ($RadioText != "")
  {
    if ($siteArea == $search->theme || $search->theme == "deh")
    {
      writeThemeRadioButton($search->cur_site_area_theme, "True", $RadioText);
    } else {
      writeThemeRadioButton($search->cur_site_area_theme, "", $RadioText);
    }
  }

  //Write the 2nd current site area radio button if required
  if ($RadioText2 != "")
  {
    if ($RadioTheme2 == $search->theme )
    {
      writeThemeRadioButton($RadioTheme2, "True", $RadioText2);
    } else {
      writeThemeRadioButton($RadioTheme2, "", $RadioText2);
    }
  }

//  //Start DEH Radio Button
//  if($search->theme == "deh")
//  {
//    writeThemeRadioButton("deh", "True", "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>");
//  } else {
//    writeThemeRadioButton("deh", "", "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>");
//  }
//  //End DEH Radio Button

  //Start Environment Portal Radio Button
  if($search->theme == "all")
  {
    writeThemeRadioButton("all", "True", "Environment Portal");
  } else {
    writeThemeRadioButton("all", "", "Environment Portal");
  }
  //End Environment Portal Radio Button

  //Start australia.gov.au Radio Button
  if($search->theme == "allgov")
  {
    writeThemeRadioButton("allgov", "True", "australia.gov.au");
  } else {
    writeThemeRadioButton("allgov", "", "australia.gov.au");
  }
  //End australia.gov.au Radio Button

  if ($search->show_pub_search_again == "yes")
  {

    echo "<hr align=\"center\" width=\"80%\" />\n";

  //Start All Content Option
    if($isPublicationsSearch == "False")
    {
      writeTypeRadioButton("True", "", "True", "All content");
    } else {
      writeTypeRadioButton("True", "", "", "All content");
    }
  //End All Content Option

  //Start Publications Only
    if($isPublicationsSearch == "True")
    {
      writeTypeRadioButton("True", "publications", "True", "Publications only");
    } else {
      if($search->theme == "allgov")
      {
        writeTypeRadioButton("False", "publications", "", "Publications only");
      } else {
        writeTypeRadioButton("True", "publications", "", "Publications only");
      }
    }
  //End Publications Only
  }

        echo "</p>\n";
        echo "<input type=\"submit\" id=\"submit\" value=\"search\" />\n";
        echo "<a href=\"http://www.funnelback.com\"><img src=\"http://www.deh.gov.au/images/funnelback_search.gif\" width=\"102\" height=\"29\" alt=\"Search powered by Funnelback\" /></a>\n";
        echo "</form>\n";
        echo "</fieldset>\n";
        echo "</div>";
        echo "<div id=\"agencysearchresults\">\n";

        if (count($search->results) > 0) {
          echo "<ol start=\"" . $search->currstart . "\">\n";
          for ($i = 0; $i < count($search->results); $i++) {
            echo "  <li>" .
              "<strong><a href=\"" . htmlspecialchars($search->results[$i]->live_url) . "\">" . $search->results[$i]->title . "</a></strong><div class=\"agencysearchsummary\"><span>Extract:</span> " . $search->results[$i]->summary . "</div><div class=\"agencysearchinfo\">" .
              htmlspecialchars($search->results[$i]->live_url) . " - " . (int)($search->results[$i]->filesize / 1024) . "kb - <strong>[ " . $search->results[$i]->filetype . " ]</strong> - " .
              $search->results[$i]->date .
              "</div>" .
            "</li>\n";
          }
          echo "</ol>\n";

          showPageNav();
        }

        echo "</div>\n";
      }
    }
    else {
      // display search form
      showSearchForm("Please enter a search term.");
    }
  }

  /*
   * This function writes the radio button for the theme section search again box
   */
function writeThemeRadioButton($tmpThemeName, $tmpChecked, $tmpLabel)
{
  echo "<input type=\"radio\" id=\"theme_$tmpThemeName\" name=\"theme\" value=\"$tmpThemeName\"";
  if ($tmpChecked == "True") { echo " checked=\"checked\""; }
  //Add the show hide function for the "Publications only" radio button
  if ($tmpThemeName == "allgov")
  {
    echo " onclick=\"hideRadioButton(this.form.type)\"";
  } else {
    echo " onclick=\"showHidden('typeoption_publications')\"";
  }
  echo " style=\"border: 0px;\" /><label for=\"theme_$tmpThemeName\">$tmpLabel</label>";
  echo "<br />\n";
}

  /*
   * This function writes the radio button for the search again box.  It has special code for the show hide functionality
   */
function writeTypeRadioButton($tmpShow, $tmpThemeName, $tmpChecked, $tmpLabel)
{
  if ($tmpShow == "True")
  {
    echo "<span class=\"shown\" id=\"typeoption_$tmpThemeName\">";
  } else {
    echo "<span class=\"hidden\" id=\"typeoption_$tmpThemeName\">";
  }
  echo "<input type=\"radio\" id=\"type_$tmpThemeName\" name=\"type\" value=\"$tmpThemeName\"";
  if ($tmpChecked == "True") { echo " checked=\"checked\""; }
  echo " style=\"border: 0px;\" /><label for=\"type_$tmpThemeName\">$tmpLabel</label></span>";
  echo "<br />\n";
}


  /*
   * initiate the panoptics search
   */
  searchPanoptics();
?>


<?php
  /*
   * 2006 10 20 - Alex Fahey 0419 47 98 98
   * This file has been built to provide the search results for all non-DEH webiste.
   * It expects to be passed the following parameters via the URL
   *    query --> This is what the user is searching for
   *    theme --> This is used indicate where the user would like to search
   *    type --> This is used indicate if the search is for any specific type of document (NB: only a value of "publications" has any effect at this time)
   *
   * It also expects the following variable to be set in the page that is including this script
   *   $siteArea --> This indicates where the search page is located and sets the default search areas
   *   Below is example code to show how to include the search code and $siteArea into a web page
   *    <?php $siteArea = "atmosphere"; ?>
   *    <?php include '../includes/php/funnelback.php'; ?>
   *
   *


//Setup some inital constants
// define constants
define("COLLECTION", "deh");  // tells funnelback to use the DEH federated collection
define("RESULTS_PER_PAGE", 20);
define("PAGING_BUFFER", 5);   // how many pages either side of the current page to display
define("DISPLAY_SPELLING_SUGGESTION", true);  // when keywords are spealt incorrectly, should the form suggest an alternative?
define("SEARCH_URL", "http://bureau-query.funnelback.com/search/xml.cgi");
define("SEARCH_URL_AUST_GOV", "http://agencysearch.australia.gov.au/search/xml.cgi");

// define globals
$search = new CSearch;  // CSearch object containing our results data
$is_result_sum = null;  // are we in the results summary heirarchy?
$result_sum_tag = ""; // which tag are we within the results summary?

$is_spelling = null;    // are we in the suggested spelling heirarchy?
$spelling_tag = "";   // which tag are we within the suggested spelling?

$result = null;     // temporary CResult object
$is_result = null;    // are we in the results heirarchy?
$result_tag = "";     // which tag are we within the results?

$a_href = "";

//Update the query if one has been passed to this script
if (isset($_GET["query"])) $search->query = $_GET["query"];

//Update the theme if one has been passed to this script
if (isset($_GET["theme"])) $search->theme = $_GET["theme"];

//Work out if we are doing a publication search
if (isset($_GET["type"])) {if($_GET["type"] == "publications") {$search->is_publications_search = True; }}

//Write the page title
print "<h1><a name=\"top\" id=\"top\">Search results";
if ($search->is_publications_search) print " (publications only)";
print "</a></h1>";

//Get the site and search site from the URL
if (isset($_GET["site"])) $search->site = $_GET["site"];
if (isset($_GET["searchsite"]))
{
  if ($_GET["searchsite"] != "")
  {
    $search->search_site = $_GET["searchsite"];
  } else {
    if (isset($_GET["site"])) $search->search_site = $_GET["site"];
  }
} else {
  if (isset($_GET["site"])) $search->search_site = $_GET["site"];

}

//Sort out the search options based on the current site
if (isset($_GET["site"]))
{
  switch($_GET["site"])
  {

  case "ahc.gov.au":
    $RadioText = "Australian Heritage Council";
    $RadioSite =  "ahc.gov.au";
  break;

  }
}


  class CSearch {
    var $theme;
    var $query;
    var $fully_matching;
    var $partially_matching;
    var $total_matching;
    var $num_ranks;
    var $currstart;
    var $currend;
    var $nextstart;
    var $results;
    var $page;
    var $spelling;
    var $is_publications_search;
    var $site;
    var $search_site;

    function CSearch() {
      $this->theme = "";
      $this->query = "";
      $this->fully_matching = 0;
      $this->partially_matching = 0;
      $this->total_matching = 0;
      $this->num_ranks = 0;
      $this->currstart = 0;
      $this->currend = 0;
      $this->nextstart = 0;
      $this->results = array();
      $this->page = 0;
      $this->spelling = null;
      $this->$is_publications_search = False;
      $this->$site = "";
      $this->$search_site = "";
    }
  }

  class CResult {
    var $rank;
    var $score;
    var $title;
    var $collection;
    var $live_url;
    var $summary;
    var $cache_url;
    var $date;
    var $filesize;
    var $filetype;
    var $tier;
    var $docnum;

    function CResult() {
      // a very nice constructor
    }
  }

  function startElement($parser, $name, $attrs)
  {
    global $is_result_sum, $result_sum_tag;
    global $is_spelling, $spelling_tag;
    global $is_result, $result, $result_tag;

    // results summary
    if ($is_result_sum)
      $result_sum_tag = $name;
    elseif ($name == "RESULTS_SUMMARY")
      $is_result_sum = true;

    // suggested spelling
    if ($is_spelling)
      $spelling_tag = $name;
    elseif ($name == "SPELL")
      $is_spelling = true;

    // results
    if ($is_result)
      $result_tag = $name;
    elseif ($name == "RESULT") {
      $is_result = true;
      $result = new CResult();
    }
  }

  function characterData($parser, $data)
  {
    global $is_result_sum, $result_sum_tag, $search;
    global $is_spelling, $spelling_tag;
    global $is_result, $result_tag, $result;

    // results summary
    if ($is_result_sum) {
      if ($data = trim($data)) {
        switch($result_sum_tag) {
          case "FULLY_MATCHING":
            $search->fully_matching = (int)$data;
            break;
          case "PARTIALLY_MATCHING":
            $search->partially_matching = (int)$data;
            break;
          case "TOTAL_MATCHING":
            $search->total_matching = (int)$data;
            break;
          case "NUM_RANKS":
            $search->num_ranks = (int)$data;
            break;
          case "CURRSTART":
            $search->currstart = (int)$data;
            break;
          case "CURREND":
            $search->currend = (int)$data;
            break;
        }
      }
    }

    // suggested spelling
    if ($is_spelling) {
      if ($data = trim($data)) {
        switch($spelling_tag) {
          case "TEXT":
            $search->spelling .= $data;
            break;
        }
      }
    }

    // results
    if ($is_result) {
      if ($data = trim($data)) {
        switch($result_tag) {
          case "RANK":
            $result->rank .= $data;
            break;
          case "SCORE":
            $result->score .= $data;
            break;
          case "TITLE":
            $result->title .= $data;
            break;
          case "COLLECTION":
            $result->collection .= $data;
            break;
          case "LIVE_URL":
            $result->live_url .= $data;
            break;
          case "SUMMARY":
            $result->summary .= $data;
            break;
          case "CACHE_URL":
            $result->cache_url .= $data;
            break;
          case "DATE":
            $result->date .= $data;
            break;
          case "FILESIZE":
            $result->filesize .= $data;
            break;
          case "FILETYPE":
            $result->filetype .= $data;
            break;
          case "TIER":
            $result->tier .= $data;
            break;
          case "DOCNUM":
            $result->docnum .= $data;
            break;
        }
      }
    }
  }

  function endElement($parser, $name)
  {
    global $is_result_sum, $result_sum_tag;
    global $is_spelling, $spelling_tag;
    global $is_result, $result_tag, $result, $search;

    // results summary
    if ($name == "RESULTS_SUMMARY") {
      $result_sum_tag = "";
      $is_result_sum = false;
    }

    // suggested spelling
    if ($name == "SPELL") {
      $spelling_tag = "";
      $is_spelling = false;

      // if the suggested spelling is the same as the query, then there was no suggestion
      if ($search->spelling == $search->query) $search->spelling = null;
    }

    // results
    if ($name == "RESULT") {
      $result_tag = "";
      $is_result = false;
      array_push($search->results, $result);
      $result = null;
    }
  }

  /*
   * retrieveResults determines the HTTP request, sends it off to SEARCH_URL, retrieves the XML reply and parses it
   * into the CSearch structure defined above.
   *
   * pre: a search query has been made
   * post: returns true if the process was successful having populated the global search object with the reply


function retrieveResults() {
  global $search, $a_href, $isPublicationsSearch;
  $content = "";
  if (isset($_GET["p"])) $search->page = (int)$_GET["p"] - 1;
  $scope = ((bool)$_GET["s"]) ? "on" : "off";

  //If the theme is "allgov" then we need to search the Australia.gov index, otherwise just search the DEH collection
  if($search->theme == "allgov")
  {
    $location = SEARCH_URL_AUST_GOV."?collection=gov&query=" . urlencode($search->query) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE) . "&fqp=1&gscope1=0";
  } else {
    $location = SEARCH_URL . "?collection=" . urlencode(COLLECTION) . "&query=" . urlencode($search->query) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE);

    //Sort out the site we are searching
    if($search->search_site !="") {$location = $location . "&scope=" . urlencode($search->search_site); }

    //Sort out the Theme option
    if($search->theme != "") {$location = $location . "&meta_x_sand=" . urlencode($search->theme); }

    //Sort out the URL Restrictions for this search
    //if($search->is_publications_search) {$location = $location . "&scope=" . urlencode(",/publications/");}
  }

  //Print the location string to debug
  print "$location";

  //Set up the page and Next links
  $a_href = $_SERVER["PHP_SELF"] . "?query=" . urlencode($search->query) . "&amp;s=" . urlencode($_GET["s"]);

  //If it is a Publications search then set the theme to pubs
  if ( $search->theme ) { $a_href = $a_href . "&amp;theme=" . urlencode($search->theme);}
  if($isPublicationsSearch == "True")
  {
    $a_href = $a_href . "&amp;type=publications";
  }

    // retrieve xml feed
    $myWebContext = $_SERVER['MY_SERVER_LOCATION'];

    if ($myWebContext == "DMZ") {
      if (!($fp = fopen($location, "r"))) {
        return false;
      }
      while ($filechunk = fread($fp, 4096)) {
        $content .= $filechunk;
      }
      fclose($fp);
    }
    else {
      $proxyHost = 'proxy.deh.gov.au';
      $proxyPort = '8080';

      $proxySock = fsockopen($proxyHost,$proxyPort);
      if ($proxySock) {
        fputs($proxySock, "GET $location HTTP/1.0\r\n");
        fputs($proxySock, "Host: $proxyHost\r\n\r\n");
        while (!feof($proxySock)) {
          $content .= fread($proxySock,4096);
        }
        fclose($proxySock);
        $content = substr($content, strpos($content,"\r\n\r\n")+4);
      }
      else {
        return false;
      }
    }

    // add CDATA to summary tags
    // this will prevent the <b>...</b> tags from being interpreted as another XML tag
    $content = str_replace("<summary>", "<summary><![CDATA[", $content);
    $content = str_replace("</summary>", "]]></summary>", $content);

    $xmlParser = xml_parser_create();
    xml_parser_set_option($xmlParser, XML_OPTION_SKIP_WHITE, 1);
    xml_set_element_handler($xmlParser, "startElement", "endElement");
    xml_set_character_data_handler($xmlParser, "characterData");
    xml_parse($xmlParser, $content);
    xml_parser_free($xmlParser);

    // uncomment the following if you wish to view the results of the feed
    // leave this commented when in production
    // echo "<pre>";
    // var_dump($location);
    // var_dump($myWebContext);
    // var_dump($search);
    // phpinfo();
    // echo "</pre>";

    return true;
  }

  /*
   * showPageNav creates the paging navigation shown at the bottom of search results.
   *
   * pre: retrieveResults previously called to populate the global search object with data
   * post: none

  function showPageNav() {
    global $search, $a_href;

    $total_pages = $search->total_matching / RESULTS_PER_PAGE;
    // check if there is a remainder
    if (is_int($total_pages))
      $total_pages = (int)$total_pages;
    else
      $total_pages = (int)($total_pages + 1);

    // make sure current page is less than $total_pages
    if ($search->page > $total_pages) $search->page = $total_pages - 1;

    // find our max previous page
    if ($search->page < PAGING_BUFFER)
      $start = 0;
    else
      $start = $search->page - PAGING_BUFFER;

    echo "<div id=\"agencysearchpagenav\">Pages ($total_pages): ";
    if ($search->page != 0) echo "<a href=\"" . $a_href . "&amp;p=1" . "\"><span>&#171; first</span></a> " .
      "<a href=\"" . $a_href . "&amp;p=" . urlencode($search->page) . "\"><span>&#8249; prev</span></a> ";
    if ($search->page - PAGING_BUFFER > 0) echo " ... ";
    for ($i = $start; $i < $total_pages; $i++) {
      if ($i == $search->page)
        echo "<strong>";    // highlight the current page
      else
        echo "<a href=\"" . $a_href . "&amp;p=" . urlencode($i + 1) . "\">";

      echo ($i + 1);

      if ($i == $search->page)
        echo "</strong> ";    // finish highlighting the current page
      else
        echo "</a> ";

      // break out of loop if we have reached our paging limit
      if ($i + 1 == $search->page + PAGING_BUFFER) break;

      // display link separator
      if ($i != $total_pages - 1) echo "- ";
    }
    if ($search->page + PAGING_BUFFER < $total_pages - 1) echo " ... ";
    if ($search->page != $total_pages - 1) echo "<a href=\"" . $a_href . "&amp;p=" . urlencode($search->page + 2) . "\"><span>next &#8250;</span></a> " .
      "<a href=\"" . $a_href . "&amp;p=" . urlencode($total_pages) . "\"><span>last &#187;</span></a>";
    echo "</div>\n";
  }

  /*
   * showSearchForm does what it suggests
   *
   * pre: this function requires a passed string via $msg.  This string is then displayed at the top of the form.
   * post: the form outputted to the screen.

  function showSearchForm($msg) {
    global $search;

    if (DISPLAY_SPELLING_SUGGESTION)
      if ($search->spelling)
        $msg .= " <strong>Did you mean: <a href=\"?s=" . $_GET["s"] . "&amp;query=" . urlencode($search->spelling) . "\">" . $search->spelling . "</a>?</strong>";

?>
<div id="agencysearchform">
<p><?php echo $msg ?></p>
<?php
  }

//This function displays the search form and if a previous search has been made, also displays the results.
function searchPanoptics()
{
  global $search, $siteArea, $isPublicationsSearch, $RadioText, $RadioText2, $RadioText3, $RadioText4, $RadioText5, $RadioSite, $RadioSite2, $RadioSite3, $RadioSite4, $RadioSite5, $PublicationType;
  if ($search->query != "")
  {
    // display search results
    if (retrieveResults())
    {
      showSearchForm("Search summary: <strong>" . $search->fully_matching . "</strong> fully matching plus <strong>" . $search->partially_matching . "</strong> partially matching documents found.");
      echo "<fieldset>\n<legend>Search again</legend>\n<form action=\"search_new.php\" method=\"get\">\n";
      echo "<label for=\"query\">Search:</label>&nbsp;<input type=\"text\" name=\"query\" value=\"".str_replace('\"',"&quot;",$search->query)."\" id=\"query\" size=\"25\" />\n";
      echo "<p class=\"searchoptions\">\n";

      //Write the current site radio button
      if ($RadioText != "") {writeSearchSiteRadioButton($RadioSite, ($search->search_site == $RadioSite), $RadioText);}

      //Write the second site radio button
      if ($RadioText2 != "") {writeSearchSiteRadioButton($RadioSite2, ($search->search_site == $RadioSite2), $RadioText2);}

      //Write the third site radio button
      if ($RadioText3 != "") {writeSearchSiteRadioButton($RadioSite3, ($search->search_site == $RadioSite3), $RadioText3);}

      //Write the third site radio button
      if ($RadioText4 != "") {writeSearchSiteRadioButton($RadioSite4, ($search->search_site == $RadioSite4), $RadioText4);}

      //Write the third site radio button
      if ($RadioText5 != "") {writeSearchSiteRadioButton($RadioSite5, ($search->search_site == $RadioSite5), $RadioText5);}

      //Start Environment Portal Radio Button
      if($search->theme == "all")
      {
        writeSearchSiteRadioButton("all", "True", "Environment Portal");
      } else {
        writeSearchSiteRadioButton("all", "", "Environment Portal");
      }
      //End Environment Portal Radio Button

      //Start australia.gov.au Radio Button
      if($search->theme == "allgov")
      {
        writeSearchSiteRadioButton("allgov", "True", "australia.gov.au");
      } else {
        writeSearchSiteRadioButton("allgov", "", "australia.gov.au");
      }
      //End australia.gov.au Radio Button

      echo "</p>\n";
      echo "<input type=\"submit\" id=\"submit\" value=\"search\" />\n";
      echo "<a href=\"http://www.funnelback.com\"><img src=\"http://www.deh.gov.au/images/funnelback_search.gif\" width=\"102\" height=\"29\" alt=\"Search powered by Funnelback\" /></a>\n";
      echo "</form>\n";
      echo "</fieldset>\n";
      echo "</div>";
      echo "<div id=\"agencysearchresults\">\n";

      if (count($search->results) > 0)
      {
        echo "<ol start=\"" . $search->currstart . "\">\n";
        for ($i = 0; $i < count($search->results); $i++)
        {
          echo "  <li>" .
          "<strong><a href=\"" . htmlspecialchars($search->results[$i]->live_url) . "\">" . $search->results[$i]->title . "</a></strong><div class=\"agencysearchsummary\"><span>Extract:</span> " . $search->results[$i]->summary . "</div><div class=\"agencysearchinfo\">" .
          htmlspecialchars($search->results[$i]->live_url) . " - " . (int)($search->results[$i]->filesize / 1024) . "kb - <strong>[ " . $search->results[$i]->filetype . " ]</strong> - " .
          $search->results[$i]->date .
          "</div>" .
          "</li>\n";
        }
        echo "</ol>\n";
        showPageNav();
      }
      echo "</div>\n";
    }
  } else {
    // Display an error message
    showSearchForm("Please enter a search term.");
  }
}

//This function writes the radio button for the theme section search again box
function writeSearchSiteRadioButton($tmpSiteName, $tmpChecked, $tmpLabel)
{
  echo "<input type=\"radio\" id=\"searchsite_$tmpSiteName\" name=\"searchsite\" value=\"$tmpSiteName\"";
  if ($tmpChecked) { echo " checked=\"checked\""; }
  //Add the show hide function for the "Publications only" radio button
  if ($$tmpSiteName == "allgov")
  {
    echo " onclick=\"hideRadioButton(this.form.type)\"";
  } else {
    echo " onclick=\"showHidden('typeoption_publications')\"";
  }
  echo " /><label for=\"searchsite_$tmpSiteName\">$tmpLabel</label>";
  echo "<br />\n";
}

//initiate the panoptics search
searchPanoptics();
   */

?>
