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
use yii\helpers\ArrayHelper;

/**
 * Class TileTomTom
 * @package sjaakp\locator\tiles
 * use: tileLayer([ 'TomTom', 'key' => '... your Api Key ...' ])
 * @link https://developer.tomtom.com/maps-api
 */
class TileTomTom extends BaseTile
{
    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function run($data)
    {
        $variants = [ 'Basic', 'Hybrid', 'Labels' ];

        $v = $this->variant ? $this->variant : 'Basic';
        if (! in_array($v, $variants))        {
            throw new InvalidConfigException("Locator: '$v' is unknown tile variant of provider 'TomTom'.");
        }
        $scheme = strtolower($v);

        $apiKey = ArrayHelper::remove($data, 'key');
        if (! $apiKey)  {
            throw new InvalidConfigException("Locator: key for provider 'TomTom' is not set.");
        }

        $year = date('Y');

        $opts = array_merge([
            'maxZoom' => 22,
            'subdomains' => 'abcd',
            'attribution' => "<a href=\"//tomtom.com\" target=\"_blank\" rel=\"noopener noreferrer\">&copy;  1992-$year TomTom.</a>",
        ], $data);
        return $this->encode("https://{s}.api.tomtom.com/map/1/tile/$scheme/tow/{z}/{x}/{y}.png?key=$apiKey", $opts);
    }
}
