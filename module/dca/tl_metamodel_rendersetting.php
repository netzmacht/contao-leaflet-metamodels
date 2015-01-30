<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes']['leaflet_map extends default'] = array
(
    '+advanced' => array('leaflet_template', 'leaflet_style', 'leaflet_feOnly'),
);

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_template'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_template'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'eval'                    => array(
        'tl_class'            => 'w50',
        'chosen'              => true,
        'includeBlankOption'  => true,
    ),
    'sql'                     => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_style'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_style'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array(
        'tl_class'            => 'w50',
    ),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['leaflet_feOnly'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['leaflet_feOnly'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'default'                 => true,
    'eval'                    => array(
        'tl_class'            => 'w50',
    ),
    'sql'                     => "char(1) NOT NULL default ''"
);
