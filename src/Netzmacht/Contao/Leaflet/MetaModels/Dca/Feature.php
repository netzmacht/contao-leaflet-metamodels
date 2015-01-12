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
use Netzmacht\Contao\DevTools\Dca\Options\OptionsBuilder;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Leaflet\Model\StyleModel;

class Feature
{
    public function getAttributes($dataContainer)
    {
        $options = array();

        if ($dataContainer->activeRecord) {
            $layer     = LayerModel::findByPK($dataContainer->activeRecord->pid);

            if (!$layer) {
                return $options;
            }

            $factory   = new Factory();
            $metaModel = $factory->byId($layer->metamodel);

            if($metaModel) {
                foreach($metaModel->getAttributes() as $name => $attribute) {
                    $options[$attribute->get('id')] = $attribute->getName();
                }
            }
        }

        return $options;
    }

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

    public function generateRow($row)
    {
        return sprintf('%s <span class="tl_gray">%s</span>', $row['title'], $row['type']);
    }
}
