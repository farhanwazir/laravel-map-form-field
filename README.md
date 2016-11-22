# Laravel Map Form Field

###USAGE
```php
        Form::map('mappy', null, [
            'height' => '350px',
            'onStaged' => 'onMapInitialized',
            'position' => $position,
            'onMarkerPositionChanged' => 'onMarkerPositionChanged',
        ], [
            'latitude' => ['name' => 'latitude', 'class' => 'map-place-search-controls map-place-search map-latlng-controls'],
            'longitude' => ['name' => 'longitude', 'class' => 'map-place-search-controls map-place-search map-latlng-controls'],
            'search' => ['class' => 'map-place-search-controls map-place-search'],
        ]);
```
