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

namespace OpenDxp\Bundle\GoogleMarketingBundle\EventListener\Frontend;

use OpenDxp\Bundle\CoreBundle\EventListener\Traits\OpenDxpContextAwareTrait;
use OpenDxp\Bundle\CoreBundle\EventListener\Traits\PreviewRequestTrait;
use OpenDxp\Bundle\CoreBundle\EventListener\Traits\ResponseInjectionTrait;
use OpenDxp\Bundle\CoreBundle\EventListener\Traits\StaticPageContextAwareTrait;
use OpenDxp\Bundle\GoogleMarketingBundle\EventListener\Traits\EnabledTrait;
use OpenDxp\Bundle\GoogleMarketingBundle\Tracker\Tracker;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Tool;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class GoogleAnalyticsCodeListener
{
    use EnabledTrait;
    use ResponseInjectionTrait;
    use OpenDxpContextAwareTrait;
    use PreviewRequestTrait;
    use StaticPageContextAwareTrait;

    public function __construct(private Tracker $tracker)
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $request = $event->getRequest();
        if (!$event->isMainRequest() && !$this->matchesStaticPageContext($request)) {
            return;
        }

        // only inject analytics code on non-admin requests
        // and check for static page context for CLI generation
        if (!$this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_DEFAULT)
            && !$this->matchesStaticPageContext($request)) {
            return;
        }

        if (!Tool::useFrontendOutputFilters()) {
            return;
        }

        if ($this->isPreviewRequest($request)) {
            return;
        }

        $response = $event->getResponse();
        if (!$this->isHtmlResponse($response)) {
            return;
        }

        $code = $this->tracker->generateCode();
        if (empty($code)) {
            return;
        }

        $this->injectBeforeHeadEnd($response, $code);
    }
}
