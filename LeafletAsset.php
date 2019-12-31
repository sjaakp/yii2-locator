<?php
/**
 * sjaakp/yii2-locator
 * ----------
 * Leaflet wrapper for Yii2 framework
 * Version 1.0.0
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
            '//unpkg.com/leaflet@1.6.0/dist/leaflet.css',
            'integrity' => 'sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==',
            'crossorigin' => ''
        ],
    ];

    public $js = [
        [
            '//unpkg.com/leaflet@1.6.0/dist/leaflet.js',
            'integrity' => 'sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==',
            'crossorigin' => ''
        ],
        'locator.js'
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
