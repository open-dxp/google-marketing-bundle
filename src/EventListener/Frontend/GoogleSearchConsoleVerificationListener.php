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

namespace OpenDxp\Bundle\GoogleMarketingBundle\EventListener\Frontend;

use OpenDxp\Bundle\CoreBundle\EventListener\Traits\OpenDxpContextAwareTrait;
use OpenDxp\Config;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
class GoogleSearchConsoleVerificationListener implements EventSubscriberInterface
{
    use OpenDxpContextAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 64],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        $conf = Config::getReportConfig();

        if (isset($conf['google_search_console']) && isset($conf['google_search_console']['sites'])) {
            $sites = $conf['google_search_console']['sites'];

            if (is_array($sites)) {
                foreach ($sites as $site) {
                    if ($site['verification'] && $request->getPathInfo() === '/' . $site['verification']) {
                        $response = new Response('google-site-verification: ' . $site['verification']);
                        $event->setResponse($response);

                        break;
                    }
                }
            }
        }
    }
}
