Yii2-locator
------------
#### Leaflet-wrapper for Yii2 PHP framework ####

[![Latest Stable Version](https://poser.pugx.org/sjaakp/yii2-locator/v/stable)](https://packagist.org/packages/sjaakp/yii2-locator)
[![Total Downloads](https://poser.pugx.org/sjaakp/yii2-locator/downloads)](https://packagist.org/packages/sjaakp/yii2-locator)
[![License](https://poser.pugx.org/sjaakp/yii2-locator/license)](https://packagist.org/packages/sjaakp/yii2-locator)

This is a wrapper of the [Leaflet](https://leafletjs.com/) JavaScript
geomapping library for the
[Yii 2.0](https://yiiframework.com/ "Yii") PHP Framework. It's an Yii2 
[Widget](https://www.yiiframework.com/doc/api/2.0/yii-base-widget) that can be used to display
geographical data stored in an [ActiveRecord](https://www.yiiframework.com/doc/api/2.0/yii-db-activerecord),
as well as to update it. **Yii2-locator** optionally has a search facility. It can use several
providers, for the map tiles as well as for the geocoding service.

A demonstration of **yii2-locator** is [here](https://sjaakpriester.nl/software/locator).

## Installation ##

The preferred way to install **yii2-locator** is through [Composer](https://getcomposer.org/). 
Either add the following to the require section of your `composer.json` file:

`"sjaakp/yii2-locator": "*"` 

Or run:

`composer require sjaakp/yii2-locator "*"` 

You can manually install **yii2-locator** by
 [downloading the source in ZIP-format](https://github.com/sjaakp/yii2-locator/archive/master.zip).

### GeoJSON ###

**Yii2-locator** handles data in [GeoJSON format](https://geojson.org/). Some databases
store these directly. Others, like MySQL and MariaDB, use their own format for spatial data.
My [Yii2-spatial](https://github.com/sjaakp/yii2-spatial) extension can be used to
transform [MySQL format](https://dev.mysql.com/doc/refman/5.5/en/spatial-datatypes.html) to
GeoJSON and vice versa. In that case, the model should be extended from `sjaakp\spatial\ActiveRecord`
in stead of the usual `yii\db\ActiveRecord`.

## Usage ##

A typical usage scenario is like this: suppose we have a database table with some geographical data,
let's say the table `tower`. If we use MySQL or MariaDB, the model `Tower` is extended like this:

    class Tower extends sjaakp\spatial\ActiveRecord    {
        public static function tableName()
        {
            return 'tower';
        }
        // ...
    }    


The table `tower` has, among others, the following fields:

- `location`: `POINT` the location of the tower,
- `mapcenter`: `POINT` the center of the map,
- `mapzoom`: `int` the zoom level of the map.

#### View ####

In a yii2 *view*, displaying a map of a tower is simple as this:

    <?php
    use sjaakp\locator\Locator;
    /**
     * @var app\models\Tower $model
     */
    ?>
    ...
    <?php
        $map = Locator::begin([
            'height' => 480,
            // ... other options ...
        ]);
        
        $map->modelCenter($model, 'mapcenter'); // set the map's center
        
        $map->modelZoom($model, 'mapzoom'); // set the map's zoom level

        $map->modelFeature($model, 'location'); // place a marker at the tower's location

        Locator::end();
    ?>
    ...

#### Index ####

Displaying a map with all the towers in, say, the *index* view, can be accomplished with:

    <?php
    use sjaakp\locator\Locator;
    /**
     * @var yii\data\ActiveDataProvider $dataProvider
     */
    ?>
    ...
    <?php 
        $map = Locator::begin([
            'leafletOptions' => [
                'center' =>  [48.8, 2.3],   // Paris
                'zoom' => 5,
                // ... more options ...
            ],
        ]);

        $map->modelFeatures($dataProvider, 'location'); // provide the tower locations

        Locator::end();
    ?>
    ...

#### Active Locator ####

In a *create* or *update* view, **Locator** can be used in a form:

    <?php
    use yii\widgets\ActiveForm;
    use sjaakp\locator\Locator;
    /**
     * @var app\models\Tower $model
     */
    ?>
    ...
    <?php $form = ActiveForm::begin(); ?>
    ...
    <?php
        $map = Locator::begin([
            // ... Locator options ...
        ]);
        
        $map->activeCenter($model, 'mapcenter'); // allow the map's center to be chenged

        $map->activeZoom($model, 'mapzoom'); // allow the map's zoom level to be changed

        $map->activeMarker($model, 'location'); // allow the model's location to be changed

        $map->finder(); // add an interactive Search control to the map

        Locator::end();
    ?>
    ...
    <?php ActiveForm::end(); ?>
    ...

## Methods ##

- **tileLayer(*$data*)** - Add a tile to the map. `$data: string|array`: tile provider name,
 or name with options. See [Tile names](#tile-names). Return: `$this`.
- **center(*$lat, $lng* = null)** - Set the center of the map. `$lat` and `$lng` 
 are the latitude and longitude, `float`. `$lat` can also be an array `[<lat>, <lng>]`.
 Return: `$this`.
- **modelCenter(*$model, $attribute*)** - Set the center of the map to the value of
 `$attribute` in `$model`. This should be a 
 [GeoJSON Feature](https://geojson.org/geojson-spec.html#feature-objects).
 Return: `$this`.
- **activeCenter(*$model, $attribute*)** - Create an [ActiveField](https://www.yiiframework.com/doc/api/2.0/yii-widgets-activefield)
  for the center of the map, coupled to the value (a GeoJSON Feature) of
 `$attribute` in `$model`. Return: `$this`.
- **zoom(*$z*)** - Set the zoom level of the map. `$z`: `integer`. Return: `$this`.
- **modelZoom(*$model, $attribute*)** - Set the zoom level of the map to the value of
 `$attribute` in `$model`. Return: `$this`.
- **activeZoom(*$model, $attribute*)** - Create an ActiveField for the zoom level of the map,
 coupled to the value of `$attribute` in `$model`. Return: `$this`.
- **feature(*$feature*)** - Add a [GeoJSON Feature](https://geojson.org/geojson-spec.html#feature-objects)
 to the map. Return: `$this`.
- **modelFeature(*$model, $attribute*)** - Add the value of
 `$attribute` in `$model` as a GeoJSON Feature to the map. Return: `$this`.
- **modelFeatures(*$dataProvider, $attribute*)** - Add multiple GeoJSON features to the map,
 provided by [ActiveDataProvider](https://www.yiiframework.com/doc/api/2.0/yii-data-activedataprovider)
  `$dataProvider`, using attribute `$attribute`. Return: `$this`.
- **marker(*$lat* = null, *$lng* = null, *$options* = [ ])** - Add marker to the map. Return: `$this`.
    - If `$lat == null`: marker appears at the first click point on the map.
    - If `$lat` and `$lng` are `float`s: these are the latitude and longitude.
    - If `$lat` is an array: `[<latitude>, <longitude>]`.
     
  `$options` Options for the marker:
  - `'type'`: the [type](#marker-types) of the marker. If not set: `'Marker'`.
  - Other options are passed to the marker's constructor. 
- **modelMarker(*$model, $attribute, $options* = [ ])** - Set the location of the marker
  to the value (a [GeoJSON Feature](https://geojson.org/geojson-spec.html#feature-objects))
  of `$attribute` in `$model`. Return: `$this`.
- **activeMarker(*$model, $attribute, $options* = [ ])** - Create an ActiveField for the marker
 location, coupled to the value of `$attribute` in `$model`. Return: `$this`.
- **geocoder(*$options*)** - Set the geocoder of the map. Return: `$this`.
    - `$options` is `string`: the [name](#geocoder names) of the geocoder provider.
    - `$options` is `array`: first item is name, rest are geocoder options.

- **finder(*$geocoder* = null, *$position* = 'topright')** - Add a 
 [Search Control](https://sjaakpriester.nl/software/leaflet-search), using `$geocoder`,
  to the map, with specified [position](https://leafletjs.com/reference.html#control-position). 
  Return: `$this`.
- **getVar()** - Get the name of the JavaScript variable assigned to the Leaflet map. For
    advanced uses.
  
**Locator** is an [Yii2 Widget](https://www.yiiframework.com/doc/api/2.0/yii-base-widget),
so it inherits all of its methods. 

#### Chainable ####

Most of **Locator**'s methods return `this`, so they are *chainable*. This means that the
absolute minimum code to display a map in a view would be something like:

    <?php
    use sjaakp\locator\Locator;
    
    ...
    <?php
        Locator::begin([
            // ... options ...
        ])->modelCenter($model, 'mapcenter')
            ->modelZoom($model, 'mapzoom')
            ->modelFeature($model, 'location')
            ->end();
    ?>
    ...

## Properties ##

- **$height** `int|string|false` Height of the Locator element. If `int` in pixels, if
    `string` any other valid CSS-value. If `false`, the height is not set. Notice that
    in that case the height must be set with some other means, otherwise the map will have a 
    height of zero, and be invisible. Default: `400`.
- **$tile** `string|array` Name or configuration of the first [tile layer](#tile-names).
    Default: `'OpenStreetMap'`.
- **$marker** `array` Type and options for the default [marker](#marker-types).
    Default: `[ 'type' => 'DotMarker' ]`.    
- **$options** `array` HTML options of the map container. Use this to explicitly set the ID.
    Default: `[ ]` (empty array).
- **$leafletOptions** `array` JavaScript [options](https://leafletjs.com/reference.html#map-option) of the map.
    Default: `[ ]` (empty array).
- **$cluster** `null|true|array` Options for [MarkerClusterer](https://github.com/Leaflet/Leaflet.markercluster#options).
    If `null`: no clustering. If `true`: clustering with default options. Default: `null`.
- **$popup** `null|true|array` [Options](https://leafletjs.com/reference.html#popup-option) for popups.
    If `null`: no popups. If `true`: popups with default options. Default: `null`.
- **$scale** `null|int` Display a [Scale Control](https://leafletjs.com/reference.html#control-scale)
    on the map. Can be `null` (no Scale Control), `SCALE_METRIC`, `SCALE_IMPERIAL` or `SCALE_BOTH`.
    Default: `SCALE_METRIC`.
- **$urlTemplate** `string` URL template used when marker is clicked. If not set, nothing happens.
    If `$popup` is set, a popup is shown with contents from the resulting URL. Otherwise a jump
    is performed to the URL. `'{xxx}'` is replaced by the Marker option with the name `'xxx'`.
    Typical use: `$urlTemplate = 'view/{id}'`. Default: `null`.
- **$fly** `bool` Whether to use ['fly-animation'](https://leafletjs.com/reference.html#map-flyto)
    when a Marker is placed after find. 
- **$tileNamespace** `string` Namespace of the `Tile*` classes, defining the tile layers.
    Use this to add your own tile layer.       
  
**Locator** is an [Yii2 Widget](https://www.yiiframework.com/doc/api/2.0/yii-base-widget),
so it inherits all of its properties. 
 
## Tile Names ##

**Locator** retrieves its map tiles from a tile provider or map provider. Tiles are identified by the name
of the provider, or by an `array` with the name as the first item and options in the rest
of the array. This value is used in the `$tile` property, and in the `tileLayer()` method.
A map can have more than one tile layers, which make sense if they are partly transparent.

Some providers offer tiles in a few *variants*. They are indicated with a suffix to the
provider name, seperated by a dot. For example: `'OpenStreetMap'` and `'OpenStreetMap.BlackAndWhite'`.

Commercial tile providers expect some sort of API key. This should be added to the options.
Often, an API key can be obtained free of charge for small or non-commercial applications.

Out of the box, **Locator** supports several tile providers. They each have a PHP class file 
in the `src/tiles` directory. Currently, the following tile providers are supported (there
may be more in the future):

|Name|Variants|Required option|
|----|---|--------|
|[OpenStreetMap](https://www.openstreetmap.org/about)|BlackAndWhite, HOT| |
|OpenMapSurfer|Roads, Hybrid, AdminBounds, ContourLines, Hillshade| |
|[OpenTopoMap](https://opentopomap.org/about#verwendung)| | |
|[Wikimedia](https://commons.wikimedia.org/wiki/Commons:Map_resources)| | |
|[Carto](https://github.com/CartoDB/basemap-styles)|Light, Dark, Voyager| |
|[Stamen](http://maps.stamen.com)|Toner, TonerBackground, TonerHybrid, TonerLines, TonerLabels, TonerLite, Watercolor, Terrain, TerrainBackground, TerrainLabels| |
|EsriWorld| | |
|[Here](https://developer.here.com)|lots (see TileHere.php) |`[ 'apiKey' => '...' ]`|
|[TomTom](https://developer.tomtom.com/maps-api)|Basic, Hybrid, Labels|`[ 'key' => '...' ]`|
|[Kadaster](https://pdok-ngr.readthedocs.io/services.html#tile-map-service-tms) (Netherlands only)| | |
|[Amsterdam](https://map.data.amsterdam.nl/)|light, zw| |

If `$tile` is not set, **Locator** uses tiles from *OpenStreetMap*.

## Geocoder Names ##

**Locator**'s Search functionality uses information from a *geocoding service*.
 The service is set by the first parameter of the `finder()` method. This can be a `string`
 which is the name of the geocoding service, or an `array` with the name as first item,
 followed by options.
    
Generally, there will be no options, apart from the API key some providers expect. Other options may be added.

Currently, **Locator** 
supports the following providers (there may be more in the future):

|Name|Required option|
|----|------------|
|[Nominatim](https://nominatim.org), by [OpenStreetMap](https://www.openstreetmap.org/about)| |
|[GeoNames](https://geonames.org)|`[ 'username' => '...' ]` |
|[Here](https://developer.here.com/documentation/authentication/dev_guide/index.html)|`[ 'apiKey' => '...' ]` |
|[TomTom](https://developer.tomtom.com/search-api/search-api-documentation/)|`[ 'key' => '...' ]` |
|[Kadaster](https://github.com/PDOK/locatieserver/wiki/API-Locatieserver) (Netherlands only)| |

Notice that some providers may stipulate that you should use their service only on map
tiles of the same provider.

If you don't explicitly set a geocoder, **Leaflet-search** uses *Nominatim*.

## Marker Types ##

In the property `$marker`, and in methods like `marker()`, `modelMarker()` etc. the
marker type can be set. This is an `array` with `[ 'type' => '<marker type>' ]`,
 supplemented with marker options (dependent on the type). For instance:

    $map->marker = [ 'type' => 'Marker', 'opacity' => 0.5 ]

Apart from **Leaflet**'s own [Marker](https://leafletjs.com/reference.html#marker) and
[CircleMarker](https://leafletjs.com/reference.html#circlemarker), **Locator** sports two
other markers:

#### DotMarker ####

A simple extension of [CircleMarker](https://leafletjs.com/reference.html#circlemarker). It has fixed
radius and always has (at least) the class name `'dot-marker'`. The default marker of 
**Locator** is a DotMarker.

#### SpriteMarker ####

A marker with a [DivIcon](https://leafletjs.com/reference.html#divicon). Use this to display 
[FontAwesome](https://fontawesome.com) markers like so:
 
    $map->marker = [ 'type' => 'SpriteMarker', 'className' => 'far fa-2x fa-dog' ]



