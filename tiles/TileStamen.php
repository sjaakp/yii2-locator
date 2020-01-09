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
 * Class TileStamen
 * @package sjaakp\locator\tiles
 */
class TileStamen extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $schemes = [
            'Toner' => 'toner',
            'TonerBackground' => 'toner-background',
            'TonerHybrid' => 'toner-hybrid',
            'TonerLines' => 'toner-lines',
            'TonerLabels' => 'toner-labels',
            'TonerLite' => 'toner-lite',
            'Watercolor' => 'watercolor',
            'Terrain' => 'terrain',
            'TerrainBackground' => 'terrain-background',
            'TerrainLabels' => 'terrain-labels',
        ];

        $v = $this->variant ? $this->variant : 'Toner';
        if (! isset($schemes[$v]))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Stamen'.");
        }
        $scheme = $schemes[$v];

        $ext = $scheme == 'watercolor' ? 'jpg' : 'png';
        $url = "https://stamen-tiles-{s}.a.ssl.fastly.net/$scheme/{z}/{x}/{y}.$ext";

        $minZ = $scheme == 'watercolor' ? 1 : 0;
        $maxZ = $scheme == 'watercolor' ? 16 : (strncmp($scheme, 'terrain', 7) == 0 ? 18 : 20);

        $opts = array_merge([
            'attribution' => "Map tiles by <a href=\"//stamen.com\" target=\"_blank\" rel=\"noopener noreferrer\">Stamen Design</a>, $this->ccAttr &mdash; Map data $this->osmAttr",
            'minZoom' => $minZ,
            'maxZoom' => $maxZ,
            'subdomains' => 'abcd',
        ], $data);
        return $this->encode($url, $opts);
    }
}
