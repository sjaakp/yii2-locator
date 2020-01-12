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
 */
L.geo.Nominatim = L.geo.Geocoder.extend({
    url: 'https://nominatim.openstreetmap.org/',

    mark(place) {
        const latlng = L.latLng(place.lat, place.lon),
            bb = place.boundingbox,
            bbox = L.latLngBounds([bb[0], bb[2]], [bb[1], bb[3]]);
        this.placeMarker(latlng, bbox);
    },

    search(address) {
        const url = this.constructUrl(this.url + 'search', { format: 'json', q: address });
        return this.fetchJson(url)
    },

    suggest(address, datalist)    {
        // const url = this.constructUrl(this.url + 'search', { format: 'json', q: address });
        this.search(address)
            .then(json => {
                // console.log(json);
                datalist.innerHTML = json.reduce((a, v) => a + `<option data-id="${v.osm_type.charAt(0).toUpperCase()}${v.osm_id}">${v.display_name}</option>`, '');
            })
            .catch(error => this.fire(error));
    },

    lookup(id)  {
        const url = this.constructUrl(this.url + 'reverse', {
            format: 'json',
            osm_type: id.charAt(0),
            osm_id: id.slice(1)
        });
        this.fetchJson(url)
            .then(json => this.mark(json))
            .catch(error => this.fire(error));
    },

    geocode(address)    {
        // fetch('//nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address))
        //     .then(response => response.json())
        this.search(address)
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

    fetchResults(address, options = {})  {
        const query = encodeURIComponent(address),
            url = this.constructUrl(`${this.url}${query}.json`, options);

        return this.fetchJson(url)
            .then(json => {
                if (json.summary.numResults < 1) throw('notfound');
                return json.results;
            })
    },

    suggest(address, datalist)  {
        this.datalist = datalist;
        this.fetchResults(address,{ typeahead: true })
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

L.Search = L.Control.extend({
    initialize(options) {
        L.setOptions(this, options || {});
    },

    onAdd(map)  {
        const datalistId = map.getContainer().id + '_dl';
        const container = L.DomUtil.create('div', 'locator-search');
        const input = L.DomUtil.create('input', null, container);
        input.type = 'text';
        input.setAttribute('list', datalistId);
        L.DomEvent.on(input, 'input', function (e) {
            const v = e.target.value;
            if (v.length >= 2)  {
                this._geocoder.suggest(e.target.value, datalist);
            }
        }, map);
        L.DomEvent.on(input, 'change', function (e) {
            const val = e.target.value,
                opts = datalist.childNodes;
            e.target.value = '';
            container.classList.remove('open');
            for (let i = 0; i < opts.length; i++) {
                if (val.startsWith(opts[i].innerText)) {
                    this._geocoder.lookup(opts[i].dataset.id);
                    return;
                }
            }
            this.find(val);
        }, map);
        const button = L.DomUtil.create('button', null, container);
        button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M505 442.7L405.3 343c-4.5-4.5-10.6-7-17-7H372c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6.1-34zM208 336c-70.7 0-128-57.2-128-128 0-70.7 57.2-128 128-128 70.7 0 128 57.2 128 128 0 70.7-57.2 128-128 128z"/></svg>';
        button.title = 'Search';
        L.DomEvent.on(button, 'click', function(e) {
            e.preventDefault();
            this.toggle();
        }, this);
        const datalist = L.DomUtil.create('datalist', null, container);
        datalist.id = datalistId;
        return container;
    },

    toggle()  {
        const c = this.getContainer(),
            cc = c.classList,
            bOpen = cc.contains('open');
        cc.toggle('open');
        if (! bOpen)  {
            c.children[0].focus();
        }
    },
});

L.search = function(options) {
    return new L.Search(options);
};

L.Map.include({
    find(address) {
        this._geocoder.geocode(address);
        return this;
    },

    geocoder(name, options)  {
        this._geocoder = new L.geo[name](this, options);
        return this;
    },

    finder(options)  {
        this.addControl(L.search(options));
    }
});

L.Map.addInitHook(function() {
    this.geocoder('Nominatim', {});
});
