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

use Netzmacht\Contao\Leaflet\Listener\Dca\VectorDcaListener;
use Netzmacht\Contao\Leaflet\MetaModels\EventListener\Dca\RendererDcaListener;
use Netzmacht\Contao\Toolkit\Dca\Listener\Button\StateButtonCallbackListener;

$GLOBALS['TL_DCA']['tl_leaflet_mm_renderer'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_leaflet_layer',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'    => 'primary',
                'pid'   => 'index',
                'alias' => 'unique',
            ],
        ],
    ],

    'list' => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['title'],
            'flag'                  => 1,
            'panelLayout'           => 'limit',
            'headerFields'          => ['title', 'type', 'metamodel'],
            'child_record_callback' => [RendererDcaListener::class, 'generateRow'],
        ],
        'label'             => [
            'fields' => ['title', 'type'],
            'format' => '%s <span class="tl_gray">[%s]</span>',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [StateButtonCallbackListener::class, 'handleButtonCallback'],
                'toolkit'         => [
                    'state_button' => [
                        'stateColumn' => 'active',
                    ],
                ],
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    'palettes'     => [
        '__selector__' => ['type', 'referenceType', 'standalone'],
    ],
    'metapalettes' => [
        'default'                                        => [
            'title'  => ['title', 'alias', 'type'],
            'config' => [],
            'active' => ['active'],
        ],
        'marker extends default'                         => [
            'config'              => ['coordinates'],
            'popup before active' => ['addPopup', 'renderSettings'],
            'icon before active'  => ['icon', 'iconAttribute'],
            '+active'             => ['deferred', 'ignoreForBounds'],
        ],
        'geojson extends default'                        => [
            '+title'  => ['geojsonAttribute'],
            '+active' => ['deferred'],
        ],
        'reference extends default'                      => [
            'config'  => ['referenceType', 'referenceAttribute', 'standalone'],
            '+active' => ['deferred', 'ignoreForBounds'],
        ],
        'referencereflayer extends reference'            => [
            '-active' => ['deferred', 'ignoreForBounds'],
        ],
        'referencerefmarkerstandalone extends reference' => [
            'popup before active' => ['addPopup', 'renderSettings'],
            'icon before active'  => ['icon', 'iconAttribute'],
            'style before active' => ['style', 'styleAttribute'],
        ],
        'referencerefvectorstandalone extends reference' => [
            'popup before active' => ['addPopup', 'renderSettings'],
            'icon before active'  => ['icon', 'iconAttribute'],
            'style before active' => ['style', 'styleAttribute'],
        ],
    ],

    'metasubselectpalettes' => [
        'coordinates' => [
            'single'   => ['coordinatesAttribute'],
            'separate' => ['latitudeAttribute', 'longitudeAttribute'],
        ],

        'addPopup' => [
            'attribute' => ['popupAttribute'],
        ],
    ],

    'fields' => [
        'id'                   => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'               => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pid'                  => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting'              => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'alias'                => [
            'label'         => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['alias'],
            'exclude'       => true,
            'inputType'     => 'text',
            'save_callback' => [
                [Netzmacht\Contao\Toolkit\Dca\Listener\Save\GenerateAliasListener::class, 'handleSaveCallback'],
                ['netzmacht.contao_leaflet.listeners.dca.validator', 'validateAlias'],
            ],
            'eval'          => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'unique' => true],
            'sql'           => "varchar(255) NOT NULL default ''",
            'toolkit'       => [
                'alias_generator' => [
                    'factory' => 'netzmacht.contao_leaflet.definition.alias_generator.factory_default',
                    'fields'  => ['title'],
                ],
            ],
        ],
        'type'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['type'],
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ],
            'options_callback' => [RendererDcaListener::class, 'getRendererTypes'],
            'reference'        => &$GLOBALS['TL_LANG']['leaflet_mm_renderer'],
            'sql'              => "varchar(32) NOT NULL default ''",
        ],
        'coordinates'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['coordinates'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ],
            'options'   => ['single', 'separate'],
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_renderer'],
            'sql'       => "varchar(8) NOT NULL default ''",
        ],
        'coordinatesAttribute' => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['coordinatesAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'latitudeAttribute'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'longitudeAttribute'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'geojsonAttribute'     => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['geojsonAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'renderSettings'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['renderSettings'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getRenderSettings'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['renderSettings'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'active'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['active'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'flag'      => 12,
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'deferred'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['deferred'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => true,
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => false, 'isBoolean' => true],
            'sql'       => "char(1) NOT NULL default '1'",
        ],
        'addPopup'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['addPopup'],
            'exclude'   => true,
            'inputType' => 'select',
            'filter'    => true,
            'sorting'   => true,
            'search'    => false,
            'options'   => ['attribute', 'render'],
            'flag'      => 12,
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(16) NOT NULL default ''",
        ],
        'popupAttribute'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['popupAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['latitudeAttribute'],
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'customIcon'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['customIcon'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ],
            'options'   => ['attribute', 'icon'],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'icon'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['icon'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => ['netzmacht.contao_leaflet.listeners.dca.marker', 'getIcons'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'chosen'             => true,
                'includeBlankOption' => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'iconAttribute'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['iconAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'referenceType'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceType'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['reflayer', 'refmarker', 'refvector'],
            'reference' => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceTypes'],
            'eval'      => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
                'chosen'             => true,
            ],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'referenceAttribute'   => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['referenceAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'eval'             => [
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'standalone'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['standalone'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => false,
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true, 'isBoolean' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'style'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['style'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [VectorDcaListener::class, 'getStyles'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'chosen'             => true,
                'includeBlankOption' => true,

            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'styleAttribute'       => [
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['styleAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => [RendererDcaListener::class, 'getAttributes'],
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['longitudeAttribute'],
            'eval'             => [
                'mandatory'          => false,
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'chosen'             => true,
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ],
        'ignoreForBounds'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_leaflet_mm_renderer']['ignoreForBounds'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'default'   => false,
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];
