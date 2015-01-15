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


use MetaModels\IItem;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;

/**
 * Interface LoadsReferred describes MetaModel features which can also being loaded when called in the deferred mode.
 *
 * This means that each feature can be handled as GeoJSON feature.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Feature
 */
interface LoadsDeferred
{
    /**
     * Apply feature to an item.
     *
     * @param IItem             $item              Current meta model item.
     * @param FeatureCollection $featureCollection The geo json feature collection.
     * @param DefinitionMapper  $mapper            The definition mapper.
     * @param LatLngBounds      $bounds            Optional LatLng bounds.
     *
     * @return void
     */
    public function applyGeoJson(
        IItem $item,
        FeatureCollection $featureCollection,
        DefinitionMapper $mapper,
        LatLngBounds $bounds = null
    );
}
