<?php

declare(strict_types=1);

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\GoogleMarketingBundle\Event;

final class GoogleAnalyticsEvents
{
    /**
     * Triggered before a tracking code block is rendered. Can be used to add additional code
     * snippets to the tracking block.
     *
     * @Event("OpenDxp\Bundle\GoogleMarketingBundle\Model\Event\TrackingDataEvent")
     */
    const string CODE_TRACKING_DATA = 'opendxp.tracking.google.code.tracking_data';
}
