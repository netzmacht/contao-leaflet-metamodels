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

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['leaflet_map extends select'] = [
    '-display' => [
        'select_table',
        'select_column',
        'select_id',
        'select_alias',
    ],
];
