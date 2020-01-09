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
 * Class TileAmsterdam
 * @package sjaakp\locator\tiles
 */
class TileAmsterdam extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $variants = [ 'light', 'zw' ];

        $v = $this->variant;
        if (! $v && in_array($v, $variants))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Amsterdam'.");
        }
        if ($v) $v = '_' . $v;

        $opts = array_merge([
            'minZoom' => 11,
            'maxZoom' => 21,
            'boundVec' => [[52.1698, 4.48663], [52.6135, 5.60867]],
            'subdomains' => '1234',
            'attribution' => '&copy; <a href="//map.data.amsterdam.nl/" target="_blank" rel="noopener noreferrer">amsterdam.nl</a>',
        ], $data);
        return $this->encode("//t{s}.data.amsterdam.nl/topo_wm$v/{z}/{x}/{y}.png", $opts);
    }
}
