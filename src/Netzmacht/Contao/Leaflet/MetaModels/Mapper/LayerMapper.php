<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Mapper;

use MetaModels\IItems;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\GeoJsonMapper;
use Netzmacht\Contao\Leaflet\Mapper\Layer\AbstractLayerMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Feature;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Request\RequestUrl;
use Netzmacht\LeafletPHP\Definition;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Plugins\Ajax\GeoJsonAjax;

class LayerMapper extends AbstractLayerMapper implements GeoJsonMapper
{
    protected static $definitionClass = 'Netzmacht\LeafletPHP\Definition\Group\GeoJson';

    protected static $type = 'metamodels';

    /**
     * Features registry.
     *
     * @var array
     */
    protected $features = array();

    /**
     * {@inheritdoc}
     */
    protected function getClassName(\Model $model, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        if ($model->deferred) {
            return 'Netzmacht\LeafletPHP\Plugins\Ajax\GeoJsonAjax';
        }

        return parent::getClassName($model, $mapper, $bounds);
    }

    /**
     * {@inheritdoc}
     */
    protected function build(
        Definition $definition,
        \Model $model,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    ) {
        parent::build($definition, $model, $mapper, $bounds);

        if ($definition instanceof GeoJsonAjax) {
            $requestBuilder = RequestUrl::createBuilder($model->id);
            $requestBuilder->addQueryParameters(base64_encode(\Environment::get('requestUri')));

            $definition->setUrl($requestBuilder->getUrl());
        } else {
            $this->applyFeatures($definition, $model, $mapper, $bounds);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleGeoJson(\Model $model, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $layer = new GeoJson($model->alias ?: ('layer_' . $model->id));
        $this->applyFeatures($layer, $model, $mapper, $bounds);

        return $layer->toGeoJson();
    }

    /**
     * Get Features.
     *
     * @param $model
     *
     * @return Feature[]
     */
    private function getFeatures($model)
    {
        if (!array_key_exists($model->id, $this->features[$model->id])) {
            $features = array();

            $this->features[$model->id] = $features;
        }

        return $this->features[$model->id];
    }

    /**
     * @param LayerModel   $model
     * @param LatLngBounds $bounds
     *
     * @return IItems
     */
    private function getItems(LayerModel $model, LatLngBounds $bounds = null)
    {
    }

    /**
     * @param LayerGroup       $definition
     * @param LayerModel       $model
     * @param DefinitionMapper $mapper
     * @param LatLngBounds     $bounds
     */
    protected function applyFeatures(
        LayerGroup $definition,
        LayerModel $model,
        DefinitionMapper $mapper,
        LatLngBounds $bounds
    ) {
        $features = $this->getFeatures($model);
        $items    = $this->getItems($model, $bounds);

        foreach ($items as $item) {
            foreach ($features as $feature) {
                $feature->apply($item, $definition, $mapper, $bounds);
            }
        }
    }
}
