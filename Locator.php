<?php
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

namespace sjaakp\locator;

use yii\base\Model;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class Locator
 * @package sjaakp\locator
 */
class Locator extends Widget   {
    /**
     * @var int | string | false
     * Height of the Leaflet map.
     * - int        height in pixels
     * - string     valid CSS height (f.i. in ems)
     * - false      height is not set; caution: the height MUST be set by some other means (CSS), otherwise
     *              the map will not appear.
     */
    public $height = 400;

    /**
     * @var array
     * HTML options of the map container.
     * Use this to explicitly set the ID.
     */
    public $options = [];

    /**
     * @var array
     * Options of the map.
     * @link https://leafletjs.com/reference-1.6.0.html#map-option
     */
    public $leafletOptions = [];

    /**
     * @var null|array options for MarkerClusterer
     * if null: no clustering
     * [] (empty array): cluster with default options
     * @link https://github.com/Leaflet/Leaflet.markercluster#options
     */
    public $cluster;

    /**
     * @var null|array options for popup
     * if null: no popup
     * [] (empty array): popup with default options
     * popup has one extra option:
     *      - 'loading': the HTML shown while loading; default: '<i class="far fa-spinner fa-spin fa-lg"></i>'
     * @link https://leafletjs.com/reference-1.6.0.html#popup-option
     */
    public $popup;

    const SCALE_METRIC = 1;
    const SCALE_IMPERIAL = 2;
    const SCALE_BOTH = self::SCALE_METRIC | self::SCALE_IMPERIAL;

    /**
     * @var null|int SCALE_xxx
     * display scale control; null: no scale
     * @link https://leafletjs.com/reference-1.6.0.html#control-scale
     */
    public $scale = self::SCALE_METRIC;

    /**
     * @var string template for url used when marker is clicked.
     *  If popup is set, popup is shown with contents from url, otherwise jump is performed to url.
     *  If not set, nothing happens after marker click.
     *  '{xxx}' gets replaced by Marker option <xxx>
     *  typical use: urlTemplate = 'view/{id}'
     */
    public $urlTemplate;

    /**
     * @var array options for default marker
     */
    public $marker = [ 'type' => 'DotMarker' ];

    public $defaultOptions = [
        'center' => [51.505, -0.09],
        'zoom' => 13,
    ];

    /**
     * @var string|array
     * - string: name of tile layer preset
     * - array: first item is name, rest is options,
     *      or: array is complete tile config
     *
     */
    public $tile = 'OpenStreetMap';

