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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\GoogleMarketingBundle\Config;

use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteId;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteIdProvider;
use OpenDxp\Model\Site;

class SiteConfigProvider
{
    public function __construct(private readonly SiteIdProvider $siteIdProvider, private readonly ConfigProvider $configProvider)
    {
    }

    public function getSiteConfig(?Site $site = null): ?array
    {
        $siteId = $this->getSiteId($site);
        $config = $this->configProvider->getConfig();

        return $config->getConfigForSite($siteId->getConfigKey());
    }

    public function isSiteReportingConfigured(?Site $site = null): bool
    {
        $siteId = $this->getSiteId($site);
        $config = $this->configProvider->getConfig();

        return $config->isReportingConfigured($siteId->getConfigKey());
    }

    private function getSiteId(?Site $site = null): SiteId
    {
        $siteId = null;
        if (null === $site) {
            $siteId = $this->siteIdProvider->getForRequest();
        } else {
            $siteId = SiteId::forSite($site);
        }

        return $siteId;
    }
}
