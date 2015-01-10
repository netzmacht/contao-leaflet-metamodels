<?php

$GLOBALS['TL_MODELS']['tl_leaflet_mm_feature'] = 'Netzmacht\Contao\Leaflet\MetaModels\Model\FeatureModel';

$GLOBALS['LEAFLET_MAPPERS'][] = 'Netzmacht\Contao\Leaflet\MetaModels\Mapper\LayerMapper';

$GLOBALS['LEAFLET_LAYERS']['metamodels'] = array
(
    'children' => false,
    'icon'     => 'system/modules/leaflet-metamodels/assets/img/reference.png',
    'label'    => function ($row, $label) {
        $reference = \Netzmacht\Contao\Leaflet\Model\LayerModel::findByPk($row['reference']);

        if ($reference) {
            $label .= '<span class="tl_gray"> (' . $reference->title . ')</span>';
        }

        return $label;
    }
);
