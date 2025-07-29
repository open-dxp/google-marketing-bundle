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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Controller;

use OpenDxp\Bundle\GoogleMarketingBundle\Config\SiteConfigProvider;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use OpenDxp\Controller\UserAwareController;
use OpenDxp\Model\Site;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/portal")
 *
 * @internal
 */
class PortalController extends UserAwareController
{
    use JsonHelperTrait;

    /**
     * @Route("/portlet-analytics-sites", name="opendxp_bundle_googlemarketing_portal_portletanalyticssites", methods={"GET"})
     *
     *
     */
    public function portletAnalyticsSitesAction(
        TranslatorInterface $translator,
        SiteConfigProvider $siteConfigProvider
    ): JsonResponse {
        $sites = new Site\Listing();
        $data = [
            [
                'id' => 0,
                'site' => $translator->trans('main_site', [], 'admin'),
            ],
        ];

        foreach ($sites->load() as $site) {
            if ($siteConfigProvider->isSiteReportingConfigured($site)) {
                $data[] = [
                    'id' => $site->getId(),
                    'site' => $site->getMainDomain(),
                ];
            }
        }

        return $this->jsonResponse(['data' => $data]);
    }
}
