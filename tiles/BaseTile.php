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

use yii\base\BaseObject;
use yii\helpers\Json;

class BaseTile extends BaseObject
{
    public $variant;

    /**
     * @param string $url
     * @param array $options
     * @param string $type
     * @return string
     */
    public function encode($url, $options, $type = 'tileLayer')
    {
        $opts = empty($options) ? '{}' : Json::encode($options);
        return "L.$type(\"$url\", $opts)";
    }

    protected $osmAttr = '&copy; <a href="//openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>';
    protected $odbL = '&copy; <a href="//openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">ODbL</a>';
    protected $ccAttr = '<a href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank" rel="noopener noreferrer">CC-BY-SA</a>';
}
