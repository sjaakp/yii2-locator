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
 * Class TileWikimedia
 * @package sjaakp\locator\tiles
 */
class TileWikimedia extends BaseTile
{
    /**
     * @return string
     */
    public function run($data)
    {
        $opts = array_merge([
            'attribution' => '<a href="//wikimediafoundation.org/wiki/Maps_Terms_of_Use" target="_blank" rel="noopener noreferrer">Wikimedia</a>'
        ], $data);
        return $this->encode('//maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', $opts);
    }
}
