<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
* config/mod_abk_settings.php
*
* Settings file for the module abk (address book)
* This settings file is used on a per module basis and there should be one copy
* per module (named mod_MOD_settings.php)
* stores all of the module settings for the ARK instance
* there are inline comments and therefore most variables should
* be self evident
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
* @category   admin
* @package    ark
* @author     Henriette Roued <henriette@roued.com>
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @author     Guy Hunt <guy.hunt@lparchaeology.com>
* @copyright  1999-2008 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/php/config/mod_abk_settings.php
* @since      File available since Release 0.6
*/


// READ THE WIKI BEFORE EDITING THIS FILE

// DEV NOTE: IF YOU ADD TO THIS FILE ADD A WIKI ENTRY

// REGISTER STUFF
//	var to set which field to show on the register
$conf_regist_view_field = 'name';

/**  SUBFORMS
*
* describe the subforms and the vars they need to display properly
*
* 1 - set up any validation rules you need in the vd_settings file. Anything mod
*  specific ought to go in this settings file, example syntax is given below.
*
* 2 - set up any fields to put into the form. generally these ought to go into 
*  the field_settings file as this means they can be used by other modules. mod
*  specific fields may go into this file at the top in the 'fields' section.
*
* 3 - add in any custom validation
*
* 4 - set up the form using the standard subform format. The form is an array
*  containing variables that define the form an an array of fields
*
* Note: custom validation
*
*  In order to add custom validation rules on a use per use basis of a field
*  the additional or custom rules may be inserted into the validation arrays for
*  each field using the following shorthand syntax. This must be done before
*  adding the field to the suform
*  $field['add_validation][] = $my_custom_rule;
*
* view_state = the default view state (min or max)
* edit_state = the default edit state (edit or entry or view)
* sf_title = this is the nickname of markup to display in the title bar of the sf
* sf_html_id = this is the tag to apply to the <form> element of the subform
* script = the script to use on this subform
*
* Options:
* op_label = the label for the options row of the form (markup nname)
* op_input = the label to appear in the button (markup nname)
* op_register_mod = embedded registers need this
* op_subform_style = embedded registers need this set true to display like an sf
* op_xmi_mod = the xmi viewer needs this to know which module to display
* sf_op_attributetype = attribute displaying forms may use this
*/

// A subform that handles itemval conflicts raised by $conf_mcd_itemval
$conf_mcd_itemval_conflicts =
    array(
        'view_state' => 'max',
        'edit_state' => 'view',
        'sf_nav_type' => 'nmedit',
        'sf_title' => 'dnarecord', 
        'sf_html_id' => 'abk_itemvalconflict', // Must be unique
        'script' => 'php/subforms/sf_dnarecord.php',
        'op_recursive' => FALSE,
        'fields' =>
            array(
            ),
    );

// A subform that makes the itemvalue editable
$conf_mcd_itemval =
    array(
        'view_state' => 'max',
        'edit_state' => 'view',
        'sf_nav_type' => 'nmedit',
        'sf_title' => 'itemval', 
        'sf_html_id' => 'abk_itemval', // Must be unique
        'script' => 'php/subforms/sf_itemval.php',
        'op_label' => 'space',
        'op_input' => 'save',
        'op_modtype' => FALSE, //if each modtype uses same fields (see below)
        'conflict_res_sf' => 'conf_mcd_itemval_conflicts',
        'fields' =>
            array(
                $conf_field_abk_itemval
            ),
    );

// A subform which handles modtype conflict issues raised by $conf_mcd_abktype,
// used in when editing modtype
$conf_mcd_modtype_conflicts =
    array(
        'view_state' => 'max',
        'edit_state' => 'view',
        'sf_nav_type' => 'nmedit',
        'sf_title' => 'abktypeconflicts', 
        'sf_html_id' => 'abk_modtypeconflict', // Must be unique
        'script' => 'php/subforms/sf_modtype_conflicts.php',
        'op_label' => 'space',
        'op_input' => 'save',
        'op_modtype' => FALSE, //if each modtype uses same fields (see below)
        'fields' =>
            array(
                $conf_field_abktype
            ),
    );

// A subform to make the module modtype editable
$conf_mcd_modtype =
    array(
        'view_state' => 'max',
        'edit_state' => 'view',
        'sf_nav_type' => 'nmedit',
        'sf_title' => 'abktype', 
        'sf_html_id' => 'abk_modtype', // Must be unique
        'script' => 'php/subforms/sf_modtype.php',
        'op_label' => 'space',
        'op_input' => 'save',
        'op_modtype' => FALSE, //if each modtype uses same fields (see below)
        'conflict_res_sf' => 'conf_mcd_modtype_conflicts',
        'fields' =>
            array(
                $conf_field_abktype
            ),
    );


$conf_mcd_abkdesc =
    array(
        'view_state' => 'max',
        'edit_state' => 'view',
        'sf_title' => 'desc', //appears in the titlebar of the subform (mk nname)
        'sf_html_id' => 'abk_abk_desc', //the form id tag (must be unique)
        'script' => 'php/subforms/sf_txt.php',
        'sf_nav_type' => 'name',
        'op_label' => 'space',
        'op_input' => 'edit',
        'fields' => array(
            $conf_field_name,
            $conf_field_initials,            
        )       
);


