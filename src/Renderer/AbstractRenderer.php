<?php

/**
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2016 netzmacht David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Renderer;

use MetaModels\Attribute\IAttribute as Attribute;
use MetaModels\IItems;
use MetaModels\IMetaModel as MetaModel;
use MetaModels\IItem as Item;
use MetaModels\Items;
use MetaModels\Render\Setting\Factory as RenderSettingFactory;
use MetaModels\Render\Setting\ICollection as RenderSetting;
use MetaModels\Render\Template;
use Netzmacht\Contao\Leaflet\Filter\Filter;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\MetaModels\Renderer;
use Netzmacht\Contao\Leaflet\Model\IconModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Type\Icon;

/**
 * Class AbstractFeature is the base implementation of the MetaModels item renderer interface.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Feature
 */
abstract class AbstractRenderer implements Renderer
{
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
    protected $icons = array();

    /**
     * Fallback icon.
     *
     * @var Icon|null
     */
    protected $fallbackIcon;

    /**
     * Construct.
     *
     * @param RendererModel $model      The feature model.
     * @param LayerModel    $layerModel The layer model.
     */
    public function __construct(RendererModel $model, LayerModel $layerModel)
    {
        $this->model      = $model;
        $this->layerModel = $layerModel;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(
        MetaModel $metaModel,
        IItems $items,
        DefinitionMapper $mapper,
        Filter $filter = null,
        $deferred = false
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        Filter $filter = null,
        $deferred = false
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayers(
        Item $item,
        GeoJson $dataLayer,
        DefinitionMapper $mapper,
        Filter $filter = null
    ) {
    }

    /**
     * Generate the content of the popup.
     *
     * @param Item          $item     The metamodel item.
     * @param RenderSetting $settings The given metamodel settings.
     *
     * @return null|string
     */
    protected function getPopupContent(Item $item, RenderSetting $settings = null)
    {
        if (!$this->model->addPopup) {
            return null;
        }

        if ($this->model->addPopup === 'attribute') {
            $popupAttribute = $this->getAttribute('popupAttribute', $item);
            $format         = $this->getOutputFormat($settings, 'text');
            $parsed         = $item->parseAttribute($popupAttribute->getColName(), $format, $settings);

            if (isset($parsed[$format])) {
                return $parsed[$format];
            }

            return $parsed['text'];
        }

        $value = $item->parseValue($this->getOutputFormat($settings), $settings);

        if ($settings) {
            $template       = new Template($settings->get('template'));
            $template->view = $settings;
        } else {
            $template = new Template('metamodel_full');
        }

        // Metamodels always expects an list of items. Instead of requiring customized templates,
        // we pretend of having multiple items.

        $template->details = $this->getDetailsCaption($item->getMetaModel());
        $template->items   = new Items(array($item));
        $template->data    = array($value);
        $template->caller  = $this;

        return $template->parse($this->getOutputFormat($settings, 'html5'));
    }

    /**
     * Get the defined output format.
     *
     * @param RenderSetting $settings The render settings.
     * @param string        $default  The default output format.
     *
     * @return mixed|null|string
     */
    protected function getOutputFormat(RenderSetting $settings = null, $default = 'text')
    {
        if ($settings) {
            $format = $settings->get('format');

            if (strlen($format)) {
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
    protected function getRenderSettings(MetaModel $metaModel)
    {
        if ($this->model->renderSettings) {
            $settings = RenderSettingFactory::byId($metaModel, $this->model->renderSettings);

            return $settings;
        }

        return null;
    }

    /**
     * Get a MetaModel attribute by a column name of the FeatureModel which contains the id.
     *
     * @param string $column The name of the attribute id.
     * @param Item   $item   The metamodel item.
     *
     * @return Attribute
     */
    protected function getAttribute($column, Item $item)
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
    protected function getIcon($itemId)
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
        $collection = IconModel::findMultipleByIds($values);
        if (!$collection) {
            return;
        }

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
    protected function loadFallbackIcon(DefinitionMapper $mapper)
    {
        if ($this->model->icon) {
            $iconModel = IconModel::findByPk($this->model->icon);

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
     * @see    MetaModels\ItemList::getDetailsCaption
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDetailsCaption(MetaModel $metaModel)
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
