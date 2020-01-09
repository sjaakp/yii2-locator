<?php
/**
 * sjaakp/yii2-locator
 * ----------
 * Leaflet wrapper for Yii2 framework
 * Version 1.0.0
 * Copyright (c) 2020
 * Sjaak Priester, Amsterdam
 * MIT License
 * https://github.com/sjaakp/yii2-locator
 * https://sjaakpriester.nl
 */

namespace sjaakp\locator\tiles;

use sjaakp\locator\tiles\BaseTile;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class TileHere
 * @package sjaakp\locator\tiles
 * use: tileLayer([ 'Here', 'apiKey' => '... your Api Key ...', 'language' => 'eng' ])
 * @link https://developer.here.com/documentation/map-tile/dev_guide/topics/request-constructing.html
 */
class TileHere extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $variants = [
            'NormalDay' => [ 'scheme' => 'normal.day' ],
            'NormalDayGrey' => [ 'scheme' => 'normal.day.grey' ],
            'NormalDayTransit' => [ 'scheme' => 'normal.day.transit' ],
            'NormalDayTraffic' => [ 'scheme' => 'normal.traffic.day', 'base' => 'traffic', 'resource' => 'traffictile' ],
            'NormalNight' => [ 'scheme' => 'normal.night' ],
            'NormalNightGrey' => [ 'scheme' => 'normal.night.grey' ],
            'NormalNightTransit' => [ 'scheme' => 'normal.night.transit' ],
            'NormalNightTraffic' => [ 'scheme' => 'normal.traffic.night', 'base' => 'traffic', 'resource' => 'traffictile' ],
            'ReducedDay' => [ 'scheme' => 'reduced.day' ],
            'ReducedNight' => [ 'scheme' => 'reduced.night' ],
            'BasicMap' => [ 'resource' => 'basetile' ],
            'MapLabels' => [ 'resource' => 'labeltile' ],
            'TrafficFlow' => [ 'base' => 'traffic', 'resource' => 'flowtile' ],
            'HybridDay' => [ 'base' => 'aerial', 'scheme' => 'hybrid.day' ],
            'HybridDayGrey' => [ 'base' => 'aerial', 'scheme' => 'hybrid.grey.day' ],
            'HybridDayTransit' => [ 'base' => 'aerial', 'scheme' => 'hybrid.day.transit' ],
            'HybridDayTraffic' => [ 'base' => 'traffic', 'scheme' => 'hybrid.traffic.day', 'resource' => 'traffictile' ],
            'PedestrianDay' => [ 'scheme' => 'pedestrian.day' ],
            'PedestrianNight' => [ 'scheme' => 'pedestrian.night' ],
            'SatelliteDay' => [ 'base' => 'aerial', 'scheme' => 'satellite.day' ],
            'TerrainDay' => [ 'base' => 'aerial', 'scheme' => 'terrain.day' ],
        ];

        $v = $this->variant ? $this->variant : 'NormalDay';
        if (! isset($variants[$v]))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Here'.");
        }
        $var = $variants[$v];
        $base = $var['base'] ?? 'base';
        $scheme = $var['scheme'] ?? 'normal.day';
        $resource = $var['resource'] ?? 'maptile';

        $apiKey = ArrayHelper::remove($data, 'apiKey');
        if (! $apiKey)  {
            throw new InvalidConfigException("Locator: apiKey for provider 'Here' is not set.");
        }

        $language = ArrayHelper::remove($data, 'language');
        $year = date('Y');

        $opts = array_merge([
            'maxZoom' => 20,
            'subdomains' => '1234',
            'attribution' => "Map &copy; 1987-$year <a href=\"//developer.here.com\" target=\"_blank\" rel=\"noopener noreferrer\">HERE</a>",
        ], $data);
        return $this->encode("https://{s}.$base.maps.ls.hereapi.com/maptile/2.1/$resource/newest/$scheme/{z}/{x}/{y}/256/png?apiKey=$apiKey&lg=$language", $opts);
    }
}
