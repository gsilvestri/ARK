<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* micro_view.php
*
* Index for micro view
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
* @link       http://ark.lparchaeology.com/svn/micro_view.php
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
$pagename = 'micro_view';
$error = FALSE;
$message = FALSE;


// -- REQUESTS -- //
$lang = reqArkVar('lang', $default_lang);
$view = reqArkVar('view');
$ste_cd = reqArkVar('ste_cd', $default_site_cd);
$item_key = reqArkVar('item_key', $default_itemkey);
$$item_key = reqQst($_REQUEST, $item_key);
$update_db = reqQst($_REQUEST, 'update_db');
$submiss_serial = reqArkVar('submiss_serial');
$quickedit = reqQst($_REQUEST, 'quickedit'); // quickedit for forms
$cre_by = reqQst($_SESSION,'user_id'); // user info for forms
$cre_by_name = reqQst($_SESSION,'soft_name'); // user info for forms


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


// -- MARKUP -- //
$mk_go = getMarkup('cor_tbl_markup', $lang, 'go');


// -- MODULE -- //
// select module based on the item key
$mod_short = substr($sf_key, 0, 3);
if ($mod_short) {
    //Pull mod specific settings
    $module = 'mod_'.$mod_short;
    $mod_alias = getAlias('cor_tbl_module', $lang, 'itemkey', $sf_key, 1);
    include_once ("config/mod_{$mod_short}_settings.php");
}


// PART3 - DISP ARRAY
//    This is based on an array per mod in the session or alternatively the settings

// Set up names of variable variables
// $disp_cols - the live name of the cols for the current module
$disp_cols = $mod_short.'_disp_cols';
// $conf_mcd_cols - settings array for this module from the mod settings
$conf_mcd_cols = $mod_short.'_conf_mcd_cols';

// first check for a setup in the session
$$disp_cols = reqQst($_SESSION, $disp_cols);

// handle reset request
$disp_reset = reqQst($_REQUEST,'disp_reset');
if ($disp_reset == 'default') {
    // kill the existing dis_cols to force a revert to settings
    $$disp_cols = FALSE;
}

// handle changes of item (in tab view we need to force a reload)
if ($$disp_cols) {
    // go back to the live column package variable
    $col_pkg = $$conf_mcd_cols;
    if (array_key_exists('op_display_type', $col_pkg)) {
        if ($col_pkg['op_display_type'] == 'tabs') {
            // if the record has changed, force a reload
            $tmp = $$disp_cols;
            if ($tmp[0]['col_sf_val'] != $$item_key) {
                // kill the existing dis_cols to force a revert to settings
                $$disp_cols = FALSE;
            }
        }
    }
}

// if the disp_cols are not present, get them fresh from settings
if (!$$disp_cols) {
    // go back to the live column package variable
    $col_pkg = $$conf_mcd_cols;
    $$disp_cols = $col_pkg['columns'];
    // if this is a tab view routine, pre-process these now
    if (array_key_exists('op_display_type', $col_pkg)) {
        $conf_col_view = $col_pkg['op_display_type'];
    } else {
        $conf_col_view = 'cols';
    }
    if ($conf_col_view == 'tabs') {
        // process the columns (which may contain embedded or XMI columns)
        $$disp_cols = prcsTabCols($$disp_cols);
        $_SESSION[$disp_cols] = $$disp_cols;
    }
    unset($col_pkg);
}

// PART4 - Handle requests to change the subform state
$sf_nav = reqQst($_REQUEST,'sf_nav');

// PART4a - Handle requests to change the view state
if ($sf_nav == 'min' OR  $sf_nav == 'max') {
    // temporarily make the dynamically named disp cols static
    $temp_cols = $$disp_cols;
    
    // Get the col and row of the sf
    $col = reqQst($_REQUEST,'col_id');
    $row = reqQst($_REQUEST,'sf_id');
    $temp_cols["$col"]['subforms']["$row"]['view_state'] = $sf_nav;
    
    // make the static named disp cols dynamic again
    $$disp_cols = $temp_cols;
    
    unset($temp_cols);
    unset($col);
    unset($row);
}

// PART4b - Handle requests to change the edit state
if ($sf_nav == 'edit' OR  $sf_nav == 'view' OR  $sf_nav == 'ent') {
    // temporarily make the dynamically named disp cols static    
    $temp_cols = $$disp_cols;
    
    // Get the col and row of the sf
    $col = reqQst($_REQUEST, 'col_id');
    $row = reqQst($_REQUEST, 'sf_id');
    $temp_cols[$col]['subforms'][$row]['edit_state'] = $sf_nav;
    
    // make the static named disp cols dynamic again
    $$disp_cols = $temp_cols;
    // clean up
    unset($temp_cols);
    unset($col);
    unset($row);
}


