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

use Contao\Model;
use MetaModels\Attribute\IAttribute as Attribute;
use MetaModels\IItem as Item;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Definition\Style;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\Request;
use Netzmacht\Contao\Leaflet\Model\StyleModel;
use Netzmacht\Contao\Toolkit\Data\Model\Repository;
use Netzmacht\LeafletPHP\Value\GeoJson\ConvertsToGeoJsonFeature;
use Netzmacht\LeafletPHP\Value\GeoJson\Feature;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\HasPopup;
use Netzmacht\LeafletPHP\Definition\Layer;
use Netzmacht\LeafletPHP\Definition\UI\Marker;
use Netzmacht\LeafletPHP\Definition\Vector;
use Netzmacht\LeafletPHP\Definition\Vector\Path;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Model\VectorModel;
use Netzmacht\Contao\Leaflet\Model\MarkerModel;

/**
 * Class ReferenceRenderer renders a metamodel items attribute as reference to a marker, layer or vector.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Renderer
 */
class ReferenceRenderer extends AbstractRenderer
{
    /**
     * List of references. A reference can be a layer, marker or vector definition.
     *
     * @var Layer[]|Marker[]|Vector[]
     */
    private $references = [];

    /**
     * Preloaded styles.
     *
     * @var Style[]
     */
    private $styles = [];

    /**
     * Fallback style.
     *
     * @var Style|null
     */
    private $fallbackStyle;

