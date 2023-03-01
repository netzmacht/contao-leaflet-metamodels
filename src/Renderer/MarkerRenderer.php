<?php

/**
 * Contao Leaflet MetaModels integration.
 *
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2019 netzmacht David Molineus
 * @license    LGPL 3.0-or-later https://github.com/netzmacht/contao-leaflet-metamodels/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace Netzmacht\Contao\Leaflet\MetaModels\Renderer;

use MetaModels\IItem as Item;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Filter\BboxFilter;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\Request;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Value\LatLng;
use Netzmacht\LeafletPHP\Definition\UI\Marker;

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
        Request $request = null,
        $deferred = false
    ): void {
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
        Request $request = null,
        $deferred = false
    ): void {
        if ($this->model->deferred == $deferred) {
            $marker = $this->buildMarker($item, $parentId);

            if ($marker === null) {
                return;
            }

            $filter = $request && $request->getFilter();
            if ($this->layerModel->boundsMode === 'fit' && $filter instanceof BboxFilter) {
                if (!$filter->getBounds()->contains($marker->getLatLng())) {
                    return;
                }
            }

            $feature = $mapper->convertToGeoJsonFeature($marker, $this->model);
            if ($feature) {
                $featureCollection->addFeature($feature);
            }
        }
    }

    /**
     * Build the marker.
     *
     * @param Item   $item     The metamodel item.
     * @param string $parentId Id of the parent layer.
     *
     * @return Marker|null
     */
    protected function buildMarker(Item $item, $parentId): ?Marker
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
     * Get coordinates for the given MetaModel item.
     *
     * @param Item $item The MetaModel item.
     *
     * @return LatLng|null
     */
    protected function getCoordinates(Item $item): ?LatLng
    {
        if ($this->model->coordinates === 'separate') {
            $latAttribute = $this->getAttribute('latitudeAttribute', $item);
            $lngAttribute = $this->getAttribute('longitudeAttribute', $item);

            $lat = $item->get($latAttribute->getColName());
            $lng = $item->get($lngAttribute->getColName());


            if ($lat === '' || $lat === null || $lat === '' || $lat === null) {
                return null;
            }

            return new LatLng($lat, $lng);
        }

        $attribute = $this->getAttribute('coordinatesAttribute', $item);
        $value     = $item->get($attribute->getColName());

        if (strlen($value)) {
            return LatLng::fromString($value);
        }

        return null;
    }
}
