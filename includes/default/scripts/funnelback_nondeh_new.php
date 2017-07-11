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
$SearchAgainPage = "";
$PublicationsSearchString = "";
switch($search->site)
{

  case "ahc.gov.au":
    define("THEME", "ahc");
    $search->theme = "ahc";
    $search->cur_site_area_theme = "ahc";
    $search->url_limit_string = "ahc.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "yes";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "ahc";
    $RadioText = "Australian Heritage Council";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "anra.gov.au":
    define("THEME", "anra");
    $search->theme = "anra";
    $search->cur_site_area_theme = "anra";
    $search->url_limit_string = "anra.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "anra";
    $RadioText = "Australian Natural Resources Atlas";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "culturaldata.gov.au":
    define("THEME", "culturaldata");
    $search->theme = "culturaldata";
    $search->cur_site_area_theme = "culturaldata";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "culturaldata";
    $RadioText = "Cultural Data Online";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "artsearch.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "artbank.gov.au":
    define("THEME", "artbank");
    $search->theme = "artbank";
    $search->cur_site_area_theme = "artbank";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "artbank";
    $RadioText = "Artbank";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "artsearch.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "cmc.gov.au":
    define("THEME", "cmc");
    $search->theme = "cmc";
    $search->cur_site_area_theme = "cmc";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "cmc";
    $RadioText = "Cultural Ministers Council";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "artsearch.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "culture.gov.au":
    define("THEME", "culture");
    $search->theme = "culture";
    $search->cur_site_area_theme = "culture";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "culture";
    $RadioText = "Culture and Recreation Portal";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "artsearch.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "arts.gov.au":
    define("THEME", "arts");
    $search->theme = "arts";
    $search->cur_site_area_theme = "arts";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "no";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "yes";
    $search->show_search_australia_gov = "yes";
    $siteArea = "arts";
    $RadioText = "Arts and culture";
    $RadioText2 = "<acronym title=\"Department of the Environment, Water, Heritage and the Arts\">DEWHA</acronym>";
    $RadioTheme2 = "deh";
    $SearchAgainPage = "newartsearch.php";
/*    $SearchAgainPage = "http://www.arts.gov.au/search";*/
  break;

  case "australianalps.deh.gov.au":
    define("THEME", "alps");
    $search->theme = "alps";
    $search->cur_site_area_theme = "alps";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "alps";
    $RadioText = "Australian Alps";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "cockatooisland.gov.au":
    define("THEME", "cockatoo");
    $search->theme = "cockatoo";
    $search->cur_site_area_theme = "cockatoo";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "yes";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "cockatoo";
    $RadioText = "Cockatoo Island";
    $RadioText2 = "Sydney Harbour Trust";
    $RadioTheme2 = "harbourportal";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/pubs/";
  break;

  case "cwg.gov.au":
    define("THEME", "watergrants");
    $search->theme = "watergrants";
    $search->cur_site_area_theme = "watergrants";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "waterrating";
    $RadioText = "Community Water Grants";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "harbourtrust.gov.au":
    define("THEME", "harbour");
    $search->theme = "harbour";
    $search->cur_site_area_theme = "harbour";
    $search->show_pub_search_again = "no";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "harbour";
    $RadioText = "harbourtrust.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/docs/";
  break;

  case "heritage.gov.au":
    define("THEME", "heritage");
    $search->theme = "heritage";
    $search->cur_site_area_theme = "heritage";
    $search->show_pub_search_again = "no";
    $search->show_search_heritage_portal = "yes";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "heritage";
    $RadioText = "heritage.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "lebmf.gov.au":
    define("THEME", "lebmf");
    $search->theme = "lebmf";
    $search->cur_site_area_theme = "lebmf";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "lebmf";
    $RadioText = "Lake Eyre Basin Agreement";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "napswq.gov.au":
    define("THEME", "salinity");
    $search->theme = "salinity";
    $search->cur_site_area_theme = "salinity";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "salinity";
    $RadioText = "Action Plan for Salinity &amp; Water Quality";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "nht.gov.au":
    define("THEME", "nht");
    $search->theme = "nht";
    $search->cur_site_area_theme = "nht";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "yes";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "nht";
    $RadioText = "Natural Heritage Trust";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "npi.gov.au":
    define("THEME", "deh");
    $search->theme = "deh";
    $search->cur_site_area_theme = "deh";
    $search->url_limit_string = "npi.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "npi";
    $RadioText = "npi.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "nrm.gov.au":
    define("THEME", "nrm");
    $search->theme = "nrm";
    $search->cur_site_area_theme = "nrm";
    $search->url_limit_string = "";
    $search->show_pub_search_again = "no";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "nrm";
    $RadioText = "nrm.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "oilrecycling.gov.au":
    define("THEME", "oilrecycling");
    $search->theme = "oilrecycling";
    $search->cur_site_area_theme = "oilrecycling";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "oilrecycling";
    $RadioText = "Used oil recycling";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "apfp":
    define("THEME", "heritage");
    $search->theme = "heritage";
    $search->cur_site_area_theme = "heritage";
    $search->url_limit_string = "/apfp/";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "heritage";
    $RadioText = "Asia-Pacific Focal Point";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "orer.gov.au":
    define("THEME", "atmosphere");
    $search->theme = "atmosphere";
    $search->cur_site_area_theme = "atmosphere";
    $search->url_limit_string = "orer.gov.au";
    $search->show_pub_search_again = "no";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "orer";
    $RadioText = "orer.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "waterrating.gov.au":
    define("THEME", "waterrating");
    $search->theme = "waterrating";
    $search->cur_site_area_theme = "waterrating";
    $search->url_limit_string = "waterrating.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "waterrating";
    $RadioText = "waterrating.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "waterwatch.gov.au":
    define("THEME", "waterwatch");
    $search->theme = "waterwatch";
    $search->cur_site_area_theme = "waterwatch";
    $search->url_limit_string = "waterwatch.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "yes";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "waterwatch";
    $RadioText = "waterwatch.gov.au";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;

  case "weeds.gov.au":
    define("THEME", "deh");
    $search->theme = "weeds";
    $search->cur_site_area_theme = "weeds";
    $search->url_limit_string = "weeds.gov.au";
    $search->show_pub_search_again = "yes";
    $search->show_search_heritage_portal = "no";
    $search->show_search_environment_portal = "no";
    $search->show_search_arts_portal = "no";
    $search->show_search_australia_gov = "yes";
    $siteArea = "weeds";
    $RadioText = "weeds.gov.au";
    $RadioText2 = "Weeds Portal";
    $RadioTheme2 = "weedsportal";
    $SearchAgainPage = "search.php";
    $PublicationsSearchString = "/publications/";
  break;
}
  // you do not need to change anything else below, anything you change might break functionality
  // ********* end customisable area *********



  $a_href = "";
  $search->query = "";
