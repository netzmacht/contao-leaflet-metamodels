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


use Netzmacht\Javascript\Encoder;
use Netzmacht\Javascript\Type\ConvertsToJavascript;
use Netzmacht\LeafletPHP\Definition\AbstractDefinition;
use Netzmacht\LeafletPHP\Definition\LabelTrait;
use Netzmacht\LeafletPHP\Definition\Layer;

class StaticLayer extends AbstractDefinition implements Layer, ConvertsToJavascript
{
    use LabelTrait

    /**
     * Get the type of the definition.
     *
     * @return string
     */
    public static function getType()
    {
        return 'Geojson.Static';
    }

    /**
     * Encode the javascript representation of the object.
     *
     * @param Encoder $encoder The javascript encoder.
     * @param bool    $finish  If true the statement should be finished with an semicolon.
     *
     * @return string
     */
    public function encode(Encoder $encoder, $finish = true)
    {
    }
}
