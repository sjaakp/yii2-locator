<?php
/**
 * sjaakp/yii2-locator
 * ----------
 * Leaflet wrapper for Yii2 framework
 * Version 1.0.3 TMS => WMTS
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
 * @link https://www.pdok.nl/introductie/-/article/basisregistratie-topografie-achtergrondkaarten-brt-a-
 *
 * 31-10-2021: url updated, 'standaard' and 'water' added
 */
class TileKadaster extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $variants = [ 'standaard', 'grijs', 'pastel', 'water' ];

        $v = $this->variant;
        if (! $v && in_array($v, $variants))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'Kadaster'.");
        }

        $opts = array_merge([
            'minZoom' => 5,
            'maxZoom' => 19,
            'boundVec' => [[50.5, 0.0], [54, 10.4]],
            'attribution' => "&copy; <a href=\"//kadaster.nl\" target=\"_blank\" rel=\"noopener noreferrer\">Kadaster</a> ($this->ccAttr)",
        ], $data);
//        return $this->encode("https://geodata.nationaalgeoregister.nl/tiles/service/wmts?service=WMTS&version=1.0.0&layer=brtachtergrondkaart$v&tilematrixset=EPSG:3857&format=image/png&request=GetTile&tilematrix={z}&tilerow={y}&tilecol={x}", $opts);
        return $this->encode("https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0?service=wmts&version=2.0&layer=$v&tilematrixset=EPSG:3857&format=image/png&request=GetTile&tilematrix={z}&tilerow={y}&tilecol={x}", $opts);

//      Sample with aerial photo, works
//        return $this->encode("https://service.pdok.nl/hwh/luchtfotorgb/wmts/v1_0?service=WMTS&version=1.0.0&layer=Actueel_ortho25&tilematrixset=EPSG:3857&format=image/jpeg&request=GetTile&tilematrix={z}&tilerow={y}&tilecol={x}", $opts);

//      This also works but it seems undocumented. Found on https://github.com/geppyz/leaflet-test-map
//        return $this->encode("//geodata.nationaalgeoregister.nl/tiles/service/wmts/brtachtergrondkaart$v/EPSG:3857/{z}/{x}/{y}.png", $opts);
    }
}
