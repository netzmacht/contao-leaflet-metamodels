<?php

/*
 * Backend Module.
 */
$GLOBALS['BE_MOD']['leaflet']['leaflet_layer']['tables'][] = 'tl_leaflet_mm_feature';

/*
 * Register models.
 */
$GLOBALS['TL_MODELS']['tl_leaflet_mm_feature'] = 'Netzmacht\Contao\Leaflet\MetaModels\Model\FeatureModel';

/*
 * Add leaflet mapper.
 */
$GLOBALS['LEAFLET_MAPPERS'][] = 'Netzmacht\Contao\Leaflet\MetaModels\Mapper\LayerMapper';

/*
 * Add metamodels layer.
 */
$GLOBALS['LEAFLET_LAYERS']['metamodels'] = array
(
    'children'   => false,
    'icon'       => 'system/modules/leaflet-metamodels/assets/img/layer.png',
    'metamodels' => true,
    'label'    => function ($row, $label) {
        return $label;
    }
);

/*
 * Metamodels layer features
 */
$GLOBALS['LEAFLET_MM_FEATURES']['marker']    = 'Netzmacht\Contao\Leaflet\MetaModels\Feature\MarkerFeature';
$GLOBALS['LEAFLET_MM_FEATURES']['geojson']   = 'Netzmacht\Contao\Leaflet\MetaModels\Feature\GeoJsonFeature';
$GLOBALS['LEAFLET_MM_FEATURES']['reference'] = 'Netzmacht\Contao\Leaflet\MetaModels\Feature\ReferenceFeature';
