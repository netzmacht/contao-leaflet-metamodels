<?php

/**
 * Contao Leaflet MetaModels integration.
 *
 * @package    contao-leaflet-metamodels
 * @author     Cliff Parnitzky <contao@cliff-parnitzky.de>
 * @copyright  2015-2020 netzmacht David Molineus
 * @license    LGPL 3.0-or-later https://github.com/netzmacht/contao-leaflet-metamodels/blob/master/LICENSE
 * @filesource MetaModels\CoreBundle\Contao\Hooks\ModuleCallback
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Contao\Hooks;

use MetaModels\CoreBundle\Contao\Hooks\AbstractContentElementAndModuleCallback;

/**
 * This class provides callbacks for tl_module.
 */
class LeafletLayerCallback extends AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName = 'tl_leaflet_layer';

    /**
     * Called from tl_content.onload_callback.
     *
     * @param \Contao\DataContainer $dataContainer The data container calling this method.
     *
     * @return void
     */
    public function buildFilterParameterList(\Contao\DataContainer $dataContainer)
    {
        parent::buildFilterParamsFor($dataContainer, 'metamodels');
    }
}
