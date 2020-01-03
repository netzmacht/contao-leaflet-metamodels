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

namespace Netzmacht\Contao\Leaflet\MetaModels\Model;

use Contao\Model;

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
 * @property string     type
 * @property mixed|null icon
 * @property mixed|null referenceAttribute
 * @property mixed|null referenceType
 * @property mixed|null standalone
 * @property mixed|null styleAttribute
 * @property mixed|null deferred
 * @property string     style
 * @property string     ignoreForBounds
 * @property string     alias
 */
final class RendererModel extends Model
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_leaflet_mm_renderer';
}
