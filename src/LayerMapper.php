<?php

/**
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2016 netzmacht David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels;

use MetaModels\Factory;
use MetaModels\Filter\Setting\FilterSettingFactory;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Filter\Filter;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\GeoJsonMapper;
use Netzmacht\Contao\Leaflet\Mapper\Layer\AbstractLayerMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Frontend\RequestUrl;
use Netzmacht\JavascriptBuilder\Type\Expression;
use Netzmacht\LeafletPHP\Definition;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
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
     * MetaModel factory.
     *
     * @var Factory
     */
    private $metaModelFactory;

    /**
     * Filter setting factory.
     *
     * @var FilterSettingFactory
     */
    private $filterSettingFactory;

    /**
     * LayerMapper constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->metaModelFactory     = $GLOBALS['container']['metamodels-factory.factory'];
        $this->filterSettingFactory = $GLOBALS['container']['metamodels-filter-setting-factory.factory'];
    }

    /**
     * {@inheritdoc}
     */
    protected function buildConstructArguments(
        \Model $model,
        DefinitionMapper $mapper,
        Filter $filter = null,
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
        Filter $filter = null,
        Definition $parent = null
    ) {
        parent::build($definition, $model, $mapper, $filter);

        if ($definition instanceof OmnivoreLayer) {
            $metaModelName = $this->metaModelFactory->translateIdToMetaModelName($model->metamodel);
            $metaModel     = $this->metaModelFactory->getMetaModel($metaModelName);
            $items         = $this->getItems($metaModel, $model, $filter);

            if (!$items->getCount()) {
                return;
            }

            /** @var GeoJson $layer */
            $layer      = $definition->getCustomLayer();
            $collection = $layer->getInitializationData();
            $renderers  = $this->getRenderers($model, $metaModel, $items, $mapper, $filter);

            if ($model->boundsMode) {
                $layer->setOption('boundsMode', $model->boundsMode);
            }

            foreach ($items as $item) {
                foreach ($renderers as $renderer) {
                    $renderer->loadData($item, $collection, $mapper, $definition->getId(), $filter);
                    $renderer->loadLayers($item, $layer, $mapper, $filter);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleGeoJson(\Model $model, DefinitionMapper $mapper, Filter $filter = null)
    {
        $collection    = new FeatureCollection();
        $metaModelName = $this->metaModelFactory->translateIdToMetaModelName($model->metamodel);
        $metaModel     = $this->metaModelFactory->getMetaModel($metaModelName);
        $items         = $this->getItems($metaModel, $model, $filter);
        $renderers     = $this->getRenderers($model, $metaModel, $items, $mapper, $filter, true);

        foreach ($items as $item) {
            foreach ($renderers as $renderer) {
                $renderer->loadData($item, $collection, $mapper, $model->alias, $filter, true);
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
     * @param Filter           $filter    The requested bounds.
     * @param bool             $deferred  Load for deferred mode.
     *
     * @return Renderer[]
     */
    private function getRenderers(
        LayerModel $model,
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        Filter $filter = null,
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
                    $renderer->prepare($metaModel, $items, $mapper, $filter, $deferred);

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
     * @param Filter     $filter    Optional request filter.
     *
     * @return Items
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getItems(
        MetaModel $metaModel,
        LayerModel $model,
        Filter $filter = null
    ) {
        $metaModelFilter = $metaModel->getEmptyFilter();

        $filterSetting = $this->filterSettingFactory->createCollection($model->metamodel_filtering);
        $filterSetting->addRules(
            $metaModelFilter,
            array_merge(
                deserialize($model->metamodel_filteraprams, true),
                $this->getFilterParameters($filterSetting)
            )
        );

        return $metaModel->findByFilter(
            $metaModelFilter,
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
        }

        return $options;
    }
}