    /**
     * @var array tile layer providers
     *  - '<property>' replace with own property, remove property from setting (useful for apikeys)
     *  - '{provider.property}' replace with property of other provider (attribution shortcut)
     *  - '{key:default}' replace with variant[key] or default
     */
    public $providers = [
        'OpenStreetMap' => [
            'url' => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'maxZoom' => 18,
            'attribution' => '&copy; <a href="//openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
        ],
        'OSMBlackAndWhite' => [
            'url' => '//toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png',
            'attribution' => '{OpenStreetMap.attribution}, Tiles <a href="//www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a>',
            'opacity' => .75,
        ],
        'OSMHot' => [
            'url' => '//{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
            'maxZoom' => 18,
            'attribution' => '{OpenStreetMap.attribution}, Tiles courtesy of <a href="//www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
        ],
        'EsriWorld' => [
            'url' => '//server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',
            'attribution' => '&copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (HongKong), and the GIS User Community'
        ],
        'Wikimedia' => [
            'url' => '//maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png',
            'attribution' => '<a href="//wikimediafoundation.org/wiki/Maps_Terms_of_Use" target="_blank">Wikimedia</a>',
            'minZoom' => 1
        ],
        'Carto' => [    // @link https://github.com/CartoDB/basemap-styles
            'url' => '//cartodb-basemaps-{s}.global.ssl.fastly.net/{scheme:light_all}/{z}/{x}/{y}.png',
            'attribution' => '{OpenStreetMap.attribution} &copy; <a href="//carto.com/attribution/" target="_blank">Carto</a>',
            'subdomains' => 'abcd',
            'maxZoom' => 20,
            'variants' => [
                'light' => [ 'scheme' => 'light_all'],
                'dark' => [ 'scheme' => 'dark_all'],
                'voyager' => [ 'scheme' => 'rastertiles/voyager'],
            ]
        ],
        'OpenMapSurfer' => [
            'url' => '//maps.heigit.org/openmapsurfer/tiles/{scheme:roads}/webmercator/{z}/{x}/{y}.png',
            'maxZoom' => 19,
            'attribution' => 'Imagery from <a href="//giscience.uni-hd.de/" target="_blank">GIScience Research Group @ University of Heidelberg</a> | Map data',
            'variants' => [
                'Roads' => [ 'scheme' => 'roads' ],
                'Hybrid' => [ 'scheme' => 'hybrid' ],
                'AdminBounds' => [ 'scheme' => 'adminb' ],
                'ContourLines' => [ 'scheme' => 'asterc' ],
                'Hillshade' => [ 'scheme' => 'asterh' ],
            ]
        ],
        'OpenTopoMap' => [ // @link https://opentopomap.org/about#verwendung
            'url'=> 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
            'maxZoom' => 17,
            'subdomains' => 'abc',
            'attribution' => '{OpenStreetMap.attribution}, <a href="//viewfinderpanoramas.org" target="_blank">SRTM</a> | Map style: &copy; <a href="//opentopomap.org" target="_blank">OpenTopoMap</a> (<a href="//creativecommons.org/licenses/by-sa/3.0/" target="_blank">CC-BY-SA</a>)',
        ],
        'Stamen' => [
            'url' => 'https://stamen-tiles-{s}.a.ssl.fastly.net/{scheme:toner}/{z}/{x}/{y}.{ext:png}',
            'attribution' => 'Map tiles by <a href="//stamen.com" target="_blank">Stamen Design</a>, <a href="//creativecommons.org/licenses/by/3.0" target="_blank">CC BY 3.0</a> &mdash; Map data {OpenStreetMap.attribution}',
            'minZoom' => '{minZ:0}',
            'maxZoom' => '{maxZ:20}',
            'subdomains' => 'abcd',
            'variants' => [
                'Toner' => [ 'scheme' => 'toner' ],
                'TonerBackground' => [ 'scheme' => 'toner-background' ],
                'TonerHybrid' => [ 'scheme' => 'toner-hybrid' ],
                'TonerLines' => [ 'scheme' => 'toner-lines' ],
                'TonerLabels' => [ 'scheme' => 'toner-labels' ],
                'TonerLite' => [ 'scheme' => 'toner-lite' ],
                'Watercolor' => [ 'scheme' => 'watercolor', 'ext' => 'jpg', 'minZ' => 1, 'maxZ' => 16  ],
                'Terrain' => [ 'scheme' => 'terrain', 'maxZ' => 18  ],
                'TerrainBackground' => [ 'scheme' => 'terrain-background', 'maxZ' => 18  ],
                'TerrainLabels' => [ 'scheme' => 'terrain-labels', 'maxZ' => 18  ],
            ]
        ],
        'TomTom' => [   // use: tileLayer([ 'TomTom', 'apiKey' => '... your Api Key ...' ])
                        // @link https://developer.tomtom.com/maps-api
            'url' => 'https://{s}.api.tomtom.com/map/1/tile/{scheme:basic}/main/{z}/{x}/{y}.png?key=<apiKey>',
            'maxZoom' => 22,
            'subdomains' => 'abcd',
            'attribution' => '<a href="//tomtom.com" target="_blank">&copy;  1992-2020 TomTom.</a>',
            'variants' => [
                'Basic' => [ 'scheme' => 'basic' ],
                'Hybrid' => [ 'scheme' => 'hybrid' ],
                'Labels' => [ 'scheme' => 'labels' ],
            ],
        ],
        'Here' => [ // use: tileLayer([ 'Here', 'apiKey' => '... your Api Key ...', 'language' => 'eng' ])
                    // @link https://developer.here.com/documentation/map-tile/dev_guide/topics/request-constructing.html
            'url' => 'https://{s}.{base:base}.maps.ls.hereapi.com/maptile/2.1/{resource:maptile}/newest/{scheme:normal.day}/{z}/{x}/{y}/256/png?apiKey=<apiKey>&lg=<language>',
            'maxZoom' => 20,
            'subdomains' => '1234',
            'attribution' => 'Map &copy; 1987-2020 <a href="//developer.here.com" target="_blank">HERE</a>',
            'variants' => [
                'NormalDay' => [ 'scheme' => 'normal.day' ],
                'NormalDayGrey' => [ 'scheme' => 'normal.day.grey' ],
                'NormalDayTransit' => [ 'scheme' => 'normal.day.transit' ],
                'NormalDayTraffic' => [ 'scheme' => 'normal.traffic.day', 'base' => 'traffic', 'resource' => 'traffictile' ],
                'NormalNight' => [ 'scheme' => 'normal.night' ],
                'NormalNightGrey' => [ 'scheme' => 'normal.night.grey' ],
                'NormalNightTransit' => [ 'scheme' => 'normal.night.transit' ],
                'NormalNightTraffic' => [ 'scheme' => 'normal.traffic.night', 'base' => 'traffic', 'resource' => 'traffictile' ],
                'ReducedDay' => [ 'scheme' => 'reduced.day' ],
                'ReducedNight' => [ 'scheme' => 'reduced.night' ],
                'BasicMap' => [ 'resource' => 'basetile' ],
                'MapLabels' => [ 'resource' => 'labeltile' ],
                'TrafficFlow' => [ 'base' => 'traffic', 'resource' => 'flowtile' ],
                'HybridDay' => [ 'base' => 'aerial', 'scheme' => 'hybrid.day' ],
                'HybridDayGrey' => [ 'base' => 'aerial', 'scheme' => 'hybrid.grey.day' ],
                'HybridDayTransit' => [ 'base' => 'aerial', 'scheme' => 'hybrid.day.transit' ],
                'HybridDayTraffic' => [ 'base' => 'traffic', 'scheme' => 'hybrid.traffic.day', 'resource' => 'traffictile' ],
                'PedestrianDay' => [ 'scheme' => 'pedestrian.day' ],
                'PedestrianNight' => [ 'scheme' => 'pedestrian.night' ],
                'SatelliteDay' => [ 'base' => 'aerial', 'scheme' => 'satellite.day' ],
                'TerrainDay' => [ 'base' => 'aerial', 'scheme' => 'terrain.day' ],
            ]
        ],
        'Kadaster' => [   // Netherlands
            'url' => '//geodata.nationaalgeoregister.nl/tiles/service/tms/1.0.0/brtachtergrondkaart{scheme:}/EPSG:3857/{z}/{x}/{y}.png',
            'tms' => true,
            'zoomOffset' => -1,
            'minZoom' => 7,
            'maxZoom' => 19,
            'boundVec' => [[50.5, 0.0], [54, 10.4]],
            'attribution' => '&copy; <a href="//kadaster.nl" target="_blank">Kadaster</a><span class="printhide">, (<a href="//creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC-BY-SA</a>)</span>.',
            'variants' => [
                'grijs' => [ 'scheme' => 'grijs' ],
                'pastel' => [ 'scheme' => 'pastel' ],
            ]
        ],
        'Amsterdam' => [
            'url' => '//t{s}.data.amsterdam.nl/topo_wm{scheme:}/{z}/{x}/{y}.png',
            'minZoom' => 11,
            'maxZoom' => 21,
            'boundVec' => [[52.1698, 4.48663], [52.6135, 5.60867]],
            'subdomains' => '1234',
            'attribution' => '&copy; <a href="//map.data.amsterdam.nl/" target="_blank">amsterdam.nl</a>',
            'variants' => [
                'light' => [ 'scheme' => '_light' ],
                'zw' => [ 'scheme' => '_zw' ],
            ]
        ],
    ];

