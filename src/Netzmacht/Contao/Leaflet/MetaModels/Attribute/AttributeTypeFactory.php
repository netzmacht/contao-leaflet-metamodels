<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Attribute;


use MetaModels\Attribute\IAttributeTypeFactory;

class AttributeTypeFactory implements IAttributeTypeFactory
{
    /**
     * {@inheritdoc}
     */
    public function getTypeName()
    {
        return 'leaflet_map';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeIcon()
    {
        return 'system/modules/leaflet/assets/img/map.png';
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new LeafletMapSelect($metaModel, $information);
    }

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType()
    {
        return false;
    }

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType()
    {
        return true;
    }

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType()
    {
        return true;
    }
}
