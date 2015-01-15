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


use MetaModels\IItem as Item;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\GeoJson\StaticFeature;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;
use Netzmacht\LeafletPHP\Plugins\Ajax\GeoJsonAjax;

/**
 * Class GeoJsonFeature implements a MetaModel map feature for GeoJSON data.
 *
 * The GeoJSON data can be stored in an attribute or be linked by a file attribute.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Feature
 */
class GeoJsonFeature extends AbstractFeature implements LoadsDeferred
{
    /**
     * {@inheritdoc}
     */
    public function apply(Item $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        $attribute = $this->getAttribute('geojsonAttribute', $item);

        switch ($attribute->get('type')) {
            case 'file':
                $this->loadFeaturesFromFile(
                    $item,
                    $attribute->getColName(),
                    function ($path, $key) use ($parentLayer) {
                        $id    = $parentLayer->getId() . '_file_' . $key;
                        $layer = new GeoJsonAjax($id, $path);

                        $layer->setUrl($path);
                        $parentLayer->addLayer($layer);
                    }
                );
                break;

            default:
                $parentLayer->addLayer(new StaticFeature($item->get($attribute->getColName())));
        }
    }

    /**
     * Apply feature to an item.
     *
     * @param Item              $item              Current meta model item.
     * @param FeatureCollection $featureCollection The geo json feature collection.
     * @param DefinitionMapper  $mapper            The definition mapper.
     * @param LatLngBounds      $bounds            Optional LatLng bounds.
     *
     * @return void
     */
    public function applyGeoJson(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    ) {
        $attribute = $this->getAttribute('geojsonAttribute', $item);

        switch ($attribute->get('type')) {
            case 'file':
                $this->loadFeaturesFromFile(
                    $item,
                    $attribute->getColName(),
                    function ($path) use ($featureCollection) {
                        $path = TL_ROOT . '/' . $path;

                        if (file_exists($path)) {
                            $content = file_get_contents($path);
                            $featureCollection->addFeature(new StaticFeature($content));
                        }
                    }
                );
                break;

            default:
                $featureCollection->addFeature(new StaticFeature($item->get($attribute->getColName())));
        }
    }

    /**
     * Load features from file attribute.
     *
     * @param Item      $item      The metamodel.
     * @param string    $attribute The attribute name.
     * @param \callable $callback  The callback being called for each file.
     *
     * @return void
     */
    private function loadFeaturesFromFile(Item $item, $attribute, $callback)
    {
        $value = $item->parseAttribute($attribute);

        if (!is_array($value['raw'])) {
            return;
        }

        foreach ($value['raw']['path'] as $key => $path) {
            $callback($path, $key);
        }
    }
}
