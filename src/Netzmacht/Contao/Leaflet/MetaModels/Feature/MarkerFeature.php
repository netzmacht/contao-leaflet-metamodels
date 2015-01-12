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
class MarkerFeature extends AbstractFeature implements LoadsReferred
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

    /**
     * Get coordinates for the given metamodel item.
     *
     * @param Item $item The MetaModel item.
     *
     * @return LatLng|null
     */
    protected function getCoordinates(Item $item)
    {
        if ($this->model->coordinates == 'separate') {
            $latAttribute = $this->getAttribute('latitudeAttribute', $item);
            $lngAttribute = $this->getAttribute('longitudeAttribute', $item);

            $lat = $item->get($latAttribute->getColName());
            $lng = $item->get($lngAttribute->getColName());

            if (!strlen($lat) || !strlen($lng)) {
                return null;
            }

            return new LatLng($lng, $lng);
        }

        $attribute = $this->getAttribute('coordinatesAttribute', $item);
        $value     = $item->get($attribute->getColName());

        if (strlen($value)) {
            return LatLng::fromString($value);
        }

        return null;
    }

    /**
     * Build the marker.
     *
     * @param Item             $item   The metamodel item.
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return Marker
     */
    protected function buildMarker(Item $item, DefinitionMapper $mapper)
    {
        $metaModel   = $item->getMetaModel();
        $coordinates = $this->getCoordinates($item);

        if (!$coordinates) {
            return null;
        }

        $settings   = $this->getRenderSettings($metaModel);
        $icon       = $this->getIcon($item, $mapper);
        $popup      = $this->getPopupContent($item, $settings);
        $identifier = sprintf('mm_%s_%s_marker', $metaModel->getTableName(), $item->get('id'));
        $marker     = new Marker($identifier, $coordinates);

        if ($this->model->options) {
            $marker->setOptions((array) json_decode($this->model->options, true));
        }

        if ($icon) {
            $marker->setIcon($icon);
        }

        if ($popup) {
            $marker->setPopupContent($popup);
        }

        // @codingStandardsIgnoreStart
        // TODO: Attributes mapping
        // @codingStandardsIgnoreEnd

        return $marker;
    }
}
