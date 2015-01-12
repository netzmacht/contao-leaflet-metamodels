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

use MetaModels\IItem;
use Netzmacht\Contao\Leaflet\Mapper\DefinitionMapper;
use Netzmacht\LeafletPHP\Definition\GeoJson\FeatureCollection;
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;

/**
 * A Feature can be applied to a MetaModel item so that it will be added to a layer group.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels
 */
interface Feature
{
    /**
     * Apply feature to an item.
     *
     * @param IItem            $item        Current meta model item.
     * @param LayerGroup       $parentLayer The parent layer.
     * @param DefinitionMapper $mapper      The definition mapper.
     * @param LatLngBounds     $bounds      Optional LatLng bounds.
     *
     * @return void
     */
    public function apply(IItem $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null);
}
