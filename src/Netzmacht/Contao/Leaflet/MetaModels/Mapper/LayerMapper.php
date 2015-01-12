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

use MetaModels\Factory;
use MetaModels\Filter\Setting\Factory as FilterSettingFactory;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IItems;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\GeoJsonMapper;
use Netzmacht\Contao\Leaflet\Mapper\Layer\AbstractLayerMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Feature;
use Netzmacht\Contao\Leaflet\MetaModels\Feature\LoadsReferred;
use Netzmacht\Contao\Leaflet\MetaModels\Model\FeatureModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Frontend\RequestUrl;
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

        $referred = false;

        if ($definition instanceof GeoJsonAjax) {
            $requestBuilder = RequestUrl::createBuilder($model->id);
            $requestBuilder->setQueryParameter('filter', urlencode(base64_encode(\Environment::get('requestUri'))));

            $definition->setUrl($requestBuilder->getUrl());

            $referred = true;
        }

        $this->applyFeatures($definition, $model, $mapper, $bounds, $referred);
    }

    /**
     * {@inheritdoc}
     */
    public function handleGeoJson(\Model $model, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $collection = new Definition\GeoJson\FeatureCollection();
        $features   = $this->getFeatures($model);
        $items      = $this->getItems($model, $bounds);

        foreach ($items as $item) {
            foreach ($features as $feature) {
                if ($feature instanceof LoadsReferred) {
                    $feature->applyGeoJson($item, $collection, $mapper, $bounds);
                }
            }
        }

        return $collection;
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
        if (!array_key_exists($model->id, $this->features)) {
            $features   = array();
            $collection = FeatureModel::findBy(
                array('active=1', 'pid=?'),
                array($model->id),
                array('order' => 'sorting')
            );

            if ($collection) {
                foreach ($collection as $featureModel) {
                    $features[] = $this->createFeature($featureModel);
                }
            }

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
        $metaModel = Factory::byId($model->metamodel);
        $filter    = $metaModel->getEmptyFilter();

        $filterSetting = FilterSettingFactory::byId($model->metamodel_filtering);
        $filterSetting->addRules(
            $filter,
            array_merge(
                deserialize($model->metamodel_filteraprams, true),
                $this->getFilterParameters($filterSetting)
            )
        );

        return $metaModel->findByFilter(
            $filter,
            $model->metamodel_sortby,
            0,
            $model->metamodel_use_limit ? ($model->metamodel_limit ?: 0) : 0,
            $model->metamodel_sortby_direction
            /* $this->getAttributeNames() */
        );
    }

    /**
     * @param LayerGroup       $definition
     * @param LayerModel       $model
     * @param DefinitionMapper $mapper
     * @param LatLngBounds     $bounds
     * @param bool             $referred
     */
    protected function applyFeatures(
        LayerGroup $definition,
        LayerModel $model,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $referred = false
    ) {
        $features = $this->getFeatures($model);
        $items    = $this->getItems($model, $bounds);

        foreach ($items as $item) {
            foreach ($features as $feature) {
                if ($referred && !$feature instanceof LoadsReferred) {
                    $feature->apply($item, $definition, $mapper, $bounds);
                }
            }
        }
    }

    /**
     * Create a feature.
     *
     * @param FeatureModel $featureModel
     *
     * @return Feature
     */
    private function createFeature(FeatureModel $featureModel)
    {
        if (!isset($GLOBALS['LEAFLET_MM_FEATURES'][$featureModel->type])) {
            throw new \RuntimeException(sprintf('Metamodel feature "%s" does not exists', $featureModel->type));
        }

        $class = $GLOBALS['LEAFLET_MM_FEATURES'][$featureModel->type];

        if (is_callable($class)) {
            return call_user_func($class, $featureModel);
        }

        return new $class($featureModel);
    }

    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ICollection $filterSettings The filter settings.
     *
     * @return string[]
     */
    protected function getFilterParameters(ICollection $filterSettings)
    {
        $params = array();

        foreach (array_keys($filterSettings->getParameterFilterNames()) as $strName) {
            $varValue = \Input::getInstance()->get($strName);
            if (is_string($varValue)) {
                $params[$strName] = $varValue;
            }
        }

        return $params;
    }
}
