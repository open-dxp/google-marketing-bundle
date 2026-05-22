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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Config;

class Config
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * @param array<string, mixed> $reportConfig
     */
    public static function fromReportConfig(array $reportConfig): self
    {
        return new self($reportConfig['analytics'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function isSiteConfigured(string $configKey): bool
    {
        $config = $this->getConfigForSite($configKey);

        if (null === $config) {
            return false;
        }

        $trackId = $this->normalizeStringValue($config['trackid']);
        if (null === $trackId) {
            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getConfigForSite(string $configKey): ?array
    {
        if (!isset($this->config['sites']) || !isset($this->config['sites'][$configKey])) {
            return null;
        }

        return $this->config['sites'][$configKey];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguredSites(): array
    {
        $sites = $this->config['sites'];
        if (is_array($sites)) {
            return $sites;
        }

        return [];
    }

    public function isReportingConfigured(string $configKey): bool
    {
        $config = $this->getConfigForSite($configKey);

        if (null === $config) {
            return false;
        }

        $profile = $this->normalizeStringValue($config['profile']);
        if (null === $profile) {
            return false;
        }

        return true;
    }

    private function normalizeStringValue(mixed $value): ?string
    {
        if (null === $value) {
            return $value;
        }

        $value = trim((string)$value);
        if (empty($value)) {
            return null;
        }

        return $value;
    }
}
