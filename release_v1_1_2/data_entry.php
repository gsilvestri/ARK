<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* data_entry.php
*
* Index page for data entry
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
* @copyright  1999-2008 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/data_entry.php
* @since      File available since Release 0.6
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
$pagename = 'data_entry';
$error = FALSE;
$message = FALSE;


// -- REQUESTS -- //
$lang = reqArkVar('lang', $default_lang);
$view = reqArkVar('view');
$ste_cd = reqArkVar('ste_cd', $default_site_cd);
$update_db = reqQst($_REQUEST, 'update_db');
$submiss_serial = reqArkVar('submiss_serial');
$item_key = reqArkVar('item_key', $default_itemkey);
$$item_key = reqQst($_REQUEST, $item_key);
$quickedit = reqQst($_REQUEST, 'quickedit');


// -- SF_KEY & SF_VAL -- //
// setup the sf_key and sf_val
$sf_key = $item_key;
$sf_val = $$item_key; 


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


// select the relevant page contents
if ($view != 'regist') {
    // if it isnt a register then it must be the global
    $plc = $cur_code_dir.'global_'.$view.'.php';
}
$view_title = getMarkup('cor_tbl_markup', $lang, $view);

/** The main content is a single column containing one or more subforms.
* the columns is an array named after the view. Therefore you must have a
* view to corrctly load an array. You must also have a module in order to 
* include the right settings file.
*
*/

// Get the mod settings
$mod_short = substr($item_key, 0, 3);
if ($mod_short) {
    $module = 'mod_'.$mod_short;
    $mod_alias = getAlias('cor_tbl_module', $lang, 'itemkey', $item_key, 1);
    //Pull mod specific settings
    include ('config/mod_' . $mod_short . '_settings.php');
} else {
    $mod_alias = FALSE;
}

// get the relevant column array
if ($view != 'home' && $view != 'files'){
    $col_name = 'conf_dat_'.$view;
    $col = $$col_name;
} else {
    $col = FALSE;
}
// manually set up some vars for this column
$disp_cols = 'data_entry_single';
$$disp_cols = array($col);
$cur_col_id = 0;

// DETFRM ENT STATE - Set all sf_conf edit_states to 'ent' AND remove the option
// to change the state when in a detfrm routine:
if ($view == 'detfrm') {
    // temporarily make the dynamically named disp cols static
    $temp_cols = $col;
    if (is_array($temp_cols)) {
        foreach ($temp_cols['subforms'] as $cur => $sf_conf) {
            $temp_cols['subforms']["$cur"]['edit_state'] = 'ent';
            // This will remove the option to alter the edit state
            $temp_cols['subforms']["$cur"]['sf_nav_type'] = 'name';
        }
    }
    $col = $temp_cols;
}

// SF_NAV = MIN / MAX - Handle requests to change the subform state
$sf_nav = reqQst($_REQUEST,'sf_nav');
if ($sf_nav == 'min' OR  $sf_nav == 'max') {
    // temporarily make the dynamically named disp cols static
    $temp_cols = $col;
    // Get the col and row of the sf
    //$row = reqQst($_REQUEST,'sf_id');
    $row = reqArkVar('sf_id');
    $temp_cols['subforms']["$row"]['view_state'] = $sf_nav;
    // make the static named disp cols dynamic again
    $col = $temp_cols;
    unset($temp_cols);
    unset($row);
}

// MINIMISER - Handle requests to using the sf_minimiser
if ($minimiser == TRUE) {
    $updating = reqQst($_REQUEST,'update_db');
    $nav_min = reqQst($_REQUEST,'nav_min');
    // If the nav_min flag has been sent (potential change to focussed sf)
    if ($nav_min == 1) {
        // Set up the 'row variable' to indicate the 'row' (or sf) to focus on
        // Fix to get around the fact that reqArkVar can't see the difference between 0 and false
        if (reqQst($_REQUEST,'sf_id') == '0') {
            $row = 0;
        } else {
            $row = reqArkVar('sf_id');
        }
        // Save this to the session
        $_SESSION['minimiser_focus_row'] = $row;
    } else {
        // if on an update or quickedit routine preserve the current focus
        if ($updating or $quickedit) {
            $row = $_SESSION['minimiser_focus_row'];
        } else {
            // else reset both the live and session vars to 0
            $row = 0;
            $_SESSION['minimiser_focus_row'] = 0;
        }
    }
    // make changes to the column
    if ($nav_min) {
        // temporarily make the dynamically named disp cols static
        $temp_cols = $col;
        // Loop over every sf in the target column
        // Set them all to min unless they match the row
        foreach ($temp_cols['subforms'] as $key => $sf) {
            // If the nav is off just leave the sf alone
            if ($sf['sf_nav_type'] != 'none') {
                // If this is the focus SF then MAX it
                if ($row == $key) {
                    $temp_cols['subforms'][$key]['view_state'] = 'max';
                // else MIN it
                } else {
                    $temp_cols['subforms'][$key]['view_state'] = 'min';
                }
            }
        }
        // make the static named disp cols dynamic again
        $col = $temp_cols;
        unset($temp_cols);
        unset($tgt_row);
        // GH Edit 6/6/11 probably try to remove this unset if needed
        // unset($row);
    } else {
        $temp_cols = $col;
        if(is_array($temp_cols)){
            foreach ($temp_cols['subforms'] as $cur => $sf_conf) {
                $temp_cols['subforms']["$cur"]['edit_state'] = 'ent';
                $temp_cols['subforms']["$cur"]['view_state'] = 'min';
            }
        }
        $temp_cols['subforms'][$row]['view_state']='max';
        $col=$temp_cols;
    }
}

