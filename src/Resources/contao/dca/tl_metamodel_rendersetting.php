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

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes']['leaflet_map extends default'] = [
    '+advanced' => ['leaflet_template', 'leaflet_style', 'leaflet_feOnly'],
];

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_template'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_template'],
    'exclude'   => true,
    'inputType' => 'select',
    'eval'      => [
        'tl_class'           => 'w50',
        'chosen'             => true,
        'includeBlankOption' => true,
    ],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_style'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_style'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => [
        'tl_class' => 'w50',
    ],
    'sql'       => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_feOnly'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_feOnly'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'default'   => true,
    'eval'      => [
        'tl_class' => 'w50',
    ],
    'sql'       => "char(1) NOT NULL default ''",
];
