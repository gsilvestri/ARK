<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
* map_functions.php
*
* map functions (utilising WMS and WFS)
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
* @category   map
* @package    ark
* @author     Stuart Eve <stuarteve@lparchaeology.com>
* @copyright  1999-2007 L - P : Partnership Ltd.
* @license    http://ark.lparchaeology.com/license
* @link       http://ark.lparchaeology.com/svn/php/map/map_functions.php
* @since      File available since Release 0.8
*/

// {{{ buildLegend()        
/**
* takes an array of layers and builds up a legend for map control
*
* generates a var that contains an unordered list as a legend
*
* @param array $legend_array  a properly formed array containing a list of WxS servers with layers
* @return string $var  a fully resolved html string
* @access public
* @since 0.8
*/

function buildLegend($legend_array)
{
    global $skin_path, $map_more_info_button;
    
    //the first thing to do is to clean out the legend array so that we only load the layers we need
    
    $legend_array = cleanLegendArray($legend_array);
    
    $var = "<ul id=\"legend\" class=\"layer_list\">\n";
    //loop over the legend array building as we go
    foreach ($legend_array['servers'] as $server_key => $server) {
        $var .= "<li class=\"server\" id=\"server_{$server_key}\">";
        $var .= "<a href=\"#\"><img class=\"legend_control\" onclick=\"hideChildren(this.parentNode.parentNode);swapImage(this);\" src=\"$skin_path/images/legend/group_show.png\" alt=\"folder\" /></a>{$server['title']}\n";
        $var .= "<ul>";
        foreach ($server['layers'] as $layer_key => $layer_value) {
            //check if we have sub-layers
            if (is_array($layer_value) && array_key_exists('sub_layers',$layer_value)) {
                if (!empty($layer_value['sub_layers'])) {
                    $var .= "<li class=\"group\" id=\"{$layer_value['title']}\">";
                    $var .= "<a href=\"#\"><img class=\"legend_control\" onclick=\"hideChildren(this.parentNode.parentNode);swapImage(this);\" src=\"$skin_path/images/legend/group_show.png\" alt=\"folder\" /></a>{$layer_value['title']}\n";
                }
                $var .= "<ul>";
                foreach ($layer_value['sub_layers'] as $sublayer_key => $sublayer_value) {
                    $var .= "<li class=\"layer\" id=\"{$sublayer_value['name']}\">";
                    $var .= "<a href=\"#\"><img src=\"$skin_path/images/legend/hide.png\" onclick=\"showChildren(this.parentNode.parentNode);swapImage(this);\" alt=\"hide\" /></a> {$sublayer_value['title']}\n";
                    $var .= "<a href=\"#\"><img src=\"$skin_path/images/osgeo/zoom-layer.png\" alt=\"zoom\" onclick=\"zoomLayer('{$sublayer_value['title']}');\" /></a>\n";
                    
                    //check if this layer should be on
                    if (array_key_exists('status',$sublayer_value)) {
                        if ($sublayer_value['status'] == '1') {
                            $var .= "<a href=\"#\"><img class=\"img_checkbox\"src=\"$skin_path/images/onoff/chk_on.png\" onclick=\"swapImage(this);hideLayer('{$sublayer_value['title']}');\" alt=\"chk_on\" /></a>\n";
                        } else {
                            $var .= "<a href=\"#\"><img class=\"img_checkbox\"src=\"$skin_path/images/onoff/chk_off.png\" onclick=\"swapImage(this);showLayer('{$sublayer_value['title']}');\" alt=\"chk_off\" /></a>\n";
                        }
                    }
                    
                    //now setup the more info button - this is mainly useful if you store 
                    //information about your gis layers in an ARK format
                    if ($map_more_info_button == TRUE) {
                        $more_info_name = explode('_',$sublayer_value['name']);
                        $mi_length = count($more_info_name);
                        if ($mi_length >= 2) {
                            $mi_item_stecd = $more_info_name[$mi_length-2];
                            $mi_item_value = $more_info_name[$mi_length-1];
                            $mi = $mi_item_stecd . "_" . $mi_item_value;
                            $mi_href =  "micro_view.php?item_key=gis_cd&amp;gis_cd=$mi";
                            $var .= "<a href=\"$mi_href\" target=\"_blank\">more</a>\n";
                        }
                    }
                    
                    if (array_key_exists('sld_array',$sublayer_value)) {
                        $var .= "<ul style=\"display:none\">"; 
                        foreach ($sublayer_value['sld_array'] as $legend_item) {
                            $var .= "<li class=\"class\" id=\"{$sublayer_value['name']}\">";
                            $var .= "<img src=\"$skin_path/images/legend/tree.png\" alt=\"tree\" />\n";
                            //now grab the colour for the legend icon
                            if (array_key_exists('type',$legend_item)) {
                                $type = $legend_item['type'];
                            } else {
                                $type = 4;
                            }
                            if (array_key_exists('colour',$legend_item)) {
                                $rgb = rgb2hex2rgb($legend_item['colour']);
                                $r = $rgb['r'];
                                $g = $rgb['g'];
                                $b = $rgb['b'];
                            } else {
                                $r = 0;
                                $g = 0;
                                $b = 0;
                            }
                            $var .= "<img class=\"classImg\" alt=\"class\" src=\"$skin_path/images/create_png.php?r=$r&g=$g&b=$b&type=$type\"/>\n";
                            $var .= " {$legend_item['name']}";
                            $var .= "</li>";
                        }
                        $var .= "</ul>"; 
                    }
                    $var .= "</li>";
                }
                $var .= "</ul>";
                $var .= "</li>\n";
               
            } else {
                $var .= "<li class=\"layer\" id=\"{$layer_value['name']}\">";
                $var .= "{$layer_value['title']}\n";
                $var .= "<a href=\"#\"><img src=\"$skin_path/images/osgeo/zoom-layer.png\" alt=\"zoom\" onclick=\"zoomLayer('{$layer_value['title']}');\" /></a>\n";
                if (array_key_exists('status',$layer_value)) {
                    if ($layer_value['status'] == '1') {
                        $var .= "<a href=\"#\"><img class=\"img_checkbox\"src=\"$skin_path/images/onoff/chk_on.png\" onclick=\"swapImage(this);hideLayer('{$layer_value['title']}');\" alt=\"chk_on\" /></a>\n";
                    } else {
                        $var .= "<a href=\"#\"><img class=\"img_checkbox\"src=\"$skin_path/images/onoff/chk_off.png\" onclick=\"swapImage(this);showLayer('{$layer_value['title']}');\" alt=\"chk_off\" /></a>\n";
                    }
                }
                
                //now setup the more info button - this is mainly useful if you store 
                //information about your gis layers in an ARK format
                if ($map_more_info_button == TRUE) {
                    $more_info_name = explode('_',$layer_value['name']);
                    $mi_length = count($more_info_name);
                    if ($mi_length >= 2) {
                        $mi_item_stecd = $more_info_name[$mi_length-2];
                        $mi_item_value = $more_info_name[$mi_length-1];
                        $mi = $mi_item_stecd . "_" . $mi_item_value;
                        $mi_href =  "micro_view.php?item_key=gis_cd&amp;gis_cd=$mi";
                        $var .= "<a href=\"$mi_href\" target=\"_blank\">more</a>\n";
                    }
                }
                $var .= "<ul>";
                if (array_key_exists('sld_array',$layer_value)) {
                    $var .= "<ul>"; 
                    foreach ($layer_value['sld_array'] as $legend_item) {
                        $var .= "<li class=\"class\" id=\"s_{$server_key}_l_{$layer_key}\">";
                        $var .= "<img src=\"$skin_path/images/legend/tree.png\" alt=\"tree\" />\n";
                        //now grab the colour for the legend icon
                        if (array_key_exists('type',$legend_item)) {
                            $type = $legend_item['type'];
                        } else {
                            $type = 5;
                        }
                        if (array_key_exists('colour',$legend_item)) {
                            $rgb = rgb2hex2rgb($legend_item['colour']);
                            $r = $rgb['r'];
                            $g = $rgb['g'];
                            $b = $rgb['b'];
                        } else {
                            $r = 0;
                            $g = 0;
                            $b = 0;
                        }
                        $var .= "<img class=\"classImg\" alt=\"class\" src=\"$skin_path/images/create_png.php?r=$r&g=$g&b=$b&type=$type\"/>\n";
                        $var .= " {$legend_item['name']}";
                        $var .= "</li>";
                    }
                    $var .= "</ul>"; 
                }
                $var .= "</ul>";
                $var .= "</li>\n";
            }
        }
        $var .= "</ul>\n";
        $var .= "</li>\n";
    }
    $var .= "</ul>\n";
    return ($var);
}
// }}}
// {{{ buildLegendAdmin()        
/**
* takes an array of layers and builds up a legend with admin controls
*
* generates a form that can be used to submit a legend array
*
* @param array $legend_array  a properly formed array containing a list of WxS servers with layers
* @param array $extra_params OPTIONAL - if you want to send any extra params with the form submission (array of key/value pairs)
* @return string $var  a fully resolved html string
* @access public
* @since 0.8
*/

