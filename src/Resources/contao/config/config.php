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

use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;

/*
 * Backend Module.
 */

$GLOBALS['BE_MOD']['leaflet']['leaflet_layer']['tables'][] = 'tl_leaflet_mm_renderer';

/*
 * Register models.
 */

$GLOBALS['TL_MODELS']['tl_leaflet_mm_renderer'] = RendererModel::class;
