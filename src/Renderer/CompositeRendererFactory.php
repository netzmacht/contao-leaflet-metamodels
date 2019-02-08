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
use RuntimeException;
use function array_merge;
use function array_unique;

/**
 * Class CompositeRendererFactory
 */
final class CompositeRendererFactory implements RendererFactory
{
    /**
     * Renderer factories.
     *
     * @var RendererFactory[]
     */
    private $factories;

    /**
     * CompositeRendererFactory constructor.
     *
     * @param RendererFactory[] $factories Renderer factories.
     */
    public function __construct(iterable $factories)
    {
        $this->factories = $factories;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportedTypes(): array
    {
        $supportedTypes = [];

        foreach ($this->factories as $factory) {
            $supportedTypes[] = $factory->supportedTypes();
        }

        return array_unique(array_merge(...$supportedTypes));
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException When no renderer could be created.
     */
    public function create(RendererModel $rendererModel, LayerModel $layerModel): Renderer
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($rendererModel->type)) {
                return $factory->create($rendererModel, $layerModel);
            }
        }

        throw new RuntimeException(sprintf('Creating renderer of type "%s failed'. $rendererModel->type));
    }
}