// PART4c - Handle requests to change the subform column
if ($sf_nav == 'mv_col_r' || $sf_nav == 'mv_col_l') {
    
    // temporarily make the dynamically named disp cols static
    $temp_cols = $$disp_cols;
    
    // Get the col and row of the sf
    $col = reqQst($_REQUEST,'col_id');
    $row = reqQst($_REQUEST,'sf_id');
    
    // set up the target
    if ($sf_nav == 'mv_col_r') {
        $tgt_col = $col+1;
    }
    if ($sf_nav == 'mv_col_l') {
        $tgt_col = $col-1;
    }
    
    // remove the subform from the column and hold in a var
    $tmp_sf = $temp_cols["$col"]['subforms']["$row"];
    unset ($temp_cols["$col"]['subforms']["$row"]);
    
    // un_shift the tmp array into the top of the target col
    array_unshift($temp_cols["$tgt_col"]['subforms'], $tmp_sf);
    
    // make the static named disp cols dynamic again
    $$disp_cols = $temp_cols;
    
    unset($temp_cols);
    unset($tmp_sf);
    unset($col);
    unset($tgt_col);
    unset($row);
}

// PART4d - Handle requests to change the subform row
if ($sf_nav == 'mv_up' OR $sf_nav == 'mv_dn') {
    // temporarily make the dynamically named disp cols static
    $temp_cols = $$disp_cols;
    
    // Get the col and row of the sf
    $col = reqQst($_REQUEST,'col_id');
    $row = reqQst($_REQUEST,'sf_id');
    
    // set up the target
    if ($sf_nav == 'mv_up') {
    //get key of sf above
    $tgt_row = $row-1;
    }
    if ($sf_nav == 'mv_dn') {
    $tgt_row = $row+1;
    }
    
    // extract the arrays to swap
    $tmp_sf = $temp_cols["$col"]['subforms']["$row"];
    $tmp_old = $temp_cols["$col"]['subforms']["$tgt_row"];
    
    // now reinsert them swapped over
    $temp_cols["$col"]['subforms'][$row] = $tmp_old;
    $temp_cols["$col"]['subforms'][$tgt_row] = $tmp_sf;
    
    // make the static named disp cols dynamic again
    $$disp_cols = $temp_cols;
    
    // clean up
    unset($temp_cols);
    unset($tgt_row);
    unset($row);
    unset($col);
}


// PART5 - Handle requests to change the subform state
$col_nav = reqQst($_REQUEST,'col_nav');


// PART6 - save variables to the session
//The disp cols
$_SESSION[$disp_cols] = $$disp_cols;


// PART7 - Make the RESULTS NAV
$record_nav = mkRecordNav($conf_record_nav, 'micro_view', FALSE);


// PART8 - Custom Page Title
// this is done so far down the page in order to use data in the page title
$page_title = $page_title.': '.$$item_key;


// -- PROCESS -- //
// We process delete related routines at the top of the page so as to avoid conflicts
// delfrag - makes routine updates of individual fragments
if ($update_db === 'delfrag') {
    include_once('php/update_db.php');
    // if a frag has other frags chained to it, the user needs to confirm the delete
    // this is supported using an overlay. First check to see if the error is a chain
    // error
    if ($error) {
        foreach ($error as $key => $err) {
            if (array_key_exists('chain', $err)) {
                $mk_confirmdel = getMarkup('cor_tbl_markup', $lang, 'confirmdel');
                $mk_delete = getMarkup('cor_tbl_markup', $lang, 'delete');
                $redirecturl = "{$_SERVER['PHP_SELF']}?item_key=$item_key&amp;$item_key={$$item_key}";
                $redirecturl = urlencode($redirecturl);
                $error[]['vars'] = "$mk_confirmdel: <a href=\"overlay_holder.php?overlay=true&amp;item_key={$item_key}&amp;{$item_key}={$$item_key}&amp;delete_key=cor_tbl_{$err['delete_dclass']}&amp;delete_val={$err['delete_id']}&amp;lang=$lang&amp;sf_conf=conf_mcd_deleterecord&amp;lboxreload=$redirecturl\" rel=\"lightbox\">$mk_delete</a>";
            }
        }
    }
}
// delete_XXX - makes deletions of entire records
$dynamic_delete_name = 'delete_'.$mod_short;
if ($update_db === $dynamic_delete_name) {
    $delete_key = reqQst($_REQUEST, 'delete_key');
    $delete_val = reqQst($_REQUEST, 'delete_val');
    include_once ('php/subforms/update_delete_record.php');
    if ($delete_success) {
        $error[]['vars'] = getMarkup('cor_tbl_markup', $lang, 'err_recwasdel');
        $message = FALSE;
    }
}


