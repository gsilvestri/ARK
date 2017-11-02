<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* data_view.php
*
* Index page for data view
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
* @author     Henriette Roued <henriette@roued.com>
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @copyright  1999-2008 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/data_view.php
* @since      File available since Release 0.6
*
*
* NOTES: This page has three different required and persistent UI control variables. These
* are NOT the same and should NOT be used interchangeably. They are as follows:
* - $view: governs the ARK standard 'view' set up (not really used in this page but needed
*     for reasons of standardisation, plus could also be useful at some point)
* - $ftr_mode: this controls the type of user interface offered. The settings for this are
*     basic|standard|advanced. This mostly affects the way that filters are presented and
*     the level of tools presented to the user. Note that 'basic' mode also affects $disp_mode
* - $disp_mode: governs the way that results are presented to the user. Feeds are now handled
*     by feed.php and downloads are also handled in an overlay (at present). Therefore the main
*     current use of this var is to set up the chat|table|thumb|text display options for the
*     results themselves.
*
*/


// -- INCLUDE SETTINGS AND FUNCTIONS -- //
include('config/settings.php');
include('php/global_functions.php');
include('php/validation_functions.php');


// -- SESSION -- //
// Start the session
session_name($ark_name);
session_start();


// -- MANUAL configuration vars for this page -- //
$pagename = 'data_view';
$error = FALSE;
$message = FALSE;


// -- REQUESTS -- //
$lang = reqArkVar('lang', $default_lang);
$view = reqArkVar('view'); // beware! this is not really used by data_view (left in for standardisation) GH 24/11/11
$ftr_mode = reqArkVar('ftr_mode', 'standard'); // this is page specific... move out of this block? GH 24/11/11
$results_mode = reqArkVar('results_mode', 'disp'); // this is page specific... move out of this block? GH 24/11/11
$ste_cd = reqArkVar('ste_cd', $default_site_cd);
$phpsessid = reqQst($_REQUEST, 'PHPSESSID');
$update_db = reqQst($_REQUEST, 'update_db');
$submiss_serial = reqArkVar('submiss_serial');
$perpage = reqArkVar('perpage', $conf_viewer_rows);
$page = reqArkVar('page', '1'); // Request the current page (default to page 1)
// also flag up changes to perpage
$perpage_test = reqQst($_REQUEST, 'perpage');
// a special case for feeds
$limit = reqQst($_REQUEST, 'limit');
if ($limit) {
    $perpage = $limit;
    $perpage_test = TRUE;
}


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
// textile
include_once ('lib/php/classTextile.php');
$textile = new Textile;


// ---- MARKUP ---- //
$mk_norec = getMarkup('cor_tbl_markup', $lang, 'norec');
$mk_nofilters = getMarkup('cor_tbl_markup', $lang, 'nofilters');


// ---- FILTERS ---- //
// Run the filters by including filters.php
$filters_exec = 'off';
include_once ($cur_code_dir.'filters.php');


// ---- COUNT RESULTS ---- //
// note the number of results for future reference
if ($results_array) {
    $total_results = count($results_array);
} else {
    $total_results = 0;
}


// ---- PAGINATION ---- //
// allow an override to reset to page 1 eg. if the filters have been exec'ed
// also reset to page 1 if the perpage is changed
if (isset($oride_page) or $perpage_test) {
    $page = 1;
}

// do the pagination
// NOTE: overide to remove pagination: $perpage == 'inf'
if ($results_array && $perpage && $perpage != 'inf') {
    // save out the results unpaged for future use
    $_SESSION['unpaged_results_array'] = $results_array;
    // page the results
    $page_array = pageResults($results_array, $page, $perpage);
    // output the paged results to the live var
    $results_array = $page_array['paged_results'];
    // flag paging as having taken place
    $pg = 'on';
} else {
    // flag paging as not having taken place
    $pg = FALSE;
}


// ---- UI STUFF ---- //
// NOTES: $view is NOT the same as $ftr_mode is NOT the same as $disp_mode see doc header

// RESULTS MODE

// display modes for on screen results
// disp_mode=chat
// disp_mode=table
// disp_mode=text
// disp_mode=thumb
// disp_mode=map
$disp_mode = reqArkVar('disp_mode', 'table');
$mk_disp_mode = getMarkup('cor_tbl_markup', $lang, $disp_mode);

