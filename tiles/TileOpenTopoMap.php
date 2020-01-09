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

/**
 * Class TileOpenTopoMap
 * @package sjaakp\locator\tiles
 * @link https://opentopomap.org/about#verwendung
 */
class TileOpenTopoMap extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $opts = array_merge([
            'maxZoom' => 17,
            'subdomains' => 'abc',
            'attribution' => $this->osmAttr . ', <a href="//viewfinderpanoramas.org" target="_blank" rel="noopener noreferrer">SRTM</a> | Map style: &copy; <a href="//opentopomap.org" target="_blank" rel="noopener noreferrer">OpenTopoMap</a> (' . $this->ccAttr . ')',
        ], $data);
        return $this->encode('//{s}.tile.opentopomap.org/{z}/{x}/{y}.png', $opts);
    }
}
