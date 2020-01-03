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

use MetaModels\IItem as Item;
use MetaModels\IItems as Items;
use MetaModels\IMetaModel as MetaModel;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\Contao\Leaflet\Mapper\Request;
use Netzmacht\LeafletPHP\Value\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\GeoJson;

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
     * @param Request          $request   Optional request filter.
     * @param bool             $deferred  Prepare the renderer for the fetching data only.
     *
     * @return void
     */
    public function prepare(
        MetaModel $metaModel,
        Items $items,
        DefinitionMapper $mapper,
        Request $request = null,
        $deferred = false
    ): void;

    /**
     * Load data from the item and pass it to the feature collection.
     *
     * @param Item              $item              Current meta model item.
     * @param FeatureCollection $featureCollection The data layer.
     * @param DefinitionMapper  $mapper            The definition mapper.
     * @param string            $parentId          Id of the parent layer.
     * @param Request           $request           Optional request filter.
     * @param bool              $deferred          Data is loaded in deferred mode.
     *
     * @return void
     */
    public function loadData(
        Item $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        $parentId,
        Request $request = null,
        $deferred = false
    ): void;

    /**
     * Load layers from the item and assign them to the parent layer.
     *
     * @param Item             $item      Current meta model item.
     * @param GeoJson          $dataLayer The data layer.
     * @param DefinitionMapper $mapper    The definition mapper.
     * @param Request          $request   Optional request filter.
     *
     * @return void
     */
    public function loadLayers(
        Item $item,
        GeoJson $dataLayer,
        DefinitionMapper $mapper,
        Request $request = null
    ): void;
}
