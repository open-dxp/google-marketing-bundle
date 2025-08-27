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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Tracker;

use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeCollector;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteId;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteIdProvider;

abstract class AbstractTracker implements TrackerInterface
{
    private SiteIdProvider $siteIdProvider;

    private ?CodeCollector $codeCollector = null;

    public function __construct(SiteIdProvider $siteIdProvider)
    {
        $this->siteIdProvider = $siteIdProvider;
    }

    public function generateCode(?SiteId $siteId = null): ?string
    {
        if (null === $siteId) {
            $siteId = $this->siteIdProvider->getForRequest();
        }

        return $this->buildCode($siteId);
    }

    /**
     * Generates code for a specific site config
     *
     *
     */
    abstract protected function buildCode(SiteId $siteId): ?string;

    public function addCodePart(string $code, ?string $block = null, bool $prepend = false, ?SiteId $siteId = null): void
    {
        $action = $prepend ? CodeCollector::ACTION_PREPEND : CodeCollector::ACTION_APPEND;

        $this->getCodeCollector()->addCodePart($code, $block, $action, $siteId);
    }

    /**
     * Lazy initialize the code collector
     *
     */
    protected function getCodeCollector(): CodeCollector
    {
        if (null === $this->codeCollector) {
            $this->codeCollector = $this->buildCodeCollector();
        }

        return $this->codeCollector;
    }

    /**
     * Builds the code collector which allows to add additional content to
     * specific blocks.
     *
     */
    abstract protected function buildCodeCollector(): CodeCollector;
}
