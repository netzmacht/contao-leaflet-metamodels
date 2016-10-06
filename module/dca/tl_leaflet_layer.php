<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

array_insert(
    $GLOBALS['TL_DCA']['tl_leaflet_layer']['list']['operations'],
    0,
    array(
        'metamodels' => array
        (
            'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodels'],
            'href'                => 'table=tl_leaflet_mm_renderer',
            'icon'                => 'edit.gif',
            'button_callback'     => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Layer::callback('generateRendererButton'),
        )
    )
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['metapalettes']['metamodels extends default'] = array
(
    '+title'  => array('metamodel'),
    '+config' => array(
        'boundsMode',
        'metamodel_use_limit', 
        'metamodel_sortby', 
        'metamodel_sortby_direction',
        'metamodel_filtering',
        'metamodel_filterparams'
    ),
    '+expert' => array('onEachFeature', 'pointToLayer'),
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['metasubpalettes']['metamodel_use_limit'] = array(
    'metamodel_limit',
    'metamodel_offset',
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel']                  = array
(
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Layer::callback('getMetaModels'),
    'eval'             => array
    (
        'mandatory'          => true,
        'submitOnChange'     => true,
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
    ),
    'sql'              => "int(11) NOT NULL default '0'"
);


/*
 * Fields.
 */
$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_use_limit'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_use_limit'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50 m12'),
    'sql'       => "char(1) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_offset'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_offset'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'digit', 'tl_class' => 'w50'),
    'sql'       => "smallint(5) NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_limit'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_limit'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'digit', 'tl_class' => 'w50 clr'),
    'sql'       => "smallint(5) NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_sortby'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_sortby'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('MetaModels\Dca\Content', 'getAttributeNames'),
    'eval'             => array
    (
        'includeBlankOption' => true,
        'chosen'             => true,
        'tl_class'           => 'w50 clr'
    ),
    'sql'              => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_sortby_direction'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_sortby_direction'],
    'exclude'   => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_leaflet_layer'],
    'options'   => array('ASC' => 'ASC', 'DESC' => 'DESC'),
    'eval'      => array
    (
        'includeBlankOption' => false,
        'chosen'             => true,
        'tl_class'           => 'w50'
    ),
    'sql'       => "varchar(4) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_filtering'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filtering'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('MetaModels\Dca\Content', 'getFilterSettings'),
    'default'          => '',
    'eval'             => array
    (
        'includeBlankOption' => true,
        'submitOnChange'     => true,
        'chosen'             => true,
        'tl_class'           => 'w50'
    ),
    'sql'              => "int(10) NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_leaflet_layer']['fields']['metamodel_filterparams'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filterparams'],
    'exclude'   => true,
    'inputType' => 'mm_subdca',
    'eval'      => array
    (
        'tl_class'   => 'clr m12',
        'flagfields' => array
        (
            'use_get' => array
            (
                'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_layer']['metamodel_filterparams_use_get'],
                'inputType' => 'checkbox'
            ),
        ),
    ),
    'sql'       => 'longblob NULL',
);
