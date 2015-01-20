<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels;

use MetaModels\Factory;
use MetaModels\Filter\Setting\Factory as FilterSettingFactory;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\GeoJsonMapper;
use Netzmacht\Contao\Leaflet\Mapper\Layer\AbstractLayerMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Renderer;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Frontend\RequestUrl;
use Netzmacht\JavascriptBuilder\Type\Expression;
use Netzmacht\LeafletPHP\Definition;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Plugins\Omnivore\OmnivoreLayer;

/**
 * Class LayerMapper maps the metamodels layer definition.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Mapper
 */
class LayerMapper extends AbstractLayerMapper implements GeoJsonMapper
{
    /**
     * Definition class.
     *
     * @var string
     */
    protected static $definitionClass = 'Netzmacht\LeafletPHP\Plugins\Omnivore\GeoJson';

    /**
     * The layer type.
     *
     * @var string
     */
    protected static $type = 'metamodels';

    /**
     * Renderer registry.
     *
     * @var array
     */
    private $renderers = array();

    /**
     * {@inheritdoc}
     */
    protected function buildConstructArguments(
        \Model $model,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $elementId = null
    ) {
        $layer = new GeoJson($this->getElementId($model, $elementId));
        $layer->setOptions($this->getLayerOptions($model));

        $request   = RequestUrl::create($model->id);
        $elementId = $this->getElementId($model, $elementId);
        $arguments = array($elementId, $request, array(), $layer);

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function build(
        Definition $definition,
        \Model $model,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        Definition $parent = null
    ) {
        parent::build($definition, $model, $mapper, $bounds);

        if ($definition instanceof OmnivoreLayer) {
            $metaModel = Factory::byId($model->metamodel);
            $items     = $this->getItems($metaModel, $model, $bounds);

            if (!$items->getCount()) {
                return;
            }

            /** @var GeoJson $layer */
            $layer      = $definition->getCustomLayer();
            $collection = $layer->getInitializationData();
            $renderers  = $this->getRenderers($model, $metaModel, $items, $mapper, $bounds);

            foreach ($items as $item) {
                foreach ($renderers as $renderer) {
                    $renderer->loadData($item, $collection, $mapper, $definition->getId(), $bounds);
                    $renderer->loadLayers($item, $layer, $mapper, $bounds);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleGeoJson(\Model $model, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $collection = new FeatureCollection();
        $metaModel  = Factory::byId($model->metamodel);
        $items      = $this->getItems($metaModel, $model, $bounds);
        $renderers  = $this->getRenderers($model, $metaModel, $items, $mapper, $bounds, true);

        foreach ($items as $item) {
            foreach ($renderers as $renderer) {
                $renderer->loadData($item, $collection, $mapper, $model->alias, $bounds, true);
            }
        }

        return $collection;
    }

    /**
     * Get Renderer.
     *
     * @param LayerModel       $model     The layer model.
     * @param MetaModel        $metaModel The MetaModel.
     * @param Items            $items     The MetaModel items.
     * @param DefinitionMapper $mapper    The definition mapper.
     * @param LatLngBounds     $bounds    The requested bounds.
     * @param bool             $deferred  Load for deferred mode.
     *
     * @return Renderer[]
     */
    private function getRenderers(
        LayerModel $model,
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $deferred = false
    ) {
        if (!array_key_exists($model->id, $this->renderers)) {
            $renderers  = array();
            $collection = RendererModel::findBy(
                array('active=1', 'pid=?'),
                array($model->id),
                array('order' => 'sorting')
            );

            if ($collection) {
                /** @var RendererModel $rendererModel */
                foreach ($collection as $rendererModel) {
                    $renderer = $this->createRenderer($rendererModel, $model);
                    $renderer->prepare($metaModel, $items, $mapper, $bounds, $deferred);

                    $renderers[] = $renderer;
                }
            }

            $this->renderers[$model->id] = $renderers;
        }

        return $this->renderers[$model->id];
    }

    /**
     * Get all MetaModel items.
     *
     * @param MetaModel  $metaModel The MetaModel.
     * @param LayerModel $model     The layer model.
     *
     * @return Items
     */
    private function getItems(
        MetaModel $metaModel,
        LayerModel $model
        // , LatLngBounds $bounds = null
    ) {
        $filter = $metaModel->getEmptyFilter();

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
            // $this->getAttributeNames() - Do we have to limit the attributes here?
        );
    }

    /**
     * Create a feature.
     *
     * @param RendererModel $rendererModel The Feature model.
     * @param LayerModel    $layerModel    The layer model.
     *
     * @return Renderer
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws \RuntimeException If the renderer does not exists.
     */
    private function createRenderer(RendererModel $rendererModel, LayerModel $layerModel)
    {
        if (!isset($GLOBALS['LEAFLET_MM_RENDERER'][$rendererModel->type])) {
            throw new \RuntimeException(sprintf('Metamodel renderer "%s" does not exists', $rendererModel->type));
        }

        $class = $GLOBALS['LEAFLET_MM_RENDERER'][$rendererModel->type];

        if (is_callable($class)) {
            return call_user_func($class, $rendererModel, $layerModel);
        }

        return new $class($rendererModel, $layerModel);
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

    /**
     * Extract layer options from the model.
     *
     * @param LayerModel $model The layer model.
     *
     * @return array
     */
    protected function getLayerOptions(LayerModel $model)
    {
        $options = array();

        if ($model->pointToLayer) {
            $options['pointToLayer'] = new Expression($model->pointToLayer);
        }

        if ($model->onEachFeature) {
            $options['onEachFeature'] = new Expression($model->onEachFeature);

            return $options;
        }

        return $options;
    }
}
