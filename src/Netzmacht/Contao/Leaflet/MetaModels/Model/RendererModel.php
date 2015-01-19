<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2015 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\Leaflet\MetaModels\Model;


/**
 * The feature model.
 *
 * @property int        latitudeAttribute
 * @property int        longitudeAttribute
 * @property mixed|null iconAttribute
 * @property mixed|null addPopup
 * @property mixed|null renderSettings
 * @property mixed|null popupAttribute
 * @property mixed|null options
 * @property mixed|null coordinates
 * @property mixed|null coordinatesAttribute
 * @property mixed|null type
 * @property mixed|null icon
 * @property mixed|null referenceAttribute
 * @property mixed|null referenceType
 * @property mixed|null standalone
 * @property mixed|null styleAttribute
 * @property mixed|null deferred
 */
class RendererModel extends \Model
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_leaflet_mm_renderer';
}