    /**
     * @param string|array $data
     * @return $this
     * @throws InvalidConfigException
     */
    public function tileLayer($data)   {
        if (is_string($data)) $data = [$data];
        if (isset($data[0]))    {   // named tile
            $names = explode('.', array_shift($data));
            $name = $names[0];
            if (! isset($this->providers[$name])) {
                throw new InvalidConfigException("Locator: '$name' is unknown tile provider.");
            }
            $data = array_merge($this->providers[$name], $data);
            $variantData = ArrayHelper::remove($data, 'variants');
            if ($variantData)  {
                if (count($names) > 1)  {
                    $variant = $names[1];
                    if (! isset($variantData[$variant])) {
                        throw new InvalidConfigException("Locator: '$variant' is unknown tile variant of provider '$name'.");
                    }
                    $variantData = $variantData[$variant];
                }
                foreach ($data as $key => $val) {
                    if (is_numeric($val) || is_string($val))   {
                        $rep = preg_replace_callback('/{(\w+):([\w.]*)}/', function($m) use($variantData) {
                            return $variantData[$m[1]] ?? $m[2];
                        }, $val);
                        $data[$key] = in_array($key, [ 'minZoom', 'maxZoom', 'zoomOffset' ]) ? $rep + 0 : $rep;
                    }
                }
            }
            foreach ($data as $key => $val) {
                if (is_int($val) || is_string($val))   {
                    $rep = preg_replace_callback('/{(\w+).(\w*)}/', function($m) {
                        return $this->providers[$m[1]][$m[2]];
                    }, $val);
                    $data[$key] = is_int($val) ? (int) $rep : $rep;
                }
            }
        }   // end if named tile
        $url = preg_replace_callback('/<(\w+)>/', function($m) use(&$data) {
            return ArrayHelper::remove($data, $m[1]);
        }, ArrayHelper::remove($data, 'url'));

        if (empty($url))    {
            throw new InvalidConfigException("Locator: url of provider is not set.");
        }
        $opts = empty($data) ? '{}' : Json::encode($data);
        $this->_js[] = ".addLayer(L.tileLayer(\"$url\", $opts))";
        return $this;
    }

