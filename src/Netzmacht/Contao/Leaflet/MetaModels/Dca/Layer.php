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

use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Toolkit\ServiceContainerTrait;

/**
 * Helper class for the metamodels integration into tl_leaflet_layer.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
class Layer
{
    use ServiceContainerTrait;

    /**
     * Layers definition.
     *
     * @var array
     */
    private $layers;

    /**
     * The database connection.
     *
     * @var \Database
     */
    private $database;

    /**
     * Construct.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        $this->layers   = &$GLOBALS['LEAFLET_LAYERS'];
        $this->database = static::getServiceContainer()->getDatabaseConnection();

        \Controller::loadLanguageFile('leaflet_layer');
    }

    /**
     * Get all metamodels.
     *
     * @return array
     */
    public function getMetaModels()
    {
        $result  = $this->database->query('SELECT id, name FROM tl_metamodel ORDER BY name');
        $options = OptionsBuilder::fromResult($result, 'id', 'name')->getOptions();

        return $options;
    }

    /**
     * Generate the metamodels renderer button.
     *
     * @param array  $row        Current row.
     * @param string $href       The button href.
     * @param string $label      The button label.
     * @param string $title      The button title.
     * @param string $icon       The button icon.
     * @param string $attributes Optional attributes.
     *
     * @return string
     */
    public function generateRendererButton($row, $href, $label, $title, $icon, $attributes)
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
