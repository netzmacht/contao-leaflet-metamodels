<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * Class Subscriber handles events for the leaflet map attribute.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Attribute
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Boot the system in the backend.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->addListener(
            GetPropertyOptionsEvent::NAME,
            array($this, 'getColumnNames')
        );

        $this->addListener(
            GetPropertyOptionsEvent::NAME,
            array($this, 'getMapTemplates')
        );
    }

    /**
     * Retrieve all column names for the current selected table.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getColumnNames(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getPropertyName() !== 'select_sorting')
        ) {
            return;
        }

        $database = $this->getServiceContainer()->getDatabase();
        $result   = array();

        foreach ($database->listFields('tl_leaflet_map') as $arrInfo) {
            if ($arrInfo['type'] != 'index') {
                $result[$arrInfo['name']] = $arrInfo['name'];
            }
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
    public function getMapTemplates(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_rendersetting')
            || ($event->getPropertyName() !== 'leaflet_template')
        ) {
            return;
        }

        $event->setOptions(\Controller::getTemplateGroup('leaflet_map_'));
    }
}
