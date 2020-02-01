/*!
 * Locator 1.0.0
 * (c) 2020 Sjaak Priester, Amsterdam
 * MIT License
 * https://github.com/sjaakp/yii2-locator
 * https://sjaakpriester.nl
 */
!function(){"use strict";!function(t){if(t&&"undefined"!=typeof window){var e=document.createElement("style");e.setAttribute("type","text/css"),e.innerHTML=t,document.head.appendChild(e)}}(".sprite-marker{display:flex;align-items:center;justify-content:center;color:var(--fa-secondary-color)}@keyframes locator-spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}.locator-loading{width:1.5em;animation:locator-spin 2s infinite linear}"),L.TouchDragHandler=L.Handler.extend({addHooks:function(){this._map.dragging.disable(),this._map.tap&&this._map.tap.disable(),this._map._container.addEventListener("touchstart",this._handleTouch),this._map._container.addEventListener("touchend",this._handleTouch),L.DomEvent.on(this._map._container,"click",this._handleTouch,this)},removeHooks:function(){this._map.dragging.enable(),this._map.tap&&this._map.tap.enable(),this._map._container.removeEventListener("touchstart",this._handleTouch),this._map._container.removeEventListener("touchend",this._handleTouch),L.DomEvent.off(this._map._container,"click",this._handleTouch,this)},_handleTouch:function(t){return"touchstart"===t.type&&1===t.touches.length?(L.DomUtil.addClass(t.target,"leaflet-touchdrag"),this._map.dragging.disable()):(L.DomUtil.removeClass(t.target,"leaflet-touchdrag"),this._map.dragging.enable()),!0}}),L.DotMarker=L.CircleMarker.extend({initialize:function(t,e){void 0===e&&(e={});var i=e.className?e.className+" ":"";L.CircleMarker.prototype.initialize.call(this,t,Object.assign(e,{radius:8,weight:2,fillOpacity:1,className:i+"dot-marker"}))}}),L.dotMarker=function(t,e){return void 0===e&&(e={}),new L.DotMarker(t,e)},L.SpriteMarker=L.Marker.extend({initialize:function(t,e){void 0===e&&(e={});var i=e.className?e.className+" ":"";e.icon=L.divIcon({html:e.html||!1,iconSize:e.iconSize||24,bgPos:e.bgPos||null,className:i+"sprite-marker"}),L.Marker.prototype.initialize.call(this,t,e)}}),L.spriteMarker=function(t,e){return void 0===e&&(e={}),new L.SpriteMarker(t,e)},L.Map.addInitHook((function(){var t=this;if(null!==this.options.scale){var e=this.options.scale,i=1&e,n=2&e;this.addControl(L.control.scale({metric:i,imperial:n}))}this.options.touchdrag&&this.addHandler("touchdrag",L.TouchDragHandler),this.on("geofound",(function(e){t.disarm()}),this)})),L.Map.include({_markers:null,_armed:!1,getMarkers:function(){return this._markers||(null!==this.options.cluster?(this._markers=L.markerClusterGroup(this.options.cluster),this.addLayer(this._markers)):this._markers=this),this._markers},arm:function(t){return this._armed||(L.DomUtil.addClass(this.getContainer(),"locator-armed"),this.options.marker=t,this.on("click",(function(e){return this.disarm(),this.addMarker(e.latlng,t)})),this._armed=!0),this},disarm:function(){return this._armed&&(L.DomUtil.removeClass(this.getContainer(),"locator-armed"),this.off("click"),this._armed=!1),this},addFeature:function(t){return this.getMarkers().addLayer(L.geoJSON(t,{pointToLayer:function(t,e){return this.newMarker(e,Object.assign({},this.options.marker,t.properties))}.bind(this)})),this},monitorZoom:function(t){return this.on("zoomend",(function(e){document.getElementById(t).value=e.target.getZoom()})).fire("zoomend")},monitorCenter:function(t){return this.on("moveend",(function(e){var i=e.target.getCenter();document.getElementById(t).value=JSON.stringify({type:"Point",coordinates:[i.lng,i.lat]})})).fire("moveend")},newMarker:function(t,e){void 0===e&&(e=null),null==e&&(e=this.options.marker);var i=e.type||"Marker";"Marker"===i&&e.icon&&(e.icon=L.icon(e.icon));var n=new L[i](t,e).on("moveend",(function(t){var e=t.target.options.monitor;e&&(document.getElementById(e).value=JSON.stringify(t.target.toGeoJSON()))})).on("click",(function(t){var e=this.options.urlTemplate;if(e){var i=e.match(/{\w+}/g);if(i&&i.forEach((function(i){e=e.replace(i,t.target.options[i.slice(1,-1)])}),this),null!==this.options.popup){var n=this.options.popup,r=L.popup(n).setLatLng(t.latlng).setContent('<svg class="locator-loading" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M304 48c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.49 48 48zm-48 368c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zm208-208c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.49-48-48-48zM96 256c0-26.51-21.49-48-48-48S0 229.49 0 256s21.49 48 48 48 48-21.49 48-48zm12.922 99.078c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.491-48-48-48zm294.156 0c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48c0-26.509-21.49-48-48-48zM108.922 60.922c-26.51 0-48 21.49-48 48s21.49 48 48 48 48-21.49 48-48-21.491-48-48-48z"/></svg>').openOn(this);fetch(e).then((function(t){return t.text()})).then((function(t){return r.setContent(t)}))}else window.location=e}}),this);return e.title&&n.bindTooltip(e.title),n},addMarker:function(t,e){var i=this.newMarker(t,e);return this.addLayer(i),this.marker=i,i.fire("moveend"),this}})}();
//# sourceMappingURL=locator.js.map
