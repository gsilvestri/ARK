<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* map_view.php
*
* Index for the map view
*
* PHP versions 4 and 5
*
* LICENSE:
*    ARK - The Archaeological Recording Kit.
*    An open-source framework for displaying and working with archaeological data
*    Copyright (C) 2008  L - P : Partnership Ltd.
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @category   base
* @package    ark
* @author     Guy Hunt <guy.hunt@lparchaeology.com>
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @copyright  1999-2008 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/map_view.php
* @since      File available since Release 0.6
*/


// PART1 - Basic setup

//this page
$pagename = 'map_view';

//GLOBAL INCLUDES
include('config/settings.php');
include('php/global_functions.php');
include('php/map/map_functions.php');

// Start the session
session_name($ark_name);
session_start();


// -- PAGE SETTINGS -- //
// handle missing config
if (!$pagename) {
    die ('ADMIN ERROR: No $pagename variable setup. Required as of v1.1, supersedes $filename');
}
// handle missing config
$pg_settings_nm = 'conf_page_'.$pagename;
$pg_settings = $$pg_settings_nm;
if (!$pg_settings) {
    die ("ADMIN ERROR: No settings (${$pg_settings_nm})found for the page $pagename");
}
// title for this HTML page
$page_title = $ark_name.' - '.$pg_settings['title'];
// the page's sgrp value
$psgrp = $pg_settings['sgrp'];
// current code directory (location of any files related to this page)
$cur_code_dir = $pg_settings['cur_code_dir'];


// -- AUTH -- //
$db = dbConnect($sql_server, $sql_user, $sql_pwd, $ark_db);
include_once ('php/auth/inc_auth.php');
// ANON LOGINS
// check for anon logins
//check if this is an anonymous login - if it is then prevent edits
if (isset($anonymous_login['username']) && $$liveuser->getProperty('handle') == $anonymous_login['username']){
    $anon_login = TRUE;
} else {
    $anon_login = FALSE;
}


// -- OTHER -- //
// browser
$browser = browserDetect();
$stylesheet = getStylesheet($browser);


// -- MAP SETTINGS -- //
include_once('php/map/getwmcmap.php');
if (isset($wmc) && is_array($wmc)) {
    $wmc_code = addslashes($wmc['wmc']);
    $_SESSION['legend_array'] = unserialize($wmc['legend_array']);
} else {
    $wmc = FALSE;
    $wmc_code = FALSE;
}

//GLOBALY required variables
$lang = reqArkVar('lang', $default_lang);
$view = reqArkVar('view', 'home');
$phpsessid = reqArkVar('PHPSESSID');
$update_db = reqQst($_REQUEST,'update_db');
$submiss_serial = reqArkVar('submiss_serial');
$map_action = reqQst($_REQUEST, 'map_action');


// PART2 - select the relevant page contents

$plc = $cur_code_dir.'global_map_view.php';
$view_title = getMarkup('cor_tbl_markup', $lang, $view);
$mk_map = getMarkup('cor_tbl_markup', $lang, 'map');
$mk_mapsize = getMarkup('cor_tbl_markup', $lang, 'map_mapsize');
$main_area_width = 'auto';

// ---------OUTPUT--------- //

?>

<?php echo "<!DOCTYPE ".$doctype.">" ?>

<html>
<head>
    <!-- title -->
    <title><?php echo $page_title ?></title>
    
    <!-- meta -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    
    <!-- stylesheets -->
    <link href="<?php echo $stylesheet ?>" type="text/css" rel="stylesheet"  media="screen" />
    <link href="<?php echo $skin_path ?>/stylesheets/ark_main_print.css" type="text/css" rel="stylesheet" media="print" />
    <link href="<?php echo $skin_path ?>/stylesheets/lightbox.css" type="text/css" rel="stylesheet" />
    <link href="<?php echo $skin_path ?>/images/ark_favicon.ico" rel="shortcut icon" />
    
    <!-- javascript libraries -->
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/jquery.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/php.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/prototype.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/scriptaculous.js?load=effects"></script>
    
    <!-- ARK javascript -->
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/js_functions.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/lightbox.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/livesearch.js"></script>
</head>