/**  REGISTER
*
* A form used for issuing new items to this module
*
* The register is essentially just another subform. In most uses it is simply
* used as a standalone form. However it may be used in an embedded form
* within another module's pages. In this case, some additional custom validation
* is generally required
*
* 1 - set up any validation rules you need in the vd_settings file. Anything mod
*  specific ought to go in this settings file, example syntax is given below.
*
* 2 - set up any fields to put into the form. generally these ought to go into 
*  the field_settings file as this means they can be used by other modules. mod
*  specific fields may go into this file at the top in the 'fields' section.
*
* 3 - add in any custom validation
*
* 4 - set up the form using the standard subform format. The form is an array
*  containing variables that define the form an an array of fields
*
* Note: custom validation
*
*  In order to add custom validation rules on a use per use basis of a field
*  the additional or custom rules may be inserted into the validation arrays for
*  each field using the following shorthand syntax. This must be done before
*  adding the field to the suform
*  $field['add_validation][] = $my_custom_rule;
*/


// Subform Package
// Register Subform Conf
$conf_register =
    array(
        'view_state' => 'max',
        'edit_state' => 'edit',
        'sf_title' => 'register', 
        'sf_html_id' => 'abk_cd_register', // Must be unique
        'sf_nav_type' => 'none',
        'script' => 'php/subforms/sf_register_tbl.php',
        'op_label' => 'save',
        'op_input' => 'save',
        'op_reg_mode' => 'tbl',
        'op_sf_cssclass' => 'register', // Applies custom CSS class so it is displayed differently than other subforms
        'fields' =>
            array(
                $conf_field_abk_cd,
                $conf_field_abktype,
                $conf_field_name,
                $conf_field_initials,
                $conf_reg_op_no_enter
            ),
);

/* The column holding the register subform. */
$conf_dat_regist =
    array(
        'col_id' => 'main_column',
        'col_alias' => FALSE,
        'col_type' => 'register_col',
        'subforms' => array(
            $conf_register
        )
);

/**  MICRO VIEW
*
* settings for the micro view page
*
* essentially the micro view page is used to display a single record. This page
* makes use of the subforms set up above and assembles them into columns
* according to the settings given in this section. First the subforms are
* packaged into columns and then these are packaged together ofr convenience
*
* 1 - make up columns
*
* 2 - package columns into an array
*
* 3 - set display options
*
*/ 

// Columns
//  col_id = the html id attribute to set for the div of the column
//  col_alias = the words to display in to users int he column header
// FIXME:
// col_alias should be renamed col_name and make use of markup. This is
// NOT an alias and this is currently language dependant
//  col_type = the html class attribute of the column div (must match a valid css class)
//  subforms = an array of subforms in the order they are displayed

$conf_mcd_col_1 =
    array(
        'col_id' => 'main_column',
        'col_alias' => FALSE,
        'col_type' => 'primary_col',
        'subforms' => array(
            $conf_mcd_abkdesc,
        )
);

/*$conf_mcd_col_2 =
    array(
        'col_id' => '2nd_column',
        'col_alias' => FALSE,
        'col_type' => 'secondary_col',
        'subforms' => array(
            $conf_mcd_xmiabk
        )
);
*/

// Columns Package
$abk_conf_mcd_cols =
    array(
        'op_display_type' => 'cols',
        'op_top_col' => 'main_column', // string to match the 'col_id'
        'columns' =>
            array(
                  $conf_mcd_col_1,
                  //$conf_mcd_col_2
        )        
);

/**  DATA VIEW
*
* settings for the data view page
*
* the dataview page is used to display many records from different modules
* often simultaneously. This means that each mdule must know what to display
* in this context. The data view page can display in several formats:
*
* table - the data is expressed as a series of xhtml tables. Each module
*  needs to know what columns to display and how to make up the column headers
*  for each column (field)
*
* chat - this is typically used to display a snippet of text from a freetext
*  type search. This means that the settings for this are minimal
*
* map - this displays a map of the results with marker labels for each item
*
*/

// Table
//  This is basically a subform package and follows the subform rules
$conf_mac_table =
    array(
        'op_no_rows' => 15,
        'fields' =>
            array(
                $conf_field_abk_cd,
                $conf_field_name,
                $conf_field_initials,
                $conf_reg_op
        )
);
// Text
//  This is basically a subform package and follows the subform rules
$conf_mac_text =
    array(
        'op_no_rows' => 25,
        'fields' =>
            array(
                $conf_field_abktype,
                $conf_field_short_desc,
                $conf_field_issuedto,
                $conf_field_issuedon,
                $conf_reg_op_view
        )
);    

// Thumbs
//  This is basically a subform package and follows the subform rules
$conf_mac_thumb =
    array(
        'op_no_rows' => 25,
        'fields' =>
            array(
                $conf_field_abk_cd
        )
);

// XMI VIEWER STUFF
//  Any given item may be viewed in a reduced form from within another module
//  this part of the settings file describes how this module represents itself
//  when called into an XMI view

// fields as set up at the top of the settings file. here we simply call them into
// the package

$fst_conf_xmidisplay =
    array(
        $conf_field_name,
        $conf_field_initials,
);

?>