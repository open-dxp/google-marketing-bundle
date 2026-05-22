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
 * @copyright  Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\GoogleMarketingBundle\Tracker;

use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteId;

interface TrackerInterface
{
    /**
     * Generates code for a specific site. If no site is passed the current site will be
     * automatically resolved.
     *
     *
     * @return null|string Null if no tracking is configured
     */
    public function generateCode(?SiteId $siteId = null): ?string;

    /**
     * Adds additional code to the tracker. Code can either be added to all trackers
     * or be restricted to a specific site.
     *
     * @param string $code        The code to add
     * @param string|null $block  The block where to add the code (will use default block if none given)
     * @param bool $prepend       Whether to prepend the code to the code block
     * @param SiteId|null $siteId Restrict code to a specific site
     */
    public function addCodePart(string $code, ?string $block = null, bool $prepend = false, ?SiteId $siteId = null): void;
}
