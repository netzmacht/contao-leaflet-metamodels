<?php

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
            'mode'                    => 1,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'limit',
            'headerFields'            => array('title', 'type'),
        ),
        'label' => array
        (
            'fields'                  => array('title', 'type'),
            'format'                  => '%s <span class="tl_gray">[%s]</span>',
        ),
        'global_operations' => array
        (
            'icons' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['icons'],
                'href'                => 'table=tl_leaflet_icon',
                'icon'                => 'system/modules/leaflet/assets/img/icons.png',
                'attributes'          => 'onclick="Backend.getScrollOffset();"'
            ),
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
        'fixed extends default' => array(
            'config' => array('stroke', 'fill'),
        ),
    ),

    'metasubpalettes' => array(
        'stroke'    => array('color', 'weight', 'opacity', 'dashArray', 'lineCap', 'lineJoin'),
        'fill'      => array('fillColor', 'fillOpacity',)
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
            'options'   => &$GLOBALS['LEAFLET_MM_FEATURES'],
            'reference' => &$GLOBALS['TL_LANG']['leaflet_mm_feature'],
            'sql'       => "varchar(32) NOT NULL default ''"
        ),
        'longitudeAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['longitudeAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['longitudeAttribute'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'helpwizard'         => true,
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
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'helpwizard'         => true,
            ),
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ),
        'popupAttribute'    => array
        (
            'label'            => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['popupAttribute'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => array('Netzmacht\Contao\Leaflet\MetaModels\Dca\Feature', 'getAttributes'),
            'reference'        => &$GLOBALS['TL_LANG']['tl_leaflet_mm_feature']['latitudeAttribute'],
            'eval'             => array(
                'mandatory'          => false,
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'helpwizard'         => true,
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
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true,
                'helpwizard'         => true,
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
    ),
);
