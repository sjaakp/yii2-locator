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

/*
    Plugin for Leaflet
    Tested with Leaflet 1.6.0
    21-05-2018 ... 14-01-2020

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

import './locator.scss';
import './touchdrag.js';
import './dotmarker.js';
import './spritemarker.js';

L.Map.addInitHook(function() {
    if (this.options.scale !== null) {
        const s = this.options.scale,
            metric = s & 1,
            imperial = s & 2;
        this.addControl(L.control.scale({ metric: metric, imperial: imperial }));    // add scale control
    }
    if (this.options.touchdrag) this.addHandler('touchdrag', L.TouchDragHandler);       // and touchdrag handler
});


L.Map.include({

    _markers: null,
    _armed: false,      // true if click on map produces a marker

    getMarkers()  {
        if (! this._markers)    {
            if (this.options.cluster !== null)  {
                this._markers = L.markerClusterGroup(this.options.cluster);
                this.addLayer(this._markers);
            }
            else this._markers = this;
        }
        return this._markers;
    },

    arm(markerOpts) {       // prepare for marker on click
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

    disarm() {
        if (this._armed)    {
            L.DomUtil.removeClass(this.getContainer(), 'locator-armed');
            this.off('click');
            this._armed = false;
        }
        return this;
    },

    addFeature(feature)   {
        this.getMarkers().addLayer(L.geoJSON(feature, {
            pointToLayer: function(ft, latlng) {
                return this.newMarker(latlng, Object.assign({}, this.options.marker, ft.properties));
            }.bind(this),
        }));
        return this;
    },

    monitorZoom(id) {
        return this.on('zoomend', function(e) {
            document.getElementById(id).value = e.target.getZoom();
        }).fire('zoomend');
    },

    monitorCenter(id) {
        return this.on('moveend', function(e) {
            const c = e.target.getCenter();
            document.getElementById(id).value = JSON.stringify({
                type: 'Point',
                coordinates: [c.lng, c.lat]
            });
        }).fire('moveend');
    },

    /**
     * Create a new marker of any type
     * @param latlng
     * @param options if null, use this.options.marker
     * @returns {*}
     */
    newMarker(latlng, options = null) {
        if (options == null) options = this.options.marker;
        const type = options.type || 'Marker';
        if (type === 'Marker' && options.icon)  {
            options.icon = L.icon(options.icon);
        }
        let r = new L[type](latlng, options).on('moveend', function(e) {
            const id = e.target.options.monitor;
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
                    const pOpts = this.options.popup,
                        popup = L.popup(pOpts).setLatLng(e.latlng).setContent('<svg class="locator-loading" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/></svg>')
                            .openOn(this);

                    fetch(url)
                        .then(response => response.text())
                        .then(html => popup.setContent(html));
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

    addMarker(latlng, markerOpts)  {
        const m = this.newMarker(latlng, markerOpts);
        this.addLayer(m);
        this._marker = m;
        m.fire('moveend');
        return this;
    }
});
