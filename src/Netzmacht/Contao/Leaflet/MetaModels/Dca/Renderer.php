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

use MetaModels\Factory as MetaModelsFactory;
use Netzmacht\Contao\Toolkit\Dca\Callback\Callbacks;
use Netzmacht\Contao\Toolkit\Dca\Manager;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Model\LayerModel;

/**
 * Helper class for tl_leaflet_mm_feature.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
class Renderer extends Callbacks
{
    /**
     * Name of the data container.
     *
     * @var string
     */
    protected static $name = 'tl_leaflet_mm_renderer';

    /**
     * Helper service name.
     *
     * @var string
     */
    protected static $serviceName = 'leaflet.mm.dca.renderer-callbacks';

    /**
     * Meta models factory.
     *
     * @var MetaModelsFactory
     */
    private $metaModelsFactory;

    /**
     * Renderer constructor.
     *
     * @param Manager           $manager           Data container manager.
     * @param MetaModelsFactory $metaModelsFactory MetaModels factory.
     */
    public function __construct(Manager $manager, MetaModelsFactory $metaModelsFactory)
    {
        parent::__construct($manager);

        $this->metaModelsFactory = $metaModelsFactory;
    }

    /**
     * Get all attribute ids of a metamodel.
     *
     * @param \DataContainer $dataContainer The data container driver.
     *
     * @return array
     */
    public function getAttributes($dataContainer)
    {
        $options = array();

        if ($dataContainer->activeRecord) {
            $layer = LayerModel::findByPk($dataContainer->activeRecord->pid);

            if (!$layer) {
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
     * Get all render settings of a metamodel.
     *
     * @param \DataContainer $dataContainer The data container driver.
     *
     * @return array
     */
    public function getRenderSettings($dataContainer)
    {
        $settings = array();

        if ($dataContainer->activeRecord) {
            $layer = LayerModel::findByPk($dataContainer->activeRecord->pid);

            if (!$layer) {
                return $settings;
            }

            $result = \Database::getInstance()
                ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=?')
                ->execute($layer->metamodel);

            return OptionsBuilder::fromResult($result, 'name')->getOptions();
        }

        return $settings;
    }

    /**
     * Generate the row label.
     *
     * @param array $row Current row.
     *
     * @return string
     */
    public function generateRow($row)
    {
        return sprintf('%s <span class="tl_gray">%s</span>', $row['title'], $row['type']);
    }
}
