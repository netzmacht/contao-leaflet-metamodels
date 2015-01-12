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

use MetaModels\IItem as ITem;
use MetaModels\IMetaModel;
use MetaModels\Items;
use MetaModels\Render\Setting\Factory as RenderSettingFactory;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\MetaModels\Feature;
use Netzmacht\Contao\Leaflet\MetaModels\Model\FeatureModel;
use Netzmacht\Contao\Leaflet\Model\IconModel;
use Netzmacht\LeafletPHP\Definition\Type\Icon;

abstract class AbstractFeature implements Feature
{
    /**
     * @var FeatureModel
     */
    protected $model;

    /**
     * Construct.
     *
     * @param FeatureModel $model
     */
    public function __construct(FeatureModel $model)
    {
        $this->model = $model;
    }

    /**
     * Generate the content of the popup.
     *
     * @param Item        $item
     * @param ICollection $settings
     *
     * @return null|string
     */
    protected function getPopupContent(Item $item, ICollection $settings = null)
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
            $template = new Template($settings->get('template'));
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
     * @param ICollection $settings
     *
     * @param string      $default
     *
     * @return mixed|null|string
     */
    protected function getOutputFormat(ICollection $settings = null, $default = 'text')
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
     * @param Item             $item
     * @param DefinitionMapper $mapper
     *
     * @return Icon|null
     */
    protected function getIcon(Item $item, DefinitionMapper $mapper)
    {
        $iconModel = null;

        if ($this->model->iconAttribute) {
            $iconAttribute = $this->getAttribute('iconAttribute', $item);
            $iconId        = $item->get($iconAttribute->getColName());
            $iconModel     = IconModel::findActiveByPK($iconId);
        }

        if (!$iconModel && $this->model->icon) {
            $iconModel = IconModel::findByPk($this->model->icon);
        }

        if (!$iconModel) {
            return null;
        }

        return $mapper->handle($iconModel);
    }

    /**
     * @param $metaModel
     *
     * @return \MetaModels\Render\Setting\Collection|\MetaModels\Render\Setting\ICollection
     */
    protected function getRenderSettings($metaModel)
    {
        $settings    = null;

        if ($this->model->renderSettings) {
            $settings = RenderSettingFactory::byId($metaModel, $this->model->renderSettings);

            return $settings;
        }

        return $settings;
    }

    /**
     * @param      $column
     * @param Item $item
     *
     * @return \MetaModels\Attribute\IAttribute
     */
    protected function getAttribute($column, Item $item)
    {
        return $item->getMetaModel()->getAttributeById($this->model->$column);
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
     * @return string
     * @see    MetaModels\ItemList::getDetailsCaption
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDetailsCaption(IMetaModel $metaModel)
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
