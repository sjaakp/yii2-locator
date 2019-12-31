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

L.Map.include({

    find: function(address) {
        return this.findNominatim(address);
    },

    findNominatim: function(address) {
        const server = '//nominatim.openstreetmap.org/search';
        let map = this,
            req1 = new XMLHttpRequest();
        req1.addEventListener('load', function() {
            let resp = JSON.parse(this.response);
            if (resp.length > 0) {
                let found = resp[0],
                    latlng = L.latLng(found.lat, found.lon);
                map.placeMarker(latlng);
            }
            else    {
                map.fire('notfound');
            }
        });
        req1.open('GET', server + '?format=json&q=' + encodeURIComponent(address));
        req1.send();
        return this;
    }
});
