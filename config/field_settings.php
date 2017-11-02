<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* config/field_settings.php
*
* stores settings for 'fields' for this ARK instance. This makes
* the fields available for use in any subform in any module, as of v1.1
* all fields go in this file, and no longer in any of the mod_settings.
*
* PHP versions 4 and 5
*
* LICENSE:
*    ARK - The Archaeological Recording Kit.
*    An open-source framework for displaying and working with archaeological data
*    Copyright (C) 2012  L - P : Heritage LLP.
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
* @category   admin
* @package    ark
* @author     Henriette Roued <henriette@roued.com>
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @author     Guy Hunt <guy.hunt@lparchaeology.com>
* @copyright  1999-2012 L - P : Heritage LLP
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/config/field_settings.php
* @since      File available since Release 0.6
*/

/**
*
* VALIDATION RULES
*
*/

include('vd_settings.php');

/**
*
* FIELDS
*
* These arrays contain the info about each field.
*
* See the documentation for further information about what to put into
* each field: required vars, field_op vars and class specific vars.
*
* Documentation: http://ark.lparchaeology.com/wiki/index.php/Field_settings.php
*
* As of v1.1 all fields go in this file. No fields go in the mod settings.
*
*/

// -- ITEM KEY FIELDS -- //

/**
*
* Before v1.1 many of these fields were in their respective module settings
* files.
*
*/

// Itemkey for mod_abk (Address Book)
$conf_field_abk_cd =
    array(
        'field_id' => 'conf_field_abk_cd',
        'dataclass' => 'itemkey',
        'classtype' => 'abk_cd',
        'module' => 'abk',
        'aliasinfo' =>
        array(
            'alias_tbl' => 'cor_tbl_module',
            'alias_col' => 'itemkey',
                'alias_src_key' => 'abk_cd',
                'alias_type' => '1',
        ),
        'editable' => TRUE, // if FALSE, update_db will not process this field
        'hidden' => 'issuenext', // this makes the itemkey hidden in the register
        'field_op_default' => 'next', // and defaults to the next available number
        'add_validation' => $key_add_validation,
        'edt_validation' => $key_edt_validation
);

$conf_field_abk_cd['add_validation'][] = $key_vd_modtype;
$conf_field_abk_cd['edt_validation'][] = $key_vd_modtype;

// Set up the abktype (Address Book)
$conf_field_abktype =
    array(
        'field_id' => 'conf_field_abktype',
        'dataclass' => 'modtype',
        'classtype' => 'abktype',
        'aliasinfo' =>
            array(
                'alias_tbl' => 'cor_tbl_col',
                'alias_col' => 'dbname',
                'alias_src_key' => 'abktype',
                'alias_type' => '1',
        ),
        'editable' => TRUE, // if FALSE, update_db will not process this field
        'hidden' => FALSE,
        'add_validation' => 'none',
        'edt_validation' => 'none'
);


// This is field is used for changing the itemvalue
$conf_field_abk_itemval =
    array(
        'field_id' => 'conf_field_abk_cd',
        'dataclass' => 'itemkey',
        'classtype' => 'abk_cd',
        'module' => 'abk',
        'aliasinfo' =>
            array(
                'alias_tbl' => 'cor_tbl_module',
                'alias_col' => 'itemkey',
                'alias_src_key' => 'abk_cd',
                'alias_type' => '1',
            ),
        'editable' => TRUE, // if FALSE, update_db will not process this field
        'hidden' => FALSE, // this makes the itemkey hidden in the register
        'add_validation' => $key_add_validation,
        'edt_validation' => $key_edt_validation
);


// -- TEXT FIELDS -- //

// TXT fields for address book module

$conf_field_name =
    array(
        'field_id' => 'conf_field_name',
        'dataclass' => 'txt',
        'classtype' => 'name',
        'aliasinfo' => FALSE,
        'editable' => TRUE,
        'hidden' => FALSE,
        'add_validation' => $txt_add_validation,
        'edt_validation' => $txt_edt_validation
);

$conf_field_initials =
    array(
        'field_id' => 'conf_field_initials',
        'dataclass' => 'txt',
        'classtype' => 'initials',
        'aliasinfo' => FALSE,
        'editable' => TRUE,
        'hidden' => FALSE,
        'add_validation' => $txt_add_validation,
        'edt_validation' => $txt_edt_validation
);

// -- OPTIONAL FIELDS -- //

/** OPTIONAL FIELDS
* Optional fields are used in registers and viewing modes
* in the results view to navigate between views.
*/

$conf_reg_op =
    array(
        'dataclass' => 'op',
        'classtype' => 'none',
        'options' => 'view,enter,qed',
        'editable' => FALSE,
        'hidden' => FALSE
);
$conf_reg_op_no_qed = 
    array(
        'dataclass' => 'op',
        'classtype' => 'none',
        'options' => 'view,enter',
        'editable' => FALSE,
        'hidden' => FALSE
);
$conf_reg_op_view = 
    array(
        'dataclass' => 'op',
        'classtype' => 'none',
        'options' => 'view',
        'editable' => FALSE,
        'hidden' => FALSE
); 
$conf_reg_op_no_enter = 
    array(
        'dataclass' => 'op',
        'classtype' => 'none',
        'options' => 'view,qed',
        'editable' => FALSE,
        'hidden' => FALSE
);


/** DELETE FIELD
*
* For the delete frag routine, the following validation vars are required
* DO NOT PUT THIS INTO A SUBFORM
*
*/

$conf_field_delete = 
    array(
        'dataclass' => 'delete',
        'classtype' => 'delete',
        'editable' => TRUE,
        'hidden' => TRUE,
        'del_validation' => $del_validation
);

?>