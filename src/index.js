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

/*
    Plugin for Leaflet
    Tested with Leaflet 1.6.0
    21-05-2018 ... 29-12-2019

    Map has extra options:
        marker      Plain object, options used for creating markers
                        the option 'type' determines the type of marker (Marker, CircleMarker, DotMarker, SpriteMarker)
                        the option 'icon' is a plain object with options to create the icon for a regular Marker (i.e. not the icon itself)
        urlTemplate     String, url jumped to or used to get popup contents when marker is clicked. If not set, no jump is performed.
                        '{xxx}' gets replaced by Marker option <xxx>
                        typical use: urlTemplate = '/view/{id}'
        cluster     null|object. MarkerClusterer options; null: no clusters.
        popup       null|object. Popup options; null: no popups.
        touchdrag   Boolean. If true, map can only be panned with two fingers; warning overlay appears if attempted with one finger.
                        Intended to avoid conflict with page scrolling on small devices.
        scale       null: no scale | 1: metric scale | 2: imperial scale | 3: both.

    Two new classes:

    DotMarker is an extension of CircleMarker. It gets the HTML class 'dot-marker'.

    SpriteMarker is a Marker with a DivIcon. It has all the options of DivIcon. It gets the HTML class 'sprite-marker'.
        Typical use: set the 'html' option to a SVG-element, either with an explicit path or a use class; use the option 'className' to colorize the sprite

    Marker, CircleMarker, DotMarker, SpriteMarker each have extra options
        monitor     String, the id of a HTML text input updated at moveend
        title       HTML of tooltip

    @link https://leafletjs.com/
    @link http://www.liedman.net/tiled-maps/ - The Hitchhacker’s Guide To Tiled Maps
 */

import './touchdrag.js';
import './dotmarker.js';
import './spritemarker.js';
import './find.js';

L.Map.addInitHook(function() {
    if (this.options.scale !== null) {
        let s = this.options.scale,
            metric = s & 1,
            imperial = s & 2;
        this.addControl(L.control.scale({ metric: metric, imperial: imperial }));    // add scale control
    }
    if (this.options.touchdrag) this.addHandler('touchdrag', L.TouchDragHandler);       // and touchdrag handler
});


L.Map.include({

    _markers: null,
    _armed: false,      // true if click on map produces a marker

    getMarkers: function()  {
        if (! this._markers)    {
            if (this.options.cluster !== null)  {
                this._markers = L.markerClusterGroup(this.options.cluster);
                this.addLayer(this._markers);
            }
            else this._markers = this;
        }
        return this._markers;
    },

    arm: function(markerOpts) {       // prepare for marker on click
        if (! this._armed)  {
            L.DomUtil.addClass(this.getContainer(), 'locator-armed');
            this.options.marker = markerOpts;
            this.on('click', function(e) {
                this.disarm();
                return this.addMarker(e.latlng, markerOpts);
            });
            this._armed = true;
        }
        return this;
    },

    disarm: function() {
        if (this._armed)    {
            L.DomUtil.removeClass(this.getContainer(), 'locator-armed');
            this.off('click');
            this._armed = false;
        }
        return this;
    },

    addFeature: function(feature)   {
        this.getMarkers().addLayer(L.geoJSON(feature, {
            pointToLayer: function (ft, latlng) {
                return this.newMarker(latlng, Object.assign({}, this.options.marker, ft.properties));
            }.bind(this),
        }));
        return this;
    },

    monitorZoom: function(id) {
        return this.on('zoomend', function(e) {
            document.getElementById(id).value = e.target.getZoom();
        }).fire('zoomend');
    },

    monitorCenter: function(id) {
        return this.on('moveend', function(e) {
            const c = e.target.getCenter();
            document.getElementById(id).value = JSON.stringify({
                type: 'Point',
                coordinates: [c.lng, c.lat]
            });
        }).fire('moveend');
    },

    newMarker: function(latlng, options) {      // create a new marker of any type
        let type = options.type || 'Marker';
        if (type === 'Marker' && options.icon)  {
            options.icon = L.icon(options.icon);
        }
        let r = new L[type](latlng, options).on('moveend', function(e) {
            let id = e.target.options.monitor;
            if (id) {
                document.getElementById(id).value = JSON.stringify(e.target.toGeoJSON());
            }
        }).on('click', function (e)    {
            let url = this.options.urlTemplate;
            if (url)  {
                let matches = url.match(/{\w+}/g);
                if (matches)    {
                    matches.forEach(function(match) {
                        url = url.replace(match, e.target.options[match.slice(1, -1)]);
                    }, this);
                }
                if (this.options.popup !== null)   {
                    let pOpts = this.options.popup,
                        popup = L.popup(pOpts).setLatLng(e.latlng).setContent(pOpts.loading).openOn(this);

                    let request = new XMLHttpRequest();
                    request.open('GET', url, true);

                    request.onload = function() {
                        if (this.status >= 200 && this.status < 400) {
                            popup.setContent(this.response);
                        }
                    };
                    request.send();
                }
                else    {
                    window.location = url;
                }
            }
        }, this);   // context = map

        if (options.title)  {
            r.bindTooltip(options.title);
        }
        return r;
    },

    addMarker: function(latlng, markerOpts)  {
        let m = this.newMarker(latlng, markerOpts);
        this.addLayer(m);
        this._marker = m;
        m.fire('moveend');
        return this;
    },

    placeMarker: function(latlng)   {
        if (this._marker)    {
            this._marker.setLatLng(latlng);
        }
        else {
            this.unarm().addMarker(latlng, this.options.marker);
        }
        this.panTo(latlng);
    },
});