function buildLegendAdmin($legend_array, $extra_params = FALSE)
{
    //the legend is one big form
    global $skin_path, $lang, $form_method;
    $mk_legend_admin_instr = getMarkup('cor_tbl_markup', $lang, 'legend_admin_instr');
    $var = "<div>$mk_legend_admin_instr</div>";
    $var .= "<form method=\"$form_method\" name=\"legend_form\">\n";
    $var .= "<ul id=\"legend\" class=\"layer_list\">\n";
    //loop over the legend array building as we go
    foreach ($legend_array['servers'] as $server_key => $server) {
        $var .= "<li class=\"group\" id=\"server_{$server_key}\">";
        $var .= "<a href=\"#\"><img class=\"legend_control\" onclick=\"hideChildren(this.parentNode.parentNode);swapImage(this);\" src=\"$skin_path/images/legend/group_show.png\" alt=\"folder\" /></a>{$server['title']}\n";
        $var .= "<ul>"; 
        foreach ($server['layers'] as $layer_key => $layer_value) {
            //check if we have sub-layers
            if (is_array($layer_value) && array_key_exists('sub_layers',$layer_value)) {
                $var .= "<li class=\"layer\" id=\"server_{$server_key}_layer_{$layer_key}\">";
                $var .= "<a href=\"#\"><img class=\"legend_control\" onclick=\"hideChildren(this.parentNode.parentNode);swapImage(this);\" src=\"$skin_path/images/legend/group_show.png\" alt=\"folder\" /></a>{$layer_value['title']}\n";
                $var .= "<a href=\"#\"><img class=\"parent_img_checkbox\" src=\"$skin_path/images/onoff/chk_off.png\" onclick=\"swapImage(this);checkAll(this);\" alt=\"chk_off\" />\n";
                $var .= "<input style=\"display:none\" type=\"checkbox\" name=\"layers_on[]\" value=\"s_{$server_key}_l_{$layer_key}\"></a>\n";
                $var .= "<ul>";
                foreach ($layer_value['sub_layers'] as $sublayer_key => $sublayer_value) {
                    $var .= "<li class=\"layer\" id=\"s_{$server_key}_l_{$layer_key}_sl_{$sublayer_key}\">";
                    $var .= "<a href=\"#\"><img src=\"$skin_path/images/legend/show.png\" onclick=\"hideChildren(this.parentNode.parentNode);swapImage(this);\" alt=\"show\" /></a> {$sublayer_value['title']}\n";
                    $var .= "<a href=\"#\"><img src=\"$skin_path/images/osgeo/zoom-layer.png\" alt=\"zoom\" /></a>\n";
                    $var .= "<a href=\"#\"><img class=\"img_checkbox\" src=\"$skin_path/images/onoff/chk_off.png\" onclick=\"swapImage(this);\" alt=\"chk_off\" />\n";
                    $var .= "<input style=\"display:none\" type=\"checkbox\" name=\"layers_on[]\" value=\"s_{$server_key}_l_{$layer_key}_sl_{$sublayer_key}\"></a>\n";
                    
                    if (array_key_exists('sld_array',$sublayer_value)) {
                        $var .= "<ul>"; 
                        foreach ($sublayer_value['sld_array'] as $legend_item) {
                            $var .= "<li class=\"class\" id=\"s_{$server_key}_l_{$layer_key}_sl_{$sublayer_key}\">";
                            $var .= "<img src=\"$skin_path/images/legend/tree.png\" alt=\"tree\" />\n";
                            //now grab the colour for the legend icon
                            if (array_key_exists('type',$legend_item)) {
                                $type = $legend_item['type'];
                            } else {
                                $type = 4;
                            }
                            if (array_key_exists('colour',$legend_item)) {
                                $rgb = rgb2hex2rgb($legend_item['colour']);
                                $r = $rgb['r'];
                                $g = $rgb['g'];
                                $b = $rgb['b'];
                            } else {
                                $r = 0;
                                $g = 0;
                                $b = 0;
                            }
                            $var .= "<img class=\"classImg\" alt=\"class\" src=\"$skin_path/images/create_png.php?r=$r&g=$g&b=$b&type=$type\"/>\n";
                            $var .= " {$legend_item['name']}";
                            $var .= "</li>";
                        }
                        $var .= "</ul>"; 
                    }
                    $var .= "</li>";
                }
                $var .= "</ul>";
                $var .= "</li>\n";
               
            } else {
                $var .= "<li class=\"layer\" id=\"server_{$server_key}_layer_{$layer_key}\">";
                $var .= "<a href=\"#\"><img src=\"$skin_path/images/legend/show.png\" alt=\"folder\" /></a> {$layer_value['title']}\n";
                $var .= "<a href=\"#\"><img src=\"$skin_path/images/osgeo/zoom-layer.png\" alt=\"zoom\" /></a>\n";
                $var .= "<a href=\"#\"><img class=\"img_checkbox\"src=\"$skin_path/images/onoff/chk_off.png\" onclick=\"swapImage(this);checkAll(this);\" alt=\"chk_off\" />\n";
                $var .= "<input style=\"display:none\" type=\"checkbox\" name=\"layers_on[]\" value=\"s_{$server_key}_l_{$layer_key}\"></a>\n";
                $var .= "<ul>";
                if (array_key_exists('sld_array',$layer_value)) {
                    $var .= "<ul>"; 
                    foreach ($layer_value['sld_array'] as $legend_item) {
                        $var .= "<li class=\"class\" id=\"s_{$server_key}_l_{$layer_key}\">";
                        $var .= "<img src=\"$skin_path/images/legend/tree.png\" alt=\"tree\" />\n";
                        //now grab the colour for the legend icon
                        if (array_key_exists('type',$legend_item)) {
                            $type = $legend_item['type'];
                        } else {
                            $type = 5;
                        }
                        if (array_key_exists('colour',$legend_item)) {
                            $rgb = rgb2hex2rgb($legend_item['colour']);
                            $r = $rgb['r'];
                            $g = $rgb['g'];
                            $b = $rgb['b'];
                        } else {
                            $r = 0;
                            $g = 0;
                            $b = 0;
                        }
                        $var .= "<img class=\"classImg\" alt=\"class\" src=\"$skin_path/images/create_png.php?r=$r&g=$g&b=$b&type=$type\"/>\n";
                        $var .= " {$legend_item['name']}";
                        $var .= "</li>";
                    }
                    $var .= "</ul>"; 
                }
                $var .= "</ul>";
                $var .= "</li>\n";
            }
        }
        $var .= "</ul>\n";
        $var .= "</li>\n";
    }
    $var .= "</ul>\n";
    //now check if we have any extra parameters that we want to send
    if (is_array($extra_params)) {
        foreach ($extra_params as $key => $value) {
            $var .= "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
        }
    }
    $var .= "<input type=\"submit\" value=\"submit\"/>\n";
    $var .= "</form>";
    return ($var);
}
// }}}
// {{{ buildOpenLayersToolbar()
        
/**
*  creates a javascript string for setting up the OpenLayers toolbar
*
*
* @param array $legend_array  the legend array
* @param string $map  the name of the js map object OPTIONAL (set as 'map' by default)
* @return string $string  the js code to print
* @access public
* @since 0.6
*/