    /**
     * @param float | array $lat
     * @param float | null $lng
     * Set the center.
     * - $lat is an array of [ lat, lng ]
     * - $lat and $lng are both floats
     * @return $this
     */
    public function center($lat, $lng = null)  {
        if (! is_array($lat)) {
            $lat = [$lat, $lng];
        }
        $this->leafletOptions['center'] = $lat;
        return $this;
    }

    /** @param $model Model
     * Set map center to value of $attribute in $model; should be a GeoJSON Feature.
     * @link http://geojson.org/geojson-spec.html#feature-objects
     * @return $this
     */
    public function modelCenter($model, $attribute) {
        $feature = Html::getAttributeValue($model, $attribute);
        if ($feature) {
            $feat = Json::decode($feature);
            if (isset($feat['geometry'])) $feat = $feat['geometry'];
            $this->center(array_reverse($feat['coordinates']));  // swap coordinates; Leaflet uses lat-lng, GeoJSON uses lng-lat
        }
        return $this;
    }

    /** @param $model \yii\db\BaseActiveRecord
     * Set map center to value of $attribute in $model and link activeHiddenInput.
     * Should be a GeoJSON Feature. @link http://geojson.org/geojson-spec.html#feature-objects
     * @return $this
     */
    public function activeCenter($model, $attribute) {
        $idInput = Html::getInputId($model, $attribute);
        $this->_js[] = ".monitorCenter('$idInput')";
        $this->addInput($model, $attribute);
        return $this->modelCenter($model, $attribute);
    }

    /**
     * @param $z
     * @return $this
     * Set map zoom to $z
     */
    public function zoom($z)    {
        $this->leafletOptions['zoom'] = $z;
        return $this;
    }

    /** @param $model Model
     * @return $this
     * Set map zoom to value of $attribute in $model.
     */
    public function modelZoom($model, $attribute)   {
        $z = Html::getAttributeValue($model, $attribute);
        if ($z) $this->zoom($z);
        return $this;
    }

    /** @param $model \yii\db\BaseActiveRecord
     * @return $this
     * Set map zoom to value of $attribute in $model and link activeHiddenInput.
     */
    public function activeZoom($model, $attribute)   {
        $idInput = Html::getInputId($model, $attribute);
        $this->_js[] = ".monitorZoom('$idInput')";
        $this->addInput($model, $attribute);
        return $this->modelZoom($model, $attribute);
    }

    public function feature($feature)  {
        $this->_js[] = ".addFeature($feature)";
        return $this;
    }

    /** @param $model Model
     * @return $this
     * Add feature $attribute in $model to map.
     */
    public function modelFeature($model, $attribute)   {
        $feat = Html::getAttributeValue($model, $attribute);
        if ($feat) $this->feature($feat);
        return $this;
    }

