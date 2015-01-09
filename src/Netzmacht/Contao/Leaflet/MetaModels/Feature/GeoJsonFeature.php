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
use Netzmacht\LeafletPHP\Definition\Group\LayerGroup;
use Netzmacht\LeafletPHP\Definition\Type\LatLngBounds;

class GeoJsonFeature extends AbstractFeature
{
    /**
     * Apply feature to an item.
     *
     * @param Item             $item        Current meta model item.
     * @param LayerGroup       $parentLayer The parent layer.
     * @param DefinitionMapper $mapper
     * @param LatLngBounds     $bounds
     */
    public function apply(Item $item, LayerGroup $parentLayer, DefinitionMapper $mapper, LatLngBounds $bounds = null)
    {
        // TODO: Implement apply() method.
    }
}
