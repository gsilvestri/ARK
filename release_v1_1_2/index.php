<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* index.php
*
* Main index of Ark installation
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
* @link       http://ark.lparchaeology.com/svn/index.php
* @since      File available since Release 0.6
*/

// INCLUDES
include('config/settings.php');
include('php/global_functions.php');
$db = dbConnect($sql_server, $sql_user, $sql_pwd, $ark_db);
$lang = $default_lang;


//THE ENTIRE INDEX PAGE IS A FUNCTION
function login_page($errorMessage) {

include('config/settings.php');

// PART1 - Basic setup
global $skin, $arkname, $lang;

// pagename
$pagename = 'user_home';

//MARKUP
$mk_arkname = getMarkup('cor_tbl_markup', $lang, $arkname_mk);
$mk_splash = getMarkup('cor_tbl_markup', $lang, 'splash');


//GLOBAL INCLUDES
include ('config/settings.php');
$db = dbConnect($sql_server, $sql_user, $sql_pwd, $ark_db);
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

$browser = browserDetect();
$stylesheet = getStylesheet($browser);
$page_title = $mk_arkname;
$cwidth = reqQst($_REQUEST, 'nwidth');


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
    <div id="user-info">
    </div>
    </div>

    <!-- FIXED NAVIGATION -->
    <div id="navcontainer" >
    <ul id="navlist">
    <li><a href="http://ark.lparchaeology.com/wiki/">help</a></li>
    </ul>
    </div>
    
    </div>


<!-- The LEFT PANEL -->
<div id="lpanel">
    <h1>Log in</h1>
    <?php if ($errorMessage) echo "<div id=\"message\"><p>$errorMessage</p></div>" ?>

    <div class="login_form">
        <form id="login" method="post" action="<?php echo $ark_dir ?>user_home.php">
            <ul>
                <li class="row">
                    <h5 class="login_label">User name:</h5>
                    <span class="login_inp">
                        <input class="login_inp" name="handle" type="text"  />
                    </span>
                </li>
                <li class="row">
                    <h5 class="login_label">Password:</h5>
                    <span class="login_inp">
                        <input class="login_inp" name="passwd" type="password" />
                    </span>
                </li>
                <li class="row">
                    <h5 class="login_label">&nbsp;</h5>
                    <span class="login_inp">
                        <input class="button" type="submit" value="log in" />
                    </span>
                </li>
            </ul>
        </form>
    </div>
</div>

<!-- THE MAIN AREA -->
<div id="main" class="main_normal">
    <div id="message"><?php echo $page_title ?></div>
    <div id="splash"><?php echo "$mk_splash" ?></div>
</div>


<!-- ARK FOOTER -->
<div id="ark_footer">
    <?php $footer = mkArkFooter(); echo($footer); ?>
</div>


<!-- end CONTENT WRAPPER -->
</div>


</body>
</html>

<?php

}

//DO AUTHENTICATION

// This is the part of the script checks auth status and does the redirections as appropriate
include_once ('config/settings.php');

//LOGOUT ROUTINE
$logout = reqQst($_REQUEST,'logout');
session_name($ark_name);
session_start();
if ($logout) {
    session_destroy();
    session_name($ark_name);
    session_start();
    $_SESSION['loginMessage'] = 'You logged out';
}

$target_url = reqQst($_SESSION, 'target_url');

//LOGIN AUTHENTICATION
if (reqQst($_SESSION, 'authorised')) {
    //there is a user logged in
    if ($target_url) {
        header("Location: $target_url");
    } else {
        header("Location: user_home.php");
    }
} else {
    // Unauthorised login attempt
    if ($_SESSION && !$logout) {
        $_SESSION['loginMessage'] = 'Your username or password was entered incorrectly';
    }
    // No session established, no POST variables
    // Display the login form and any error message
    login_page(reqQst($_SESSION, 'loginMessage'));
    session_destroy();
}

?>