// set the cur_max for the benfit of the minimiser function
$cur_max = $row;

// sf's expect $$disp_cols to be set up
// In the case of data entry there is only one col
$disp_cols = 'data_entry_single';
$$disp_cols = array($col);

// MARKUP
$mk_go = getMarkup('cor_tbl_markup', $lang, 'go');

// MAKE THE DATA ENTRY NAV
$entry_nav = mkRecordNav($conf_entry_nav, 'data_entry', $view);

// ---- PROCESS ---- //
if ($update_db === 'delfrag') {
    include_once('php/update_db.php');
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
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/php.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/prototype.js"></script>
	<script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/tablekit.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>lib/js/scriptaculous.js?load=effects"></script>
    
    <!-- ARK javascript -->
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/js_functions.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/lightbox.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/livesearch.js"></script>
    <script type="text/javascript" src="<?php echo $ark_dir ?>js/chaintable.js"></script>
</head>

<body>

<!-- THE CONTENT WRAPPER -->
<div id="wrapper" class="wrp_normal">

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
        <?php print(mkNavMain($authorised_pages,$conf_linklist))?>
    </div>
</div>

<!-- The LEFT PANEL -->
<div id="lpanel">
    <?php include_once($cur_code_dir.'left_panel.php') ?>
</div>

<!-- THE MAIN AREA -->
<div id="main" class="main_normal">
<?php 
if ($view != 'home') {
    //RECORD NAVIGATION
    echo $entry_nav;
}
?>
<div id="page_level"><?php if(isset($plc)) include_once ("$plc") ?></div>

<div id="mod_level">

<?php

feedBk('error');
feedBk('message');

// The data entry forms are always single column. Loop over the forms including them
if ($col && !$error) {
    if ($view != 'regist') { // This is not a register
        if ($sf_val) {            
            if (array_key_exists('col_type',$col)){
                $col_type = $col['col_type'];
            } else {
                $col_type = FALSE;
            }
            if ($col_type == 'primary_col') {
                // print a div for the column
                printf("<div id=\"column-{$col['col_id']}\" class=\"{$col['col_type']}\">\n");
                $cur_col_id = 0; // only 1 column so always 0
                foreach ($col['subforms'] as $cur_sf_id => $sf_conf) {
                    if (array_key_exists('op_condition', $sf_conf)) {
                        if (chkSfCond($item_key, $$item_key, $sf_conf['op_condition'])) {
                            //set the sf state
                            $sf_state = 
                                getSfState(
                                    $col['col_type'],
                                    $sf_conf['view_state'],
                                    $sf_conf['edit_state']
                            );
                            //include the subform script
                            include($sf_conf['script']);
                            unset ($sf_state);
                            unset($sf_conf);
                        }
                    } else {
                        //set the sf state
                        $sf_state = 
                            getSfState(
                                $col['col_type'],
                                $sf_conf['view_state'],
                                $sf_conf['edit_state']
                        );
                        //include the subform script
                        include($sf_conf['script']);
                        unset ($sf_state);
                        unset($sf_conf);
                    }
                }
                printf("</div>");
            } else {
                echo "Use a primary_col in data entry (for minimser use 'on')";
            }
        }
    } else { // This must be a register
        // Print the toggle for expanding the register
        print("<div id=\"width_toggle\">");
        print("<a id=\"toggle\" class=\"expand\" href=\"#\" onclick=\"toggleWidth('toggle', 'wrapper', 'main');\">&#x21E5;</a>");
        print("</div>");
        printf("<div id=\"column-{$col['col_id']}\" class=\"{$col['col_type']}\">\n");        
        foreach ($col['subforms'] as $cur_sf_id => $sf_conf) {
            //set the sf state
            $sf_state = 
                getSfState(
                    'primary_col',
                    $sf_conf['view_state'],
                    $sf_conf['edit_state']
            );
            //include the subform script
            include($sf_conf['script']);
            unset ($sf_state);
            unset($sf_conf);
        }
        printf("</div>");
    }
}

?>

</div>
</div>


<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>


<!-- end CONTENT WRAPPER -->
</div>


</body>
</html>