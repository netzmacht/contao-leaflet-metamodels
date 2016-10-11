<?php

/**
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2016 netzmacht David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['leaflet_map extends select'] = array
(
    '-display' => array(
        'select_table',
        'select_column',
        'select_id',
        'select_alias',
    )
);
