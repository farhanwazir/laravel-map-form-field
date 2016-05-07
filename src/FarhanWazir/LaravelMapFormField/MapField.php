<?php
/*
    Author: Farhan Wazir
    Email: seejee1@gmail.com
    License: MIT
    Dependency: laravelcollective/html and farhanwazir/laravelgooglemaps
    Code Ref: https://laravelcollective.com/docs/5.0/html#custom-macros
*/

Form::macro('map', function($name, $value = null, $settings = false, $attributes = false){
    //Default settings
    $default_settings = [
        'search' => true,
        'latLng' => true,
        'search-align' => 'top_center',
        'latLng-align' => 'bottom_center'
    ];

    //TODO: Merge user settings with default
    $settings = $default_settings;

    //search field
    Form::text('fw_map_form_field_place_search', null, ['placeholder' => 'Search place', 'id' => 'fw-map-form-field-place-search']);
    Form::text('fw_map_form_field_lat', null, ['placeholder' => 'Latitude', 'id' => 'fw-map-form-field-lat']);
    Form::text('fw_map_form_field_lng', null, ['placeholder' => 'Longitude', 'id' => 'fw-map-form-field-lng']);

    //Map facade, see detail at https://github.com/farhanwazir/laravelgooglemaps
    $GMaps = app()->make('GMaps');
    $GMaps->injectControlsInTopCenter = ['document.getElementById("fw-map-form-field-place-search")'];
    $GMaps->injectControlsInBottomCenter = ['document.getElementById("fw-map-form-field-lat")', 'document.getElementById("fw-map-form-field-lng")'];

    $config = array();
    $config['map_height'] = "100%";
    $config['center'] = 'Clifton, Karachi';
    $config['onboundschanged'] = 'if (!centreGot) {
            var mapCentre = map.getCenter();
            marker_0.setOptions({
                position: new google.maps.LatLng(mapCentre.lat(), mapCentre.lng())
            });
        }
        centreGot = true;';
    $GMaps->initialize($config);

    $marker = array();
    $marker['draggable'] = true;
    $marker['ondragend'] = '
        iw_'. GMaps::map_name .'.close();
        reverseGeocode(event.latLng, function(status, result, mark){
            if(status == 200){
                iw_'. GMaps::map_name .'.setContent(result);
                iw_'. GMaps::map_name .'.open('. GMaps::map_name .', mark);
            }
        }, this);
        ';
    $GMaps->add_marker($marker);

    $map = $GMaps->create_map();
    return $map['js'] . $map['html'];
});

