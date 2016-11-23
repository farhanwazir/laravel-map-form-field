# Laravel Map Form Field

###USAGE
```php
        Form::map('mappy', null, [
            'height' => '350px',
            'onStaged' => 'onMapInitialized',
            'position' => $position,
            'async' => true, /* for ajax use only, when use async then it needs to call loadScript_map() manually 
                                after response print in screen. */
            'onMarkerPositionChanged' => 'onMarkerPositionChanged',
        ], [
            'latitude' => ['name' => 'latitude', 'class' => 'map-place-search-controls map-place-search map-latlng-controls'],
            'longitude' => ['name' => 'longitude', 'class' => 'map-place-search-controls map-place-search map-latlng-controls'],
            'search' => ['class' => 'map-place-search-controls map-place-search'],
        ]);
```

More details will update later, i'm very busy in my projects. Hope fully after a week, you will see complete, please click on watch button.
