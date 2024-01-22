<?php

/**
 * Contao Leaflet MetaModels integration.
 *
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Cliff Parnitzky <contao@cliff-parnitzky.de>
 * @copyright  2015-2020 netzmacht David Molineus
 * @license    LGPL 3.0-or-later https://github.com/netzmacht/contao-leaflet-metamodels/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace Netzmacht\Contao\Leaflet\MetaModels\MapLayer;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Model;
use Contao\StringUtil;
use MetaModels\Factory;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory as FilterSettingFactory;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use MetaModels\ItemList;
use MetaModels\Render\Setting\IRenderSettingFactory as RenderSettingFactory;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * Render setting factory.
     *
     * @var RenderSettingFactory
     */
    private $renderSettingFactory;

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
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Filter url builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

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
        RenderSettingFactory $renderSettingFactory,
        EventDispatcherInterface $eventDispatcher,
        FilterUrlBuilder $filterUrlBuilder,
        RepositoryManager $repositoryManager,
        RouterInterface $router,
        RendererFactory $rendererFactory,
        Adapter $inputAdapter
    ) {
        parent::__construct();

        $this->metaModelFactory     = $metaModelFactory;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->repositoryManager    = $repositoryManager;
        $this->router               = $router;
        $this->inputAdapter         = $inputAdapter;
        $this->rendererFactory      = $rendererFactory;
        $this->eventDispatcher      = $eventDispatcher;
        $this->filterUrlBuilder     = $filterUrlBuilder;
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

            $items = $this->getItems($model);

            if (! $items->getCount()) {
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

        if ($metaModel === null || ! $model instanceof LayerModel) {
            return null;
        }

        $items     = $this->getItems($model);
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
        if (! array_key_exists($model->id, $this->renderer)) {
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
     * @param LayerModel $model The layer model.
     *
     * @return Items
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getItems(LayerModel $model): Items
    {
        $filterParams = StringUtil::deserialize($model->metamodel_filterparams, true);
        $itemRenderer = new ItemList(
            $this->metaModelFactory,
            $this->filterSettingFactory,
            $this->renderSettingFactory,
            $this->eventDispatcher,
            $this->filterUrlBuilder,
            'mm_leaflet_' . $model->id
        );

        // @codingStandardsIgnoreStart
        // FIXME: filter URL should be created from local request and not from master request.
        // @codingStandardsIgnoreEnd
        $filterUrl = $this->filterUrlBuilder->getCurrentFilterUrl();

        $itemRenderer
            ->setMetaModel((int) $model->metamodel, 0)
            ->setLimit(
                (bool) $model->metamodel_use_limit,
                (int) $model->metamodel_offset,
                (int) $model->metamodel_limit
            )
            ->setPageBreak((int) $model->perPage)
            ->setSorting($model->metamodel_sortby, $model->metamodel_sortby_direction)
            ->setFilterSettings((int) $model->metamodel_filtering)
            ->setFilterParameters(
                $filterParams,
                $this->getFilterParameters($filterUrl, $itemRenderer->getFilterSettings()->getParameters())
            );

        $itemRenderer->prepare();

        return $itemRenderer->getItems();
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
     * @param FilterUrl $filterUrl  The filter URL to obtain parameters from.
     * @param string[]  $parameters The filter parameters.
     *
     * @return string[]
     */
    private function getFilterParameters(FilterUrl $filterUrl, array $parameters): array
    {
        $result = [];
        foreach ($parameters as $name) {
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, $name, 'slugNget')) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * Get parameter from get or slug.
     *
     * @param FilterUrl $filterUrl The filter URL to obtain parameters from.
     * @param string    $sortParam The sort parameter name to obtain.
     * @param string    $sortType  The sort URL type.
     *
     * @return string|null
     */
    private function tryReadFromSlugOrGet(FilterUrl $filterUrl, string $sortParam, string $sortType): ?string
    {
        $result = null;

        switch ($sortType) {
            case 'get':
                $result = $filterUrl->getGet($sortParam);
                break;
            case 'slug':
                $result = $filterUrl->getSlug($sortParam);
                break;
            case 'slugNget':
                $result = ($filterUrl->getGet($sortParam) ?? $filterUrl->getSlug($sortParam));
                break;
            default:
        }

        // Mark the parameter as used (otherwise, a 404 is thrown)
        $this->inputAdapter->get($sortParam);

        return $result;
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
        $filterUrl     = $this->filterUrlBuilder->getCurrentFilterUrl();
        $filterSetting = $this->filterSettingFactory->createCollection($model->metamodel_filtering);
        $params        = $this->getFilterParameters($filterUrl, $filterSetting->getParameters());

        if (isset($GLOBALS['objPage'])) {
            $params['context']   = 'page';
            $params['contextId'] = $GLOBALS['objPage']->id;
        }

        $params['layerId'] = $model->id;

        return $this->router->generate('leaflet_layer', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
