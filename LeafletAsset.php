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
            '//unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            'integrity' => "sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=",
            'crossorigin' => ''
        ],
    ];

    public $js = [
        [
            '//unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            'integrity' => 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=',
            'crossorigin' => ''
        ],
        'locator.js'
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
