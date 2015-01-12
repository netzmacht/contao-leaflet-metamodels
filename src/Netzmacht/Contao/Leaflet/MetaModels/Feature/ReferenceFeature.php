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
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\HasPopup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Definition\UI\Marker;
use Netzmacht\LeafletPHP\Definition\Vector\Path;

class ReferenceFeature extends AbstractFeature
{
    public function apply(IItem $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $model = $this->fetchReferenceModel($item);

        if ($model) {
            $definition = $mapper->handle($model, $bounds, $this->getElementId($item));

            $this->applyPopup($item, $definition);
            $this->applyMarker($item, $mapper, $definition);
            $this->applyStyle($item, $mapper, $definition);

            $parentLayer->addLayer($definition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function applyGeoJson(
        IItem $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    ) {
        // TODO: Implement applyGeoJson() method.
    }


    protected function fetchReferenceModel(IItem $item)
    {
        $reference = $this->getAttribute('referenceAttribute', $item)->getColName();
        $reference = $item->get($reference);

        switch ($this->model->referenceType) {
            case 'layer':
                return LayerModel::findActiveByPK($reference);
                break;

            case 'vector':
                return VectorModel::findActiveByPK($reference);
                break;

            case 'marker':
                return VectorModel::findActiveByPK($reference);
                break;

            default:
                return null;
        }
    }

    /**
     * @param Item             $item
     * @param DefinitionMapper $mapper
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
     * @param IItem $item
     * @param       $definition
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
     * @param IItem            $item
     * @param DefinitionMapper $mapper
     * @param                  $definition
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
     * @param IItem            $item
     * @param DefinitionMapper $mapper
     * @param                  $definition
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
     * @param IItem $item
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
