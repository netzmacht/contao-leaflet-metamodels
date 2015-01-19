<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

/*
 * Backend Module.
 */
$GLOBALS['BE_MOD']['leaflet']['leaflet_layer']['tables'][] = 'tl_leaflet_mm_renderer';

/*
 * Register models.
 */
$GLOBALS['TL_MODELS']['tl_leaflet_mm_renderer'] = 'Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel';

/*
 * Add leaflet mapper.
 */
$GLOBALS['LEAFLET_MAPPERS'][] = 'Netzmacht\Contao\Leaflet\MetaModels\LayerMapper';

/*
 * Add metamodels layer.
 */
$GLOBALS['LEAFLET_LAYERS']['metamodels'] = array
(
    'children'   => false,
    'icon'       => 'system/modules/leaflet-metamodels/assets/img/layer.png',
    'metamodels' => true,
    'label'    => function ($row, $label) {
        $metaModel = \MetaModels\Factory::byId($row['metamodel']);

        if (!$metaModel) {
            return $label;
        }

        return $label . sprintf(
            ' <span><a href="%s" class="tl_gray">(%s)</a></span>',
            \Backend::addToUrl(
                sprintf('do=metamodel_%s&amp;table=%s', $metaModel->getTableName(), $metaModel->getTableName())
            ),
            $metaModel->getName()
        );
    }
);

/*
 * Metamodels layer renderers
 */
$GLOBALS['LEAFLET_MM_RENDERER']['marker']    = 'Netzmacht\Contao\Leaflet\MetaModels\Renderer\MarkerRenderer';
$GLOBALS['LEAFLET_MM_RENDERER']['geojson']   = 'Netzmacht\Contao\Leaflet\MetaModels\Renderer\GeoJsonRenderer';
$GLOBALS['LEAFLET_MM_RENDERER']['reference'] = 'Netzmacht\Contao\Leaflet\MetaModels\Renderer\ReferenceRenderer';