if (isset($_GET["query"])) $search->query = htmlspecialchars(urldecode ($_GET["query"]), ENT_QUOTES);
  $search->altquery = $search->query;

//Update the theme if a different radio button has been checked by the user
  if (isset($_GET["theme"])) $search->theme = $_GET["theme"];

  //IF the current them is not the same as the them for the current site area then get rid of the scope search
  if($search->theme != $search->cur_site_area_theme)
  {
    $search->url_limit_string = "";
  }

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
    var $show_search_australia_gov;
    var $show_search_environment_portal;
    var $show_search_arts_portal;
    var $show_search_heritage_portal;

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
      $this->show_search_australia_gov = "no";
      $this->show_search_environment_portal = "no";
      $this->show_search_arts_portal = "no";
      $this->show_search_heritage_portal = "no";
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
    var $md;
    var $x;
    var $y;
    var $z;
    var $Q;

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
          case "MD":
            $result->md .= $data;
            break;
          case "X":
            $result->x .= $data;
            break;
          case "Y":
            $result->y .= $data;
            break;
          case "Z":
            $result->z .= $data;
            break;
          case "Q":
            $result->Q .= $data;
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

//echo "<h1>Results</h1>";
//foreach ($result as $value) {
//    echo "<p>Value: $value</p>\n";
//}

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
    global $search, $a_href, $isPublicationsSearch, $siteArea, $PublicationsSearchString;
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
        $location = SEARCH_URL_AUST_GOV."?collection=agencies&query=" . urlencode(str_replace('\"','"',$search->query)) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE) . "&gscope1=0";

    } else {
        $location = SEARCH_URL . "?collection=" . urlencode(COLLECTION) . "&query=" . urlencode($search->query) . "&start_rank=" . urlencode(($search->page * RESULTS_PER_PAGE) + 1) . "&num_ranks=" . urlencode(RESULTS_PER_PAGE);

        //Sort out the theme for the search and page links
  if ($isDebuging == "True")
  {
    print "<h1>Limit to = ".$search->url_limit_string."</h1>";
    print "Publications Search String = ".$PublicationsSearchString."</h1>";
  }

  if($search->theme == "all")
  {
    $search->url_limit_string = "";
    if($isPublicationsSearch == "True") {$search->url_limit_string = $PublicationsSearchString; }
    if($search->url_limit_string != "") {$location = $location . "&scope=" . urlencode($search->url_limit_string); };
  } else {
    $location = $location . "&profile=" . urlencode($search->theme);
    if($isPublicationsSearch == "True") {$search->url_limit_string = $search->url_limit_string . $PublicationsSearchString; }
    if($search->url_limit_string != "") {$location = $location . "&scope=" . urlencode($search->url_limit_string); };
  }
    }


  if ($isDebuging == "True")
  {
    print "<h1>Limit to = ".$search->url_limit_string."</h1>";
    print "Request sent to FunnelBack: $location";
  }

  //Set up the page and Next links
  //if($search->site == "greenhouse.gov.au")
  //{
   // $a_href = "http://draft/greenhouse.gov.au/search.php?query=" . urlencode($search->query);
  //}
    if($search->site == "arts.gov.au")
  {
    $a_href = "http://www.environment.gov.au/newartsearch.php?query=" . urlencode($search->query);
  } else {
    $a_href = "/search.php?query=" . urlencode($search->query);
  }
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
      $proxyLogin = "pblookup";
      $proxyPWD = "pbl00kup";

      $proxySock = fsockopen($proxyHost,$proxyPort);

      if ($proxySock)
      {
        fputs($proxySock, "GET $location HTTP/1.0\r\nHost: $proxyHost:8080\r\nProxy-Authorization: Basic ".base64_encode("$proxyLogin:$proxyPWD")."\r\n\r\n");
        while (!feof($proxySock))
        {
          $content .= fread($proxySock,4096);
        }
        fclose($proxySock);
        $content = substr($content, strpos($content,"\r\n\r\n")+4);
      } else {
        return false;
      }
    }

