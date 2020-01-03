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

use Contao\Model\Collection;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IItem;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Event\GetHashEvent;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for the MetaModels layer integration.
 */
final class Subscriber implements EventSubscriberInterface
{
    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * Subscriber constructor.
     *
     * @param RepositoryManager $repositoryManager Database connection.
     */
    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            GetHashEvent::NAME            => 'getItemHash',
            GetPropertyOptionsEvent::NAME => 'getPropertyOptions'
        );
    }

    /**
     * Create the hash for a metamodel item.
     *
     * @param GetHashEvent $event The get hash event.
     *
     * @return void
     */
    public function getItemHash(GetHashEvent $event): void
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
    public function getPropertyOptions(GetPropertyOptionsEvent $event): void
    {
        $model = $event->getModel();

        if ($model instanceof Model) {
            $item      = $model->getItem();
            $attribute = $item->getAttribute($event->getPropertyName());

            if (!$attribute) {
                return;
            }

            $selectTable = $attribute->get('select_table');
            $alias       = $attribute->get('select_alias');

            if ($selectTable === 'tl_leaflet_layer') {
                $collection = $this->fetchOptionsCollection(LayerModel::class, $attribute);
                $options    = OptionsBuilder::fromCollection($collection, [$this, 'parseLayerLabel'], $alias)
                    ->groupBy('pid', array($this, 'parseLayerGroup'))
                    ->getOptions();

                $event->setOptions($options);
            } elseif ($selectTable === 'tl_leaflet_vector' || $selectTable === 'tl_leaflet_marker') {
                $class      = $GLOBALS['TL_MODELS'][$selectTable];
                $collection = $this->fetchOptionsCollection($class, $attribute);
                $options    = OptionsBuilder::fromCollection($collection, $alias, $attribute->get('select_column'))
                    ->groupBy('pid', array($this, 'parseLayerGroup'))
                    ->getOptions();

                $event->setOptions($options);
            }
        }
    }

    /**
     * Parse the layer grouped value into the layer tree.
     *
     * @param int $value The layer id.
     *
     * @return string
     */
    public function parseLayerGroup($value): string
    {
        $repository = $this->repositoryManager->getRepository(LayerModel::class);
        $label      = '';

        do {
            $layer = $repository->find((int) $value);

            if ($layer) {
                if ($label) {
                    $label = '/' . $label;
                }

                $label = $layer->title . ' [' . $layer->id . ']' . $label;
                $value = $layer->pid;
            } else {
                $value = 0;
            }
        } while ($value > 0);

        return $label ?: '/';
    }

    /**
     * Parse the layer label.
     *
     * @param array $row The layer label.
     *
     * @return string
     */
    public function parseLayerLabel(array $row): string
    {
        return $row['title'] . ' [' . $row['type'] . ']';
    }

    /**
     * Fetch all options for a given model class.
     *
     * @param string     $modelClass The model class.
     * @param IAttribute $attribute  The MetaModel attribute which contains the select definitions.
     *
     * @return Collection|null
     */
    private function fetchOptionsCollection(string $modelClass, IAttribute $attribute): ?Collection
    {
        $repository = $this->repositoryManager->getRepository($modelClass);

        if ($attribute->get('select_where')) {
            return $repository->findBy(
                [$attribute->get('select_where')],
                [],
                ['order' => $attribute->get('select_sorting')]
            );
        }

        return $repository->findAll(['order' => $attribute->get('select_sorting')]);
    }
}
