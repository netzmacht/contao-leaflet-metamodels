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

namespace Netzmacht\Contao\Leaflet\MetaModels\Renderer;

use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;

/**
 * Interface RendererFactory describes a factory which is responsible to create a renderer for the MetaModels leaflet
 * layer integration.
 */
interface RendererFactory
{
    /**
     * Check if renderer type is supported.
     *
     * @param string $type The renderer type as string.
     *
     * @return bool
     */
    public function supports(string $type): bool;

    /**
     * Get a list of supported types.
     *
     * @return array|string[]
     */
    public function supportedTypes(): array;

    /**
     * Create the renderer
     *
     * @param RendererModel $rendererModel The renderer model.
     * @param LayerModel    $layerModel    The layer model.
     *
     * @return Renderer
     */
    public function create(RendererModel $rendererModel, LayerModel $layerModel): Renderer;
}