// feeds
// Handled by feed.php

// downloads
// Handled by download.php

// RESULTS NAV
$results_nav = mkResultsNav($conf_results_nav, $ftr_mode, $mk_disp_mode);


// ---- PROCCESS RESULTS ---- //

// select the function to be used to return the exported results
$mk_func = 'mkResults'.ucfirst($disp_mode);
// if there are results - run the output function
if ($results_array) {
    $result_output = $mk_func($results_array, $filters);
} else {
    // no results have been returned
    if ($filters_exec == 'on') {
        $message[] = $mk_norec;
    } else {
        // Offer a message saying to add a filter
        $message[] = $mk_nofilters;
        // manually send this msg to the main area (left panel will report and remove the $message[] above)
        $result_output = "<div class=\"dv_feedback\"><div id=\"message\"><p>$mk_nofilters</p></div></div>\n";
    }
}

// paging nav
if ($pg == 'on' && $result_output) {
    // make the paging nav the vars are:
    // current pg no|total num of pages|number of res perpage|number of pages listed
    $result_output .=
        mkNavPage($page, $page_array['total'], $perpage, $conf_num_res_pgs, $total_results);
}
// if the perpage is set to infinity, we need to display the reduced nav
if ($results_array && $perpage == 'inf') {
    $result_output .=
        mkNavPage($page, 0, $perpage, $conf_num_res_pgs, $total_results);
}

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
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/php.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/prototype.js"></script>
	<script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/tablekit.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/scriptaculous.js?load=effects"></script>
    
    <!-- ARK javascript -->
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/js_functions.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/lightbox.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/livesearch.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/chaintable.js"></script>
    <script type="text/javascript">
        var $j = jQuery.noConflict();
        $j(document).ready(function(){
            // drawer toggle script
            $j(".dr_toggle").click(function(){
                // console.log("test");
                var $marginLefty = $j(".dr");
                $marginLefty.animate({
                    marginLeft: parseInt($marginLefty.css('marginLeft'),10) == 0 ?
                        $marginLefty.outerWidth() :
                        0
                });
                $j("#filter_panel").toggleClass('dr_shadows');
                $j("div.dr").toggleClass('dr_shadows');
                return false;
            });
            $j("ul#ftr_options .save").click(function(){
                // console.log("test");
                var $marginLefty = $j(".dr");
                $marginLefty.animate({
                    marginLeft: parseInt($marginLefty.css('marginLeft'),10) == 0 ?
                        $marginLefty.outerWidth() :
                        0
                });
                $j("#filter_panel").toggleClass('dr_shadows');
                $j("div.dr").toggleClass('dr_shadows');
                return false;
            });
        });
        
        $j(window).load(function(){
            // set heights in left panel
            var main_ht = $j("#main").height();
            var lpanel_ht = $j("#lpanel").height();
            var largest_child = 0;
            $j('#lpanel').children().each(function() {
                var ht = $j(this).height();
                if (ht > largest_child) {
                    largest_child = ht;
                };
            });
            var largest = max(main_ht, largest_child, lpanel_ht);
            console.log ("largest: " + largest);
            // set all the heights to the same thing
            $j('#lpanel').children().each(function() {
                $j(this).height(largest);
            });
            $j('#lpanel').height(largest);
            $j('#main').height(largest);
        });
    </script>
</head>

<body>

<!-- THE CONTENT WRAPPER -->
<div id="wrapper" class="wrp_results">

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
        <?php print(mkNavMain($authorised_pages, $conf_linklist)) ?>
    </div>
</div>


<!-- The LEFT PANEL -->
<div id="lpanel">
    <?php include($cur_code_dir.'filter_panel.php') ?>
</div>


<!-- THE MAIN AREA -->
<div id="main" class="main_results">

<?php

// the results nav
echo "$results_nav";

// feedback
if ($error) {
    echo "<div class=\"dv_feedback\">\n";
    feedBk('error');
    echo "</div>\n";
}
if ($message) {
    echo "<div class=\"dv_feedback\">\n";
    feedBk('message');
    echo "</div>\n";
}

// the results
if (isset($result_output)) {
    echo "$result_output";
}

?>

</div>


<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>


<!-- end content WRAPPER -->
</div>


</body>
</html>
