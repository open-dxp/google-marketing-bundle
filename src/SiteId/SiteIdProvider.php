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

namespace OpenDxp\Bundle\GoogleMarketingBundle\SiteId;

use InvalidArgumentException;
use OpenDxp\Http\Request\Resolver\SiteResolver;
use OpenDxp\Model\Site;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class SiteIdProvider
{
    public function __construct(private readonly SiteResolver $siteResolver)
    {
    }

    /**
     * Resolve the site identifier for the given request
     */
    public function getForRequest(?Request $request = null): SiteId
    {
        if ($this->siteResolver->isSiteRequest($request)) {
            $site = $this->siteResolver->getSite($request);
            if (!$site) {
                throw new RuntimeException('Failed to fetch site for site request');
            }

            return SiteId::forSite($site);
        }

        return SiteId::forMainDomain();
    }

    /**
     * Get a site id for a config key
     */
    public function getSiteId(string $configKey): SiteId
    {
        foreach ($this->getSiteIds() as $siteId) {
            if ($siteId->getConfigKey() === $configKey) {
                return $siteId;
            }
        }

        throw new InvalidArgumentException(sprintf('Site config for key "%s" was not found', $configKey));
    }

    /**
     * Get all available site ids
     *
     *
     * @return SiteId[]
     */
    public function getSiteIds(bool $includeMainDomain = true): array
    {
        $sites = new Site\Listing();

        $ids = [];

        if ($includeMainDomain) {
            $ids[] = SiteId::forMainDomain();
        }

        foreach ($sites->load() as $site) {
            $ids[] = SiteId::forSite($site);
        }

        return $ids;
    }
}
