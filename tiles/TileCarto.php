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
 * Class TileCarto
 * @package sjaakp\locator\tiles
 * @link https://github.com/CartoDB/basemap-styles
 */
class TileCarto extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $schemes = [
            'Light' => 'light_all',
            'Dark' => 'dark_all',
            'Voyager' => 'rastertiles/voyager',
        ];

        $v = $this->variant ? $this->variant : 'light';
        if (! isset($schemes[$v]))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Carto'.");
        }
        $scheme = $schemes[$v];

        $opts = array_merge([
            'attribution' => $this->osmAttr . ' &copy; <a href="//carto.com/attribution/" target="_blank" rel="noopener noreferrer">Carto</a>',
            'subdomains' => 'abcd',
            'maxZoom' => 20,
        ], $data);
        return $this->encode("//cartodb-basemaps-{s}.global.ssl.fastly.net/$scheme/{z}/{x}/{y}.png", $opts);
    }
}
