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

use MetaModels\Attribute\Select\Select;

/**
 * Class LeafletMapSelect extends the default select attribute to preset it to the leaflet table.
 *
 * @package Netzmacht\Contao\Leaflet\MetaModels\Attribute
 */
class LeafletMapSelect extends Select
{
    /**
     * {@inheritdoc}
     */
    protected function getSelectSource()
    {
        return 'tl_leaflet_map';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdColumn()
    {
        return 'id';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSortingColumn()
    {
        return $this->get('select_sorting') ?: $this->getValueColumn();
    }

    /**
     * {@inheritdoc}
     */
    protected function getValueColumn()
    {
        return 'title';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAliasColumn()
    {
        return 'alias';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
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