//    if ($isDebuging == "True")
//    {
//      print "<h2>Raw results from FunnelBack</h2> $content";
//    }

    // add CDATA to summary tags
    // this will prevent the <b>...</b> tags from being interpreted as another XML tag
//    $content = str_replace("<summary>", "<summary><![CDATA[", $content);
//    $content = str_replace("</summary>", "]]></summary>", $content);

    //Change MD tags
    $content = preg_replace("/<md f=\"x\">(.*?)<\/md>/", "<x>\\1</x>", $content);
    $content = preg_replace("/<md f=\"y\">(.*?)<\/md>/", "<y>\\1</y>", $content);
    $content = preg_replace("/<md f=\"z\">(.*?)<\/md>/", "<z>\\1</z>", $content);
    $content = preg_replace("/<md f=\"Q\">(.*?)<\/md>/", "<Q>\\1</Q>", $content);
//    $content = preg_replace("/<md f=\"X\">(.*?)<\/md>/", "<r>\\1<\/r>", $content);
//    $content = preg_replace("/<md f=\"Y\">(.*?)<\/md>/", "<s>\\1<\/s>", $content);
//    $content = preg_replace("/<md f=\"Z\">(.*?)<\/md>/", "<t>\\1<\/t>", $content);



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
    global $search, $siteArea, $isPublicationsSearch, $RadioText, $RadioText2, $RadioTheme2, $PublicationType, $SearchAgainPage;

    if ($search->query != "")
    {
      // display search results
      if (retrieveResults())
      {
        showSearchForm("Search summary: <strong>" . $search->fully_matching . "</strong> fully matching plus <strong>" . $search->partially_matching . "</strong> partially matching documents found.");
        echo "<fieldset>\n<legend>Search again</legend>\n<form action=\"".$SearchAgainPage."\" method=\"get\" class=\"test\">\n";
        echo "<label for=\"query\">Search:</label>&nbsp;<input type=\"text\" name=\"query\" value=\"".htmlspecialchars(str_replace('\"',"&quot;",str_replace("\'","'",$search->query)))."\" id=\"query\" size=\"25\" />\n";
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

  if($search->show_search_arts_portal == "yes")
  {
    //Start Arts Portal Radio Button
    if($search->theme == "artsexpanded")
    {
      writeThemeRadioButton("artsexpanded", "True", "Arts Portfolio");
    } else {
      writeThemeRadioButton("artsexpanded", "", "Arts Portfolio");
    }
    //End Arts Portal Radio Button
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

  if($search->show_search_heritage_portal == "yes")
  {
    //Start Heritage Portal Radio Button
    if($search->theme == "heritageexpanded")
    {
      writeThemeRadioButton("heritageexpanded", "True", "Heritage Portal");
    } else {
      writeThemeRadioButton("heritageexpanded", "", "Heritage Portal");
    }
    //End Heritage Portal Radio Button
  }

  if($search->show_search_environment_portal == "yes")
  {
    //Start Environment Portal Radio Button
    if($search->theme == "all")
    {
      writeThemeRadioButton("all", "True", "Environment Portal");
    } else {
      writeThemeRadioButton("all", "", "Environment Portal");
    }
    //End Environment Portal Radio Button
  }

  if($search->show_search_australia_gov == "yes")
  {
    //Start australia.gov.au Radio Button
    if($search->theme == "allgov")
    {
      writeThemeRadioButton("allgov", "True", "australia.gov.au");
    } else {
      writeThemeRadioButton("allgov", "", "australia.gov.au");
    }
    //End australia.gov.au Radio Button
  }

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
        echo "<a href=\"http://www.funnelback.com\"><img src=\"http://www.environment.gov.au/images/funnelback_search.gif\" width=\"102\" height=\"29\" alt=\"Search powered by Funnelback\" /></a>\n";
        echo "</form>\n";
        echo "</fieldset>\n";
        echo "</div>";
        echo "<div id=\"agencysearchresults\">\n";

        if (count($search->results) > 0) {
          echo "<ol start=\"" . $search->currstart . "\">\n";
          for ($i = 0; $i < count($search->results); $i++) {
            // if($search->results[$i]->title == "http://www.weeds.gov.au/searchdb.xml") // change by Kat Ng (Funnelback) 12/12/08
			if(preg_match("/www\.weeds\.gov\.au\/searchdb\.xml/",$search->results[$i]->title))		
            {
              echo "  <li><strong><a href=\"" . htmlspecialchars($search->results[$i]->Q) . "\">".$search->results[$i]->y." ".$search->results[$i]->x."</a></strong><div class=\"agencysearchsummary\"><span>Common name: </span>".$search->results[$i]->z."</div><div class=\"agencysearchinfo\">" .htmlspecialchars($search->results[$i]->Q) . " - " . (int)($search->results[$i]->filesize / 1024) . "kb - <strong>[ " . $search->results[$i]->filetype . " ]</strong> - " .$search->results[$i]->date ."</div></li>";
            } else {
              echo "  <li>" .
              "<strong><a href=\"" . htmlspecialchars($search->results[$i]->live_url) . "\">" . $search->results[$i]->title . "</a></strong><div class=\"agencysearchsummary\"><span>Extract:</span> " . $search->results[$i]->summary . "</div><div class=\"agencysearchinfo\">" .
              htmlspecialchars($search->results[$i]->live_url) . " - " . (int)($search->results[$i]->filesize / 1024) . "kb - <strong>[ " . $search->results[$i]->filetype . " ]</strong> - " .
              $search->results[$i]->date .
              "</div>" .
              "</li>\n";
            }


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
