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

// https://leafletjs.com/examples/extending/extending-3-controls.html#handlers
// https://github.com/elmarquis/Leaflet.GestureHandling

L.TouchDragHandler = L.Handler.extend({
    addHooks() {
        this._map.dragging.disable();
        if (this._map.tap) {
            this._map.tap.disable();
        }
        this._map._container.addEventListener('touchstart', this._handleTouch);
        this._map._container.addEventListener('touchend', this._handleTouch);
        L.DomEvent.on(this._map._container, 'click', this._handleTouch, this);
    },

    removeHooks() {
        this._map.dragging.enable();
        if (this._map.tap) {
            this._map.tap.enable();
        }
        this._map._container.removeEventListener('touchstart', this._handleTouch);
        this._map._container.removeEventListener('touchend', this._handleTouch);
        L.DomEvent.off(this._map._container, 'click', this._handleTouch, this);
    },

    _handleTouch(e) {
        if (e.type === 'touchstart' && e.touches.length === 1)  {
            L.DomUtil.addClass(e.target, 'leaflet-touchdrag');
            this._map.dragging.disable();
        } else {
            L.DomUtil.removeClass(e.target, 'leaflet-touchdrag');
            this._map.dragging.enable();
        }
        return true;
    },
});
