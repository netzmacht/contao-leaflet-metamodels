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

use MetaModels\IItem;
use MetaModels\Item;
use Netzmacht\Contao\Leaflet\Definition\Style;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Model\StyleModel;
use Netzmacht\Contao\Leaflet\Model\VectorModel;
use Netzmacht\Javascript\Type\Value\Expression;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\HasPopup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Definition\UI\Marker;
use Netzmacht\LeafletPHP\Definition\Vector\Path;
use Netzmacht\LeafletPHP\Plugins\Ajax\GeoJsonAjax;

/**
 * Class ReferenceFeature handles the reference feature of a MetaModel item.
 *
 * The reference allows to link to an layer, marker or icon.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Feature
 */
class ReferenceFeature extends AbstractFeature
{
    /**
     * {@inheritdoc}
     */
    public function apply(
        IItem $item,
        LayerGroup $parentLayer,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $parentModel = null
    ) {
        $model = $this->fetchReferenceModel($item);

        if ($model) {
            $definition = $mapper->handle($model, $bounds, $this->getElementId($item));

            $this->applyPopup($item, $definition);
            $this->applyMarker($item, $mapper, $definition);
            $this->applyStyle($item, $mapper, $definition);

            if ($definition instanceof GeoJson) {
                if ($parentModel->onEachFeature) {
                    $definition->setOnEachFeature(new Expression($parentModel->onEachFeature));
                }
            }

            $parentLayer->addLayer($definition);
        }
    }

    /**
     * Fetch the reference model.
     *
     * @param IItem $item The MetaModel item.
     *
     * @return \Model|null
     */
    protected function fetchReferenceModel(IItem $item)
    {
        $reference = $this->getAttribute('referenceAttribute', $item)->getColName();
        $reference = $item->get($reference);

        switch ($this->model->referenceType) {
            case 'layer':
                return LayerModel::findActiveByPK($reference);

            case 'vector':
                return VectorModel::findActiveByPK($reference);

            case 'marker':
                return VectorModel::findActiveByPK($reference);

            default:
                return null;
        }
    }

    /**
     * Get vector style.
     *
     * @param Item             $item   The MetaModel item.
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return Style|null
     */
    protected function getStyle(Item $item, DefinitionMapper $mapper)
    {
        $iconModel = null;

        if ($this->model->iconAttribute) {
            $iconAttribute = $this->getAttribute('styleAttribute', $item);
            $iconId        = $item->get($iconAttribute->getColName());
            $iconModel     = StyleModel::findActiveByPK($iconId);
        }

        if (!$iconModel && $this->model->icon) {
            $iconModel = StyleModel::findByPk($this->model->icon);
        }

        if (!$iconModel) {
            return null;
        }

        return $mapper->handle($iconModel);
    }

    /**
     * Apply the popup.
     *
     * @param IItem $item       The MetaModel item.
     * @param mixed $definition The definition.
     *
     * @return void
     */
    protected function applyPopup(IItem $item, $definition)
    {
        if ($definition instanceof HasPopup) {
            $settings = $this->getRenderSettings($item->getMetaModel());
            $popup    = $this->getPopupContent($item, $settings);

            if ($popup) {
                $definition->bindPopup($popup);
            }
        }
    }

    /**
     * Apply a marker.
     *
     * @param IItem            $item       The MetaModel item.
     * @param DefinitionMapper $mapper     The definition mapper.
     * @param mixed            $definition The definition.
     *
     * @return void
     */
    protected function applyMarker(IItem $item, DefinitionMapper $mapper, $definition)
    {
        if ($definition instanceof Marker) {
            $icon = $this->getIcon($item, $mapper);

            if ($icon) {
                $definition->setIcon($icon);
            }
        }
    }

    /**
     * Apply the path style.
     *
     * @param IItem            $item       The MetaModel item.
     * @param DefinitionMapper $mapper     The definition mapper.
     * @param mixed            $definition The definition.
     *
     * @return void
     */
    protected function applyStyle(IItem $item, DefinitionMapper $mapper, $definition)
    {
        if ($definition instanceof Path) {
            $style = $this->getStyle($item, $mapper);

            if ($style) {
                $style->apply($definition);
            }
        }
    }

    /**
     * Get the element id.
     *
     * @param IItem $item The MetaModel.
     *
     * @return null|string
     */
    protected function getElementId(IItem $item)
    {
        if ($this->model->standalone) {
            return sprintf(
                'mm_%s_%s_ref_%s',
                $item->getMetaModel()->getTableName(),
                $item->get('id'),
                $this->model->id
            );
        }

        return null;
    }
}
