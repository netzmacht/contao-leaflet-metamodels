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

namespace Netzmacht\Contao\Leaflet\MetaModels\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MetaModels\AttributeSelectBundle\MetaModelsAttributeSelectBundle;
use MetaModels\CoreBundle\MetaModelsCoreBundle;
use Netzmacht\Contao\Leaflet\MetaModels\NetzmachtContaoLeafletMetaModelsBundle;

/**
 * Class Plugin
 */
final class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(NetzmachtContaoLeafletMetaModelsBundle::class)
                ->setLoadAfter(
                    [ContaoCoreBundle::class, MetaModelsCoreBundle::class, MetaModelsAttributeSelectBundle::class]
                )
                ->setReplace(['leaflet-metamodels'])
        ];
    }
}
