<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Feature;

use MetaModels\IItem as Item;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\LatLng;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Definition\UI\Marker;

/**
 * Class MarkerFeature allows to create a marker for a metamodel item.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Feature
 */
class MarkerFeature extends AbstractFeature implements LoadsDeferred
{
    /**
     * {@inheritdoc}
     */
    public function apply(Item $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $marker = $this->buildMarker($item, $mapper);

        if ($marker) {
            $parentLayer->addLayer($marker);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyGeoJson(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    ) {
        $marker = $this->buildMarker($item, $mapper);

        if ($marker) {
            $featureCollection->addFeature($marker->toGeoJsonFeature());
        }
    }



}
