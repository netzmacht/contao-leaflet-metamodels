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

namespace Netzmacht\Contao\Leaflet\MetaModels\MapLayer;

use Contao\Backend;
use Contao\CoreBundle\Framework\Adapter;
use MetaModels\Factory as MetaModelsFactory;
use Netzmacht\Contao\Leaflet\Backend\Renderer\Label\Layer\AbstractLabelRenderer;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;

/**
 * Class MetaModelsLayerLabelRenderer
 */
final class MetaModelsLayerLabelRenderer extends AbstractLabelRenderer
{
    /**
     * MetaModels factory.
     *
     * @var MetaModelsFactory
     */
    private $metaModelsFactory;

    /**
     * Backend adapter.
     *
     * @var Adapter|Backend
     */
    private $backendAdapter;

    /**
     * MetaModelsLayerLabelRenderer constructor.
     *
     * @param MetaModelsFactory $metaModelsFactory MetaModels factory.
     * @param Adapter           $backendAdapter    Backend adapter.
     */
    public function __construct(MetaModelsFactory $metaModelsFactory, Adapter $backendAdapter)
    {
        $this->metaModelsFactory = $metaModelsFactory;
        $this->backendAdapter    = $backendAdapter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLayerType(): string
    {
        return 'metamodels';
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $row, string $label, Translator $translator): string
    {
        $name      = $this->metaModelsFactory->translateIdToMetaModelName($row['metamodel']);
        $metaModel = $this->metaModelsFactory->getMetaModel($name);

        if ($metaModel === null) {
            return $label;
        }

        return $label . sprintf(
            ' <span><a href="%s" class="tl_gray">(%s)</a></span>',
            $this->backendAdapter->addToUrl(
                sprintf('do=metamodel_%s&amp;table=%s', $metaModel->getTableName(), $metaModel->getTableName())
            ),
            $metaModel->getName()
        );
    }
}
