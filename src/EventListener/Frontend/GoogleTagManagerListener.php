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
use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeBlock;
use OpenDxp\Bundle\GoogleMarketingBundle\Event\GoogleTagManagerEvents;
use OpenDxp\Bundle\GoogleMarketingBundle\EventListener\Traits\EnabledTrait;
use OpenDxp\Bundle\GoogleMarketingBundle\Model\Event\TagManager\CodeEvent;
use OpenDxp\Bundle\GoogleMarketingBundle\SiteId\SiteIdProvider;
use OpenDxp\Config;
use OpenDxp\Http\Request\Resolver\OpenDxpContextResolver;
use OpenDxp\Tool;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

/**
 * @internal
 */
class GoogleTagManagerListener
{
    const BLOCK_HEAD_BEFORE_SCRIPT_TAG = 'beforeScriptTag';

    const BLOCK_HEAD_AFTER_SCRIPT_TAG = 'afterScriptTag';

    const BLOCK_BODY_BEFORE_NOSCRIPT_TAG = 'beforeNoscriptTag';

    const BLOCK_BODY_AFTER_NOSCRIPT_TAG = 'afterNoscriptTag';

    use EnabledTrait;
    use ResponseInjectionTrait;
    use OpenDxpContextAwareTrait;
    use PreviewRequestTrait;

    private array $headBlocks = [
        self::BLOCK_HEAD_BEFORE_SCRIPT_TAG,
        self::BLOCK_HEAD_AFTER_SCRIPT_TAG,
    ];

    private array $bodyBlocks = [
        self::BLOCK_BODY_BEFORE_NOSCRIPT_TAG,
        self::BLOCK_BODY_AFTER_NOSCRIPT_TAG,
    ];

    public function __construct(
        private SiteIdProvider $siteIdProvider,
        private EventDispatcherInterface $eventDispatcher,
        private Environment $templatingEngine
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->isEnabled() || $event->getResponse() instanceof RedirectResponse) {
            return;
        }

        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
            return;
        }

        // only inject tag manager code on non-admin requests
        if (!$this->matchesOpenDxpContext($request, OpenDxpContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        if (!Tool::useFrontendOutputFilters()) {
            return;
        }

        if ($this->isPreviewRequest($request)) {
            return;
        }

        $siteId = $this->siteIdProvider->getForRequest($event->getRequest());
        $siteKey = $siteId->getConfigKey();

        $reportConfig = Config::getReportConfig();
        if (!isset($reportConfig['tagmanager']['sites'][$siteKey]['containerId'])) {
            return;
        }

        $containerId = $reportConfig['tagmanager']['sites'][$siteKey]['containerId'];
        if (!$containerId) {
            return;
        }

        $response = $event->getResponse();
        if (!$this->isHtmlResponse($response)) {
            return;
        }

        $codeHead = $this->generateCode(
            GoogleTagManagerEvents::CODE_HEAD,
            '@OpenDxpGoogleMarketing/Analytics/Tracking/GoogleTagManager/codeHead.html.twig',
            $this->headBlocks,
            [
                'containerId' => $containerId,
            ]
        );

        $codeBody = $this->generateCode(
            GoogleTagManagerEvents::CODE_BODY,
            '@OpenDxpGoogleMarketing/Analytics/Tracking/GoogleTagManager/codeBody.html.twig',
            $this->bodyBlocks,
            [
                'containerId' => $containerId,
            ]
        );

        $content = $response->getContent();

        if (!empty($codeHead)) {
            // search for the end <head> tag, and insert the google tag manager code before
            // this method is much faster than using simple_html_dom and uses less memory
            $headEndPosition = stripos($content, '</head>');
            if ($headEndPosition !== false) {
                $content = substr_replace($content, $codeHead . '</head>', $headEndPosition, 7);
            }
        }

        if (!empty($codeBody)) {
            // insert code after the opening <body> tag
            $content = preg_replace('@<body(>|.*?[^?]>)@', "<body$1\n\n" . $codeBody, $content);
        }

        $response->setContent($content);
    }

    private function generateCode(string $eventName, string $template, array $blockNames, array $data): string
    {
        $blocks = [];
        foreach ($blockNames as $blockName) {
            $blocks[$blockName] = new CodeBlock();
        }

        $event = new CodeEvent($data, $blocks, $template);

        $this->eventDispatcher->dispatch($event, $eventName);

        return $this->renderTemplate($event);
    }

    private function renderTemplate(CodeEvent $event): string
    {
        $data = $event->getData();
        $data['blocks'] = $event->getBlocks();

        $code = $this->templatingEngine->render(
            $event->getTemplate(),
            $data
        );

        $code = trim($code);
        if (!empty($code)) {
            $code = "\n" . $code . "\n";
        }

        return $code;
    }
}
