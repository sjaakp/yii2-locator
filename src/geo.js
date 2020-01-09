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

L.geo = {
    Geocoder: L.Class.extend({
        initialize: function (map, options) {
            this._map = map;
            L.setOptions(this, options);
        },

        constructUrl(text, options = {})  {
            const opts = Object.assign({}, this.options, options),
                url = new URL(text);
            for (const k in opts)   {
                url.searchParams.set(k, opts[k]);
            }
            return url;
        },

        fetchJson(url)  {
            return fetch(url.href)
                .then(response => response.json())
        },

        placeMarker(latlng, bbox)   {
            this._map.placeMarker(latlng, bbox);
        },

        fire(err)   {
            this._map.fire(err);
        },

        suggest(address, datalist)    {
        },

        lookup(id)  {
        }
    })
};

/**
 * @link https://nominatim.org/release-docs/develop/api/Search/
 * Doesn't support suggestions
 */
L.geo.Nominatim = L.geo.Geocoder.extend({
    mark(place) {
        const latlng = L.latLng(place.lat, place.lon),
            bb = place.boundingbox,
            bbox = L.latLngBounds([bb[0], bb[2]], [bb[1], bb[3]]);
        this.placeMarker(latlng, bbox);
    },

    geocode(address)    {
        fetch('//nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address))
            .then(response => response.json())
            .then(json => {
                if (json.length < 1) throw('notfound');
                return json[0];
            })
            .then(place => this.mark(place))
            .catch(error => this.fire(error));
    }
});

/**
 * @link https://developer.here.com/documentation/geocoder/dev_guide/topics/request-constructing.html
 */
L.geo.Here = L.geo.Geocoder.extend({
    mark(place) {
        const pos = place.displayPosition,
            latlng = L.latLng(pos.latitude, pos.longitude),
            mapv = place.mapView,
            tl = mapv.topLeft,
            br = mapv.bottomRight,
            bbox = L.latLngBounds([tl.latitude, br.longitude], [br.latitude, tl.longitude]);
        this.placeMarker(latlng, bbox);
    },

    fetchData(options)  {
        options.jsonattributes = 1;
        const url = this.constructUrl('https://geocoder.ls.hereapi.com/6.2/geocode.json', options);
        this.fetchJson(url)
            .then(json => json.response.view.shift().result.shift())
            .then(result => this.mark(result.location))
            .catch(error => this.fire(error));
    },

    suggest(address, datalist)  {
        const url = this.constructUrl('https://autocomplete.geocoder.ls.hereapi.com/6.2/suggest.json', { query: address });
        this.fetchJson(url)
            .then(json => json.suggestions)
            .then(suggestions => {
                datalist.innerHTML = suggestions.reduce((a, v) => a + `<option data-id="${v.locationId}">${v.label}</option>`, '');
            })
            .catch(error => this.fire(error));
    },

    lookup(id)  {
        this.fetchData({ locationid: id });
    },

    geocode(address)    {
        this.fetchData({ searchtext: address });
    }
});

/**
 * Topological names only; no streets
 * @link http://www.geonames.org/export/geonames-search.html
 */
L.geo.GeoNames = L.geo.Geocoder.extend({
    url: 'http://api.geonames.org/search',  // no https!

    fetchGeonames(url)  {
        return this.fetchJson(url)
            .then(json => {
                const geonames = json.geonames;
                if (! geonames || geonames.length < 1) throw 'notfound';
                return geonames;
            })
    },

    mark(place) {
        const latlng = L.latLng(place.lat, place.lng),
            bb = place.bbox,
            bbox = L.latLngBounds([bb.north, bb.west], [bb.south, bb.east]);
        this.placeMarker(latlng, bbox);
    },

    suggest(address, datalist)    {
        const url = this.constructUrl(this.url, { q: address, type: 'json', style: 'short' });

        this.fetchGeonames(url)
            .then(geonames => {
                datalist.innerHTML = geonames.reduce((a, v) => a + `<option data-id="${v.geonameId}">${v.name}&emsp;${v.countryCode}</option>`, '');
            })
            .catch(error => this.fire(error));
    },

    lookup(id)  {
        const url = this.constructUrl('http://api.geonames.org/getJSON', { geonameId: id });

        this.fetchJson(url)
            .then(json => this.mark(json))
            .catch(error => this.fire(error));
    },

    geocode(address)    {
        const url = this.constructUrl(this.url, { q: address, inclBbox: true });

        this.fetchGeonames(url)
            .then(geonames => geonames.shift())
            .then(place => this.mark(place))
            .catch(error => this.fire(error));
    }
});

/**
 * @link https://developer.tomtom.com/search-api-and-extended-search-api/search-api-and-extended-search-api-documentation-geocoding/geocode
 */
L.geo.TomTom = L.geo.Geocoder.extend({
    url: 'https://api.tomtom.com/search/2/geocode/',

    suggestions: [],

    datalist: null,

    mark(place) {
        const pos = place.position,
            latlng = L.latLng(pos.lat, pos.lon),
            mapv = place.viewport,
            tl = mapv.topLeftPoint,
            br = mapv.btmRightPoint,
            bbox = L.latLngBounds([tl.lat, br.lon], [br.lat, tl.lon]);
        this.placeMarker(latlng, bbox);
    },

    fetchResults(address)  {
        const query = encodeURIComponent(address),
            url = this.constructUrl(`${this.url}${query}.json`, { typeahead: true });

        return this.fetchJson(url)
            .then(json => {
                if (json.summary.numResults < 1) throw('notfound');
                return json.results;
            })
    },

    suggest(address, datalist)  {
        this.datalist = datalist;
        this.fetchResults(address)
            .then(results => {
                this.suggestions = results;
                datalist.innerHTML = results.reduce((a, v) => a + `<option data-id="${v.id}">${v.address.freeformAddress}</option>`, '');
            })
            .catch(error => this.fire(error));
    },

    lookup(id)  {
        const found = this.suggestions.find(v => v.id === id);
        if (found) this.mark(found);
    },

    geocode(address)    {
        this.fetchResults(address)
            .then(results => results.shift())
            .then(place => this.mark(place))
            .catch(error => this.fire(error));
    }
});

/**
 * Netherlands
 * @link https://github.com/PDOK/locatieserver/wiki/API-Locatieserver
 */
L.geo.Kadaster = L.geo.Geocoder.extend({
    url: 'https://geodata.nationaalgeoregister.nl/locatieserver/v3/',

    mark(place) {
        this.placeMarker(place.centroide_ll.match(/[\d.]+/g).reverse());
    },

    suggest(address, datalist)  {
        const url = this.constructUrl(this.url + 'suggest', { q: address + ' and -type:postcode' });
        this.fetchJson(url)
            .then(json => {
                if (json.response.numFound < 1) throw('notfound');
                return json.highlighting;
            })
            .then(hilight => {
                let html = '';
                for (const id in hilight) {
                    const opt = `<option data-id="${id}">${hilight[id].suggest.shift()}</option>`;
                    html += opt;
                }
                datalist.innerHTML = html;
            })
            .catch(error => this.fire(error));
    },

    lookup(id)  {
        const url = this.constructUrl(this.url + 'lookup', { id: id });
        this.fetchJson(url)
            .then(json => json.response.docs.shift())
            .then(place => this.mark(place))
            .catch(error => this.fire(error));
    },

    geocode(address)    {
        const url = this.constructUrl(this.url + 'free', { q: address + ' and -type:postcode' });
        this.fetchJson(url)
            .then(json => {
                if (json.response.numFound < 1) throw('notfound');
                return json.response.docs.shift();
            })
            .then(place => this.mark(place))
            .catch(error => this.fire(error));
    }
});

L.Map.include({
    find(address) {
        this._geocoder.geocode(address);
        return this;
    },

    initFinder(buttonId, inputId, datalistId)   {
        const input = document.getElementById(inputId);
        const datalist = document.getElementById(datalistId);
        input.addEventListener('input', function(e) {
            const v = e.target.value;
            if (v.length >= 2)  {
                this._geocoder.suggest(e.target.value, datalist);
            }
        }.bind(this));
        input.addEventListener('change', function(e) {
            const val = e.target.value,
                opts = datalist.childNodes;
            e.target.value = '';
            for (let i = 0; i < opts.length; i++) {
                if (val.startsWith(opts[i].innerText)) {
                    this._geocoder.lookup(opts[i].dataset.id);
                    return;
                }
            }
            this.find(val);
        }.bind(this));
        return this;
    },

    initGeocoder(name, options)  {
        this._geocoder = new L.geo[name](this, options);
        return this;
    }
});

L.Map.addInitHook(function() {
    this.initGeocoder('Nominatim', {});
});
