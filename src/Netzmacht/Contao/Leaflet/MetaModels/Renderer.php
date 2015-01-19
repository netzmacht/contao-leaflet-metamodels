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

use MetaModels\IItem as Item;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;

/**
 * A Feature can be applied to a MetaModel item so that it will be added to a layer group.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels
 */
interface Renderer
{
    /**
     * Prepare the renderer for the current items list.
     *
     * @param MetaModel        $metaModel The used MetaModel.
     * @param Items            $items     The MetaModel items list.
     * @param DefinitionMapper $mapper    The definition mapper.
     * @param LatLngBounds     $bounds    Optional bounds of the map.
     * @param bool             $deferred  Prepare the renderer for the fetching data only.
     *
     * @return void
     */
    public function prepare(
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null,
        $deferred = false
    );

    /**
     * Load data from the item and pass it to the feature collection.
     *
     * @param Item              $item              Current meta model item.
     * @param FeatureCollection $featureCollection The data layer.
     * @param DefinitionMapper  $mapper            The definition mapper.
     * @param string            $parentId          Id of the parent layer.
     * @param LatLngBounds      $bounds            Optional LatLng bounds.
     * @param bool              $deferred          Data is loaded in deferred mode.
     *
     * @return void
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        LatLngBounds $bounds = null,
        $deferred = false
    );

    /**
     * Load layers from the item and assign them to the parent layer.
     *
     * @param Item             $item      Current meta model item.
     * @param GeoJson          $dataLayer The data layer.
     * @param DefinitionMapper $mapper    The definition mapper.
     * @param LatLngBounds     $bounds    Optional LatLng bounds.
     *
     * @return void
     */
    public function loadLayers(
        Item $item,
        GeoJson $dataLayer,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    );
}
