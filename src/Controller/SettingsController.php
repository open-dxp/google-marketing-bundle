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

use Exception;
use OpenDxp\Config\ReportConfigWriter;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route("/settings")]
class SettingsController extends ReportsControllerBase
{
    use JsonHelperTrait;

    #[Route('/get', name: 'opendxp_bundle_googlemarketing_settings_get', methods: ['GET'])]
    public function getAction(Request $request): JsonResponse
    {
        $this->checkPermission('google_marketing');
        $config = $this->getConfig();

        $response = [
            'values' => $config,
            'config' => [],
        ];

        return $this->jsonResponse($response);
    }

    #[Route("/save", name: 'opendxp_bundle_googlemarketing_settings_save', methods: ['PUT'])]
    public function saveAction(Request $request, ReportConfigWriter $configWriter): JsonResponse
    {
        $this->checkPermission('google_marketing');

        $values = $this->decodeJson($request->get('data'));
        if (!is_array($values)) {
            $values = [];
        }

        try {
            $configWriter->write($values);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'errors' => [$e->getMessage()],
            ];

            return $this->jsonResponse($result);
        }

        return $this->jsonResponse(['success' => true]);
    }
}
