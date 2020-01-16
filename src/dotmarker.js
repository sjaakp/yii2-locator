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

L.DotMarker = L.CircleMarker.extend({

    /**
     * @param latlng
     * @param options options for the DotMarker:
     * - 'className': HTML class of underlying SVG <path>. Notice: *not* 'class'. @link https://leafletjs.com/reference-1.6.0.html#path
     */
    initialize(latlng, options = {}) {
        let cl = options.className ? options.className + ' ' : '';
        L.CircleMarker.prototype.initialize.call(this, latlng, Object.assign(options, {
            radius: 8,
            weight: 2,
            fillOpacity: 1.0,
            className: cl + 'dot-marker'
        }));
    }
});

L.dotMarker = function(latlng, options = {}) {
    return new L.DotMarker(latlng, options);
};
