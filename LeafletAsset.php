<?php
/**
 * sjaakp/yii2-locator
 * ----------
 * Leaflet wrapper for Yii2 framework
 * Version 1.0.3 Leaflet 1.6.0 => 1.7.1
 * Copyright (c) 2019
 * Sjaak Priester, Amsterdam
 * MIT License
 * https://github.com/sjaakp/yii2-locator
 * https://sjaakpriester.nl
 */

namespace sjaakp\locator;

use yii\web\AssetBundle;

class LeafletAsset extends AssetBundle {
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'assets';

    public $css = [
        [
            '//unpkg.com/leaflet@1.7.1/dist/leaflet.css',
            'integrity' => "sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==",
            'crossorigin' => ''
        ],
    ];

    public $js = [
        [
            '//unpkg.com/leaflet@1.7.1/dist/leaflet.js',
            'integrity' => 'sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==',
            'crossorigin' => ''
        ],
        'locator.js'
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
