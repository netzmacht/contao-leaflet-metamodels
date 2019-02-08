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

namespace Netzmacht\Contao\Leaflet\MetaModels\EventListener;

use Contao\Controller;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Subscriber handles events for the leaflet map attribute.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Attribute
 */
final class AttributeSubscriber implements EventSubscriberInterface
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * AttributeSubscriber constructor.
     *
     * @param Connection $connection Database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GetPropertyOptionsEvent::NAME => [['getColumnNames'], ['getMapTemplates']]
        ];
    }

    /**
     * Retrieve all column names for the current selected table.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getColumnNames(GetPropertyOptionsEvent $event): void
    {
        if ($event->getPropertyName() !== 'select_sorting'
            || $event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute'
        ) {
            return;
        }

        $result = array();

        foreach ($this->connection->getSchemaManager()->listTableColumns('tl_leaflet_map') as $column) {
            $columnName          = $column->getName();
            $result[$columnName] = $columnName;
        }

        if (!empty($result)) {
            asort($result);
            $event->setOptions($result);
        }
    }

    /**
     * Retrieve all templates for the leaflet map template list.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getMapTemplates(GetPropertyOptionsEvent $event): void
    {
        if ($event->getPropertyName() !== 'leaflet_template'
            || $event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting'
        ) {
            return;
        }

        $event->setOptions(Controller::getTemplateGroup('leaflet_map_'));
    }
}
