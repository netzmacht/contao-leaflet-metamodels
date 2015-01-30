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

use MetaModels\Factory;
use Netzmacht\Contao\Toolkit\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Model\StyleModel;

/**
 * Helper class for tl_leaflet_mm_feature.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Dca
 */
class Renderer
{
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
            $layer = LayerModel::findByPK($dataContainer->activeRecord->pid);

            if (!$layer) {
                return $options;
            }

            $factory   = new Factory();
            $metaModel = $factory->byId($layer->metamodel);

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

            return OptionsBuilder::fromResult($result, 'id', 'name')->getOptions();
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