    public function modelFeatures($dataProvider, $attribute)    {
        $features = [];
        foreach ($dataProvider->models as $model) {
            /** @var $model Model */
            $jFeat = Html::getAttributeValue($model, $attribute);
            if ($jFeat) $features[] = $jFeat;
        }
        if (! empty($features)) {
            $feats = '[' . implode(',', $features) . ']';
            $this->feature($feats);
        }
        return $this;
    }

    /**
     * @param float | array | null $lat
     * @param float | null $lng
     * Set the marker.
     * - $lat is null: marker appears at the first click point on the map
     * - $lat is an array of [ lat, lng ]
     * - $lat and $lng are both floats
     * @param array $options options for the marker:
     * - 'type': type of marker, f.i. 'DotMarker'; if not set, the type is 'Marker'
     * - other options are passed to the marker constructor
     * @return $this
     */
    public function marker($lat = null, $lng = null, $options = [])  {
        if (is_numeric($lat) && is_numeric($lng))   {
            $lat = [
                'lat' => $lat,
                'lng' => $lng
            ];
        }
        $loc = Json::encode($lat);
        $opts = empty($options) ? '{}' : Json::encode($options);
        $this->_js[] = ".addMarker($loc, $opts)";
        return $this;
    }

    public function modelMarker($model, $attribute, $options = []) {
        $feature = Html::getAttributeValue($model, $attribute);
        if ($feature)   {
            $feat = Json::decode($feature);
            $feature = array_reverse($feat['geometry']['coordinates']);
            $this->marker($feature, null, $options);        }
        return $this;
    }

    public function activeMarker($model, $attribute, $options = [])    {
        $this->addInput($model, $attribute);
        $idInput = Html::getInputId($model, $attribute);
        $options['draggable'] = true;
        $options['monitor'] = $idInput;
        if (empty(Html::getAttributeValue($model, $attribute)))   {
            $opts = empty($options) ? '{}' : Json::encode($options);
            $this->_js[] = ".arm($opts)";
            return $this;
        }
        return $this->modelMarker($model, $attribute, $options);
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()  {
        if (isset($this->options['id'])) {
            $this->setId($this->options['id']);
        }
        else $this->options['id'] = $this->getId();

        Html::addCssClass($this->options, 'locator');

        if (is_array($this->popup) && ! isset($this->popup['loading'])) {
            $this->popup['loading'] = '<i class="fas fa-spinner fa-spin fa-lg"></i>';
        }

        if ($this->height !== false) {
            $style = '';
            if (isset($this->options['style']))
                $style = $this->options['style'];
            $h = $this->height;
            if (is_integer($h)) $h .= 'px';
            $this->options['style'] = $style . "height:$h;";
        }
        $this->_html[] = Html::tag('div', '', $this->options);

        if ($this->tile) $this->tileLayer($this->tile);
    }

    public function run()   {
        $view = $this->getView();
        $asset = LeafletAsset::register($view);

        if (! is_null($this->cluster))    {
            $pop = array_pop($asset->js);       // ensure our js comes after markercluster.js
            $asset->js[] = '//unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js';
            array_push($asset->js, $pop);
            $asset->css[] = '//unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css';
            $asset->css[] = '//unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css';
        }

        $id = $this->getId();
        $var = 'locator' . str_replace('-', '_', $id);

        foreach([ 'marker', 'cluster', 'popup', 'scale', 'urlTemplate' ] as $prop)
        {
            $this->leafletOptions[$prop] = $this->$prop;
        }

        $opts = str_replace('[]', '{}', Json::encode(array_merge($this->defaultOptions, $this->leafletOptions)));
        $call = "window.$var=L.map('$id', $opts)";
        array_unshift($this->_js, $call);

        $view->registerCss('.sprite-marker{display:flex;align-items:center;justify-content:center;color:var(--fa-secondary-color);}');

        $view->registerJs(implode('', $this->_js) . ";");
        echo implode('', $this->_html);
    }

    protected $_js = [];
    protected $_html = [];

    protected function addInput($model, $attribute)    {
        $this->_html[] = Html::activeHiddenInput($model, $attribute);
    }
}
