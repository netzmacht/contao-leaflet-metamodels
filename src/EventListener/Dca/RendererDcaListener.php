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

use Contao\DataContainer;
use MetaModels\Factory as MetaModelsFactory;
use Netzmacht\Contao\Leaflet\MetaModels\Renderer\RendererFactory;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\Listener\AbstractListener;
use Netzmacht\Contao\Toolkit\Dca\Manager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use PDO;

/**
 * Helper class for tl_leaflet_mm_feature.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
final class RendererDcaListener extends AbstractListener
{
    /**
     * Name of the data container.
     *
     * @var string
     */
    protected static $name = 'tl_leaflet_mm_renderer';

    /**
     * Meta models factory.
     *
     * @var MetaModelsFactory
     */
    private $metaModelsFactory;

    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * Layer renderer factory.
     *
     * @var RendererFactory
     */
    private $rendererFactory;

    /**
     * Renderer constructor.
     *
     * @param Manager           $manager           Data container manager.
     * @param MetaModelsFactory $metaModelsFactory MetaModels factory.
     * @param RepositoryManager $repositoryManager Repository manager.
     * @param RendererFactory   $rendererFactory   Renderer factory.
     */
    public function __construct(
        Manager $manager,
        MetaModelsFactory $metaModelsFactory,
        RepositoryManager $repositoryManager,
        RendererFactory $rendererFactory
    ) {
        parent::__construct($manager);

        $this->metaModelsFactory = $metaModelsFactory;
        $this->repositoryManager = $repositoryManager;
        $this->rendererFactory   = $rendererFactory;
    }

    /**
     * Get all attribute ids of a MetaModel.
     *
     * @param DataContainer $dataContainer The data container driver.
     *
     * @return array
     */
    public function getAttributes($dataContainer): array
    {
        $options = [];

        if ($dataContainer->activeRecord) {
            $repository = $this->repositoryManager->getRepository(LayerModel::class);
            $layer      = $repository->find((int) $dataContainer->activeRecord->pid);

            if ($layer === null) {
                return $options;
            }

            $name      = $this->metaModelsFactory->translateIdToMetaModelName($layer->metamodel);
            $metaModel = $this->metaModelsFactory->getMetaModel($name);

            if ($metaModel) {
                foreach ($metaModel->getAttributes() as $attribute) {
                    $options[$attribute->get('id')] = $attribute->getName();
                }
            }
        }

        return $options;
    }

    /**
     * Get all render settings of a MetaModel.
     *
     * @param DataContainer $dataContainer The data container driver.
     *
     * @return array
     */
    public function getRenderSettings($dataContainer): array
    {
        $settings = [];

        if ($dataContainer->activeRecord) {
            $repository = $this->repositoryManager->getRepository(LayerModel::class);
            $layer      = $repository->find((int) $dataContainer->activeRecord->pid);

            if ($layer === null) {
                return $settings;
            }

            $statement = $this->repositoryManager
                ->getConnection()
                ->prepare('SELECT id, name FROM tl_metamodel_rendersettings WHERE pid=:metaModelId');

            $statement->bindValue('metaModelId', $layer->metamodel);
            $statement->execute();

            return OptionsBuilder::fromArrayList($statement->fetchAll(PDO::FETCH_ASSOC), 'name')->getOptions();
        }

        return $settings;
    }

    /**
     * Get all renderer type options.
     *
     * @return array
     */
    public function getRendererTypes(): array
    {
        return $this->rendererFactory->supportedTypes();
    }

    /**
     * Generate the row label.
     *
     * @param array $row Current row.
     *
     * @return string
     */
    public function generateRow(array $row): string
    {
        return sprintf('%s <span class="tl_gray">%s</span>', $row['title'], $row['type']);
    }
}
