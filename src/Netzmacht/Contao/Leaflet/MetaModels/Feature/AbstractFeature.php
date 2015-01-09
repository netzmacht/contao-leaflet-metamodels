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
use MetaModels\IMetaModel as MetaModel;
use MetaModels\Render\Setting\Factory as RenderSettingFactory;
use Netzmacht\Contao\Leaflet\MetaModels\Feature;
use Netzmacht\Contao\Leaflet\MetaModels\Model\FeatureModel;

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


    protected function getPopupContent(Item $item, MetaModel $metaModel)
    {
        if (!$this->model->addPopup) {
            return null;
        }

        if ($this->model->addPopup === 'attribute') {
            $popupAttribute = $metaModel->getAttributeById($this->model->popupAttribute);

            return $item->parseAttribute($popupAttribute, $this->model->format ?: 'text');
        }

        $settings = RenderSettingFactory::byId($metaModel, $this->model->renderSettings);

        return $item->parseValue($this->model->format ?: 'text', $settings);
    }
}
