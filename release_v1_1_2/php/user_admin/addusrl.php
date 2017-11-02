<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* user_admin/addusrl.php
*
* add liveuser view that organises global subforms into something specific
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
* @category   user
* @package    ark
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @author     Guy Hunt <guy.hunt@lparchaeology.com>
* @copyright  1999-2008 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/php/user_admin/adduserl.php
* @since      File available since Release 0.6
*/

// NOTE: this script should be made available to NON logged in users as a sort of create your own account script, according to a global setting. Link off the front page next to the login box.

//Markup
$mk_adusrl_instructions = getMarkup('cor_tbl_markup', $lang, 'adusrl_instructions');

?>

<?php
printf ("<div id=\"message\">");
printf ("<p>$mk_adusrl_instructions</p>");
printf ("</div>");
?>

<?php
$frm_action = $_SERVER['PHP_SELF'];
$frm_header = getMarkup('cor_tbl_markup', $lang, 'create_user');
$update_val = 'adusrl';
$show_pw = 'on';
$mk_button = $frm_header;
include_once('php/user_admin/global_subforms/user_form_elements.php');
?>