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
    'boundsMode' => array(
        'extend' => true,
        'fit'    => true,
    ),
    'label'    => function ($row, $label) {
        /** @var \MetaModels\IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        $factory   = $serviceContainer->getFactory();
        $name      = $factory->translateIdToMetaModelName($row['metamodel']);
        $metaModel = $factory->getMetaModel($name);

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

/*
 * MetaModel attribute factory.
 */
$GLOBALS['TL_EVENTS'][\MetaModels\MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE][] = function (
    \MetaModels\Attribute\Events\CreateAttributeFactoryEvent $event
) {
    $factory = $event->getFactory();
    $factory->addTypeFactory(new \Netzmacht\Contao\Leaflet\MetaModels\Attribute\AttributeTypeFactory());
};

/*
 * MetaModel boot system.
 */
$GLOBALS['TL_EVENTS'][\MetaModels\MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND][] = function (
    MetaModels\Events\MetaModelsBootEvent $event
) {
    new \Netzmacht\Contao\Leaflet\MetaModels\Attribute\Subscriber($event->getServiceContainer());
};
