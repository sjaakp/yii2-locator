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

L.SpriteMarker = L.Marker.extend({

    /**
     * @param latlng
     * @param options options for the SpriteMarker:
     * - 'html': content of SpriteMarker; default empty. @link https://leafletjs.com/reference-1.6.0.html#divicon
     * - 'className': HTML class of DIV. Notice: *not* 'class'. @link https://leafletjs.com/reference-1.6.0.html#divicon-classname
     * Use this to display FontAwesome markers like so: 'className': 'far fa-2x fa-dog'; leave 'html' unset.
     */
    initialize(latlng, options = {}) {
        let cl = options.className ? options.className + ' ' : '';
        options.icon = L.divIcon({
            html: options.html || false,
            iconSize: options.iconSize || 24,
            bgPos: options.bgPos || null,
            className: cl + 'sprite-marker'
        });
        L.Marker.prototype.initialize.call(this, latlng, options);
    },
});

L.spriteMarker = function(latlng, options = {}) {
    return new L.SpriteMarker(latlng, options);
};
