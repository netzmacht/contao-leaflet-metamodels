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

namespace Netzmacht\Contao\Leaflet\MetaModels\MapLayer;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Model;
use Contao\StringUtil;
use MetaModels\Factory;
use MetaModels\Filter\Setting\FilterSettingFactory;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\GeoJsonMapper;
use Netzmacht\Contao\Leaflet\Mapper\Layer\AbstractLayerMapper;
use Netzmacht\Contao\Leaflet\Mapper\Request;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\MetaModels\Renderer\Renderer;
use Netzmacht\Contao\Leaflet\MetaModels\Renderer\RendererFactory;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\JavascriptBuilder\Type\Expression;
use Netzmacht\LeafletPHP\Definition;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Plugins\Omnivore\GeoJson as OmnivoreGeoJson;
use Netzmacht\LeafletPHP\Plugins\Omnivore\OmnivoreLayer;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Value\GeoJson\GeoJsonFeature;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class LayerMapper maps the metamodels layer definition.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Mapper
 */
final class MetaModelsLayerMapper extends AbstractLayerMapper implements GeoJsonMapper
{
    /**
     * Definition class.
     *
     * @var string
     */
    protected static $definitionClass = OmnivoreGeoJson::class;

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
    private $renderer = [];

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
     * Router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * Input adapter.
     *
     * @var Adapter
     */
    private $inputAdapter;

    /**
     * Renderer factory.
     *
     * @var RendererFactory
     */
    private $rendererFactory;

