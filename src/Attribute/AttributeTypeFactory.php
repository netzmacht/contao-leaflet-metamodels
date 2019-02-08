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

namespace Netzmacht\Contao\Leaflet\MetaModels\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\Helper\TableManipulator;

/**
 * Class AttributeTypeFactory for the leaflet map attribute.
 */
final class AttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Table manipulator.
     *
     * @var TableManipulator
     */
    private $tableManipulator;

    /**
     * AttributeTypeFactory constructor.
     *
     * @param Connection       $connection Database connection.
     * @param TableManipulator $tableManipulator Table manipulator.
     */
    public function __construct(Connection $connection, TableManipulator $tableManipulator)
    {
        $this->connection       = $connection;
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'leaflet_map';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeIcon(): string
    {
        return 'system/modules/leaflet/assets/img/map.png';
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new LeafletMapSelect($metaModel, $information, $this->connection, $this->tableManipulator);
    }

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType(): bool
    {
        return false;
    }

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType(): bool
    {
        return true;
    }

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType(): bool
    {
        return true;
    }
}
