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

use Netzmacht\Contao\Leaflet\MetaModels\EventListener\Dca\LayerDcaListener;

array_insert(
    $GLOBALS['TL_DCA']['tl_leaflet_layer']['list']['operations'],
    0,
    [
        'metamodels' => [
            'label'           => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodels'],
            'href'            => 'table=tl_leaflet_mm_renderer',
            'icon'            => 'edit.gif',
            'button_callback' => [
                LayerDcaListener::class,
                'generateRendererButton',
            ],
        ],
    ]
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['metapalettes']['metamodels extends default'] = [
    '+title'  => ['metamodel'],
    '+config' => [
        'boundsMode',
        'metamodel_use_limit',
        'metamodel_sortby',
        'metamodel_sortby_direction',
        'metamodel_filtering',
        'metamodel_filterparams',
    ],
    '+expert' => ['onEachFeature', 'pointToLayer'],
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['metasubpalettes']['metamodel_use_limit'] = [
    'metamodel_limit',
    'metamodel_offset',
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [LayerDcaListener::class, 'getMetaModels'],
    'eval'             => [
        'mandatory'          => true,
        'submitOnChange'     => true,
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
    ],
    'sql'              => "int(11) NOT NULL default '0'",
];


/*
 * Fields
 */

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_use_limit'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_use_limit'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50 m12'],
    'sql'       => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_offset'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_offset'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql'       => "smallint(5) NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_limit'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_limit'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['rgxp' => 'digit', 'tl_class' => 'w50 clr'],
    'sql'       => "smallint(5) NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_sortby'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_sortby'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => ['MetaModels\Dca\Content', 'getAttributeNames'],
    'eval'             => [
        'includeBlankOption' => true,
        'chosen'             => true,
        'tl_class'           => 'w50 clr',
    ],
    'sql'              => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_sortby_direction'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_sortby_direction'],
    'exclude'   => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_leaflet_layer'],
    'options'   => ['ASC' => 'ASC', 'DESC' => 'DESC'],
    'eval'      => [
        'includeBlankOption' => false,
        'chosen'             => true,
        'tl_class'           => 'w50',
    ],
    'sql'       => "varchar(4) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_filtering'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filtering'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => ['MetaModels\Dca\Content', 'getFilterSettings'],
    'default'          => '',
    'eval'             => [
        'includeBlankOption' => true,
        'submitOnChange'     => true,
        'chosen'             => true,
        'tl_class'           => 'w50',
    ],
    'sql'              => "int(10) NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_filterparams'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filterparams'],
    'exclude'   => true,
    'inputType' => 'mm_subdca',
    'eval'      => [
        'tl_class'   => 'clr m12',
        'flagfields' => [
            'use_get' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filterparams_use_get'],
                'inputType' => 'checkbox',
            ],
        ],
    ],
    'sql'       => 'longblob NULL',
];
