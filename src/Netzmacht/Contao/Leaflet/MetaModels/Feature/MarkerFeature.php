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
     * @param Item      $item
     *
     * @return LatLng
     */
    protected function getCoordinates(Item $item)
    {
        if ($this->model->coordinates == 'separate') {
            $latAttribute = $this->getAttribute('latitudeAttribute', $item);
            $lngAttribute = $this->getAttribute('longitudeAttribute', $item);

            return new LatLng(
                $item->get($latAttribute->getColName()),
                $item->get($lngAttribute->getColName())
            );
        }

        $attribute = $this->getAttribute('coordinatesAttribute', $item);

        return LatLng::fromString($item->get($attribute->getColName()));
    }

    /**
     * @param Item             $item
     * @param DefinitionMapper $mapper
     *
     * @return Marker
     */
    protected function buildMarker(Item $item, DefinitionMapper $mapper)
    {
        $metaModel   = $item->getMetaModel();
        $coordinates = $this->getCoordinates($item);
        $settings    = $this->getRenderSettings($metaModel);

        $icon       = $this->getIcon($item, $mapper);
        $popup      = $this->getPopupContent($item, $settings);
        $identifier = sprintf('mm_%s_%s_marker', $metaModel->getTableName(), $item->get('id'));
        $marker     = new Marker($identifier, $coordinates);

        if ($this->model->options) {
            $marker->setOptions((array)json_decode($this->model->options, true));
        }

        if ($icon) {
            $marker->setIcon($icon);
        }

        if ($popup) {
            $marker->setPopupContent($popup);
        }

        // TODO: Attributes mapping

        return $marker;
    }
}