<body>

<!-- THE CONTENT WRAPPER -->
<div id="wrapper" class="wrp_map">

<!-- HEADER -->
<div id="hdr-print">
    <img src="skins/<?php echo $skin ?>/images/logo.png" alt="logo" />
</div>
<div id="hdr"> 
    <div id="hdr-tools">    
        <div id="version">v<?php echo $version ?></div>
        <!-- user info -->
        <div id="user-info">
            <?php $userinfo = mkUserInfo(); echo($userinfo); ?>
        </div>
        <?php print(mkSearchBox());?>
    </div>
    
    <!-- DYNAMIC NAVIGATION -->
    <div id="navcontainer">
        <?php print(mkNavMain($authorised_pages,$conf_linklist)) ?>
    </div>
</div>


<!-- The LEFT PANEL -->
<div id="lpanel">

<?php 
$mk_mapview = getMarkup('cor_tbl_markup', $lang, 'mapview');
echo "<h1>{$mk_mapview}</h1>";

// TOOLBAR
// Make the toolbar
$tools = "<ul id=\"map_options\">";
// First make a top row of map functions
$tools .= "<label class=\"map\">$mk_map : </label>";
$tools .= "<li><a href=\"{$_SERVER['PHP_SELF']}?map_action=choose_map\" class=\"clear_map\" title=\"Choose a new map\">&nbsp;</a></li>";
$tools .= "<li><a href=\"{$_SERVER['PHP_SELF']}?map_restart=1\" class=\"refresh\" title=\"Rerun this map\">&nbsp;</a></li>";
$tools .= "<li><a href=\"overlay_holder.php?lboxreload=FALSE&amp;sf_conf=conf_map_wmcoverlay&amp;scales={$wmc['scales']}&amp;projection={$wmc['projection']}&amp;gmap_api_key={$wmc['gmap_api_key']}&amp;OSM={$wmc['OSM']}\" onclick=\"saveWMC()\" rel=\"lightbox\" class=\"save\" title=\"Save this map\" >&nbsp;</a></li>";
$tools .= "<li><a href=\"overlay_holder.php?lboxreload=FALSE&amp;sf_conf=conf_maptopdf\" onclick=\"saveWMC()\" rel=\"lightbox|200\" onclick=\"printMap()\" class=\"download\" title=\"Export to PDF\" >&nbsp;</a></li>";
// Then add a second row for the map size options
//$tools .= "<br />";
//$tools .= "<label class=\"map\">$mk_mapsize : </label>";
//$tools .= "<li><a href=\"#\" onclick=\"changeDivSize('400px','300px','map')\" class=\"map_small\" title=\"Small\" >&nbsp;</a></li>";
//$tools .= "<li><a href=\"#\" onclick=\"changeDivSize('600px','400px','map')\" class=\"map_med\" title=\"Medium\" >&nbsp;</a></li>";
//$tools .= "<li><a href=\"#\" onclick=\"changeDivSize('800px','600px','map')\" class=\"map_large\" title=\"Large\" >&nbsp;</a></li>";
// end toolbar cleanly
$tools .= "</ul>";
echo $tools;

// If a map is selected, display the legend
if ($wmc_code) {
    print('<h4>Legend</h4>');    
    print('<div id="map_view_legend">');
    if (!$map_action == 'choose_map' OR !$maptype){
        print(parseWMCMap($wmc_code,'map_view_legend'));
    }
    print('</div>');
}
?>
</div>


<!-- THE MAIN AREA -->
<div id="main" class="main_mcrview">
    <div class="map_viewlist">
        <?php
        if ($map_action == 'choose_map') {
            include_once('php/map_view/choose_map.php');
        }
        elseif ($maptype) {
            print(loadWMCMap($wmc_code,$maptype));
        } else {
            include_once('php/map_view/choose_map.php');
        }
        ?>
    </div>
</div>


<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>

</div>
</body>
</html>
