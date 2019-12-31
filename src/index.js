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
        viewUrl     String, url jumped to when marker is clicked. If not set, no jump is performed.
                        '{xxx}' gets replaced by Marker option <xxx>
                        typical use: viewUrl = '/view/{id}'
        cluster     Boolean. Whether MarkerClusterer is used.
        touchdrag   Boolean. If true, map can only be panned with two fingers; warning overlay appears if attempted with one finger.
                        Intended to avoid conflict with page scrolling on small devices.
        scale       null: no scale | 1: metric scale | 2: imperial scale | 3: both.

    Map always has a scale control with a metric scale

    Two new classes:

    DotMarker is an extension of CircleMarker. It gets the HTML class 'dot-marker'.

    SpriteMarker is a Marker with a DivIcon. It has all the options of DivIcon. It gets the HTML class 'sprite-marker'.
        Typical use: set the 'html' option to a SVG-element, either with an explicit path or a use class; use the option 'className' to colorize the sprite

    Marker, CircleMarker, DotMarker, SpriteMarker each have extra options
        monitor     String, the id of a HTML text input updated at moveend
        title       HTML of tooltip
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

    _geo: null,
    _armed: false,      // true if click on map produces a marker

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

    getGeo: function()  {
        if (! this._geo)    {
            let geo = L.geoJSON(null, {
                    pointToLayer: function(ft, latlng) {
                        let r = this.newMarker(latlng, Object.assign({}, this.options.marker, ft.properties)),
                            title = ft.properties.title;
                        if (title) r.bindTooltip(title);
                        return r;
                    }.bind(this),
                }),
                clusters = geo;
            if (this.options.cluster)   {
                clusters = L.markerClusterGroup();
                clusters.addLayer(geo);
            }
            this.addLayer(clusters);
            this._geo = geo;
        }
        return this._geo;
    },

    addFeature: function(feature)   {
        this.getGeo().addData(feature);
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
            let url = this._map.options.viewUrl;
            if (url)  {
                let matches = url.match(/{\w+}/g);
                if (matches)    {
                    matches.forEach(function(match) {
                        url = url.replace(match, this.options[match.slice(1, -1)]);
                    }, this);
                }
                window.location = url;
            }
        });
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
