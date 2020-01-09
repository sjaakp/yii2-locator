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
 * Class TileOpenStreetMap
 * @package sjaakp\locator\tiles
 */
class TileOpenStreetMap extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $urls = [
            'default' => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'BlackAndWhite' => '//toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png',
            'HOT' => '//{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        ];

        $options = [
            'default' => [
                'maxZoom' => 19,
                'attribution' => ''
            ],
            'BlackAndWhite' => [
                'maxZoom' => 18,
                'attribution' => ', Tiles <a href="//www.hotosm.org/" target="_blank" rel="noopener noreferrer">Humanitarian OpenStreetMap Team</a>'
            ],
            'HOT' => [
                'maxZoom' => 19,
                'attribution' => ', Tiles courtesy of <a href="//www.hotosm.org/" target="_blank" rel="noopener noreferrer">Humanitarian OpenStreetMap Team</a>'
            ],
        ];

        $v = $this->variant ? $this->variant : 'default';
        if (! isset($urls[$v]))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'OpenStreetMap'.");
        }

        $opts = array_merge($options[$v], $data);
        $opts['attribution'] = $this->osmAttr . $opts['attribution'];
        return $this->encode($urls[$v], $opts);
    }
}
