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
use MetaModels\IMetaModel as MetaModel;
use MetaModels\Render\Setting\Factory;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Model\IconModel;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\Icon;
use Netzmacht\LeafletPHP\Definition\Type\LatLng;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Definition\UI\Marker;

class MarkerFeature extends AbstractFeature
{
    /**
     * {@inheritdoc}
     */
    public function apply(Item $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $metaModel   = $item->getMetaModel();
        $coordinates = $this->getCoordinates($item, $metaModel);

        if ($bounds && !$bounds->contains($coordinates)) {
            return;
        }

        $icon       = $this->getIcon($item, $metaModel, $mapper);
        $popup      = $this->getPopupContent($item, $metaModel);
        $identifier = sprintf('mm_%s_%s_marker', $metaModel->getTableName(), $item->get('id'));
        $marker     = new Marker($identifier, $coordinates);

        if ($this->model->options) {
            $marker->setOptions((array) json_decode($this->model->options, true));
        }

        if ($icon) {
            $marker->setIcon($icon);
        }

        if ($popup) {
            $marker->bindPopup($popup);
        }

        // TODO: Attributes mapping

        $parentLayer->addLayer($marker);
    }

    /**
     * @param Item      $item
     * @param MetaModel $metaModel
     *
     * @return LatLng
     */
    protected function getCoordinates(Item $item, MetaModel $metaModel)
    {
        $latAttribute = $metaModel->getAttributeById($this->model->latitudeAttribute);
        $lngAttribute = $metaModel->getAttributeById($this->model->longitudeAttribute);

        return new LatLng(
            $item->get($latAttribute->getColName()),
            $item->get($lngAttribute->getColName())
        );
    }

    /**
     * @param Item             $item
     * @param MetaModel        $metaModel
     * @param DefinitionMapper $mapper
     *
     * @return Icon|null
     */
    private function getIcon(Item $item, MetaModel $metaModel, DefinitionMapper $mapper)
    {
        if ($this->model->customIcon == 'attribute') {
            $iconAttribute = $metaModel->getAttributeById($this->model->iconAttribute);
            $iconId        = $item->get($iconAttribute->getColName());
        } elseif ($this->model->customIcon == 'fix') {
            $iconId = $this->model->icon;
        } else {
            return null;
        }

        $iconModel = IconModel::findActiveByPK($iconId);

        if (!$iconModel) {
            return null;
        }

        return $mapper->handle($iconModel);
    }

}
