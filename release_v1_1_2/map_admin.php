<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* map_admin.php
*
* Index for the map administration page
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
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @copyright  1999-2010 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/map_admin.php
* @since      File available since Release 0.8
*/


// PART1 - Basic setup

// BASICS
// this page
$pagename = 'map_admin';
$error = FALSE;
$message = FALSE;

//GLOBAL INCLUDES
include_once('config/settings.php');
include_once('php/global_functions.php');
include_once('php/export_functions.php');
include_once('php/validation_functions.php');
include_once('php/map/map_functions.php');
include_once('php/map_admin/map_admin_functions.php');
include_once('config/map_admin_settings.php');

// SESSION
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


//GLOBALY required variables
$browser = browserDetect();
$stylesheet = getStylesheet($browser);
$lang = reqArkVar('lang', $default_lang);
$view = 'detfrm';
$perpage = reqArkVar('perpage', $conf_viewer_rows);
$phpsessid = reqQst($_REQUEST, 'PHPSESSID');

// REQUEST vars needed in this page
$temp_user_input = reqArkvar('temp_user_input',array());
$legend_array = reqArkVar('legend_array',array());
if (!is_array($legend_array)) {
    $legend_array = unserialize($legend_array);
}
$map_display_layers = array();

//check if we are resetting the legend
$reset_legend = reqQst($_REQUEST,'reset_legend');
if ($reset_legend) {
    $legend_array = array();
}

// PROGESS 
$prog_filename = $pagename . '_progress';
if (reqQst($_REQUEST,$prog_filename) === '0') {
    $progress = 0;
} else {
    $progress = reqArkVar($prog_filename,0);    
}
$sf_id = reqQst($_REQUEST,'sf_id');

if (is_numeric($sf_id)) {
    $progress = $sf_id+1;
    
    //if we have a sf_id it means a sf has been submitted
    //due to the progress bar (slipping to different sfs) 
    //it means we can't always grab the submitted variables - so do it here
    
    foreach ($_REQUEST as $key => $value) {
        if ($key != 'sf_id' && $key != $ark_name) {
            //put in a special fix here for the url and layers
            if ($key == 'url') {
                $url = reqQst($_REQUEST, $key);
            } elseif ($key == 'layers_on') {
                //if we have layers_on we need to build the display map array
                foreach ($value as $layer_on) {
                    //we need to explode the value on an underscore
                    $layer_on = explode('_',$layer_on);
                    $server = $layer_on[1];
                    $layer = $layer_on[3];
                    //print ("layer: $layer, server: $server \n");
                    if (array_key_exists(5,$layer_on) && array_key_exists('sub_layers',$legend_array['servers'][$server]['layers'][$layer])) {
                        $sublayer = $layer_on[5];
                        $display_layer = $legend_array['servers'][$server]['layers'][$layer]['sub_layers'][$sublayer];
                        $map_display_layers['servers'][$server]['url'] = $legend_array['servers'][$server]['url'];
                        $map_display_layers['servers'][$server]['layers'][$layer]['sub_layers'][$sublayer] = $display_layer;
                    } else {
                        $display_layer = $legend_array['servers'][$server]['layers'][$layer];
                        $map_display_layers['servers'][$server]['url'] = $legend_array['servers'][$server]['url'];
                        $map_display_layers['servers'][$server]['layers'][$layer]= $display_layer;
                    }
                }
            } elseif ($key == 'layers_off') {
                    //if we have layers_on we need to build the display map array
                    foreach ($value as $layer_off) {
                        //we need to explode the value on an underscore
                        $layer_off = explode('_',$layer_off);
                        $server = $layer_off[1];
                        $layer = $layer_off[3];
                        if (array_key_exists(5,$layer_off) && array_key_exists('sublayers',$legend_array['servers'][$server]['layers'][$layer])) {
                            $sublayer = $layer_off[5];
                           // $display_layer = $legend_array['servers'][$server]['layers'][$layer]['sublayers'][$sublayer];
                            unset ($map_display_layers['servers'][$server]['layers'][$layer]['sublayers'][$sublayer]);
                        } else {
                            //$display_layer = $legend_array['servers'][$server]['layers'][$layer];
                            unset($map_display_layers['servers'][$server]['layers'][$layer]);
                        }
                    }
            } else {
                $_SESSION['temp_user_input'][$sf_id][$key] = reqQst($_REQUEST, $key);
            }
        }
    }
    $temp_user_input = $_SESSION['temp_user_input'];
}

