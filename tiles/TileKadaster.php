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
 * Class TileKadaster
 * @package sjaakp\locator\tiles
 * Netherlands
 * @link https://pdok-ngr.readthedocs.io/services.html#tile-map-service-tms
 */
class TileKadaster extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $variants = [ 'grijs', 'pastel' ];

        $v = $this->variant;
        if (! $v && in_array($v, $variants))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Kadaster'.");
        }

        $opts = array_merge([
            'tms' => true,
            'zoomOffset' => -1,
            'minZoom' => 7,
            'maxZoom' => 19,
            'boundVec' => [[50.5, 0.0], [54, 10.4]],
            'attribution' => "&copy; <a href=\"//kadaster.nl\" target=\"_blank\" rel=\"noopener noreferrer\">Kadaster</a> ($this->ccAttr)",
        ], $data);
        return $this->encode("//geodata.nationaalgeoregister.nl/tiles/service/tms/1.0.0/brtachtergrondkaart$v/EPSG:3857/{z}/{x}/{y}.png", $opts);
    }
}
