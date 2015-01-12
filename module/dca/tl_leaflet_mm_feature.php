<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

$GLOBALS['TL_DCA']['tl_leaflet_mm_feature'] = array
(
    'config' => array(
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_leaflet_layer',
        'enableVersioning' => true,
        'sql'              => array
        (
            'keys' => array
            (
                'id'    => 'primary',
                'pid'   => 'index',
                'alias' => 'unique',
            )
        )
    ),

    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'limit',
            'headerFields'            => array('title', 'type', 'metamodel'),
            'child_record_callback'   => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'generateRow'),
        ),
        'label' => array
        (
            'fields'                  => array('title', 'type'),
            'format'                  => '%s <span class="tl_gray">[%s]</span>',
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'toggle' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => \Netzmacht\Contao\DevTools\Dca::createToggleIconCallback(
                    'tl_leaflet_mm_feature',
                    'active'
                )
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    'palettes' => array(
        '__selector__' => array('type')
    ),

    'metapalettes'    => array(
        'default' => array(
            'title'  => array('title', 'alias', 'type'),
            'config' => array(),
            'active' => array('active'),
        ),
        'marker extends default' => array(
            'config' => array('coordinates'),
            'popup before active'       => array('addPopup', 'renderSettings'),
            'icon before active'        => array('icon', 'iconAttribute')
        ),

        'geojson extends default' => array(
            '+title' => array('geojsonAttribute'),
        ),
        
        'reference extends default' =>  array(
            'config' => array('referenceType', 'referenceAttribute', 'standalone'),
            'popup before active'       => array('addPopup', 'renderSettings'),
            'icon before active'        => array('icon', 'iconAttribute'),
            'style before active'       => array('style', 'styleAttribute')
        )
    ),

    'metasubselectpalettes' => array(
        'coordinates' => array(
            'single'   => array('coordinatesAttribute'),
            'separate' => array('latitudeAttribute', 'longitudeAttribute'),
        ),

        'addPopup' => array(
            'attribute' => array('popupAttribute'),
        ),
    ),

    'fields' => array
    (
        'id'           => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'       => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'pid'       => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'       => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'alias'        => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['alias'],
            'exclude'       => true,
            'inputType'     => 'text',
            'save_callback' => array(
                \Netzmacht\Contao\DevTools\Dca::createGenerateAliasCallback('tl_leaflet_mm_feature', 'title'),
            ),
            'eval'          => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'unique' => true),
            'sql'           => "varchar(255) NOT NULL default ''"
        ),
        'type'                  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ),
            'options'   => array_keys($GLOBALS['LEAFLET_MM_FEATURES']),
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_feature'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ),
        'coordinates'                  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['coordinates'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ),
            'options'   => array('single', 'separate'),
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_feature'],
            'sql'       => "varchar(8) NOT NULL default ''"
        ),
        'coordinatesAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['coordinatesAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['longitudeAttribute'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'latitudeAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['latitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['latitudeAttribute'],
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'longitudeAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['longitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'geojsonAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['geojsonAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'renderSettings'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['renderSettings'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getRenderSettings'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['renderSettings'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'active'                => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['active'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'flag'      => 12,
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'addPopup'                => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['addPopup'],
            'exclude'   => true,
            'inputType' => 'select',
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'options'   => array('attribute', 'render'),
            'flag'      => 12,
            'eval'      => array('tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true),
            'sql'       => "varchar(16) NOT NULL default ''"
        ),
        'popupAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['popupAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['latitudeAttribute'],
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'customIcon'                  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['customIcon'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ),
            'options' => array('attribute', 'icon'),
            'sql'     => "varchar(10) NOT NULL default ''"
        ),
        'icon'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['icon'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\Dca\Marker', 'getIcons'),
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'chosen'             => true,
                'includeBlankOption' => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ),
        'iconAttribute' => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['iconAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'referenceType'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['referenceType'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options' => array('layer', 'marker', 'vector'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['referenceTypes'],
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "varchar(16) NOT NULL default ''"
        ),
        'referenceAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['referenceAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'standalone' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['standalone'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => false,
            'eval'      => array('tl_class' => 'w50', 'submitOnChange' => false, 'isBoolean' => true),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'style'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['style'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\Dca\Vector', 'getStyles'),
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'chosen'             => true,
                'includeBlankOption' => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ),
        'styleAttribute' => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['styleAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['longitudeAttribute'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
    ),
);