/** The main content is a single column containing one or more subforms.
* the columns is an array named after the view. Therefore you must have a
* view to corrctly load an array. You must also have a module in order to 
* include the right settings file.
*
*/

// request the columns setup array
if ($view != 'home'){
    $col_name = 'conf_dat_'.$view;
    $col = $$col_name;
} else {
    $col = FALSE;
}
$disp_cols = 'map_admin';
$$disp_cols = array($col);

$cur_col_id = 0;

//PART3 - Set all sf_conf edit_states to 'ent' AND remove the option to change the state
// temporarily make the dynamically named disp cols static
$temp_cols = $col;
if (is_array($temp_cols)) {
    foreach ($temp_cols['subforms'] as $cur => $sf_conf) {
        $temp_cols['subforms']["$cur"]['edit_state'] = 'ent';
        // This will remove the option to alter the edit state
        $temp_cols['subforms']["$cur"]['sf_nav_type'] = 'name';
    }
}
$col=$temp_cols;

//this page uses the progress bar
if (is_array($col)) {
    $progress_bar = mkProgressBar($col,$progress, $prog_filename);
}

// sf's expect $$disp_cols to be set up
// In the case of data entry there is only one col
$disp_cols = 'map_admin';
$$disp_cols = array($col);

// MARKUP

$mk_go = getMarkup('cor_tbl_markup', $lang, 'go');
$map_admin_instructions = getMarkup('cor_tbl_markup', $lang, 'map_admin_instructions');
$mk_mapconfigure = getMarkup('cor_tbl_markup', $lang, 'map_configure');

// ---------OUTPUT--------- //
echo "<!DOCTYPE ". $doctype . ">";
?> 

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
<div id="wrapper" class="wrp_mcrview">

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
    <div id="navcontainer" >
        <?php print(mkNavMain($authorised_pages, $conf_linklist)) ?>
    </div>
</div>

<!-- The LEFT PANEL -->

<div id="lpanel">
    <?php include($cur_code_dir.'left_panel.php') ?>
</div>

<!-- THE MAIN AREA -->
<div id="main" class="main_mcrview">
    <div id="message"><p><?php echo $mk_mapconfigure ?></p></div>

<?php
echo "<p>$map_admin_instructions</p>";
feedBk('error');
feedBk('message');
echo "<div class=\"map_admin_form\">";

if (!empty($progress_bar)) {
    print($progress_bar);
}

// The data entry forms are always single column. Loop over the forms including them
if ($col && !$error) {
    if ($view != 'regist') { // This is not a register
        if (array_key_exists('col_type',$col)){
            $col_type = $col['col_type'];
        } else {
            $col_type = FALSE;
        }
        if ($col_type == 'primary_col') {
            // print a proper div for the column
            printf("<div id=\"column-{$col['col_id']}\" class=\"mc_subform\">\n");
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
                    if ($progress == $cur_sf_id) {
                        //include the subform script
                        include($sf_conf['script']);
                    }
                    unset ($sf_state);
                    unset($sf_conf);
                }
            }
            printf("</div>");
        } else {
            echo "ADMIN ERROR: please set mapping_col as the col_type in map_admin_settings.php";
        }
    }
}
echo "</div>";

?>

<!-- close main -->
</div>


<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>


<!-- close wrapper -->
</div>

</body>
</html>