    /**
     * LayerMapper constructor.
     *
     * @param Factory              $metaModelFactory     MetaModel factory.
     * @param FilterSettingFactory $filterSettingFactory Filter setting factory.
     * @param RepositoryManager    $repositoryManager    Repository manager.
     * @param RouterInterface      $router               Router.
     * @param RendererFactory      $rendererFactory      Renderer factory.
     * @param Adapter              $inputAdapter         Input adapter.
     */
    public function __construct(
        Factory $metaModelFactory,
        FilterSettingFactory $filterSettingFactory,
        RepositoryManager $repositoryManager,
        RouterInterface $router,
        RendererFactory $rendererFactory,
        Adapter $inputAdapter
    ) {
        parent::__construct();

        $this->metaModelFactory     = $metaModelFactory;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->repositoryManager    = $repositoryManager;
        $this->router               = $router;
        $this->inputAdapter         = $inputAdapter;
        $this->rendererFactory      = $rendererFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildConstructArguments(
        Model $model,
        DefinitionMapper $mapper,
        Request $request = null,
        $elementId = null
    ): array {
        $layer = new GeoJson($this->getElementId($model, $elementId));
        $layer->setOptions($this->getLayerOptions($model));

        $route     = $this->generateRoute($model);
        $elementId = $this->getElementId($model, $elementId);

        return [$elementId, $route, [], $layer];
    }

    /**
     * {@inheritdoc}
     */
    protected function build(
        Definition $definition,
        Model $model,
        DefinitionMapper $mapper,
        Request $request = null,
        Definition $parent = null
    ): void {
        parent::build($definition, $model, $mapper, $request);

        if ($definition instanceof OmnivoreLayer && $model instanceof LayerModel) {
            $metaModelName = $this->metaModelFactory->translateIdToMetaModelName($model->metamodel);
            $metaModel     = $this->metaModelFactory->getMetaModel($metaModelName);

            if ($metaModel === null) {
                return;
            }

            $items = $this->getItems($metaModel, $model);

            if (!$items->getCount()) {
                return;
            }

            /** @var GeoJson $layer */
            $layer      = $definition->getCustomLayer();
            $collection = $layer->getInitializationData();
            $renderers  = $this->getRenderers($model, $metaModel, $items, $mapper, $request);

            if ($model->boundsMode) {
                $layer->setOption('boundsMode', $model->boundsMode);
            }

            foreach ($items as $item) {
                foreach ($renderers as $renderer) {
                    $renderer->loadData($item, $collection, $mapper, $definition->getId(), $request);
                    $renderer->loadLayers($item, $layer, $mapper, $request);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleGeoJson(Model $model, DefinitionMapper $mapper, Request $request = null): ?GeoJsonFeature
    {
        $collection    = new FeatureCollection();
        $metaModelName = $this->metaModelFactory->translateIdToMetaModelName($model->metamodel);
        $metaModel     = $this->metaModelFactory->getMetaModel($metaModelName);

        if ($metaModel === null || !$model instanceof LayerModel) {
            return null;
        }

        $items     = $this->getItems($metaModel, $model);
        $renderers = $this->getRenderers($model, $metaModel, $items, $mapper, $request, true);

        foreach ($items as $item) {
            foreach ($renderers as $renderer) {
                $renderer->loadData($item, $collection, $mapper, $model->alias, $request, true);
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
     * @param Request          $request   The requested bounds.
     * @param bool             $deferred  Load for deferred mode.
     *
     * @return Renderer[]
     */
    private function getRenderers(
        LayerModel $model,
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        Request $request = null,
        $deferred = false
    ): array {
        if (!array_key_exists($model->id, $this->renderer)) {
            $renderers  = [];
            $collection = $this->repositoryManager
                ->getRepository(RendererModel::class)
                ->findBy(['.active=1', '.pid=?'], [$model->id], ['order' => '.sorting']);

            if ($collection) {
                /** @var RendererModel $rendererModel */
                foreach ($collection as $rendererModel) {
                    $renderer = $this->createRenderer($rendererModel, $model);
                    $renderer->prepare($metaModel, $items, $mapper, $request, $deferred);

                    $renderers[] = $renderer;
                }
            }

            $this->renderer[$model->id] = $renderers;
        }

        return $this->renderer[$model->id];
    }

    /**
     * Get all MetaModel items.
     *
     * @param MetaModel  $metaModel The MetaModel.
     * @param LayerModel $model     The layer model.
     *
     * @return Items
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getItems(MetaModel $metaModel, LayerModel $model): Items
    {
        $metaModelFilter = $metaModel->getEmptyFilter();

        $filterSetting = $this->filterSettingFactory->createCollection($model->metamodel_filtering);
        $filterSetting->addRules(
            $metaModelFilter,
            array_merge(
                StringUtil::deserialize($model->metamodel_filteraprams, true),
                $this->getFilterParameters($filterSetting)
            )
        );

        return $metaModel->findByFilter(
            $metaModelFilter,
            $model->metamodel_sortby,
            0,
            $model->metamodel_use_limit ? ((int) $model->metamodel_limit) : 0,
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
     *
     * @throws \RuntimeException If the renderer does not exists.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createRenderer(RendererModel $rendererModel, LayerModel $layerModel): Renderer
    {
        return $this->rendererFactory->create($rendererModel, $layerModel);
    }

    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ICollection $filterSettings The filter settings.
     *
     * @return string[]
     */
    protected function getFilterParameters(ICollection $filterSettings): array
    {
        $params = [];

        foreach (array_keys($filterSettings->getParameterFilterNames()) as $name) {
            $value = $this->inputAdapter->get($name);
            if (is_string($value)) {
                $params[$name] = $value;
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
    protected function getLayerOptions(LayerModel $model): array
    {
        $options = [];

        if ($model->pointToLayer) {
            $options['pointToLayer'] = new Expression($model->pointToLayer);
        }

        if ($model->onEachFeature) {
            $options['onEachFeature'] = new Expression($model->onEachFeature);
        }

        return $options;
    }

    /**
     * Generate route to layer api call.
     *
     * @param Model $model The layer model.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function generateRoute(Model $model): string
    {
        $filterSetting = $this->filterSettingFactory->createCollection($model->metamodel_filtering);
        $params        = $this->getFilterParameters($filterSetting);

        if (isset($GLOBALS['objPage'])) {
            $params['context']   = 'page';
            $params['contextId'] = $GLOBALS['objPage']->id;
        }

        $params['layerId'] = $model->id;

        return $this->router->generate('leaflet_layer', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
