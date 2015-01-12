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


use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IItem;
use Netzmacht\Contao\DevTools\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Event\GetHashEvent;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Subscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            GetHashEvent::NAME            => 'getItemHash',
            GetPropertyOptionsEvent::NAME => 'getPropertyOptions'
        );
    }

    public function getItemHash(GetHashEvent $event)
    {
        $data = $event->getData();

        if ($data instanceof IItem) {
            $hash = sprintf(
                '%s_%s',
                $data->getMetaModel()->getTableName(),
                $data->get('id')
            );

            $event->setHash($hash);
        }
    }

    /**
     * Group leaflet markers, layers and vectors by their group.
     *
     * @param GetPropertyOptionsEvent $event The GetPropertyOptions Event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getPropertyOptions(GetPropertyOptionsEvent $event)
    {
        $model = $event->getModel();

        if ($model instanceof Model) {
            $item        = $model->getItem();
            $attribute   = $item->getAttribute($event->getPropertyName());

            if (!$attribute) {
                return;
            }

            $selectTable = $attribute->get('select_table');
            $alias       = $attribute->get('select_alias');

            if ($selectTable == 'tl_leaflet_layer') {
                $collection = $this->fetchOptionsCollection('Netzmacht\Contao\Leaflet\Model\LayerModel', $attribute);
                $options    = OptionsBuilder::fromCollection($collection, $alias, array($this, 'parseLayerLabel'))
                    ->groupBy('pid', array($this, 'parseLayerGroup'))
                    ->getOptions();

                $event->setOptions($options);
            } elseif ($selectTable == 'tl_leaflet_vector' || $selectTable == 'tl_leaflet_marker') {
                $class      = $GLOBALS['TL_MODELS'][$selectTable];
                $collection = $this->fetchOptionsCollection($class, $attribute);
                $options    = OptionsBuilder::fromCollection($collection, $alias, $attribute->get('select_column'))
                    ->groupBy('pid', array($this, 'parseLayerGroup'))
                    ->getOptions();

                $event->setOptions($options);
            }
        }
    }

    public function parseLayerGroup($value)
    {
        $label = '';

        do {
            $layer = LayerModel::findByPK($value);

            if ($layer) {
                if ($label) {
                    $label = '/' . $label;
                }

                $label = $layer->title . $label ;
                $value = $layer->pid;

            } else {
                $value = false;
            }
        } while ($value > 0);

        return $label ?: '/';
    }

    public function parseLayerLabel($row)
    {
        return $row['title'] . ' [' . $row['type'] . ']';
    }

    /**
     * @param            $modelClass
     * @param IAttribute $attribute
     *
     * @return \Model\Collection|null
     */
    protected function fetchOptionsCollection($modelClass, IAttribute $attribute)
    {
        if ($attribute->get('select_where')) {
            $collection = $modelClass::findBy(
                array($attribute->get('select_where')),
                array(),
                array('order' => $attribute->get('select_sorting'))
            );

            return $collection;
        } else {
            $collection = $modelClass::findAll(
                array('order' => $attribute->get('select_sorting'))
            );

            return $collection;
        }
    }
}