    /**
     * {@inheritdoc}
     */
    public function prepare(
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        Request $request = null,
        $deferred = false
    ): void {
        if ($deferred !== $this->model->deferred && $this->model->referenceType !== 'reflayer') {
            return;
        }

        $reference = $metaModel->getAttributeById($this->model->referenceAttribute);
        $values    = [];
        $icons     = [];
        $styles    = [];

        // Reference attribute should not be empty. Could happen if an attribute is deleted, so stop initialization
        // here.
        if (!$reference) {
            return;
        }

        $this->loadFallbackIcon($mapper);
        $this->loadFallbackStyle($mapper);

        $this->prepareValues($metaModel, $items, $reference, $values, $icons, $styles);
        $this->preLoadIcons($icons, $mapper);
        $this->preLoadStyles($styles, $mapper);
        $this->preLoadReferences($values, $mapper, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function loadLayers(
        Item $item,
        GeoJson $dataLayer,
        DefinitionMapper $mapper,
        Request $request = null
    ): void {
        if ($this->model->referenceType !== 'reflayer' && $this->model->deferred) {
            return;
        }

        $definition = $this->buildDefinition($item);

        if (!$definition) {
            return;
        }

        if ($definition instanceof ConvertsToGeoJsonFeature && $definition->convertsFullyToGeoJson()) {
            $feature = $mapper->convertToGeoJsonFeature($definition, $this->model);

            if ($feature) {
                $dataLayer->addData($feature);
            }
        } else {
            $dataLayer->addLayer($definition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        Request $request = null,
        $deferred = false
    ): void {
        if ($deferred == $this->model->deferred && $this->model->referenceType !== 'reflayer') {
            $definition = $this->buildDefinition($item);

            if ($definition instanceof ConvertsToGeoJsonFeature) {
                $feature = $definition->toGeoJsonFeature();

                if ($feature instanceof Feature
                    && ($this->model->ignoreForBounds || $this->layerModel->boundsMode !== 'extend')) {
                    $feature->setProperty('ignoreForBounds', true);
                }
                $featureCollection->addFeature($feature);
            }
        }
    }

    /**
     * Build the definition.
     *
     * @param Item $item The Metamodel item.
     *
     * @return Layer|Marker|Vector|null
     */
    private function buildDefinition(Item $item)
    {
        if (!isset($this->references[$item->get('id')])) {
            return null;
        }

        $definition = $this->references[$item->get('id')];

        if ($this->model->referenceType !== 'reflayer' && $this->model->standalone) {
            $this->applyPopup($item, $definition);
            $this->applyMarker($item, $definition);
            $this->applyStyle($item, $definition);
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    private function preLoadReferences(array $values, DefinitionMapper $mapper, Request $request = null): void
    {
        $modelClass = $this->getReferenceModelClass();
        if ($modelClass === null) {
            return;
        }

        /** @var Repository|Model $repository */
        $repository = $this->repositoryManager->getRepository($modelClass);
        $collection = $repository->findMultipleByIds($values) ?: [];

        foreach ($collection as $model) {
            if (!$model->active) {
                continue;
            }

            $elementId = $this->getReferenceId($model);
            $reference = $mapper->handle($model, $request, $elementId);

            if (!$reference) {
                continue;
            }

            foreach ($values as $itemId => $referenceId) {
                if ($referenceId == $model->id) {
                    $this->references[$itemId] = $reference;
                }
            }
        }
    }

    /**
     * Get class of the reference model.
     *
     * @return string|null
     */
    private function getReferenceModelClass(): ?string
    {
        switch ($this->model->referenceType) {
            case 'reflayer':
                return LayerModel::class;
            case 'refvector':
                return VectorModel::class;
            case 'refmarker':
                return MarkerModel::class;

            default:
                return null;
        }
    }

    /**
     * Prepare used values.
     *
     * @param MetaModel $metaModel The meta model.
     * @param Items     $items     The meta model items list.
     * @param Attribute $reference The reference attribute.
     * @param array     $values    The reference values.
     * @param array     $icons     The used icons.
     * @param array     $styles    The used styles.
     *
     * @return void
     */
    protected function prepareValues(
        MetaModel $metaModel,
        Items $items,
        Attribute $reference,
        &$values,
        &$icons,
        &$styles
    ): void {
        $icon  = $metaModel->getAttributeById($this->model->iconAttribute);
        $style = $metaModel->getAttributeById($this->model->styleAttribute);

        foreach ($items as $item) {
            $value  = $item->get($reference->getColName());
            $itemId = $item->get('id');

            if ($value) {
                $values[$itemId] = $this->getAttributeValue($value);
            }

            if ($icon) {
                $value = $item->get($icon->getColName());

                if ($value) {
                    $icons[$itemId] = $this->getAttributeValue($value);
                }
            }

            if ($style) {
                $value = $item->get($style->getColName());

                if ($value) {
                    $styles[$itemId] = $this->getAttributeValue($value);
                }
            }
        }
    }

    /**
     * Apply the popup.
     *
     * @param Item  $item       The MetaModel item.
     * @param mixed $definition The definition.
     *
     * @return void
     */
    protected function applyPopup(Item $item, $definition)
    {
        if ($definition instanceof HasPopup) {
            $settings = $this->getRenderSettings($item->getMetaModel());
            $popup    = $this->getPopupContent($item, $settings);

            if ($popup) {
                $definition->bindPopup($popup);
            }
        }
    }

    /**
     * Apply a marker.
     *
     * @param Item  $item       The MetaModel item.
     * @param mixed $definition The definition.
     *
     * @return void
     */
    protected function applyMarker(Item $item, $definition)
    {
        if ($definition instanceof Marker) {
            $icon = $this->getIcon($item->get('id'));

            if ($icon) {
                $definition->setIcon($icon);
            }
        }
    }


    /**
     * Pre load styles.
     *
     * @param array            $values Ids of used styles.
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return void
     */
    protected function preLoadStyles(array $values, DefinitionMapper $mapper)
    {
        $collection = StyleModel::findMultipleByIds($values);
        if (!$collection) {
            return;
        }

        foreach ($collection as $model) {
            if (!$model->active) {
                continue;
            }

            $style = $mapper->handle($model);

            if (!$style) {
                continue;
            }

            foreach ($values as $itemId => $iconId) {
                if ($iconId == $model->id) {
                    $this->styles[$itemId] = $style;
                }
            }
        }
    }

    /**
     * Apply the path style.
     *
     * @param Item  $item       The MetaModel item.
     * @param mixed $definition The definition.
     *
     * @return void
     */
    protected function applyStyle(Item $item, $definition)
    {
        if ($definition instanceof Path) {
            $style = $this->getStyle($item->get('id'));

            if ($style) {
                $style->apply($definition);
            } elseif ($this->fallbackStyle) {
                $this->fallbackStyle->apply($definition);
            }
        }
    }

    /**
     * Load fallback style.
     *
     * @param DefinitionMapper $mapper The definition mapper.
     *
     * @return void
     */
    protected function loadFallbackStyle(DefinitionMapper $mapper): void
    {
        if ($this->model->style) {
            /** @var Repository|StyleModel $repository */
            $repository = $this->repositoryManager->getRepository(StyleModel::class);
            $styleModel = $repository->findActiveByPK($this->model->style);

            if ($styleModel) {
                $this->fallbackStyle = $mapper->handle($styleModel);
            }
        }
    }

    /**
     * Get the style for the MetaModel item.
     *
     * @param int $itemId The MetaModel item id.
     *
     * @return Style|null
     */
    protected function getStyle($itemId): ?Style
    {
        if (isset($this->styles[$itemId])) {
            return $this->styles[$itemId];
        }

        return $this->fallbackStyle;
    }

    /**
     * Simplify attribute value by reducing array of select values.
     *
     * @param mixed $value The given value.
     *
     * @return mixed
     */
    protected function getAttributeValue($value)
    {
        return is_array($value) ? $value['id'] : $value;
    }

    /**
     * Get the reference id for the model.
     *
     * @param Model $model The model.
     *
     * @return null|string
     */
    private function getReferenceId(Model $model): ?string
    {
        $elementId = null;
        if ($this->model->standalone) {
            $elementId = sprintf('%s_%s_%s', $this->layerModel->alias, $this->model->alias, $model->id);

            return $elementId;
        }

        return $elementId;
    }
}