// -- OUTPUT-- //

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
    
</head>

<body>

<!-- THE CONTENT WRAPPER -->
<div id="wrapper" class="wrp_mcrview">

<!-- HEADER -->
<div id="hdr-print">
    <img src="skins/<?php echo $skin ?>/images/logo.png" alt="logo" />
</div>
<div id="hdr">
    <!-- header tools -->
    <div id="hdr-tools">
        <!-- version number -->
        <div id="version">v<?php echo $version ?></div>
        <!-- user info -->
        <div id="user-info">
            <?php $userinfo = mkUserInfo(); echo($userinfo); ?>
        </div>
        <!-- search box -->
        <?php print(mkSearchBox());?>
    </div>
    <!-- main navigation bar -->
    <div id="navcontainer">
        <?php print(mkNavMain($authorised_pages, $conf_linklist)) ?>
    </div>
</div>

<!-- The LEFT PANEL -->
<div id="lpanel">
    <?php include_once($cur_code_dir.'left_panel.php') ?>
</div>

<!-- MAIN CONTENT (IN COLUMNS) -->
<div id="main" class="main_mcrview">
<?php 

// If no Itemkey or Itemval - give feedback cleanly to the user
if (!$sf_key) {
    $message[] = 'Select a form...';
}
if (!$sf_val && $sf_key) {
    $message[] = 'Search for a '.$mod_alias.' item...';
}


//RECORD NAVIGATION
echo "$record_nav";

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

