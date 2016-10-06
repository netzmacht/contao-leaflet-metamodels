<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

use Netzmacht\Contao\Leaflet\MetaModels\Dca\Layer;
use Netzmacht\Contao\Toolkit\DependencyInjection\Services;

global $container;

$container['leaflet.mm.dca.layer'] = $container->share(
    function ($container) {
        return new Layer(
            $container[Services::DCA_MANAGER],
            $container[Services::DATABASE_CONNECTION],
            $GLOBALS['LEAFLET_LAYERS']
        );
    }
);

$container['leaflet.mm.dca.renderer-callbacks'] = $container->share(
    function ($container) {
        return new \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer(
            $container[Services::DCA_MANAGER],
            $container['metamodels-factory.factory']
        );
    }
);
