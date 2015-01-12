<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Dca;


use Netzmacht\Contao\DevTools\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\DevTools\ServiceContainerTrait;

class Layer
{
    use ServiceContainerTrait;

    private $layers;

    /**
     * @var \Database
     */
    private $database;

    public function __construct()
    {
        $this->layers   = &$GLOBALS['LEAFLET_LAYERS'];
        $this->database = static::getService('database.connection');

        \Controller::loadLanguageFile('leaflet_layer');
    }

    public function getMetaModels()
    {
        $result = \Database::getInstance()->query('SELECT id, name FROM tl_metamodel ORDER BY name');

        $options =  OptionsBuilder::fromResult($result, 'id', 'name')->getOptions();

        return $options;
    }

    public function editMetaModel()
    {

    }

    public function generateFeatureButton($row, $href, $label, $title, $icon, $attributes)
    {
        if (empty($this->layers[$row['type']]['metamodels'])) {
            return '';
        }

        return sprintf(
            '<a href="%s" title="%s">%s</a> ',
            \Backend::addToUrl($href . '&amp;id=' . $row['id']),
            $title,
            \Image::getHtml($icon, $label, $attributes)
        );
    }
}
