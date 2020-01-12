Yii2-locator
------------
#### Leaflet-wrapper for Yii2 PHP framework ####

[![Latest Stable Version](https://poser.pugx.org/sjaakp/yii2-locator/v/stable)](https://packagist.org/packages/sjaakp/yii2-locator)
[![Total Downloads](https://poser.pugx.org/sjaakp/yii2-locator/downloads)](https://packagist.org/packages/sjaakp/yii2-locator)
[![License](https://poser.pugx.org/sjaakp/yii2-locator/license)](https://packagist.org/packages/sjaakp/yii2-locator)

This is a wrapper of the brilliant [Leaflet](https://leafletjs.com/) JavaScript
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
GeoJSON and vice versa.

## Usage ##

A typical usage scenario is like this: suppose we have a database table with some geographical data,
let's say the table `towers`.

`towers` has, among others, the following fields:

- `location`: `POINT` the location of the tower,
- `mapcenter`: `POINT` the center of the map,
- `mapzoom`: `int` the zoom level of the map.

#### View ####

In a yii2 view, displaying a map of a tower is simple as this:

    <?php
    use sjaakp\locator\Locator;
    /**
     * @var yii\web\View $this
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


#### Chainable ####

Most of **Locator**'s methods return `this`, so they are *chainable*. This means that the
absolute minimum code to display a map in a view is something like:

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

