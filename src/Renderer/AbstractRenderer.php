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

use MetaModels\Attribute\IAttribute as Attribute;
use MetaModels\IItems;
use MetaModels\IMetaModel as MetaModel;
use MetaModels\IItem as Item;
use MetaModels\Items;
use MetaModels\Render\Setting\ICollection as RenderSetting;
use MetaModels\Render\Setting\RenderSettingFactory;
use MetaModels\Render\Template;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\Request;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\Model\IconModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Type\Icon;

/**
 * Class AbstractFeature is the base implementation of the MetaModels item renderer interface.
 */
abstract class AbstractRenderer implements Renderer
{
    /**
     * Render setting factory.
     *
     * @var RenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    protected $repositoryManager;

    /**
     * The renderer model.
     *
     * @var RendererModel
     */
    protected $model;

    /**
     * The layer model of the parent layer.
     *
     * @var LayerModel
     */
    protected $layerModel;

    /**
     * List of preloaded icons.
     *
     * @var Icon[]
     */
    protected $icons = [];

    /**
     * Fallback icon.
     *
     * @var Icon|null
     */
    protected $fallbackIcon;

    /**
     * Construct.
     *
     * @param RenderSettingFactory $renderSettingFactory Render setting factory.
     * @param RepositoryManager    $repositoryManager    Repository manager.
     * @param RendererModel        $model                The feature model.
     * @param LayerModel           $layerModel           The layer model.
     */
    public function __construct(
        RenderSettingFactory $renderSettingFactory,
        RepositoryManager $repositoryManager,
        RendererModel $model,
        LayerModel $layerModel
    ) {
        $this->renderSettingFactory = $renderSettingFactory;
        $this->repositoryManager    = $repositoryManager;
        $this->model                = $model;
        $this->layerModel           = $layerModel;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(
        MetaModel $metaModel,
        IItems $items,
        DefinitionMapper $mapper,
        Request $filter = null,
        $deferred = false
    ): void {
    }

    /**
     * {@inheritdoc}
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        Request $filter = null,
        $deferred = false
    ): void {
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayers(
        Item $item,
        GeoJson $dataLayer,
        DefinitionMapper $mapper,
        Request $filter = null
    ): void {
    }

    /**
     * Generate the content of the popup.
     *
     * @param Item          $item     The metamodel item.
     * @param RenderSetting $settings The given metamodel settings.
     *
     * @return null|string
     */
    protected function getPopupContent(Item $item, RenderSetting $settings = null): ?string
    {
        if (!$this->model->addPopup) {
            return null;
        }

        if ($this->model->addPopup === 'attribute') {
            $popupAttribute = $this->getAttribute('popupAttribute', $item);
            if ($popupAttribute === null) {
                return null;
            }

            $format = $this->getOutputFormat($settings);
            $parsed = $item->parseAttribute($popupAttribute->getColName(), $format, $settings);

            return ($parsed[$format] ?? $parsed['text']);
        }

        $value = $item->parseValue($this->getOutputFormat($settings), $settings);
        $data  = [];

        if ($settings) {
            $template     = new Template($settings->get('template'));
            $data['view'] = $settings;
        } else {
            $template = new Template('metamodel_full');
        }

        // MetaModels always expects an list of items. Instead of requiring customized templates,
        // we pretend of having multiple items.
        $data['details'] = $this->getDetailsCaption($item->getMetaModel());
        $data['items']   = new Items([$item]);
        $data['data']    = [$value];
        $data['caller']  = $this;

        $template->setData($data);

        return $template->parse($this->getOutputFormat($settings, 'html5'));
    }

    /**
     * Get the defined output format.
     *
     * @param RenderSetting $settings The render settings.
     * @param string        $default  The default output format.
     *
     * @return string
     */
    protected function getOutputFormat(RenderSetting $settings = null, string $default = 'text'): string
    {
        if ($settings) {
            $format = (string) $settings->get('format');

            if ($format !== '') {
                return $format;
            }
        }

        return $default;
    }

    /**
     * Get the render setting.
     *
     * @param MetaModel $metaModel The MetaModel.
     *
     * @return RenderSetting|null
     */
    protected function getRenderSettings(MetaModel $metaModel): ?RenderSetting
    {
        if ($this->model->renderSettings) {
            return $this->renderSettingFactory->createCollection($metaModel, $this->model->renderSettings);
        }

        return null;
    }

    /**
     * Get a MetaModel attribute by a column name of the FeatureModel which contains the id.
     *
     * @param string $column The name of the attribute id.
     * @param Item   $item   The metamodel item.
     *
     * @return Attribute|null
     */
    protected function getAttribute($column, Item $item): ?Attribute
    {
        return $item->getMetaModel()->getAttributeById($this->model->$column);
    }

    /**
     * Get the icon for the MetaModel item.
     *
     * @param int $itemId The MetaModel item id.
     *
     * @return Icon|null
     */
    protected function getIcon($itemId): ?Icon
    {
        if (isset($this->icons[$itemId])) {
            return $this->icons[$itemId];
        }

        return $this->fallbackIcon;
    }

    /**
     * Pre load icon values.
     *
     * @param array            $values Icon ids.
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return void
     */
    protected function preLoadIcons(array $values, DefinitionMapper $mapper)
    {
        $repository = $this->repositoryManager->getRepository(IconModel::class);
        $collection = $repository->findMultipleByIds($values) ?: [];

        foreach ($collection as $model) {
            if (!$model->active) {
                continue;
            }

            $icon = $mapper->handle($model);

            if (!$icon) {
                continue;
            }

            foreach ($values as $itemId => $iconId) {
                if ($iconId == $model->id) {
                    $this->icons[$itemId] = $icon;
                }
            }
        }
    }

    /**
     * Load fallback icon.
     *
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return void
     */
    protected function loadFallbackIcon(DefinitionMapper $mapper): void
    {
        if ($this->model->icon) {
            $repository = $this->repositoryManager->getRepository(IconModel::class);
            $iconModel  = $repository->find((int) $this->model->icon);

            if ($iconModel) {
                $this->fallbackIcon = $mapper->handle($iconModel);
            }
        }
    }

    /**
     * Retrieve the caption text for the "Show details" link.
     *
     * Stolen from MetaModels\ItemList::getDetailsCaption
     *
     * In order to achieve the correct caption text, the function tries several translation strings sequentially.
     * The first language key that is set will win, even if it is to be considered empty.
     *
     * This message is looked up in the following order:
     * 1. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][<render settings id>]['details']
     * 2. $GLOBALS['TL_LANG']['MSC'][<mm tablename>]['details']
     * 3. $GLOBALS['TL_LANG']['MSC']['details']
     *
     * @param MetaModel $metaModel The MetaModel.
     *
     * @return string
     * @see    \MetaModels\CoreBundle\ItemList::getDetailsCaption
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDetailsCaption(MetaModel $metaModel): ?string
    {
        $tableName = $metaModel->getTableName();
        if (isset($this->objView)
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['details'])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['details'];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName]['details'])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName]['details'];
        }

        return $GLOBALS['TL_LANG']['MSC']['details'];
    }
}
