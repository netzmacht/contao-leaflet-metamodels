<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Renderer;

use MetaModels\IItem as Item;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Type\LatLng;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Definition\UI\Marker;
use Netzmacht\LeafletPHP\Plugins\Omnivore\OmnivoreLayer;

/**
 * MarkerRenderer renders a map marker from a MetaModels item.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Renderer
 */
class MarkerRenderer extends AbstractRenderer
{

    /**
     * {@inheritdoc}
     */
    public function prepare(
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $deferred = false
    ) {
        if ($deferred != $this->model->deferred) {
            return;
        }

        $values = array();

        $this->loadFallbackIcon($mapper);

        if ($this->model->iconAttribute) {
            $attribute = $metaModel->getAttributeById($this->model->iconAttribute);

            if (!$attribute) {
                return;
            }

            /** @var Item $item */
            foreach ($items as $item) {
                $value = $item->get($attribute->getColName());

                if ($value) {
                    $values[$item->get('id')] = is_array($value) ? $value['id'] : $value;
                }
            }

            $this->preLoadIcons($values, $mapper);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        LatLngBounds $bounds = null,
        $deferred = false
    ) {
        if ($this->model->deferred == $deferred) {
            $marker  = $this->buildMarker($item, $parentId);
            $feature = $mapper->convertToGeoJsonFeature($marker, $this->model);

            if ($feature) {
                $featureCollection->addFeature($feature, true);
            }
        }
    }

    /**
     * Build the marker.
     *
     * @param Item   $item     The metamodel item.
     * @param string $parentId Id of the parent layer.
     *
     * @return Marker
     */
    protected function buildMarker(Item $item, $parentId)
    {
        $metaModel   = $item->getMetaModel();
        $coordinates = $this->getCoordinates($item);

        if (!$coordinates) {
            return null;
        }

        $settings   = $this->getRenderSettings($metaModel);
        $icon       = $this->getIcon($item->get('id'));
        $popup      = $this->getPopupContent($item, $settings);
        $identifier = sprintf('%s_%s_%s_marker', $parentId, $metaModel->getTableName(), $item->get('id'));
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
}
