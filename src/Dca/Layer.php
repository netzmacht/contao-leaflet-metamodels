<?php

/**
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2016 netzmacht David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Dca;

use Netzmacht\Contao\Toolkit\Dca\Callback\Callbacks;
use Netzmacht\Contao\Toolkit\Dca\Manager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;

/**
 * Helper class for the metamodels integration into tl_leaflet_layer.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
class Layer extends Callbacks
{
    /**
     * Name of the data container.
     *
     * @var string
     */
    protected static $name = 'tl_leaflet_layer';

    /**
     * Helper service name.
     *
     * @var string
     */
    protected static $serviceName = 'leaflet.mm.dca.layer-callbacks';

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
     * @param Manager   $manager  Data container manager.
     * @param \Database $database Database connection.
     * @param array     $layers   Leaflet layer configuration.
     */
    public function __construct(Manager $manager, \Database $database, array $layers)
    {
        parent::__construct($manager);

        $this->database = $database;
        $this->layers   = $layers;

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
        $options = OptionsBuilder::fromResult($result, 'name')->getOptions();

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
