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
        'search' => false,
        'latlng' => false,
        /*'search-align' => 'top_center',
        'latLng-align' => 'bottom_center'*/
    ];

    //TODO: Merge user settings with default
    $settings = is_array($settings) ? array_merge($default_settings, $settings) : $default_settings;

    $field_name_search = 'fw_map_form_field_place_search';
    $field_name_lat = 'fw_map_form_field_lat';
    $field_name_lng = 'fw_map_form_field_lng';

    $field_class_search = (is_array($attributes) && array_key_exists('search', $attributes) && array_key_exists('class', $attributes['search']))?
        $attributes['search']['class'] : '';
    $field_class_lat = (is_array($attributes) && array_key_exists('latitude', $attributes) && array_key_exists('class', $attributes['latitude']))?
        $attributes['latitude']['class'] : '';
    $field_class_lng = (is_array($attributes) && array_key_exists('longitude', $attributes) && array_key_exists('class', $attributes['longitude']))?
        $attributes['longitude']['class'] : '';

    if(is_array($attributes)){
        if(array_key_exists('latitude', $attributes) && array_key_exists('name', $attributes['latitude']))
            $field_name_lat = $attributes['latitude']['name'];
        if(array_key_exists('longitude', $attributes) && array_key_exists('name', $attributes['longitude']))
            $field_name_lng = $attributes['longitude']['name'];
    }

    $fields = '';
    if($settings['search'])
        $fields .= Form::text($field_name_search, null, ['placeholder' => 'Search place', 'id' => 'fw-map-form-field-place-search', 'class' => $field_class_search]);
    if($settings['latlng']){
        $fields .= Form::text($field_name_lat, null, ['placeholder' => 'Latitude', 'id' => 'fw-map-form-field-lat', 'class' => $field_class_lat]);
        $fields .= Form::text($field_name_lng, null, ['placeholder' => 'Longitude', 'id' => 'fw-map-form-field-lng', 'class' => $field_class_lng]);
    }else{
        $fields .= '<input type="hidden" id="fw-map-form-field-lat"><input type="hidden" id="fw-map-form-field-lng">';
    }

    //Map facade, see detail at https://github.com/farhanwazir/laravelgooglemaps
    $GMaps = app()->make('GMaps');
    $GMaps->injectControlsInTopCenter = ['document.getElementById("fw-map-form-field-place-search")'];
    $GMaps->injectControlsInBottomCenter = ['document.getElementById("fw-map-form-field-lat")', 'document.getElementById("fw-map-form-field-lng")'];

    $marker = array();
    $marker['draggable'] = true;
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
        '. $GMaps->injectControlsInBottomCenter[0] .'.value = event.latLng.lat();
        '. $GMaps->injectControlsInBottomCenter[1] .'.value = event.latLng.lng();
        '. $GMaps->getMapName() .'.setCenter(event.latLng);
        ';

    $config = array();
    $config['map_height'] = "100%";
    $config['center'] = 'auto';
    $config['onboundschanged'] = 'var centreGot = false; if (!centreGot) {
            var mapCentre = '. $GMaps->getMapName() .'.getCenter();
            marker_0.setOptions({
                position: new google.maps.LatLng(mapCentre.lat(), mapCentre.lng())
            });
        }
        centreGot = true;
        event = {latLng: mapCentre};
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

