<?php

/**
 * @package    contao-leaflet-metamodels
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015-2016 netzmacht David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

$GLOBALS['TL_DCA']['tl_leaflet_mm_renderer'] = array
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
            'child_record_callback'   => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('generateRow'),
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
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.gif'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'toggle' => array
            (
                'label'           => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => \Netzmacht\Contao\Toolkit\Dca\Callback\CallbackFactory::stateButton(
                    'tl_leaflet_mm_renderer',
                    'active'
                )
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            )
        )
    ),

    'palettes' => array(
        '__selector__' => array('type', 'referenceType', 'standalone')
    ),
    'metapalettes' => array(
        'default'                                => array(
            'title'  => array('title', 'alias', 'type'),
            'config' => array(),
            'active' => array('active'),
        ),
        'marker extends default'                 => array(
            'config'              => array('coordinates'),
            'popup before active' => array('addPopup', 'renderSettings'),
            'icon before active'  => array('icon', 'iconAttribute'),
            '+active'             => array('deferred', 'ignoreForBounds')
        ),
        'geojson extends default'                => array(
            '+title'  => array('geojsonAttribute'),
            '+active' => array('deferred')
        ),
        'reference extends default'              => array(
            'config'  => array('referenceType', 'referenceAttribute', 'standalone'),
            '+active' => array('deferred', 'ignoreForBounds')
        ),
        'referencereflayer extends reference' => array(
            '-active' => array('deferred', 'ignoreForBounds')
        ),
        'referencerefmarkerstandalone extends reference' => array(
            'popup before active' => array('addPopup', 'renderSettings'),
            'icon before active'  => array('icon', 'iconAttribute'),
            'style before active' => array('style', 'styleAttribute')
        ),
        'referencerefvectorstandalone extends reference' => array(
            'popup before active' => array('addPopup', 'renderSettings'),
            'icon before active'  => array('icon', 'iconAttribute'),
            'style before active' => array('style', 'styleAttribute'),
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
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'alias'        => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['alias'],
            'exclude'       => true,
            'inputType'     => 'text',
            'save_callback' => array(
                \Netzmacht\Contao\Toolkit\Dca\Callback\CallbackFactory::aliasGenerator(
                    'tl_leaflet_mm_renderer',
                    'title'
                ),
            ),
            'eval'          => array('mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'unique' => true),
            'sql'           => "varchar(255) NOT NULL default ''"
        ),
        'type'                  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ),
            'options'   => array_keys($GLOBALS['LEAFLET_MM_RENDERER']),
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_renderer'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ),
        'coordinates'                  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['coordinates'],
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
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_renderer'],
            'sql'       => "varchar(8) NOT NULL default ''"
        ),
        'coordinatesAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['coordinatesAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['geojsonAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['renderSettings'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getRenderSettings'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['renderSettings'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['active'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'flag'      => 12,
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'deferred'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['deferred'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => true,
            'eval'      => array('tl_class' => 'w50', 'submitOnChange' => false, 'isBoolean' => true),
            'sql'       => "char(1) NOT NULL default '1'"
        ),
        'addPopup'                => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['addPopup'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['popupAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['customIcon'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['icon'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\Dca\MarkerCallbacks', 'getIcons'),
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['iconAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceType'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options'          => array('reflayer', 'refmarker', 'refvector'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceTypes'],
            'eval'             => array(
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ),
            'sql'              => "varchar(32) NOT NULL default ''"
        ),
        'referenceAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
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
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['standalone'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => false,
            'eval'      => array('tl_class' => 'w50', 'submitOnChange' => true, 'isBoolean' => true),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'style'          => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['style'],
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
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['styleAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => \Netzmacht\Contao\Leaflet\MetaModels\Dca\Renderer::callback('getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'ignoreForBounds' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['ignoreForBounds'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => false,
            'eval'      => array('tl_class' => 'w50'),
            'sql'       => "char(1) NOT NULL default ''"
        ),
    ),
);