function buildOpenLayersToolbar($legend_array, $map = 'map')
{
    $var = '';
    if (!is_array($legend_array)) {
        $legend_array = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $legend_array);
        $legend_array = unserialize($legend_array);
    }
    
    foreach ($legend_array['servers'] as $key => $value) {
        $url = $value['url'];
        $url = explode('?',$url);
        $url = $url[0];
        $query_layers = "";
        //check if the layers are queryable - so that we can tell the query tool what layers to query
        foreach ($value['layers'] as $layer_key => $layer_value) {
            if (array_key_exists('sub_layers',$layer_value)) {
                foreach ($layer_value['sub_layers'] as $sublayer_key => $sublayer_value) {
                    if ($sublayer_value['queryable'] == 1) {
                        $query_layers .=  $sublayer_value['name'] . ",";
                    }
                }
            } else {
                if ($layer_value['queryable'] == 1) {
                       // $query_layers .= "'" . $sublayer_value['name'] . "',";
                       $query_layers .= $layer_value['name'] . ",";
                }
            }
        }
        $query_layers = rtrim($query_layers,',');
    }
    
    //build the JS itself for insertion in the main page
    $var = '
        jQuery(document).ready(function() {

            var hoverObject = "#controlPanel";

            var originalHeight = jQuery(hoverObject).height();
            document.getElementById(\'controlPanel\').style.height = "0px";

            jQuery(hoverObject).hover(function(){

                jQuery(hoverObject).animate({ height: originalHeight }, 150);
                }, function(){ jQuery(hoverObject).animate({ height: "0px" }, 350);
                });
        });
        
        controlOverlaydiv = document.getElementById(\'controlOverlay\');
        map.addControl(new OpenLayers.Control.MousePosition({ div: document.getElementById(\'mousePosition\'), numDigits: 2 }));    

        // display the map projection
        
        map.addControl(new OpenLayers.Control.Scale(\'scaleText\'));
        map.addControl(new OpenLayers.Control.ScaleLine({div: document.getElementById(\'scaleLine\')})); 
        map.addControl(new OpenLayers.Control.Navigation({"zoomWheelEnabled": true}));
        map.addControl(new OpenLayers.Control.ZoomPanel());                

        var zoomBox = new OpenLayers.Control.ZoomBox({ title: "Zoom in box" });
        var navHistory = new OpenLayers.Control.NavigationHistory();  
        navHistory.previous.title = "View history backward";
        navHistory.next.title = "View history forward";            
        map.addControl(navHistory);
        
        // build the featureInfo control (aka query tool/button)
        featureInfo = new OpenLayers.Control.WMSGetFeatureInfo({
            url: \''. $url .'\', 
            title: \'Identify features by clicking\',
            layers: "' . $query_layers . '",
            infoFormat: "gml",
            queryVisible: true,             
        });
        
        // register events to the featureInfo control
        featureInfo.events.register("activate", featureInfo, function() { 
            toggleQueryMode(); });                
        featureInfo.events.register("deactivate", featureInfo, function() { 
            toggleQueryMode(); });

        // build the measure controls
        var optionsLine = {
            handlerOptions: {
                persist: true
            },
            displayClass: "olControlMeasureDistance",
            title: "Measure Distance"
        };

        var optionsPolygon = {
            handlerOptions: {
                persist: true
            },
            displayClass: "olControlMeasureArea",
            title: "Measure Area"
        };

        measureControls = {
            line: new OpenLayers.Control.Measure(
              OpenLayers.Handler.Path, 
              optionsLine 
            ),
            polygon: new OpenLayers.Control.Measure(
                OpenLayers.Handler.Polygon, 
                optionsPolygon
            )
        };
        
        for(var key in measureControls) {
            control = measureControls[key];
            control.events.on({
                "measure": handleMeasurements,
                "measurepartial": handleMeasurements
            });
        };                           

        // create the panel where the controls will be added
        var panel = new OpenLayers.Control.Panel({ defaultControl: zoomBox, div: document.getElementById(\'controlPanel\')});

        panel.addControls([
            zoomBox,
            new OpenLayers.Control.ZoomBox({
                title:"Zoom out box",
                displayClass: \'olControlZoomOutBox\',
                out: true
                }),
            new OpenLayers.Control.DragPan({title:\'Drag map\', displayClass: \'olControlPanMap\'}),
            featureInfo,
            new OpenLayers.Control.ZoomToMaxExtent({title: "zoom to map extent"}),
            navHistory.previous,
            navHistory.next,
            measureControls.line,
            measureControls.polygon
            ]);                    

         // add the panel to the map
         map.addControl(panel);
         
        function getFeatureInfo (event) {
            for (var i=0; i < map.layers.length; i++) {
                 var layer = map.layers[i];
                 if (layer.getFullRequestString) {
                      var url =   layer.getFullRequestString({
                                        REQUEST: "GetFeatureInfo",
                                        EXCEPTIONS: "application/vnd.ogc.se_xml",
                                        BBOX: map.getExtent().toBBOX(),
                                        X: event.xy.x,
                                        Y: event.xy.y,
                                        INFO_FORMAT: "gml",
                                        QUERY_LAYERS: "'.$query_layers.'",
                                        WIDTH: map.size.w,
                                        HEIGHT: map.size.h,
                                        FEATURE_COUNT: 10
                                        });
                 }
            }
            if (url) {
                OpenLayers.loadURL(url, "", this, parseResponse);
            }
            // do something with the response
            
            if (map.popups[0]) {
                map.popups[0].destroy();
            }
            AutoSizeAnchored = OpenLayers.Class(OpenLayers.Popup.Anchored, {
                    //   \'autoSize\': true,
                       "displayClass": "olQueryResult",
                       "contentDisplayClass": "olQueryResultContent",
                       "opacity": 0,
                       "backgroundColor": "transparent"
                   });
            
            map.addPopup(new AutoSizeAnchored(
                "queryOutput", 
                map.getLonLatFromPixel(event.xy),
                new OpenLayers.Size(200,150),
                event.text,
                null,
                true
            ));
            map.popups[0].setBackgroundColor("");
        }
        // create a new event handler for single click featureInfo query
        queryEventHandler = new OpenLayers.Handler.Click({ "map": map }, { "click":
        function(e) { getFeatureInfo(e); } });
        function parseResponse (response) {
            parseGetFeatureInfo(response.responseText, "queryOutput_contentDiv");
        }
        zoomBox.deactivate();
        featureInfo.activate();
    ';
    return $var;
}

// }}}
// {{{ changeOpenLayersLayerStatus()
        
/**
*  creates a javascript string to set the appropriate visibility for layers
*
*
* @param array $legend_array  the legend array
* @param string $map  the name of the js map object OPTIONAL (set as 'map' by default)
* @return string $var  the js code to print
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.6
*/

function changeOpenLayersLayerStatus($legend_array, $map = 'map')
{
    $var = '';
    if (!is_array($legend_array)) {
        $legend_array = unserialize($legend_array);
    }
    //loop through the current layers checking the status
    foreach ($legend_array['servers'] as $key => $value) {
        foreach ($value['layers'] as $layer_key => $layer_value) {
            if (array_key_exists('sub_layers',$layer_value)) {
                foreach ($layer_value['sub_layers'] as $sublayer_key => $sublayer_value) {
                    if (array_key_exists('status',$sublayer_value)) {
                        if ($sublayer_value['status'] == 1) {
                            $status = 'true';
                        } else {
                            $status = 'false';
                        }
                        if($sublayer_value['queryable'] == 1) {
                            $queryable = 'true';
                        } else {
                            $queryable = 'false';
                        }
                        $var .= "
                        
                            layer = map.getLayersByName(\"{$sublayer_value['title']}\");
                            for (var i=0; i < layer.length; i++) {
                                layer[i].setVisibility($status);
                                layer[i].queryable = $queryable;
                            };
                        ";
                    }
                }
                
            } else {
                if (array_key_exists('status',$layer_value)) {
                    if ($layer_value['status'] == 1) {
                        $status = 'true';
                    } else {
                        $status = 'false';
                    }
                    if ($layer_value['queryable'] == 1) {
                        $queryable = 'true';
                    } else {
                        $queryable = 'false';
                    }
                    if (array_key_exists('layer_bbox',$layer_value)) {
                        $layer_bbox = $layer_value['layer_bbox'];
                        if (!empty($layer_bbox)){
                            $var .= "
                                layer = map.getLayersByName(\"{$layer_value['title']}\");
                                for (var i=0; i < layer.length; i++) {
                                    layer[i].setVisibility($status);
                                    layer[i].queryable = $queryable;
                                    //this bounds is always in latlong
                                    var proj = new OpenLayers.Projection(\"EPSG:4326\");
                                    var layer_bounds = new OpenLayers.Bounds($layer_bbox[0],$layer_bbox[1],$layer_bbox[2],$layer_bbox[3]);
                                    layer_bounds.transform(proj, map.getProjectionObject());
                                    layer[i].maxExtent = layer_bounds;
                                };
                            ";
                        }
                    } else {
                        $var .= "
                            layer = map.getLayersByName(\"{$layer_value['title']}\");
                            for (var i=0; i < layer.length; i++) {
                                layer[i].setVisibility($status);
                                layer[i].queryable = $queryable;
                            };
                        ";
                    }
                }
            }
        }
    }
    return $var;
}

// }}}
// {{{ cleanLegendArray()
        
/**
* runs through the legend array and cleans out any layers without a status
*
*
* @param array $legend_array  the legend array
* @return array $clean_legend_array  the cleaned legend array
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.6
*/

function cleanLegendArray($legend_array)
{
    //loop the legend array and unset any layers without a status
    foreach ($legend_array['servers'] as $key => $value) {
        foreach ($value['layers'] as $layer_key => $layer_value) {
            if (array_key_exists('sub_layers',$layer_value)) {
                foreach ($layer_value['sub_layers'] as $sublayer_key => $sublayer_value) {
                    if (!array_key_exists('status',$sublayer_value)) {
                        unset($legend_array['servers'][$key]['layers'][$layer_key]['sub_layers'][$sublayer_key]);
                    }
                }
                
            } else {
                if (!array_key_exists('status',$layer_value)) {
                    unset($legend_array['servers'][$key]['layers'][$layer_key]);
                }
            }
        }
    }
    return $legend_array;
}
// }}}
// {{{ createSLDFile()

/**
* writes an SLD file that styles the query results
*
*
* @param array $wms_qlayers  the layer names (in the wms map) to query
* @param array $results_array  the results array
* @return string $file the path to the sld file
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.6
*/
//DEV NOTE: This could probably be done better using the PHP XML libraries but we need to be careful of backward compatibility
function createSLDFile($wms_qlayers, $results_array)
{
    global $export_dir, $user_id;

    // set up a file name and path
    $orig_file = tempnam($export_dir, 'xml');
    if (!file_exists($orig_file)) {
        echo "ADMIN ERROR: Unable to create file on directory: '$export_dir'<br/>";
    }
    $file = $orig_file.'.xml';

    // rename the file with the right file extension
    rename($orig_file, $file);
    if (!file_exists($file)) {
        echo "ADMIN ERROR: Unable to rename file<br/>";
    }
    $handler = fopen($file, 'w');
    $var = '
            <StyledLayerDescriptor version="1.0.0" xmlns="http://www.opengis.net/sld" xmlns:gml="http://www.opengis.net/gml" xmlns:ogc="http://www.opengis.net/ogc" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.opengis.net/sld http://schemas.opengis.net/sld/1.0.0/StyledLayerDescriptor.xsd">';

    foreach ($wms_qlayers as $layer_key => $layer) {
        
            $count = count($results_array);
            $var .= "
                    <NamedLayer>
                    <Name>$layer_key</Name>
                    <UserStyle>
                    <FeatureTypeStyle>
                    <Rule>
                    <Filter>";
                
                    if ($count > 1) {
                            $var .= "<OR>";
                    }
                    if (substr($result['itemkey'],0,3) == $wms_qlayers[$layer_key]['mod']) {
                            $var .= "
                               <PropertyIsEqualTo>
                         <PropertyName>ark_id</PropertyName>
                        <Literal>{$result['itemval']}</Literal>
                       </PropertyIsEqualTo>
                            ";
                            //flag that we have results from this itemkey
                            $flag = TRUE;
                    }
                    if (!isset($flag)) {
                                    //we have no results so set an impossible filter
                                    $var .= "
                                       <PropertyIsEqualTo>
                                 <PropertyName>ark_id</PropertyName>
                                <Literal>'NO RESULTS'</Literal>
                               </PropertyIsEqualTo>
                                    ";
                    }
                    if ($count > 1) {
                            $var .= "</OR>";
                    }
                
            $var .= "</Filter>";

                    if ($wms_qlayers[$layer_key]['geom'] == 'pgn') {
                            $var .= "
                                    <PolygonSymbolizer>
                                            <Fill>
                                                    <CssParameter name=\"fill\">#ff0000</CssParameter>
                                            </Fill>
                                            <Stroke>
                                                    <CssParameter name=\"stroke\">#000000</CssParameter>
                                            </Stroke>
                                    </PolygonSymbolizer>";
                    }
                    if ($wms_qlayers[$layer_key]['geom'] == 'pt') {
                            $var .= "
                                            <PointSymbolizer>
                                                    <Graphic>
                                                            <Mark>
                                                                    <WellKnownName>circle</WellKnownName>
                                                                    <Fill>
                                                                            <CssParameter name=\"fill\">#ff0000</CssParameter>
                                                                    </Fill>
                                                            </Mark>
                                                            <Size>10</Size>
                                                    </Graphic>
                                            </PointSymbolizer>";
                    }
                    if ($wms_qlayers[$layer_key]['geom'] == 'pl') {
                            $var .= "
                                            <LineSymbolizer>
                                                    <Stroke>
                                                            <CssParameter name=\"stroke\">#000000</CssParameter>
                                                    </Stroke>
                                            </LineSymbolizer>";
                    }

            $var .= "        
                    </Rule>
                    </FeatureTypeStyle>
                    </UserStyle>
                    </NamedLayer>
                    ";
    }
    $var .= '</StyledLayerDescriptor>';
    fwrite($handler,$var);
    fclose($handler);
    return $file;
}
// }}}
// {{{ createWFSFilterString()

/**
* writes a string that can be used to filter a WFS layer
*
*
* @param array $wfs_qlayer  the layer name (in the wfs map) to query
* @param array $results_array  the results array
* @return string $wfs_filter_string  the string to append to the URL
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @access public
* @since 0.6
*/
function createWFSFilterString($wfs_qlayer, $results_array)
{
    $have_mod_results = 0;
    $count = count($results_array);
    $var = "&FILTER=<ogc:Filter>";
    if ($count > 1) {
        $var .= "<ogc:OR>";
    }        
    foreach ($results_array as $key => $result) {
        if (substr($result['itemkey'],0,3) == substr($wfs_qlayer['mod'],0,3)) {
            $var .= "<ogc:PropertyIsEqualTo><ogc:PropertyName>ark_id</ogc:PropertyName><ogc:Literal>{$result['itemval']}</ogc:Literal></ogc:PropertyIsEqualTo>";
            $have_mod_results = 1;
        }
    }
    if ($count > 1) {
        $var .= "</ogc:OR>";
    }
    $var .= "</ogc:Filter>";
    if ($have_mod_results == 0) {
        $var = 0;
    }
return $var;
}
// }}}
// {{{ mkPlace()
        
/**
* creates an Openlayers Map that will be used to retrieve places when searched against a particular item
* this is designed to be used alongside the mkSingleMap() function
*
* @param array $placetype  an array containing the placetype or types that are to be queried against
* @return string $var  a fully resolved html string
* @access public
* @since 1.1
*/
function mkPlace($placetype)
{
    /*this function has a number of steps:
    1. grab geometry of item - and load all into OL layer
    2. grab the placetype(s) requested - set in sf_conf
    3. go to cor_lut_place and get the layernames and uris for each of the layers
    4. load these up into OL layers
    5. run the OL intersects filter on each OL layer
    6. parse the results (looking for ark_id)
    7. build the results into the form (setting aliases etc.)
    */
    
    global $lang;
    
    //STEP ONE - Grab Geometry of item
    //this function presumes that mkSingleMap() has already been run and therefore a js object called 'map' is available
    $var = "<script type=\"text/javascript\">";
    $var .= "jQuery(document).ready(function(){";
    //first get a clone of the current map object - so we don't start futzing with the single map
    //$var .= "place_map = jQuery.extend(true, {}, map);";
    //we already have our gml_layer (with our item geometry in it) within the place map
    
    //STEP TWO and THREE - grab the placetypes and get the names and uris of the layers needed to be loaded and queried
    $placetype_uris = array();
    foreach ($placetype as $key => $value) {
        $layer = getMulti(
              'cor_lut_place',
              "placetype=$value",
              'layername',
              TRUE
        );
        //now get the spatial server uris
        if (is_array($layer)) {
            foreach ($layer as $layername) {
                $placetype_uris[$layername]['uri'] = getSingle('spatial_server_uri','cor_lut_place',"layername = '$layername' AND placetype = $value");
                $placetype_uris[$layername]['alias'] = getAlias('cor_lut_placetype',$lang, 'id', $value,1);
            }
        }
    }
    //STEP FOUR, FIVE and SIX- load up the layers as Openlayers Layers. 
    //What we do here is as each new feature is added to the gml_layer (that is the geometry of the item)
    // - we run a query against the place uris - to see if we match anything
    
    foreach ($placetype_uris as $layername => $details) {
        $var .= "
        var gml_layer_loaded = false;
        places = new Array();
        //setup the layer
        var $layername = new OpenLayers.Layer.Vector(\"WFS\", {
               strategies: [new OpenLayers.Strategy.BBOX()],    
               protocol: new OpenLayers.Protocol.WFS({
                   url:  \"{$details['uri']}\",
                   featureType: \"$layername\",
                   featurePrefix: \"ms\",
                   version: \"1.1.0\",
                   srsName: \"EPSG:900913\", //DEV NOTE: THIS NEEDS TO BE SET PROGRAMATICALLY

               }),
              filter: new OpenLayers.Filter.Logical({
                  type: OpenLayers.Filter.Logical.OR,
                  filters: []
              })
           });
           map.addLayer($layername);
        //as the gml_layer is loading add new intersects to the filter array
        var refreshId = setInterval(function() {
              if (gml_layer.features.length == gml_layer_length) {
                    jQuery.each(jQuery(gml_layer.features), function(key,feature){
                        $layername.filter.filters.push(new OpenLayers.Filter.Spatial({
                            type: OpenLayers.Filter.Spatial.INTERSECTS,
                            value: feature.geometry
                        }));
                    });
                    gml_layer_loaded = true;
                    if ($layername != null) {
                        $layername.refresh({force:true});
                    }
                    gml_layer_length = 0;
                   // clearInterval(refreshId);
            }
        }, 1000);
        
        //finally let us check if we have any features that intersect. if so we need to grab them out then kill the layer
        $layername.events.register(\"featuresadded\", $layername, function() {
            if ($layername.features.length > 0 && gml_layer_loaded) {
                jQuery.each(jQuery($layername.features), function(key,feature){
                    //now we need to go and get the aliases of the places found
                    places.push(jQuery.ajax(\"api.php?request=get&dataclass=alias&alias_tbl=cor_lut_place&alias_col=id&alias_src_key=\" + feature.attributes.ark_id,{async:false}));
                    
                });
                $layername.setVisibility(false);
                jQuery('#place_ul').append('<li id=\"$layername\"class=\"row\">');
                jQuery('#$layername').append('<label class=\"form_label\">{$details['alias']}:</label>');
                jQuery.each(jQuery(places), function(key, place){
                    console.log(place);
                    jQuery('#$layername').append('<span class=\"data\">' + place.responseText + '</span>');
                });
            }
        });
        
        ";
    }
    
    $var .= "});";
    $var .= "</script>";
    echo $var;

}
// }}}
// {{{ loadWMCMap()
        
/**
* takes a WMC string and builds a map from it
*
* fills a div with the requested map
*
* @param string $wmc  the XML string of the WMC
* @param int  the id of a requested maptype if needed
* @param string $div_name  the name of the div to fill with the WMC map
* @param string $map_mode  set to 'filter' to run as a filter map or 'single' to run as a single map
* @param array $manual_gml  an array containing manual GML to put onto the map OPTIONAL
* @return string $var  a fully resolved html string
* @access public
* @since 0.8
*/
function loadWMCMap($wmc, $maptype = FALSE, $div_name = "map", $map_mode = FALSE, $manual_gml = FALSE)
{
    global $openlayers_path, $db,$browser, $skin,$wxs_query_buffer,$proxy_host;
    
    if ($map_mode == 'filter') {
        $filter_map = TRUE;
        $single_map = FALSE;
        $manual_map = FALSE;
    } elseif ($map_mode == 'single') {
        $single_map = TRUE;
        $filter_map = FALSE;
        $manual_map = FALSE;
    } elseif ($map_mode == 'manual') {
        $single_map = FALSE;
        $filter_map = FALSE;
        $manual_map = TRUE;
    } else {
        $single_map = FALSE;
        $filter_map = FALSE;
        $manual_map = FALSE;
    }
    
    if ($maptype) {
        $result = getMulti('cor_tbl_wmc',"id = $maptype");
    } else {
        $result = FALSE;
    }
    if ($filter_map) {
        global $results_array;
    }
    if ($single_map) {
         global $sf_key,$sf_val,$sf_conf,$map_timeout;
        $mod_short = substr($sf_key, 0, 3);
    }
    if ($manual_map) {
         //check if the manual gml is present and correct
         global $sf_conf;
         if (!is_array($manual_gml)) {
            echo "ADMIN ERROR: You have requested a map with manual GML but have sent an empty array";
         }
    }
    if (is_array($result)) {
        if (array_key_exists('scales',$result[0])) {
               $scales = $result[0]['scales'];
               $_SESSION['current_scales'] = $scales;
           } else {
               $scales = "100000,50000,25000,10000,7500,5000,2500,1000,500,50";
               $_SESSION['current_scales'] = $scales;
           }
           if (array_key_exists('extents',$result[0])) {
               $extents = $result[0]['extents'];
           }
           if (array_key_exists('projection',$result[0])) {
               $projection = $result[0]['projection'];
               $_SESSION['current_projection'] = $projection;
           }
           if (array_key_exists('zoom',$result[0])) {
               $zoom = $result[0]['zoom'];
               $_SESSION['current_zoom'] = $zoom;
           }
           if (array_key_exists('legend_array',$result[0])) {
               $legend_array = $result[0]['legend_array'];
               $_SESSION['legend_array'] = $legend_array;
           }    
           if (array_key_exists('OSM',$result[0])) {
               $openstreetmap = $result[0]['OSM'];
               $_SESSION['openstreetmap'] = $openstreetmap;
           }
           if (array_key_exists('gmap_api_key',$result[0])) {
               $gmap_api_key = $result[0]['gmap_api_key'];
               $_SESSION['gmap_api_key'] = $gmap_api_key;
           }
    }
    
    if(!isset($openstreetmap)){
        $openstreetmap = FALSE;
    }
    if(!isset($gmap_api_key)){
        $gmap_api_key = FALSE;
    }
    
    if ($openstreetmap OR $gmap_api_key) {
        
        if ($single_map) {
            if (array_key_exists('op_view_as_map_icon',$sf_conf) && $sf_conf['op_view_as_map_icon'] == TRUE) {
                $view_as_map = "<a class=\"sf_spat_map\" title=\"View as Map\" href=\"data_view.php?ftr_mode=standard&reset=1&results_mode=disp&disp_mode=map&ftype=manual&ftr_id=new&key=$mod_short&val_list=$sf_val\"><img  class=\"sf_spat_map\" src=\"skins/$skin/images/results/map.png\" /></a>";
            } else {
                $view_as_map = '';
            }
            $head =  "
                    <div>
                    <div id=\"$div_name\" class=\"smallmap\">
                    <img class=\"northarrow\" src=\"skins/$skin/images/legend/northarrow.png\" />
                    $view_as_map
                    </div>
                    <div id=\"wmc_code\"></div>
                    <script src=\"$openlayers_path\"></script>
            ";
        } elseif ($manual_map) {
             $head =  "
                        <div>
                        <div id=\"$div_name\" class=\"exifmap\">
                        <img class=\"northarrow\" src=\"skins/$skin/images/legend/northarrow.png\" />
                        </div>
                        <div id=\"wmc_code\"></div>
                        <script src=\"$openlayers_path\"></script>
                ";
        } else {
             $head =  "
                      <div>
                         <div id=\"$div_name\" class=\"mapview\">
                             <div id=\"controlPanel\"></div>
                             <div id=\"controlOverlay\"><div class=\"olControlScaleLine olControlNoSelect\" id=\"scaleLine\"></div> <div id=\"mapOutput\" class=\"message\">&nbsp;</div><div id=\"scaleText\"></div><div id=\"mousePosition\"></div></div>
                         </div>
                         <div id=\"wmc_code\"></div>
                         <script src=\"$openlayers_path\"></script>
             ";
        }
         
        if ($openstreetmap) {
                $head .= '<!-- bring in the OpenStreetMap OpenLayers layers.
                    Using this hosted file will make sure we are kept up
                     to date with any necessary changes -->
                    <script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
                ';
            // we can only have EITHER google maps or openstreetmap - therefore prefer the opensource and cancel google
            if (isset($gmap_api_key)) {
                $gmap_api_key = FALSE;
            }
        }
        if (isset($gmap_api_key)) {
            $head .= "<script src='http://maps.google.com/maps/api/js?v=3.2&sensor=false'></script>";
        }
        
        if ($projection == 'EPSG:27700') {
            $head .= '<script src="lib/js/OpenLayers.Projection.OrdnanceSurvey.js"></script>';
        }

        $head .= '<script type="text/javascript">';

        $foot = "
                    </script>
                </div>";

    } else {
        
        if ($single_map) {
            $head =  "
                    <div>
                    <div id=\"$div_name\" class=\"smallmap\">
                    <img class=\"northarrow\" src=\"skins/$skin/images/legend/northarrow.png\" />
                    <a class=\"sf_spat_map\" title=\"View as Map\" href=\"data_view.php?ftr_mode=standard&reset=1&results_mode=disp&disp_mode=map&ftype=manual&ftr_id=new&key=$mod_short&val_list=$sf_val&set_operator=intersect\"><img  class=\"sf_spat_map\" src=\"skins/$skin/images/results/map.png\" /></a>
                    </div>
                    <div id=\"wmc_code\"></div>
                    <script src=\"$openlayers_path\"></script>
            ";
        } elseif ($manual_map) {
            $head =  "
                    <div>
                    <div id=\"$div_name\" class=\"exifmap\">
                    <img class=\"northarrow\" src=\"skins/$skin/images/legend/northarrow.png\" />
                    </div>
                    <div id=\"wmc_code\"></div>
                    <script src=\"$openlayers_path\"></script>
            ";
        } else {
            $head =  "
                    <div>
                    <div id=\"controlPanel\"></div>
                    <div id=\"$div_name\" class=\"mapview\">
                        <div id=\"controlOverlay\"><div class=\"olControlScaleLine olControlNoSelect\" id=\"scaleLine\"></div><div id=\"mapOutput\" class=\"message\"></div><div id=\"scaleText\"></div><div id=\"mousePosition\"></div></div>
                    </div>
                    <div id=\"wmc_code\"></div>
                    <script src=\"$openlayers_path\"></script>
            ";
        }
       
        $head .= "  <script type=\"text/javascript\">";

        $foot = "
                    </script>
                </div>";
    }

    $var = $head;
    
    //make the map div half size if we are filtering - to make room for the chat results
    if ($filter_map) {
        $var .= "changeDivSize('468px','400px','map');
                 document.getElementById('controlPanel').className = 'filtermap';
                 document.getElementById('controlOverlay').className = 'filtermap';
                 document.getElementById('scaleText').className = 'filtermap';
                 document.getElementById('mousePosition').className = 'filtermap';
                 document.getElementById('mapOutput').className = 'filtermap';
                 document.getElementById('mapOutput').innerHTML = '&nbsp;';

        ";
    }
    
    $var .= '        
        var map;
        var format = new OpenLayers.Format.WMC({\'layerOptions\': {buffer: 0}});
    ';
    
    if ($proxy_host) {
        $var .= "OpenLayers.ProxyHost = \"/cgi-bin/proxy.cgi?url=\";";
    }
    
    if ($maptype) {
        $var .= 'function init(){
                 var scales = ['. $scales . '];
        ';
        if ($openstreetmap OR $gmap_api_key) {
             //we need to convert the map_extents if necessary
                if ($projection == 'EPSG:27700') {
                    $var .= 'max_bounds = new OpenLayers.Bounds.fromString("'.$extents.'");';

                } else {
                    $var .= 'max_bounds = new OpenLayers.Bounds(-20037508.34,-20037508.34,20037508.34,20037508.34);';
                }

                $var .= 'map = new OpenLayers.Map ("map", {
                                maxExtent: max_bounds,
                                scales: [' . $scales . '],
                                numZoomLevels: 25,
                                units: "m",
                                projection: new OpenLayers.Projection("'.$projection.'"),
                                displayProjection: new OpenLayers.Projection("'.$projection.'"),
                                controls:[]
                                });
                }';
        } else {
             $var .=     
                    'max_bounds = new OpenLayers.Bounds.fromString("'.$extents.'");
                     map = new OpenLayers.Map("map", {
                        projection: new OpenLayers.Projection("'.$projection.'"),
                        maxExtent: max_bounds,
                        scales: [' . $scales . '],
                        units:"m",
                        controls:[]
                    });
                }
            ';
        }
    }
    
    //set up the IE6 fix if necessary
    if ($browser == 'OLD_MSIE') {
        $ie6_fix = "
            layers = map.layers;
            for (i in layers) {
                if (typeof layers[i].params == 'object') {
                    //layers[i].params.FORMAT = 'image/gif';
                    layers[i].alpha = true;
                }
            };
        ";
    } else {
         $ie6_fix = "";
    }
    
    if (!$single_map && !$manual_map) {
    
        $layerstatus = changeOpenLayersLayerStatus($legend_array);
        $querycontrol = buildOpenLayersToolbar($legend_array);
        $var .='

             // toggle the queryEventHandler active state
             function toggleQueryMode()
             { 
        ';
        if (!$filter_map) {
            $var .= '
                if(featureInfo.active) {
                    queryEventHandler.activate();
                } else {
                    queryEventHandler.deactivate();
                }
            ';
        }  
        $var .='
                  
             }
             function calcVincenty(geometry) {
                 /**
                  * Note: this function assumes geographic coordinates and
                  *     will fail otherwise.  OpenLayers.Util.distVincenty takes
                  *     two objects representing points with geographic coordinates
                  *     and returns the geodesic distance between them (shortest
                  *     distance between the two points on an ellipsoid) in *kilometers*.
                  *
                  * It is important to realize that the segments drawn on the map
                  *     are *not* geodesics (or "great circle" segments).  This means
                  *     that in general, the measure returned by this function
                  *     will not represent the length of segments drawn on the map.
                  */
                 var dist = 0;
                 for (var i = 1; i < geometry.components.length; i++) {
                     var first = geometry.components[i-1];
                     var second = geometry.components[i];
                     dist += OpenLayers.Util.distVincenty(
                         {lon: first.x, lat: first.y},
                         {lon: second.x, lat: second.y}
                     );
                 }
                 return dist;
             }    

             function handleMeasurements(event) {
                 var geometry = event.geometry;
                 var units = event.units;
                 var order = event.order;
                 var measure = event.measure;
                 var element = document.getElementById(\'mapOutput\');
                 var out = "";
                 if(order == 1) {
                     out += "Distance: " + measure.toFixed(3) + " " + units;
                     if (map.getProjection() == "EPSG:4326") {
                         out += ", Great Circle Distance: " + 
                             calcVincenty(geometry).toFixed(3) + " km"; 
                     }        
                 } else {
                     out += "<span class=\'mapAreaOutput\'>Area: " + measure.toFixed(3) + " " + units + "<sup style=\'font-size:6px\'>2</" + "sup></span>";
                 }
                 element.innerHTML = out;
             }
             function showInfo(evt)
              {
                  if (evt.features && evt.features.length) {
                       highlightLayer.destroyFeatures();
                       highlightLayer.addFeatures(evt.features);
                       highlightLayer.redraw();
                  } else {
                      document.getElementById("query_results").innerHTML = evt.text;
                  }

              }
        ';
    }
    $var .= '
        function saveWMC(merge) {
            try {
                var text = format.write(map);
                document.getElementById("wmc_code").value = text;
            } catch(err) {
                document.getElementById("wmc_code").innerHTML = err;
            }
        }
        
        function readWMC(merge) {
                    if(merge) {
                            map = format.read(merge, {map: map});
        ' . $ie6_fix;
        
        if (isset($layerstatus) && isset($querycontrol)) {
            $var .= $layerstatus . $querycontrol;
        }
        
        $var .= '
                        map.zoomToExtent(max_bounds);
                    } else {
                        try {
                            map = format.read("' . $wmc . '",{map:"' . $div_name . '"});
                        } catch(err) {
                            document.getElementById("wmc_code").innerHTML = err;
                        }
                    }
                }
                
    ';
    
    if ($filter_map) {
        $var .= mkFilterMap($results_array,$wxs_query_buffer);
    }
    if ($single_map) {
        if (!$map_timeout) {
            $map_timeout = 1500;
        }
        $var .= mkSingleMap($sf_key, $sf_val, $sf_conf);
    }
    if ($manual_map) {
        $var .= mkManualMap($manual_gml,$sf_conf);
    }
    
    if ($maptype) {
        $var .= "init(); \n";
        if ($openstreetmap) {

            $var .= 'OSM = new OpenLayers.Layer.OSM("OSM");
                 map.addLayer(OSM);
            ';
        }

        if ($gmap_api_key) {
            $var .= 'gphy = new OpenLayers.Layer.Google("Google Physical", {type: google.maps.MapTypeId.TERRAIN,sphericalMercator: true});
                     map.addLayer(gphy);
            ';
            $var .= 'gmap = new OpenLayers.Layer.Google("Google Streets", {sphericalMercator: true});
                     map.addLayer(gmap);
            ';
            $var .= 'ghyb = new OpenLayers.Layer.Google("Google Hybrid", {type: google.maps.MapTypeId.HYBRID,sphericalMercator: true});
                     map.addLayer(ghyb);
            ';
            $var .= 'gsat = new OpenLayers.Layer.Google("Google Satellite", {type: google.maps.MapTypeId.SATELLITE,sphericalMercator: true});
                     map.addLayer(gsat);
                     
                     //the physical map runs out at a certain zoom level

                     map.events.on({ "zoomend": function (e) {
                         if (this.getZoom() > 15) {
                             map.setBaseLayer(gmap);
                         } else {
                           map.setBaseLayer(gphy);
                         }
                       }
                     });
            ';
        }
        $var .= "readWMC(\"$wmc\");\n";
        if ($filter_map || $manual_map) {
            $var .= "complete();";
        } elseif ($single_map){
            $var .= "
                complete();
                setTimeout('no_data()',$map_timeout);
                function no_data () {
                    if (data_present == null) {
                        map_div = document.getElementById(\"$div_name\");
                        map_div.innerHTML = \"<p>No spatial data available\";
                        map_div.setAttribute('style','height: 30px; background: none');
                    }
                }
            ";
        } else {
            $var .= "zoom_extents = new OpenLayers.Bounds.fromString('".$extents."');";
            $var .= "map.zoomToExtent(zoom_extents);\n";
        }
    } else {
        $var .= "readWMC();\n";
    }
    
    $var .= "saveWMC();\n";
    $var .= $foot; 
    return ($var);
}
// }}}
// {{{ mkFilterMap()
        
/**
* creates a piece of xhtml that is usually included in loadWMC() to show a filterset on a map
*
* generates a stand-alone piece of html that creates Openlayers map zoomed and filtered to the 
* the results array. It uses the OGC filter functionality to filter a pre-defined WMS map.
*
* @param array $results_array  the results array
* @param int $buffer  an optional buffer value (in map units) to buffer the results
* @param boolean $transclude  value representing if the function is being called as part of a transclusion routine
* @param mixed $filter  optional filter name or id - if this is being transcluded then the filter needs to be built from scratch
* @return string $var  a fully resolved html string
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @access public
* @since 0.8
*/
function mkFilterMap($results_array, $buffer = FALSE, $transclude = FALSE, $filter = FALSE)
{
    global $wxs_qlayers, $lang;
    //request some values from the session (usually saved during the loadWMC() routine)
    $openstreetmap = reqQst($_SESSION,'openstreetmap');
    $gmap_api_key = reqQst($_SESSION,'gmap_api_key');
    $projection = reqQst($_SESSION,'current_projection');
    $stylemap_var = '';
    
    $var = '';
    
    //before we do anything if filter exists grab the filter and then run it
    
    if ($filter){
    
        //RUN THE FILTER HERE AND GET THE RESULTS ARRAY
        echo "ADMIN ERROR: Separate filter functionality isn't completed yet.";
    
    }
    
    //now create the SLD file for the query layers
    if (!empty($results_array)) {
            $active_itemkeys = array();
            $new_wfs_qlayers = array();
            $total_results = count($results_array);
            $layer_num = 1 + round($total_results/10);
            foreach ($results_array as $key => $value) {
                    if (!in_array($value['itemkey'],$active_itemkeys)) {
                            $active_itemkeys[] = $value['itemkey'];
                    }
            }
            if (!empty($active_itemkeys)) {
                foreach ($active_itemkeys as $key => $itemkey) {
                    foreach ($wxs_qlayers as $key => $wfs_qlayer) {
                        if (substr($itemkey,0,3) == substr($wfs_qlayer['mod'],0,3)) {
                            $new_wfs_qlayers[$key] = $wfs_qlayer;
                        }
                    }
                }
            }
            if (!empty($new_wfs_qlayers)) {
                    $wfs_qlayers = $new_wfs_qlayers;
            }
           // $sld_path = createSLDFile($wfs_qlayers,$results_array);
    } else {
            $sld_path = 'mapserver/templates/ark-query-sld.xml';
    }
        
    if ($layer_num > 0){
        
        $var .= '
                    var sld, symbolizer, layer_number, layer_name;
                    var styleMap = new OpenLayers.StyleMap();
                    var gml_layer = new OpenLayers.Layer.Vector("gml_layer",{styleMap:styleMap});
                    var bbox = null;
                    var paramArray = new Array();
        ';
                    
        $var .= "function parseData(req) {

                    g =  new OpenLayers.Format.GML();
                    response = req.responseText;
                    response = response.split('&amp;TYPENAME=');
                    if (response[1] != undefined){
                        response = response[1].split('&amp;');
                        layer_name = response[0];
                    } else {
                        response = req.responseText;
                        response = response.split('&#39;');
                        layer_name = response[1];
                       // alert ('ADMIN ERROR: it seems that the layer ' + layer_name + ' specified in wxs_query_layers does not exist on the server - please check that you have valid layer names set for the array wxs_query_layers in settings.php');
                    }
        ";
                                            
        $var .= "                
                    features = g.read(req.responseText);
                    if (features.length > 0){

                        for (var i = 0; i < features.length; i++) {
                            var geometry = features[i].geometry;";
                                                  
        //if we are using OSM we need to project the coordinates
        if ($openstreetmap || $gmap_api_key && $projection != 'EPSG:900913') {
            $var .="        geometry.transform(new OpenLayers.Projection(\"$projection\"), new  OpenLayers.Projection(\"EPSG:900913\"));";
        }
        $var .= "
                            features[i].attributes['layer'] = layer_name;
                            gml_layer.addFeatures(features[i]);  

                            if (bbox == null) {
                                bbox = geometry.getBounds().clone();
                            } else {
                                bbox.extend(geometry.getBounds());
                            }
                        }
                    if(bbox){";
                    
        if ($buffer) {
            $var .=     "buffer_bounds = bbox.toArray();
                         buffer_bounds[0] = buffer_bounds[0] - $buffer;
                         buffer_bounds[1] = buffer_bounds[1] - $buffer;
                         buffer_bounds[2] = buffer_bounds[2] + $buffer;
                         buffer_bounds[3] = buffer_bounds[3] + $buffer;
                         bbox = new OpenLayers.Bounds.fromArray(buffer_bounds);
            ";
                        }

            $var .=     "
                    }
                    map.zoomToExtent(bbox);
                }
            }

            ";

            $var.=  '
                            
                    function onPopupClose(evt) {
                        selectControl.unselect(selectedFeature);
                    }
                    function onClickedPopupClose(evt) {
                        gml_layer_click.unselect(clickedFeature);
                    }
                    function onFeatureSelect(feature) {
                       selectedFeature = feature;
                       popup = new OpenLayers.Popup.FramedCloud("selected", 
                                                        feature.geometry.getBounds().getCenterLonLat(),
                                                        null,
                                                        feature.attributes["ark_id"],
                                                        null, false, onPopupClose); 
                       feature.popup = popup;
                       map.addPopup(popup);
                    }
                    function onFeatureUnselect(feature) {
                        map.removePopup(feature.popup);
                                       feature.popup.destroy();
                                       feature.popup = null;
                    }
                            
                    function makePopUpText (transport) {
                        map.popups[window["paramArray"]["popup_num"]].setContentHTML(transport.responseText);
                    }
                            
                    function onFeatureClick(feature) {
                                      
                        click_popup = new OpenLayers.Popup.FramedCloud(feature.attributes["ark_id"], 
                            feature.geometry.getBounds().getCenterLonLat(),
                            null,
                            "loading...",
                            null, true, null
                        );                            
                        map.addPopup(click_popup);

                        for (var i = 0; i < map.popups.length; i++) {
                            if (map.popups[i]["id"] == feature.attributes["ark_id"]){
                                window["paramArray"]["popup_num"] = i;
                                if (feature.attributes["layer"] == undefined) {
                                    feature.attributes["layer"] = feature.gml.featureType.toLowerCase();
                                }
                                new OpenLayers.Ajax.Request(
                                 "php/map/query_wrapper.php?layer=" + feature.attributes["layer"] + "&lang='. $lang .'&ark_id=" + map.popups[i]["id"],
                                {onComplete: makePopUpText}
                                );
                            }
                        }


                                       
                    }
                    function onFeatureUnClick(feature) {
                        map.removePopup(feature.click_popup);
                        feature.click_popup.destroy();
                        feature.click_popup = null;
                    }

                    var click_options = {
                        onSelect: onFeatureClick, 
                        callbacks:{over:onFeatureSelect,out:onFeatureUnselect}

                    };
            ';
            
            $var .= "
                    
                    function complete() {
                        gml_layer_click = new OpenLayers.Control.SelectFeature(gml_layer, click_options);
                        map.addControl(gml_layer_click);
                        gml_layer_click.activate();
                        map.addLayer(gml_layer);
                        
            ";
            
            //now we setup the queryable WFS layer
            if ($gmap_api_key || $openstreetmap) {
                $wfs_projection = "EPSG:900913";
            } else {
                $wfs_projection = $projection;
            }
            foreach ($wxs_qlayers as $layer => $layer_array) {
                $result_array_copy = $results_array;
                $chunk_results = array_chunk ($result_array_copy,10);
                $layer_number = 1;
                foreach ($chunk_results as $value) {
                    $filter = createWFSFilterString($wxs_qlayers[$layer], $value);
                    if ($filter) {
                        $layer_name = $layer;
                        $var .= "layer_name = '{$layer_name}'; layer_number = '{$layer_number}';";
                        $var .= "OpenLayers.loadURL(\"{$wxs_qlayers[$layer_name]['url']}&layer_number=$layer_number&VERSION=1.0.0&SERVICE=WFS&SRSNAME=$wfs_projection&REQUEST=GetFeature&TYPENAME={$layer_name}$filter\", \"\", null, parseData);\n";
                        if (array_key_exists('style_array', $layer_array)) {
                            $stylemap_var .= "\"$layer_name\": {";
                            foreach ($layer_array['style_array'] as $style_key => $style_val) {
                                $stylemap_var .= "$style_key: $style_val,";
                            }
                            $stylemap_var = rtrim($stylemap_var,',');
                            $stylemap_var .= "},";
                        }
                        $layer_number++;
                    }
                }
                if (isset($stylemap_var)) {
                    $stylemap_var = rtrim($stylemap_var,',');
                    $var .= "var stylemaprules = { $stylemap_var };
                            styleMap.addUniqueValueRules('default', 'layer', stylemaprules);
                    ";
                }

            }        
            $var .= "
            }";
    } else {
            $var = "<div id=\"message\">Sorry, none of these results have queryable spatial data</div>";
    }
    
    return ($var);

}
// }}}
// {{{ mkGMLfile()

/**
* writes a GML file, from two number fields
*
*
* @param string $itemkey  the itemkey for the current module
* @param string $file_location  the location to save the GML file
* @param array $geo_fields  an array containing the number fields for the geometry
* @return string $projection  string containing the EPSG code of the projection (e.g. "EPSG:27700")
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.7
*/
function mkGMLfile($itemkey,$file_location,$geo_fields,$projection)
{

    $file = $file_location;
    $handler = fopen($file, 'w+');
    
    //setup the header
    
    $var = '<wfs:FeatureCollection 
            xmlns:ms="http://mapserver.gis.umn.edu/mapserver"
            xmlns:wfs="http://www.opengis.net/wfs"
            xmlns:gml="http://www.opengis.net/gml"
            xmlns:ogc="http://www.opengis.net/ogc"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.opengis.net/wfs http://schemas.opengis.net/wfs/1.0.0/WFS-basic.xsd 
                           http://mapserver.gis.umn.edu/mapserver">';
                           
    //now we need to grab all of the items for the mod
    
    $mod = splitItemKey($itemkey);
    $gml_name = $mod . '_pt';
    $items = getAllItems($mod);
    
    foreach ($items as $key => $value) {    
        
        foreach ($geo_fields AS $field) {
               //attempt to get 'current'
               $type_no = getSingle('id', 'cor_lut_numbertype', "numbertype = '{$field['classtype']}'");
               if ($current =
                       getRow(
                           'cor_tbl_number',
                           FALSE,
                           "WHERE itemkey = '$itemkey' AND itemvalue = '{$value[$itemkey]}' AND numbertype = $type_no"
                   )) {
                   $field['current'] =
                       array(
                           'id' => $current['id'],
                           'number' => $current['number']
                   );
               } else {
                   $field['current'] = FALSE;
               }
               
               if ($field['current']) {
                   $value['coords'][] = $field['current']['number'];
               }
         }
         if (!empty($value['coords'])) {

             $var .= "<gml:featureMember> 
                        <ms:$gml_name fid=\"{$value[$itemkey]}\">
                            <ms:msGeometry> 
                                <gml:Point srsName=\"$projection\"> 
                                <gml:coordinates>{$value['coords'][0]},{$value['coords'][1]}</gml:coordinates> 
                                </gml:Point> 
                            </ms:msGeometry> 
                        <ms:ark_id>{$value[$itemkey]}</ms:ark_id>
                        </ms:$gml_name> 
                    </gml:featureMember>
            ";
         }
    }
    
    $var .= "</wfs:FeatureCollection>";
    fwrite($handler,$var);
    fclose($handler);
}
// }}}
// {{{ mkManualMap()
        
/**
* creates a piece of xhtml that is usually included in a subform via loadWMC() to show the geometry of an array of manually passed GML
*
* generates a stand-alone piece of html that creates Openlayers map zoomed and filtered to the 
* the manually passed items. It uses the OGC filter functionality to filter a pre-defined WMS map.
*
* @param array $manual_gml  an array containing x,y,rot - DEV NOTE: Currently this function only works with LatLong point data
* @param array $sf_conf  the subform conf
* @return string $var  a fully resolved html string
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @access public
* @since 1.1
*/
function mkManualMap($manual_gml)
{
    global $lang, $skin_path;
    
    //request variables (that are usually set during the parent loadWMC() routine)
    $openstreetmap = reqQst($_SESSION,'openstreetmap');
    $gmap_api_key = reqQst($_SESSION,'gmap_api_key');
    $projection = reqQst($_SESSION,'current_projection');
    $stylemap_var = '';
    $buffer = 100;

    $manual_gml = json_encode($manual_gml);
    
    $var = "
    
        manual_gml = $manual_gml;
        var bbox;
        vectors = new OpenLayers.Layer.Vector(
            \"Simple Geometry\",
            {
                styleMap: new OpenLayers.StyleMap({
                    \"default\": {
                        externalGraphic: \"$skin_path/images/openlayers_tools/photo_arrow.png\",
    ";
    
    $var .= '
                        //graphicWidth: 17,
                        graphicHeight: 20,
                        graphicYOffset: -19,
                        rotation: "${angle}",
                        fillOpacity: "${opacity}"
                    }
                })
            }
        );
    ';
    
    $var .= "
    
     function complete() {
            map.addLayer(vectors);
    
            var features = [];
            jQuery.each(manual_gml, function(){
                x = this.x;
                y = this.y;
                if (this.rot != null) {
                    rot = this.rot;
                } else {
                    rot = 0;
                }
                var proj = new OpenLayers.Projection('EPSG:4326');
                var point = new OpenLayers.Geometry.Point(x, y);
                point.transform(proj, map.getProjectionObject());
                if (bbox == null) {
                    bbox = point.getBounds().clone();
                } else {
                    bbox.extend(point.getBounds());
                }
                features.push(
                    new OpenLayers.Feature.Vector(
                        point, {angle: this.rot}
                    )
                );
            });
            vectors.addFeatures(features);
            if(bbox){"
        ;

        if ($buffer) {
            $var .=     "buffer_bounds = bbox.toArray();
                         buffer_bounds[0] = buffer_bounds[0] - $buffer;
                         buffer_bounds[1] = buffer_bounds[1] - $buffer;
                         buffer_bounds[2] = buffer_bounds[2] + $buffer;
                         buffer_bounds[3] = buffer_bounds[3] + $buffer;
                         bbox = new OpenLayers.Bounds.fromArray(buffer_bounds);
            ";
        }
        $var .=     "
            }
            map.zoomToExtent(bbox);
        }
    ";
    
    return ($var);
}
// }}}
// {{{ mkSingleMap()
        
/**
* creates a piece of xhtml that is usually included in a subform via loadWMC() to show the geometry of a single item
*
* generates a stand-alone piece of html that creates Openlayers map zoomed and filtered to the 
* the single item. It uses the OGC filter functionality to filter a pre-defined WMS map.
*
* @param array $item_key  the itemkey to zoom to
* @param array $item_val  the itemvalue to zoom to
* @param array $sf_conf  the properly formed sf_conf
* @return string $var  a fully resolved html string
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @access public
* @since 0.8
*/
function mkSingleMap($item_key, $item_val, $sf_conf)
{
    global $lang;
    
    //request variables (that are usually set during the parent loadWMC() routine)
    $openstreetmap = reqQst($_SESSION,'openstreetmap');
    $gmap_api_key = reqQst($_SESSION,'gmap_api_key');
    $projection = reqQst($_SESSION,'current_projection');
    $stylemap_var = '';
    
    $var = '';
    $mod = substr($item_key,0,3);
    if (array_key_exists('op_buffer',$sf_conf)) {
        $buffer = $sf_conf['op_buffer'];
    }
    
    if (array_key_exists('query_layers', $sf_conf) && count($sf_conf['query_layers']) > 0 ) {
        $layer_num = count($sf_conf['query_layers']);
    }
    
    if (isset($layer_num) && $layer_num > 0){
        
        $var .= '
                    var sld, symbolizer, layer_number, layer_name, data_present;
                    var styleMap = new OpenLayers.StyleMap();
                    var gml_layer = new OpenLayers.Layer.Vector("gml_layer",{styleMap:styleMap});
                    var bbox = null;
                    var paramArray = new Array();
                    var gml_layer_length = 0;
        ';
                    
        $var .= "function parseData(req) {

                    g =  new OpenLayers.Format.GML();
                    response = req.responseText;
                    response = response.split('&amp;TYPENAME=');
                    if (response[1] != undefined){
                        response = response[1].split('&amp;');
                        layer_name = response[0];
                    } else {
                        response = req.responseText;
                        response = response.split('&#39;');
                        if (response[0].substring(0,23) == '<ServiceExceptionReport') {
                            
                        } else {
                            layer_name = response[1];
                           // alert ('ADMIN ERROR: it seems that the layer ' + layer_name + ' specified in the sf_conf does not exist on the server - please check that you have valid layer names set in the sf_conf in your mod_settings.php file');
                        }
                    }
                    
        ";
                                            
        $var .= "                
                    features = g.read(req.responseText);
                    if (features.length > 0){
                        data_present = true;
                        gml_layer_length = gml_layer_length + features.length;
                        for (var i = 0; i < features.length; i++) {
                            var geometry = features[i].geometry;";
                                                  
        //if we are using OSM we need to project the coordinates
        if ($openstreetmap || $gmap_api_key && $projection != 'EPSG:900913') {
            $var .="        geometry.transform(new OpenLayers.Projection(\"$projection\"), new  OpenLayers.Projection(\"EPSG:900913\"));";
        }
        $var .= "
                            features[i].attributes['layer'] = layer_name;";

        $var .= "
                            gml_layer.addFeatures(features[i]);  

                            if (bbox == null) {
                                bbox = geometry.getBounds().clone();
                            } else {
                                bbox.extend(geometry.getBounds());
                            }
                        }
                    if(bbox){";
                    
        if (isset($buffer)) {
            $var .=     "buffer_bounds = bbox.toArray();
                         buffer_bounds[0] = buffer_bounds[0] - $buffer;
                         buffer_bounds[1] = buffer_bounds[1] - $buffer;
                         buffer_bounds[2] = buffer_bounds[2] + $buffer;
                         buffer_bounds[3] = buffer_bounds[3] + $buffer;
                         bbox = new OpenLayers.Bounds.fromArray(buffer_bounds);
            ";
        }

            $var .=     "
                    }
                    map.zoomToExtent(bbox);
                }
            }

            ";
          
            $var .= "
                    
                    function complete() {
                        var scalebar = new OpenLayers.Control.ScaleLine();
                        map.addControl(scalebar);
                        map.addLayer(gml_layer);
            ";
            //now we setup the WFS layer
            foreach ($sf_conf['query_layers'] as $layer_key => $layer) {
                    if ($gmap_api_key || $openstreetmap) {
                        $wfs_projection = "EPSG:900913";
                    } else {
                        $wfs_projection = $projection;
                    }
                    $layer_name = $layer_key;
                    $var .= "layer_name = '{$layer_name}';";
                    $filter = "&FILTER=<ogc:Filter><ogc:PropertyIsEqualTo><ogc:PropertyName>ark_id</ogc:PropertyName>";
                    $filter .= "<ogc:Literal>$item_val</ogc:Literal></ogc:PropertyIsEqualTo></ogc:Filter>\"";
                    $url = $sf_conf['query_layers'][$layer_name]['url'];
                    $var .= "OpenLayers.loadURL(\"{$sf_conf['query_layers'][$layer_name]['url']}VERSION=1.0.0&SRSNAME=$wfs_projection&SERVICE=WFS&REQUEST=GetFeature&TYPENAME={$layer_name}$filter, \"\", null, parseData);\n";
                    if (array_key_exists('style_array', $layer)) {
                        $stylemap_var .= "\"$layer_name\": {";
                        foreach ($layer['style_array'] as $style_key => $style_val) {
                            $stylemap_var .= "$style_key: $style_val,";
                        }
                        $stylemap_var = rtrim($stylemap_var,',');
                        $stylemap_var .= "},";
                    }
            }
            if ($stylemap_var) {
                $stylemap_var = rtrim($stylemap_var,',');
                $var .= "var stylemaprules = { $stylemap_var };
                        styleMap.addUniqueValueRules('default', 'layer', stylemaprules);
                ";
            }
        } else {
            echo "ADMIN ERROR: sf_conf error - you need to add some query_layers in mod_settings";
        }      
    $var .= "
    }";
    
    return ($var);
}
// }}}
// {{{ mkStyleObject()
        
/**
* creates a piece of javascript that can be used to style an openlayers vector layer
*
* generates a stand-alone piece of js
*
* @param array $style_array  the various style elements
* @return string $var  a fully resolved js string
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @access public
* @since 0.9
*/
function mkStyleObject($style_array)
{
    $var = "{";
    foreach ($style_array as $key => $value) {
        $var .= $key . ":" . $value;
    }
    $var .= '}';
    return $var;
}
// }}}
// {{{ parseArkIDs()      
/**
* takes the GML from a getFeatureInfo response and returns any ark_ids within it
*
*
* @param string $gml  the gml response from a GetFeatureInfo request
* @param string $full_id  look for a fully formed ark_id - if false it will return any numeric if - DEFAULT = TRUE
* @param string $att_name  the name of the field within the layer to return DEFAULT = 'ARK_ID'
* @return array $ark_ids  an array of unique ark_ids found within the gml
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 1.1
*/
function parseArkIDs($gml,$full_id = TRUE, $att_name = "ARK_ID")
{
    $ark_ids = array();
    //sometimes we have trouble with the case of the att names - so check for this
    if (!strpos($gml, strtoupper($att_name))) {
        $att_name = strtolower($att_name);
    } else {
        $att_name = strtoupper($att_name);
    }
    $ids = explode("$att_name>",$gml);
    foreach ($ids as $ark_id) {
        $ark_id_explode = explode('<',$ark_id);
        foreach ($ark_id_explode as $exploded_ark_id) {
            if (!$full_id) {
                if (is_numeric($exploded_ark_id)) {
                    $ark_ids[] = $exploded_ark_id;
                }
            } else {
                //check if we have a valid(ish) item value
                $poss_itemval = explode('_',$exploded_ark_id);
                if (count($poss_itemval) == 2 && is_numeric($poss_itemval[1])) {
                     if (!in_array($ark_ids,$poss_itemval)) {
                        $ark_ids[] = $exploded_ark_id;
                     }
                }
            }
        }
    }
    return ($ark_ids);
}
// }}}
// {{{ parseGetCap()
        
/**
* parses a url of a GetCapabilities request and returns a fully formed-legend array
*
*
* @param string $url  the url to query
* @param string $extra_params  any extra parameters that need passing to the form OPTIONAL
* @param string $admin  if you want to print the admin legend
* @return string $var  a fully resolved html string
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.6
*/

function parseGetCap($url,$extra_params=FALSE,$admin = FALSE)
{
    global $lang;
    $process = TRUE;
    $legend_array = reqArkVar('legend_array');
    if (!is_array($legend_array)) {
        $legend_array = unserialize($legend_array);
    }
    $err_markup = getMarkup('cor_tbl_markup', $lang, 'getcap_err');
    
    //we need to be careful with timeouts here, so insert a handler
    //create the stream context to enable timeout
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 10      // Timeout in seconds
        )
    ));
    $err_markup .= "<br> URL: ".$url;
    $data = @file_get_contents($url, 0, $context);
    try{
        if(!$xml=@simplexml_load_string($data)){
            throw new Exception($err_markup);
        }    
    }
    catch(Exception $e){
        echo $e->getMessage();
        exit();
    }
    //check if we have the url in the array already
    if (!empty($legend_array)) {
        if (is_array($legend_array['servers'])) {
            foreach ($legend_array['servers'] as $key => $value) {
                if ($value['url'] == $url) {
                    $process = FALSE;
                    if ($admin) {
                        $legend = buildLegendAdmin($legend_array,$extra_params);
                    } else {
                        $legend = buildLegend($legend_array,$extra_params);
                    }
                    $var = $legend;
                }
            }
        }
    }
    //now we have our XML as a simplexml object, we want to go through and find all the available layers
    //loop through all the nodes and find any that are layers
    //first the WMS responses
    if (array_key_exists('Capability', $xml) && $process != FALSE) {
        $legend_array['servers'][]['url'] = $url;
        $url_key = end(array_keys($legend_array['servers']));
        $url_title = (string) $xml->Service->Title;
        $legend_array['servers'][$url_key]['title'] = $url_title;
        foreach ($xml->Capability->children() as $child) {
            if (array_key_exists('Layer', $child)) {
                //add all the layers to a multidim array , so that we can then build a legend up
                  foreach ($child->Layer as $layer) {
                      if (array_key_exists('Layer', $layer)) {
                          //looks like we have a group
                          $layer_name = (string) $layer->Name;
                          $layer_title = (string) $layer->Title;
                          $legend_array['servers'][$url_key]['layers'][]['name'] = $layer_name;
                          $layer_key= end(array_keys($legend_array['servers'][$url_key]['layers']));
                          $legend_array['servers'][$url_key]['layers'][$layer_key]['title'] = $layer_title;
                      
                          foreach ($layer as $sub_layer) {
                              $title = (string) $sub_layer->Title;
                              $name = (string) $sub_layer->Name;
                              $queryable = (int) $sub_layer['queryable'];
                              $layer_bbox = array();
                              foreach($sub_layer->LatLonBoundingBox->attributes() as $a => $b) {
                                  $layer_bbox[] = (float) $b;
                              }
                              if ($title && $name) {
                                  //now try and get the SLD for this layer
                                  $sld_array = parseSLD($url, $name);
                                  $legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers'][]['name'] = $name;
                                  $sublayer_key = end(array_keys($legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers']));
                                  $legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers'][$sublayer_key]['title'] = $title;
                                  $legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers'][$sublayer_key]['queryable'] = $queryable;
                                  $legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers'][$sublayer_key]['layer_bbox'] = $layer_bbox;
                                  if (!empty($sld_array)) {
                                      $legend_array['servers'][$url_key]['layers'][$layer_key]['sub_layers'][$sublayer_key]['sld_array'] = $sld_array;                               
                                  }

                              }
                            }
                      } else {
                          //no group just add the layer in
                          $url_key = end(array_keys($legend_array['servers']));
                          $title = (string) $layer->Title;
                          $name = (string) $layer->Name;
                          $layer_bbox = array();
                          foreach($layer->LatLonBoundingBox->attributes() as $a => $b) {
                              $layer_bbox[] = (float) $b;
                          }
                          $queryable = (int) $layer['queryable'];
                          //now try and get the SLD for this layer
                          $sld_array = parseSLD($url, $name);
                          $legend_array['servers'][$url_key]['layers'][]['name'] = $name;
                          $layer_key= end(array_keys($legend_array['servers'][$url_key]['layers']));
                          $legend_array['servers'][$url_key]['layers'][$layer_key]['title'] = $title;
                          $legend_array['servers'][$url_key]['layers'][$layer_key]['queryable'] = $queryable;
                          $legend_array['servers'][$url_key]['layers'][$layer_key]['layer_bbox'] = $layer_bbox;
                          if (!empty($sld_array)) {
                              $legend_array['servers'][$url_key]['layers'][$layer_key]['sld_array'] = $sld_array;
                          }
                      }
                      if ($admin) {
                          $legend = buildLegendAdmin($legend_array,$extra_params);
                      } else {
                          $legend = buildLegend($legend_array,$extra_params);
                      }
                      $var = $legend;
                      $_SESSION['legend_array'] = $legend_array;
                  }
            } else {
                $err_nolayers = getMarkup('cor_tbl_markup', $lang, 'err_nolayers');
            }
        }
    }
    return $var;
}
// }}}
// {{{ parseGetFeatureInfo()      
/**
* takes the GML from a getFeatureInfo response and produces nice HTML
*
* 
*
* @param string $gml  the gml response from a GetFeatureInfo request
* @return string $var  the clean result
* @return array $geometry  an array of the geometries
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.8
*/
function parseGetFeatureInfo($gml)
{
    global $wxs_qlayers,$conf_micro_viewer,$lang;
    $dom = new DOMDocument();
    $mk_no_records = getMarkup('cor_tbl_markup', $lang, 'no_records');
    $var = '';
    $features_var = '';
    $link_to_record = '';
    $final_feature_var = '';

    //check if magic qoutes are on - if so we need to strip the slashes of the GML
    if(get_magic_quotes_gpc()) {
        $gml = stripslashes($gml);
    }
    
    try{
        if(!$dom->loadXML($gml)){
            throw new Exception($err_markup);
        }    
    }
    catch(Exception $e){
        echo $e->getMessage();
        //exit();
    }
    $array_dom = dom_to_array($dom);
    //this is a rather complex set of foreaches - basically we are looping through the WFSGetFeatureInfo 
    //request (which has been turned into an array) and grabbing out the relevant bit
    foreach ($array_dom as $key => $value) {
        if ($key != '#text') {
            $var .= '<ul class="qlist">';
            if (is_array($value)) {
                foreach ($value as $layer_key => $layer_value) {
                    if ($layer_key != '#text') {
                        $layername = rtrim($layer_key,'_layer');
                        //check if this is a layer that holds an ark_id
                        if (is_array($wxs_qlayers)) {
                           if (array_key_exists($layername,$wxs_qlayers)) {
                               $build_link = TRUE;
                               if (array_key_exists('title_field',$wxs_qlayers[$layername])) {
                                   if ($wxs_qlayers[$layername]['title_field'] != 'GIS') {
                                       //get field for this and return the element(s)
                                      global $$wxs_qlayers[$layername]['title_field'];
                                      $title_field = $$wxs_qlayers[$layername]['title_field'];
                                   }
                               } else {
                                   $title_field = '';
                               }
                           } else {
                               $build_link = FALSE;
                           }
                        }
                        $var .= "<li class=\"qlayer\">$layername";
                        $var .= "<ul>";
                        if (is_array($layer_value)) {
                            foreach ($layer_value as $feature_key => $feature_value) {
                                //check if we have multiple results
                                if (is_array($feature_value) && array_key_exists(0,$feature_value) && is_array($feature_value[0])) {
                                    $feature_array = $feature_value;
                                    foreach ($feature_array as $feature_key => $feature_value) {
                                        foreach ($feature_value as $feature_elem_key => $feature_elem_value) {
                                            if ($feature_elem_key != 'gml:boundedBy' && $feature_elem_key != '#text') {
                                                if ($build_link && $feature_elem_key == 'ark_id') {
                                                    $item_key = $wxs_qlayers[$layername]['mod'] . "_cd";
                                                    if ($title_field) {
                                                        $title_val = resTblTd($title_field,$item_key,$feature_elem_value);
                                                        $link_to_record = "<a href=\"$conf_micro_viewer?item_key=$item_key&amp;$item_key=$feature_elem_value\" target=\"_blank\"> $title_val</a>";
                                                    } else {
                                                        $link_to_record = "<a href=\"$conf_micro_viewer?item_key=$item_key&amp;$item_key=$feature_elem_value\" target=\"_blank\">Link to $feature_elem_value</a>";
                                                    }
                                                }
                                                if (!is_array($feature_elem_value) && $title_field == 'GIS') {
                                                    $features_var .= "<li class=\"qelement\"><span class=\"qlabel\">$feature_elem_key:</span> $feature_elem_value</li>";
                                                }
                                            }
                                        }
                                        $final_feature_var .= $link_to_record . $features_var;
                                        $features_var = '';
                                        $link_to_record = '';
                                    }
                                } else {
                                    if ($feature_key != 'gml:boundedBy' && $feature_key != '#text') {
                                        if (is_array($feature_value)) {
                                            foreach ($feature_value as $feature_elem_key => $feature_elem_value) {
                                                if ($feature_elem_key != 'gml:boundedBy' && $feature_elem_key != '#text') {
                                                    if ($build_link && $feature_elem_key == 'ark_id') {
                                                        $item_key = $wxs_qlayers[$layername]['mod'] . "_cd";
                                                        if ($title_field) {
                                                             $title_val = resTblTd($title_field,$item_key,$feature_elem_value);
                                                             $link_to_record = "<a href=\"$conf_micro_viewer?item_key=$item_key&amp;$item_key=$feature_elem_value\" target=\"_blank\"> $title_val</a>";
                                                        } else {
                                                            $link_to_record = "<a href=\"$conf_micro_viewer?item_key=$item_key&amp;$item_key=$feature_elem_value\" target=\"_blank\">Link to $feature_elem_value</a>"; 
                                                        }
                                                    }
                                                    $features_var .= "<li class=\"qelement\"><span class=\"qlabel\">$feature_elem_key:</span> $feature_elem_value</li>";
                                                }
                                             }
                                        }
                                        $final_feature_var .= $link_to_record . $features_var;
                                        $features_var = '';
                                        $link_to_record = '';
                                    }
                                }
                            }
                        }
                  //$var .= $link_to_record;
                        $var .= $final_feature_var;
                        $var.= "</ul>";
                        $var .= "</li>";
                    }

                }
            } else {
                echo $mk_no_records;
            }
            $var .= '</ul>';
        }
    }
    print $var;
    unset($dom);
}
// }}}
// {{{ parseGeometry()      
/**
* takes the GML from a getFeatureInfo response and returns the geometry
*
* 
*
* @param string $gml  the gml response from a GetFeatureInfo request
* @param string $namespace  some mapserver have specialist geom namespaces - provide this here. DEFAULT = 'ms:msGeometry'
* @return string $var  the clean result
* @return string $geometry  a single clean string of the GML representation of the geometry
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 1.1
*/
function parseGeometry($gml,$namespace = "ms:msGeometry")
{
    $geometry = explode("<$namespace>",$gml);
    if (is_array($geometry) && array_key_exists(1,$geometry)) {
        $geometry = explode("</$namespace>",$geometry[1]);
    }
    if (is_array($geometry)) {
        $geometry = $geometry[0];
        //clean up the geometry
        $geometry = ltrim($geometry);
        $geometry = rtrim($geometry);
        $geometry = preg_replace( '/\s+/', ' ', $geometry);
        $geometry = str_replace( '> <', '><', $geometry);
        $geometry = preg_replace( '/\s+/', '+', $geometry);
    } 
    return ($geometry);
}
// }}}
// {{{ parseSLD()      
/**
* takes a sld (Styled Layer Description) simplexml document and parses it into a useful array
*
* normally used for building a dynamic legend
*
* @param object $url  the url of the WxS server
* @param string $name  the name of the layer to retrieve the SLD for
* @return array $sld_array  an array of SLD rules
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.8
*/
function parseSLD($url,$name)
{
    $sld_url = explode('?',$url);
    $sld_url = $sld_url[0] . "?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetStyles&LAYERS=$name";
    $sld_data = @file_get_contents($sld_url, "r");
    $dom = new DOMDocument();
    
    try{
        if(!$dom->loadXML($sld_data)){
            throw new Exception($err_markup);
        }
    }
    catch(Exception $e){
        echo $e->getMessage();
        //exit();
    }
    $sld_array = array();
    $array_dom = dom_to_array($dom);
    
    if (array_key_exists('StyledLayerDescriptor', $array_dom) && array_key_exists('NamedLayer',$array_dom['StyledLayerDescriptor'])) {
        $version = $array_dom['StyledLayerDescriptor']['version'];
    } else {
        $version = FALSE;
    }
    if ($version == "1.1.0"){
        $feature_type_style = $array_dom['StyledLayerDescriptor']['NamedLayer']['UserStyle']['se:FeatureTypeStyle'];
        if (!array_key_exists('#text',$feature_type_style['se:Rule'])){
            $feature_type_style = $feature_type_style['se:Rule'];
        }
        foreach ($feature_type_style as $rule) {
            if (array_key_exists('se:Name',$rule)) {
                $sld_array[]['name'] = (string) $rule['se:Name'];
            }
            $rule_key = end(array_keys($sld_array));
            if (is_array ($rule) && array_key_exists('se:PolygonSymbolizer',$rule)) {
                $fill = (string) $rule['se:PolygonSymbolizer']['se:Fill']['se:SvgParameter'][0]['_value'];
                $opacity = (string) $rule['se:PolygonSymbolizer']['se:Fill']['se:SvgParameter'][1]['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['opacity'] = $opacity;
                $sld_array[$rule_key]['type'] = 2;
            } elseif (is_array ($rule) && array_key_exists('se:LineSymbolizer',$rule)) {
                $fill =  (string) $rule['se:LineSymbolizer']['se:Stroke']['se:SvgParameter'][0]['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['type'] = 1;
            } elseif (is_array ($rule) && array_key_exists('se:PointSymbolizer',$rule)) {
                $fill = (string) $rule['se:PointSymbolizer']['se:Graphic']['se:Mark']['se:Fill']['se:SvgParameter']['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['type'] = 0;
            }
        }
    } elseif ($version == "1.0.0") {
        foreach ($array_dom['StyledLayerDescriptor']['NamedLayer']['UserStyle']['FeatureTypeStyle']['Rule'] as $rule) {
            $sld_array[]['name'] = (string) $rule['Name'];
            $rule_key = end(array_keys($sld_array));
            if (is_array ($rule) && array_key_exists('PolygonSymbolizer',$rule)) {
                $fill = (string) $rule['PolygonSymbolizer']['Fill']['CssParameter'][0]['_value'];
                $opacity = (string) $rule['PolygonSymbolizer']['Fill']['CssParameter'][1]['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['type'] = 2;
                if ($opacity) {
                    $sld_array[$rule_key]['opacity'] = $opacity;
                } else {
                    $sld_array[$rule_key]['opacity'] = '1.0';
                }
            } elseif ($rule->Rule->LineSymbolizer) {
                $fill =  (string) $rule['LineSymbolizer']['Stroke']['CssParameter'][0]['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['type'] = 1;
            } elseif ($rule->PointSymbolizer) {
                $fill =  (string) $rule['PointSymbolizer']['Graphic']['Mark']['Fill']['CssParameter']['_value'];
                $sld_array[$rule_key]['colour'] = $fill;
                $sld_array[$rule_key]['type'] = 0;
            }
        }
    }

    //tidy it a bit if necess
    if (array_key_exists(0,$sld_array)) {
        if ($sld_array[0]['name'] == '') {
            unset($sld_array[0]);
        }
    }
    unset($dom);
    return ($sld_array);
}
// }}}
// {{{ parseWMCMap()
        
/**
* takes a string of WMC and builds a JS enabled legend from it
*
* generates a stand-alone html <object> that contains a legend
*
* @param string $wmc_code  a fully complete WMC document
* @param string $result_element  the id of an element in which to place the legend
* @return string $var  a fully resolved html string
* @access public
* @author Stuart Eve <stuarteve@lparchaeology.com>
* @since 0.8
*/
function parseWMCMap($wmc_code,$result_element)
{
    global $lang;
    //grab the legend array
    $wmc_code = stripslashes($wmc_code);
    $err_markup = getMarkup('cor_tbl_markup', $lang, 'wmc_err');
    $legend_array = $_SESSION['legend_array'];
    $xml=simplexml_load_string($wmc_code);
    $new_legend_array = array();
    
    foreach ($xml->LayerList->children() as $child) {
       $attributes = $child->Server->OnlineResource->attributes('http://www.w3.org/1999/xlink');
       $server_attr = $child->Server->attributes();
       $href = $attributes['href'];
       $service = explode(':',$server_attr['service']);
       $service = $service[1];
       $version = $server_attr['version'];
       $href = "$href?SERVICE=$service&VERSION=$version&REQUEST=GetCapabilities";
   }
   print(buildLegend($legend_array));
}
// }}}
// {{{ rgb2hex2rgb()
/*
* this function taken from http://php.net/manual/en/function.hexdec.php
*/
function rgb2hex2rgb($c){
   if(!$c) return false;
   $c = trim($c);
   $out = false;
   if(eregi("^[0-9ABCDEFabcdef\#]+$", $c)){
      $c = str_replace('#','', $c);
      $l = strlen($c) == 3 ? 1 : (strlen($c) == 6 ? 2 : false);

      if($l){
         unset($out);
         $out[0] = $out['r'] = $out['red'] = hexdec(substr($c, 0,1*$l));
         $out[1] = $out['g'] = $out['green'] = hexdec(substr($c, 1*$l,1*$l));
         $out[2] = $out['b'] = $out['blue'] = hexdec(substr($c, 2*$l,1*$l));
      }else $out = false;
              
   }elseif (eregi("^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$", $c)){
      $spr = str_replace(array(',',' ','.'), ':', $c);
      $e = explode(":",$c);     
      if(count($e) != 3) return false;
         $out = '#';
         for($i = 0; $i<3; $i++)
            $e[$i] = dechex(($e[$i] <= 0)?0:(($e[$i] >= 255)?255:$e[$i]));
              
         for($i = 0; $i<3; $i++)
            $out .= ((strlen($e[$i]) < 2)?'0':'').$e[$i];
                  
         $out = strtoupper($out);
   }else $out = false;
          
   return $out;
}
// }}}
//{{{ json_decode 
    ///PHP 5 function ported to PHP4
/*- taken from http://php.net/manual/en/function.json-decode.php - www at walidator dot info
*/
if ( !function_exists('json_decode') ){
    function json_decode($content, $assoc=false){
                require_once 'Services/JSON.php';
                if ( $assoc ){
                    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
                    $json = new Services_JSON;
                }
        return $json->decode($content);
    }
}

if ( !function_exists('json_encode') ){
    function json_encode($content){
                require_once 'Services/JSON.php';
                $json = new Services_JSON;
               
        return $json->encode($content);
    }
}
?>