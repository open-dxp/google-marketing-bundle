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

namespace OpenDxp\Bundle\GoogleMarketingBundle\SiteId;

use OpenDxp\Model\Site;
use OpenDxp\SystemSettingsConfig;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Represents an analytics site config key which is either just "default" without
 * an associated site or a combination of a site with its config key "site_<siteId>".
 */
class SiteId
{
    const CONFIG_KEY_MAIN_DOMAIN = 'site_0';

    private function __construct(private readonly string $configKey, private readonly ?Site $site = null)
    {
    }

    public static function forMainDomain(): self
    {
        return new self(self::CONFIG_KEY_MAIN_DOMAIN);
    }

    public static function forSite(Site $site): self
    {
        $configKey = sprintf('site_%s', $site->getId());

        return new self($configKey, $site);
    }

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function getTitle(TranslatorInterface $translator): string
    {
        $site = $this->site;

        $name = null;

        if (null === $site) {
            if (!empty($mainDomain = SystemSettingsConfig::get()['general']['domain'])) {
                return $mainDomain;
            }

            if ($currentDomain = \OpenDxp\Tool::getHostname()) {
                return $currentDomain;
            }

            return $translator->trans('main_site', [], 'admin');
        }

        if ($site->getMainDomain()) {
            $name = $site->getMainDomain();
        } elseif ($site->getRootDocument()) {
            $name = $site->getRootDocument()->getKey();
        }

        $siteSuffix = sprintf(
            '%s: %d',
            $translator->trans('site', [], 'admin'),
            $site->getId()
        );

        if (empty($name)) {
            $name = $siteSuffix;
        } else {
            $name = sprintf('%s (%s)', $name, $siteSuffix);
        }

        return $name;
    }
}
