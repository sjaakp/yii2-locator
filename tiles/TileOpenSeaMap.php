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
 * Class TileOpenSeaMap
 * @package sjaakp\locator\tiles
 */
class TileOpenSeaMap extends BaseTile
{
    /**
     * @return string
     */
    public function run($data)
    {
        $options = [
            'attribution' => 'Map data: &copy; <a href="//www.openseamap.org" target="_blank" rel="noopener noreferrer">OpenSeaMap</a> contributors'
        ];

        $opts = array_merge($options, $data);
        return $this->encode('https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', $opts);
    }
}
