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

namespace Netzmacht\Contao\Leaflet\MetaModels\Attribute;

use MetaModels\AttributeSelectBundle\Attribute\Select;

/**
 * Class LeafletMapSelect extends the default select attribute to preset it to the leaflet table.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Attribute
 */
final class LeafletMapSelect extends Select
{
    /**
     * {@inheritdoc}
     */
    protected function getSelectSource(): string
    {
        return 'tl_leaflet_map';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdColumn(): string
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortingColumn(): string
    {
        return $this->get('select_sorting') ?: $this->getValueColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function getValueColumn(): string
    {
        return 'title';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAliasColumn(): string
    {
        return 'alias';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames(): array
    {
        return array_diff(
            parent::getAttributeSettingNames(),
            array(
                'select_table',
                'select_column',
                'select_id',
                'select_alias'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        if ($this->isTreePicker()) {
            $strColNameAlias = $this->getIdColumn();
        } else {
            $strColNameAlias = 'alias';
        }

        return $varValue[$strColNameAlias];
    }
}
