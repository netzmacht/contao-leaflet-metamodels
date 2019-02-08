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

use function array_key_exists;
use function array_keys;
use MetaModels\Render\Setting\RenderSettingFactory;
use Netzmacht\Contao\Leaflet\MetaModels\Model\RendererModel;
use Netzmacht\Contao\Leaflet\Model\LayerModel;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use RuntimeException;

/**
 * Class DefaultRendererFactory
 */
final class DefaultRendererFactory implements RendererFactory
{
    /**
     * Render setting factory.
     *
     * @var RenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * Mapping between the type name and the default supported renderer.
     *
     * @var array
     */
    private $mapping = [
        'marker' => MarkerRenderer::class,
        'geojson' => GeoJsonRenderer::class,
        'reference' => ReferenceRenderer::class
    ];

    /**
     * DefaultRendererFactory constructor.
     *
     * @param RenderSettingFactory $renderSettingFactory Render setting factory.
     * @param RepositoryManager    $repositoryManager Repository manager.
     */
    public function __construct(RenderSettingFactory $renderSettingFactory, RepositoryManager $repositoryManager)
    {
        $this->renderSettingFactory = $renderSettingFactory;
        $this->repositoryManager    = $repositoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return array_key_exists($type, $this->mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function supportedTypes(): array
    {
        return array_keys($this->mapping);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException When no renderer could be created.
     */
    public function create(RendererModel $rendererModel, LayerModel $layerModel): Renderer
    {
        if (isset($this->mapping[$rendererModel->type])) {
            $className = $this->mapping[$rendererModel->type];

            return new $className($this->renderSettingFactory, $this->repositoryManager, $rendererModel, $layerModel);
        }

        throw new RuntimeException(sprintf('Creating renderer of type "%s failed'. $rendererModel->type));
    }
}
