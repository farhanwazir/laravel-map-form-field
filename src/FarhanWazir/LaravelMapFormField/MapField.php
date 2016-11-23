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
        'latlng' => true,
        'height' => '100%',
        'position' => null,
        'zoom' => 14,
        'async' => false,
        'onLoad' => '',
        'onStaged' => '',
        'onMarkerPositionChanged' => '',
    ];

    //TODO: Merge user settings with default
    $settings = is_array($settings) ? array_merge($default_settings, $settings) : $default_settings;

    /* Filter position */
    $position = is_array($settings['position']) ? $settings['position'] : explode(',', $settings['position']);
    $latlng = $settings['position'] ? $position : null;
    $field_lat_val = null;
    $field_lng_val = null;
    if(count($position) == 2){
        $position_keys = array_keys($position);
        $field_lat_val = $position[$position_keys[0]];
        $field_lng_val = $position[$position_keys[1]];
    }

    $field_name_search = 'fw_map_form_field_place_search';
    $field_name_lat = 'fw_map_form_field_lat';
    $field_name_lng = 'fw_map_form_field_lng';

    $field_class_search = (is_array($attributes) && array_key_exists('search', $attributes) && array_key_exists('class', $attributes['search']))?
        $attributes['search']['class'] : 'form-control';
    $field_class_lat = (is_array($attributes) && array_key_exists('latitude', $attributes) && array_key_exists('class', $attributes['latitude']))?
        $attributes['latitude']['class'] : 'form-control';
    $field_class_lng = (is_array($attributes) && array_key_exists('longitude', $attributes) && array_key_exists('class', $attributes['longitude']))?
        $attributes['longitude']['class'] : 'form-control';

    if(is_array($attributes)){
        if(array_key_exists('latitude', $attributes) && array_key_exists('name', $attributes['latitude']))
            $field_name_lat = $attributes['latitude']['name'];
        if(array_key_exists('longitude', $attributes) && array_key_exists('name', $attributes['longitude']))
            $field_name_lng = $attributes['longitude']['name'];
    }

    //Map facade, see detail at https://github.com/farhanwazir/laravelgooglemaps
    $GMaps = app()->make('GMaps');

    $fields = '';
    if($settings['search']){
        $fields .= Form::text($field_name_search, null, ['placeholder' => 'Search place', 'class' => $field_class_search, 'id' => 'fw-map-form-field-place-search']);
        $GMaps->injectControlsInTopCenter = ['document.getElementById("fw-map-form-field-place-search")'];
    }

    if($settings['latlng']){
        $fields .= Form::text($field_name_lat, $field_lat_val, ['placeholder' => 'Latitude', 'class' => $field_class_lat, 'id' => 'fw_map_form_field_lat']);
        $fields .= Form::text($field_name_lng, $field_lng_val, ['placeholder' => 'Longitude', 'class' => $field_class_lng, 'id' => 'fw_map_form_field_lng']);
        $GMaps->injectControlsInBottomCenter = ['document.getElementById("fw_map_form_field_lat")', 'document.getElementById("fw_map_form_field_lng")'];
    }

    $marker = array();
    $marker['draggable'] = true;
    $marker['onpositionchanged'] = $settings['onMarkerPositionChanged'];
    $marker['ondragend'] = '
        /*iw_'. $GMaps->getMapName() .'.close();*/
        iw_'. $GMaps->getMapName() .'.setContent("Loading... Please wair or if you seeing this since long then move marker or map.");
        reverseGeocode(event.latLng, function(status, result, mark){
            if(status == 200){
                iw_'. $GMaps->getMapName() .'.setContent(result);
                iw_'. $GMaps->getMapName() .'.open('. $GMaps->getMapName() .', marker_0);
            }else{
                iw_'. $GMaps->getMapName() .'.close();
            }
        }, this);
        '. $GMaps->getMapName() .'.setCenter(event.latLng);
        ';

    if($settings['latlng'])
        $marker['ondragend'] .= $GMaps->injectControlsInBottomCenter[0] .'.value = (typeof event.latLng == "object")?event.latLng.lat():"";
        '. $GMaps->injectControlsInBottomCenter[1] .'.value = (typeof event.latLng == "object")?event.latLng.lng():"";';

    $config = array();
    $config['loadAsynchronously'] = $settings['async'];
    $config['map_height'] = $settings['height'];
    $config['zoom'] = $settings['zoom'];
    $config['center'] = $settings['position'] ? (is_array($settings['position']) ? implode(',', $settings['position']) : $settings['position']) : 'auto';
    $config['onload'] = $settings['onLoad'];
    $config['onstaged'] = $settings['onStaged'];
    $config['onboundschanged'] = '
        var mapCentre = '. $GMaps->getMapName() .'.getCenter();
        marker_0.setOptions({
            position: mapCentre
        });
        centerGot = true;
        event = {latLng : mapCentre};
        '.$marker['ondragend'];
    $config['places'] = $settings['search'];
    $config['placesAutocompleteInputID'] = 'fw-map-form-field-place-search';
    $config['placesAutocompleteOnChange'] = '
    marker_0.setPosition(place.geometry.location);
     '.$marker['ondragend'].' ';
    $config['palcesAutoCompleteOnChangeFailed'] = '
        alert("No place found.");
    ';

    $GMaps->initialize($config);

    $GMaps->add_marker($marker);

    $map = $GMaps->create_map();
    return $fields . $map['js'] . $map['html'];
});
