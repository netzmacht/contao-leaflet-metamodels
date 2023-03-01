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

namespace Netzmacht\Contao\Leaflet\MetaModels\EventListener\Dca;

use Contao\Backend;
use Contao\CoreBundle\Framework\Adapter;
use Contao\Image;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Netzmacht\Contao\Toolkit\Dca\Listener\AbstractListener;
use Netzmacht\Contao\Toolkit\Dca\Manager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;

/**
 * Helper class for the metamodels integration into tl_leaflet_layer.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
final class LayerDcaListener extends AbstractListener
{
    /**
     * Name of the data container.
     *
     * @var string
     */
    protected static $name = 'tl_leaflet_layer';

    /**
     * Layers definition.
     *
     * @var array
     */
    private $layers;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Contao backend adapter.
     *
     * @var Adapter|Backend
     */
    private $backendAdapter;

    /**
     * Construct.
     *
     * @param Manager    $manager        Data container manager.
     * @param Connection $connection     Database connection.
     * @param Adapter    $backendAdapter Backend adapter.
     * @param array      $layers         Leaflet layer configuration.
     */
    public function __construct(Manager $manager, Connection $connection, Adapter $backendAdapter, array $layers)
    {
        parent::__construct($manager);

        $this->connection     = $connection;
        $this->layers         = $layers;
        $this->backendAdapter = $backendAdapter;
    }

    /**
     * Initialize the data container.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->backendAdapter->__call('loadLanguageFile', ['leaflet_layer']);
    }

    /**
     * Get all MetaModels as options.
     *
     * @return array
     *
     * @throws DBALException When an database error occurred.
     */
    public function getMetaModels(): array
    {
        $result = $this->connection
            ->executeQuery('SELECT id, name FROM tl_metamodel ORDER BY name')
            ->fetchAllAssociative();

        return OptionsBuilder::fromArrayList($result, 'name')->getOptions();
    }

    /**
     * Generate the MetaModels renderer button.
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
    public function generateRendererButton(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        if (empty($this->layers[$row['type']]['metamodels'])) {
            return '';
        }

        return sprintf(
            '<a href="%s" title="%s">%s</a> ',
            $this->backendAdapter->__call('addToUrl', [$href . '&amp;id=' . $row['id']]),
            $title,
            Image::getHtml($icon, $label, $attributes)
        );
    }
}