// the main area subforms
if ($sf_val && !$error) {
    // conf_mcd_cols - this is the entire package
    $tcolarr = $$conf_mcd_cols;
    // set up the type of view required (default to 'cols')
    if (array_key_exists('op_display_type', $tcolarr)) {
        $conf_col_view = $tcolarr['op_display_type'];
    } else {
        $conf_col_view = 'cols';
    }
    
    // ---- Column view ---- //
    if ($conf_col_view == 'cols') {
        foreach($$disp_cols as $cur_col_id => $disp_col) {
            // optional css classes
            if (array_key_exists('op_css_class', $disp_col)) {
                $css_class = $disp_col['op_css_class'];
            } else {
                $css_class = $disp_col['col_type'];
            }
            // OUTPUT
            printf("<div id=\"column-{$disp_col['col_id']}\" class=\"$css_class\">\n");
            // a title for the column (either the results of a field or markup)
            if ($col['col_mkname']) {
                if (!is_array($col['col_mkname'])) {
                    $mk_col_mkname = getMarkup('cor_tbl_markup', $lang, $col['col_mkname']);
                } else {
                    $field = $col['col_mkname'];
                    $mk_col_mkname = resTblTd($col['col_mkname'], $sf_key, $sf_val);
                }
                echo "<h1>$mk_col_mkname</h1>\n\n";
            }
            // extract the subforms from the column
            $cur_col_subforms = $disp_col['subforms'];
            foreach($cur_col_subforms as $cur_sf_id => $cur_col_subform) {
                // if this is an anon login - set the edit options to be OFF
                // DEV NOTE: This should be a full blown security check per user per SF
                // DEV NOTE: see ticket #207
                if ($anon_login) {
                    // temporarily make the cols static
                    $temp_cols = $$disp_cols;
                    // change the sf_nav (unless it is already set to 'none')
                    if ($cur_col_subform['sf_nav_type'] != 'none') {
                        // fix the new sf_nav_type
                        $temp_cols[$cur_col_id]['subforms'][$cur_sf_id]['sf_nav_type'] = 'name';
                    }
                    // force the edit state in the main array
                    $temp_cols[$cur_col_id]['subforms'][$cur_sf_id]['edit_state'] = 'view';
                    // force this copy of the $cur_col_subform['edit_state']
                    $cur_col_subform['edit_state'] = 'view';
                    // make the static named disp cols dynamic again
                    $$disp_cols = $temp_cols;
                }
                // set the sf_state
                $sf_state = 
                    getSfState(
                        $disp_col['col_type'],
                        $cur_col_subform['view_state'],
                        $cur_col_subform['edit_state']
                );
                // set the sf_conf
                $sf_conf = $cur_col_subform;
                // if the sf is conditional
                if (array_key_exists('op_condition', $cur_col_subform)) {
                    // check the condition
                    if (chkSfCond($item_key, $$item_key, $cur_col_subform['op_condition'])) {
                        include($cur_col_subform['script']);
                    }
                } else {
                    include($cur_col_subform['script']);
                }
                // cleanup this sf
                unset($sf_state);
                unset($cur_col_subform);
            }
            unset($cur_col_subforms);
            printf("</div>\n\n");
        }
    }
    
    // ---- Tabs view ---- //
    if ($conf_col_view == 'tabs') {
        // specify a default current tab
        if (!array_key_exists('op_top_col', $tcolarr)) {
            $default = 'main_column';
        } else {
            $default = $tcolarr['op_top_col'];
        }
        $curcol = reqArkVar('curcol', $default);
        if ($curcol == 'zero') {
            $curcol = '0';
        }
        // this routine is used to transform the human readable 'col_id' into
        // the numerical array key of the current column within the cols array
        foreach ($$disp_cols as $key => $col) {
            if ($col['col_id'] == $curcol) {
                $temp_var = $key;
            }
        }
        if (!isset($temp_var)) {
            // this means the curcol is not found in this sites columns (eg a season from another site)
            $curcol = $default;
            $temp_var = '0';
        }
        $cur_col_id = $temp_var;
        unset ($temp_var);
        // make up the tab nav
        $nav = mkMvTabNav($$disp_cols);
        // print the nav
        printf("<div id=\"tabnav\">\n<ul>\n$nav</ul>\n</div>\n\n");
        // loop over the columns until we hit the relevant one
        foreach ($$disp_cols as $col_key => $disp_col) {
            // if the column matches the curcol/cur_col_id it is the right one to display
            if ($disp_col['col_id'] == $curcol) {
                // extract the subforms
                $cur_col_subforms = $disp_col['subforms'];
                // optional css classes
                if (array_key_exists('op_css_class', $disp_col)) {
                    $css_class = $disp_col['op_css_class'];
                } else {
                    $css_class = $disp_col['col_type'];
                }
                // set up the sf_key and sf_nav for this column (key might not be the same as the page)
                $sf_key = $disp_col['col_sf_key'];
                $sf_val = $disp_col['col_sf_val'];
                // OUTPUT
                printf("<div id=\"column-{$disp_col['col_id']}\" class=\"$css_class\">\n");
                // loop over the subforms for the active column
                foreach($cur_col_subforms as $cur_sf_id => $cur_col_subform) {
                    // if this is an anon login - set the edit options to be OFF
                    // DEV NOTE: This should be a full blown security check per user per SF
                    // DEV NOTE: see ticket #207
                    if ($anon_login) {
                        // change the sf_nav (unless it is already set to 'none')
                        if ($cur_col_subform['sf_nav_type'] != 'none') {
                            // fix the new sf_nav_type
                            $disp_col['subforms'][$cur_sf_id]['sf_nav_type'] = 'name';
                        }
                        // force the edit state in the main array
                        $disp_col['subforms'][$cur_sf_id]['edit_state'] = 'view';
                        // force this copy of the $cur_col_subform['edit_state']
                        $cur_col_subform['edit_state'] = 'view';
                    }
                    if (array_key_exists('op_condition', $cur_col_subform)) {
                        if (chkSfCond($item_key, $$item_key, $cur_col_subform['op_condition'])) {
                            $sf_state = 
                                getSfState(
                                    $disp_col['col_type'],
                                    $cur_col_subform['view_state'],
                                    $cur_col_subform['edit_state']
                            );
                            $sf_conf = $cur_col_subform;
                            include($cur_col_subform['script']);
                            unset ($sf_state);
                            unset($cur_col_subform);
                        }
                    } else {
                        $sf_state = 
                            getSfState(
                                $disp_col['col_type'],
                                $cur_col_subform['view_state'],
                                $cur_col_subform['edit_state']
                        );
                        $sf_conf = $cur_col_subform;
                        include($cur_col_subform['script']);
                        unset ($sf_state);
                        unset($cur_col_subform);
                    }
                }
                unset($cur_col_subforms);
                printf("</div>\n\n");
            }
        }
    }
}

?>

<!-- end MAIN -->
</div>

<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>

<!-- end WRAPPER -->
</div>

</body>
</html>