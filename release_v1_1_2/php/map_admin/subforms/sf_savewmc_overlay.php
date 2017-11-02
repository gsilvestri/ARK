<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* 
*
* sf_savewmc_overlay.php
*
* this subform is generally used in an overlay format and provides the form to save metadata about a saved map
*
* PHP versions 4 and 5
*
* LICENSE:
*    ARK - The Archaeological Recording Kit.
*    An open-source framework for displaying and working with
*    archaeological data
*    Copyright (C) 2007  L - P : Partnership Ltd.
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
* @category   map admin
* @package    ark
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @copyright  1999-2009 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/php/map_admin/subforms/sf_savewmc_overlay.php   
* @since      File available since Release 0.8
*/

//as this is an overlay to be pulled using lightbox - we need to treat it as standalone form
// OVERLAY MODE
// DEV NOTE: as of v1.0 overlay_holder should take care of this... check and correct GH 23/11/2011
if ($sf_state == 'overlay') {
    // set up anything that is needed
    $scales = reqQst($_REQUEST,'scales');
    $projection = reqQst($_REQUEST,'projection');
    $OSM = reqQst($_REQUEST,'OSM');
    $gmap_api_key = reqQst($_REQUEST,'gmap_api_key');
    $updateWMC = reqQst($_REQUEST,'updateWMC');
    $update_success = FALSE;
    if ($updateWMC == 1) {
        include ('update_savewmc_overlay.php');
    }
    $sf_id = reqQst($_REQUEST,'sf_id');
    $overlay = TRUE;
    $scripts = "<script type=\"text/javascript\" src=\"$ark_dir/js/js_functions.js\"></script>\n";
    $scripts .= "<script type=\"text/javascript\" src=\"$ark_dir/lib/js/xml2json.js\"></script>\n";
    $scripts .= "<script type=\"text/javascript\" src=\"$ark_dir/lib/js/jquery.js\"></script>\n";
    $scripts .= "<script type=\"text/javascript\">jQuery.noConflict();</script>\n";
}

// ---- COMMON ---- //
// get common elements for all states

// Labels and so on

$mk_mapsave_instr = getMarkup('cor_tbl_markup', $lang, 'mapsave_instr');
$mk_name = getMarkup('cor_tbl_markup', $lang, 'map_name');
$mk_comments = getMarkup('cor_tbl_markup', $lang, 'map_comments');
$mk_public = getMarkup('cor_tbl_markup', $lang, 'map_public');
print("<div id=\"message\" class=\"message\">$message</div>"); 

// CSS
// If an optional CSS class has been specified, use it. Otherwise set a default
if (array_key_exists('op_sf_cssclass', $sf_conf)) {
    $sf_cssclass = $sf_conf['op_sf_cssclass'];
} else {
    $sf_cssclass = 'mc_subform';
}


// ---- STATE SPECFIC
// for each state get specific elements and then produce output

switch ($sf_state) {
    case 'overlay':
        
        if (!$update_success) {
            $form = "<form method=\"POST\" name=\"savewmc_overlay_form\" id=\"savewmc_overlay_form\" action=\"{$_SERVER['PHP_SELF']}\">";
            $form .= "<fieldset>";
            $form .= "<input type=\"hidden\" name=\"submiss_serial\" value=\"{$_SESSION['submiss_serial']}\" />\n";
            $form .= "<input type=\"hidden\" name=\"sf_id\" value=\"$sf_id\" />";
            $form .= "<input type=\"hidden\" name=\"sf_conf\" value=\"conf_map_wmcoverlay\" />";
            $form .= "<input type=\"hidden\" name=\"map_action\" value=\"save_map\" />";
            $form .= "<input id=\"layers\" type=\"hidden\" name=\"layers\" value=\"\" />";
            $form .= "<input type=\"hidden\" name=\"user_id\" value=\"$user_id\" />";
            $form .= "<input type=\"hidden\" name=\"updateWMC\" value=\"1\" />";
            $form .= "<input id=\"extents\" type=\"hidden\" name=\"extents\" value=\"\" />";
            $form .= "<input id=\"zoom\" type=\"hidden\" name=\"zoom\" value=\"\" />";
            $form .= "<input id=\"scales\" type=\"hidden\" name=\"scales\" value=\"$scales\" />";
            $form .= "<input id=\"projection\" type=\"hidden\" name=\"projection\" value=\"$projection\" />";
            $form .= "<input id=\"OSM\" type=\"hidden\" name=\"OSM\" value=\"$OSM\" />";
            $form .= "<input id=\"gmap_api_key\" type=\"hidden\" name=\"gmap_api_key\" value=\"$gmap_api_key\" />";
            $form .= "<input id=\"lboxreload\" type=\"hidden\" name=\"lboxreload\" value=\"$lboxreload\" />";
            $form .= "<input id=\"wmc\" type=\"hidden\" name=\"wmc\" value=\"\" />";
            // Contain the input elements in a list
            $form .= "<ul>\n";
            $form .= "<li class=\"row\">";
            $form .= "<label class=\"form_label\">$mk_name</label>";
            $form .= "<span class=\"inp\"><textarea id=\"map_name\" name=\"map_name\"></textarea></span>";
            $form .= "</li>\n";
            $form .= "<li class=\"row\">";
            $form .= "<label class=\"form_label\">$mk_comments</label>";
            $form .= "<span class=\"inp\"><textarea id=\"map_comments\" name=\"map_comments\"></textarea></span>";
            $form .= "</li>\n";
            $form .= "<li class=\"row\">";
            $form .= "<label class=\"form_label\">$mk_public</label>";
            $form .= "<input type=\"checkbox\" id=\"map_public\" value=\"1\" checked name=\"map_public\">";
            $form .= "</li>\n";
            $form .= "<li class=\"row\">";
            $form .= "<label class=\"form_label\">&nbsp;</label>";
            $form .= "<span class=\"inp\"><button type=\"submit\" />Save Map </button></span>";
            $form .= "</li>\n";
            $form .= "</ul>\n";
            $form .= "</fieldset>";
            $form .= "</form>\n";
            $form .= "<div id=\"message_response\" class=\"message\"></div>";
            print $scripts;
            print("<div id=\"{$sf_conf['sf_html_id']}\" class=\"{$sf_cssclass}\">");
            print $form;
            print("</div>");
            //now fill the WMC value
            print ("<script> 
                document.getElementById('wmc').value = parent.document.getElementById('wmc_code').value;
                extent =  parent.map.getExtent();
                document.getElementById('extents').value = extent.left + ',' + extent.bottom + ',' + extent.right + ',' + extent.top;
                zoom =  parent.map.getZoom();
                document.getElementById('zoom').value = zoom;
                layers = parent.map.layers;
                layer_value = '';
                for (i in layers) {
                    if (typeof layers[i] == 'object') {
                        if (layers[i].getVisibility() == true) {
                            layer_value = layer_value + '|' + layers[i].name + ':' + '1';
                        } else {
                            layer_value = layer_value + '|' + layers[i].name + ':' + '0';
                        }
                    } 
                }
                document.getElementById('layers').value = layer_value

            </script>");
        }
        
    break;
        
    // a default - in case the sf_state is incorrect
    default:
       echo "<div id=\"sf_savewmc_overlay\" class=\"{$sf_cssclass}\">\n";
       echo "<h3>No SF State</h3>\n";
       echo "<p>ADMIN ERROR: the sf_state for sf_savewmc_overlay was incorrectly set</p>\n";
       echo "<p>The var 'sf_state contained' '$sf_state'</p>\n";
       echo "</div>\n";
       break;
       
// ends switch
}

?>