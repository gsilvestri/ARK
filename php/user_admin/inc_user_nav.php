<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* user_admin/inc_user_nav.php
*
* script to accept a user_id and to select and change it
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
* @link       http://ark.lparchaeology.com/svn/php/user_admin/inc_user_nav.php
* @since      File available since Release 0.6
*/

//REQUEST IT
$user_id = reqQst($_REQUEST,'user_id');

//OFFER FEEDBACK
if (!$user_id) {
    $error[] = array('vars' => getMarkup('cor_tbl_markup', $lang, 'err_nouid'));
}

//OFFER A SELECTOR
//Draw a form with dd and submit

$dd = ddSimple($user_id, FALSE, 'cor_tbl_users', 'username', 'user_id', 'ORDER BY username ASC', 'code');
$mk_go = getMarkup('cor_tbl_markup', $lang, 'go');

printf("
        <form method=\"$form_method\" id=\"ste_cd_selector\" action=\"$_SERVER[PHP_SELF]\">
        <span class=\"row\"><span class=\"label\" style=\"width:auto\">
        <h5>User:</h5></span><span class=\"input\">$dd</span>
        <span class=\"input\"><button type=\"submit\">$mk_go</button></span></span></form>
");

?>