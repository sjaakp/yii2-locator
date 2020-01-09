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
 * Class TileOpenMapSurfer
 * @package sjaakp\locator\tiles
 */
class TileOpenMapSurfer extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $schemes = [
            'Roads' => 'roads',
            'Hybrid' => 'hybrid',
            'AdminBounds' => 'adminb',
            'ContourLines' => 'asterc',
            'Hillshade' => 'asterh',
        ];

        $v = $this->variant ? $this->variant : 'Roads';
        if (! isset($schemes[$v]))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'OpenMapSurfer'.");
        }
        $scheme = $schemes[$v];

        $opts = array_merge([
            'maxZoom' => 19,
            'attribution' => 'Imagery from <a href="//giscience.uni-hd.de/" target="_blank" rel="noopener noreferrer">GIScience Research Group @ University of Heidelberg</a> | Map data',
        ], $data);
        return $this->encode("//maps.heigit.org/openmapsurfer/tiles/$scheme/webmercator/{z}/{x}/{y}.png", $opts);
    }
}
