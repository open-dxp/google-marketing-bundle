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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Config;

class ConfigProvider
{
    private ?Config $config = null;

    /**
     * @param array<string, mixed>|null $configObject
     */
    public function __construct(private ?array $configObject = null)
    {
    }

    public function getConfig(): Config
    {
        if (null === $this->config) {
            $this->config = new Config($this->getConfigObject());
        }

        return $this->config;
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfigObject(): array
    {
        if (null === $this->configObject) {
            $this->configObject = $this->loadDefaultConfigObject();
        }

        return $this->configObject;
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadDefaultConfigObject(): array
    {
        $reportConfig = \OpenDxp\Config::getReportConfig();

        return $reportConfig['analytics'] ?? [];
    }
}
