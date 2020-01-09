<?php
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

namespace sjaakp\locator;

use Yii;
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
     * @var bool whether to use 'fly-animation' in placeMarker
     */
    public $fly = false;

    /**
     * @var array options for default marker
     */
    public $marker = [ 'type' => 'DotMarker' ];

    public $defaultOptions = [
        'center' => [51.4777, -0.0012], // London, Greenwich Observatory
        'zoom' => 13,
    ];

    /**
     * @var string|array
     * - string: name of tile layer preset
     * - array: first item is name, rest is options,
     *      or: array is complete tile config, with 'url' and optionally 'type'
     * @link https://leafletjs.com/reference-1.6.0.html#tilelayer-option
     */
    public $tile = 'OpenStreetMap';

    /**
     * @param string | array $data @see tile
     * @return $this
     * @throws InvalidConfigException
     */
    public function tileLayer($data)
    {
        if (is_string($data)) $data = [$data];
        if (isset($data[0])) {   // named tile
            $names = explode('.', array_shift($data));
            $name = $names[0];
            $variant = $names[1] ?? '';

            try {
                $tileObject = Yii::createObject([
                    'class' => "sjaakp\locator\\tiles\Tile$name",
                    'variant' => $variant
                ]);
            }
            catch (InvalidConfigException $e)  {
                throw new InvalidConfigException("Locator: '$name' is unknown tile provider.");
            }
            $tile = $tileObject->run($data);
        }
        else {
            $url = ArrayHelper::remove($data, 'url');
            if (empty($url))    {
                throw new InvalidConfigException("Locator: url of provider is not set.");
            }
            $type = ArrayHelper::remove($data, 'type', 'tileLayer');
            $opts = empty($data) ? '{}' : Json::encode($data);
            $tile = "L.$type(\"$url\", $opts)";
        }
        $this->_js[] = ".addLayer($tile)";
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
     * @param $options
     * @return $this
     * @throws InvalidConfigException
     * - Nominatim: $options = 'Nominatim' (doesn't support autocomplete)
     * - GeoNames: $options = [ 'GeoNames', 'username' => '...', 'maxRows' => 20, ... ]
     * - Here: $options = [ 'Here', 'apiKey' => '...', 'maxresults' => 20, ... ]
     * - TomTom: $options = [ 'TomTom', 'key' => '...', 'limit' => 20 ]
     * - Kadaster: $options = 'Kadaster' | [ 'Kadaster', 'rows' => 20 ]
     * limit-parameter is always optional
     */
    public function geocoder($options)
    {
        $gcs = [ 'Nominatim', 'GeoNames', 'Kadaster', 'Here', 'TomTom' ];

        if (is_string($options)) $options = [ $options ];
        $name = array_shift($options);
        if (! in_array($name, $gcs))
        {
            throw new InvalidConfigException("Locator: '$name' is unknown geocoder.");
        }
        $options = Json::encode($options);
        $this->_js[] = ".initGeocoder('$name', $options)";
        return $this;
    }

    /**
     * @param array $options
     * @param null $geocoder
     * @return $this
     * @throws InvalidConfigException
     */
    public function finder($options = [], $geocoder = null)
    {
        if ($geocoder) $this->geocoder($geocoder);
        $inputOptions = ArrayHelper::remove($options, 'inputOptions', []);
        $buttonOptions = ArrayHelper::remove($options, 'buttonOptions', []);
        $buttonLabel = ArrayHelper::remove($buttonOptions, 'label', 'Find');
        $id = $this->getId();
        $inputId = $id . '_fi';
        $buttonId = $id . '_fb';
        $datalistId = $id . '_fd';
        $inputOptions['id'] = $inputId;
        $inputOptions['list'] = $datalistId;
        $buttonOptions['id'] = $buttonId;
        $this->_html[] = Html::textInput($inputId, '', $inputOptions);
        $this->_html[] = Html::button($buttonLabel, $buttonOptions);
        $this->_html[] = Html::tag('datalist', '', [ 'id' => $datalistId ]);
        $this->_js[] = ".initFinder('$buttonId','$inputId','$datalistId')";
        return $this;
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

        foreach([ 'marker', 'cluster', 'popup', 'scale', 'urlTemplate', 'fly' ] as $prop)
        {
            $this->leafletOptions[$prop] = $this->$prop;
        }

        $opts = str_replace('[]', '{}', Json::encode(array_merge($this->defaultOptions, $this->leafletOptions)));
        $call = "window.$var=L.map('$id', $opts)";
        array_unshift($this->_js, $call);

        $view->registerCss('.sprite-marker{display:flex;align-items:center;justify-content:center;color:var(--fa-secondary-color);}');

        $view->registerJs(implode('', $this->_js) . ';');
        echo implode('', $this->_html);
    }

    protected $_js = [];
    protected $_html = [];

    protected function addInput($model, $attribute)    {
        $this->_html[] = Html::activeHiddenInput($model, $attribute);
    }
}
