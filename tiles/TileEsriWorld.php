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

/**
 * Class TileEsriWorld
 * @package sjaakp\locator\tiles
 */
class TileEsriWorld extends BaseTile
{
    /**
     * @return string
     */
    public function run($data)
    {
        $opts = array_merge([
            'attribution' => '&copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (HongKong), and the GIS User Community'
        ], $data);
        return $this->encode('//server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', $opts);
    }
}
