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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Controller\Reports;

use Google\Service\Analytics;
use OpenDxp\Bundle\GoogleMarketingBundle\Api\Api;
use OpenDxp\Bundle\GoogleMarketingBundle\Chart\ImageChart;
use OpenDxp\Bundle\GoogleMarketingBundle\Config\SiteConfigProvider;
use OpenDxp\Bundle\GoogleMarketingBundle\Controller\ReportsControllerBase;
use OpenDxp\Controller\KernelControllerEventInterface;
use OpenDxp\Controller\Traits\JsonHelperTrait;
use OpenDxp\Model\Document;
use OpenDxp\Model\Site;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reports/analytics")
 *
 * @internal
 */
class AnalyticsController extends ReportsControllerBase implements KernelControllerEventInterface
{
    use JsonHelperTrait;

    protected Analytics $service;

    /**
     * @Route("/deeplink", name="opendxp_bundle_googlemarketing_reports_analytics_deeplink", methods={"GET"})
     *
     *
     */
    public function deeplinkAction(Request $request, SiteConfigProvider $siteConfigProvider): RedirectResponse
    {
        $config = $siteConfigProvider->getSiteConfig();

        $url = $request->get('url');
        $url = str_replace(['{accountId}', '{internalWebPropertyId}', '{id}'], [$config['accountid'], $config['internalid'], $config['profile']], $url);
        $url = 'https://www.google.com/analytics/web/' . $url;

        return $this->redirect($url);
    }

    /**
     * @Route("/get-profiles", name="opendxp_bundle_googlemarketing_reports_analytics_getprofiles", methods={"GET"})
     *
     *
     */
    public function getProfilesAction(Request $request): JsonResponse
    {
        try {
            $data = ['data' => []];
            $result = $this->service->management_accounts->listManagementAccounts();

            $accountIds = [];
            if (is_array($result['items'])) {
                foreach ($result['items'] as $account) {
                    $accountIds[] = $account['id'];
                }
            }

            foreach ($accountIds as $accountId) {
                $propertyNames = [];
                $properties = $this->service->management_webproperties->listManagementWebproperties($accountId);

                if (is_array($properties['items'])) {
                    foreach ($properties['items'] as $property) {
                        $propertyNames[$property['id']] = $property['name'];
                    }
                }

                $details = $this->service->management_profiles->listManagementProfiles($accountId, '~all');

                if (is_array($details['items'])) {
                    foreach ($details['items'] as $detail) {
                        $name = $detail['name'];

                        if (array_key_exists($detail['webPropertyId'], $propertyNames)) {
                            $name = $propertyNames[$detail['webPropertyId']] . ': ' . $name;
                        }

                        $data['data'][] = [
                            'id' => $detail['id'],
                            'name' => $name,
                            'trackid' => $detail['webPropertyId'],
                            'internalid' => $detail['internalWebPropertyId'],
                            'accountid' => $detail['accountId'],
                        ];
                    }
                }
            }

            return $this->jsonResponse($data);
        } catch (\Exception $e) {
            return $this->jsonResponse(false);
        }
    }

    private function getSite(Request $request): ?Site
    {
        $siteId = $request->get('site');

        return Site::getById($siteId);
    }

    protected function getFilterPath(Request $request): string
    {
        if ($request->get('type') == 'document' && $request->get('id')) {
            $doc = Document::getById((int) $request->get('id'));
            if (!$doc) {
                throw $this->createNotFoundException();
            }
            $path = $doc->getFullPath();

            if ($doc instanceof Document\Page && $doc->getPrettyUrl()) {
                $path = $doc->getPrettyUrl();
            }

            if ($siteId = $request->get('site')) {
                $site = Site::getById((int) $siteId);
                $path = preg_replace('@^' . preg_quote($site->getRootPath(), '@') . '/@', '/', $path);
            }

            return $path;
        }

        return $request->get('path');
    }

    /**
     * @Route("/chartmetricdata", name="opendxp_bundle_googlemarketing_reports_analytics_chartmetricdata", methods={"GET"})
     *
     *
     */
    public function chartmetricdataAction(Request $request, SiteConfigProvider $siteConfigProvider): JsonResponse
    {
        $config = $siteConfigProvider->getSiteConfig($this->getSite($request));

        $startDate = date('Y-m-d', (time() - (86400 * 31)));
        $endDate = date('Y-m-d');

        if ($request->get('dateFrom') && $request->get('dateTo')) {
            $startDate = date('Y-m-d', strtotime($request->get('dateFrom')));
            $endDate = date('Y-m-d', strtotime($request->get('dateTo')));
        }

        $metrics = ['ga:pageviews'];
        if ($request->get('metric')) {
            $metrics = [];

            if (is_array($request->get('metric'))) {
                foreach ($request->get('metric') as $m) {
                    $metrics[] = 'ga:' . $m;
                }
            } else {
                $metrics[] = 'ga:' . $request->get('metric');
            }
        }

        $filters = [];

        if ($filterPath = $this->getFilterPath($request)) {
            $filters[] = 'ga:pagePath=='.$filterPath;
        }

        if ($request->get('filters')) {
            $filters[] = $request->get('filters');
        }

        $opts = [
            'dimensions' => 'ga:date',
        ];

        if (!empty($filters)) {
            $opts['filters'] = implode(';', $filters);
        }

        $result = $this->service->data_ga->get(
            'ga:' . $config['profile'],
            $startDate,
            $endDate,
            implode(',', $metrics),
            $opts
        );

        $data = [];

        foreach ($result['rows'] as $row) {
            $date = $row[0];

            $tmpData = [
                'timestamp' => strtotime($date),
                'datetext' => $this->formatDimension('date', $date),
            ];

            foreach ($result['columnHeaders'] as $index => $metric) {
                if (!$request->get('dataField')) {
                    $tmpData[str_replace('ga:', '', $metric['name'])] = $row[$index];
                } else {
                    $tmpData[$request->get('dataField')] = $row[$index];
                }
            }

            $data[] = $tmpData;
        }

        return $this->jsonResponse(['data' => $data]);
    }

    /**
     * @Route("/summary", name="opendxp_bundle_googlemarketing_reports_analytics_summary", methods={"GET"})
     *
     *
     */
    public function summaryAction(Request $request, SiteConfigProvider $siteConfigProvider): JsonResponse
    {
        $config = $siteConfigProvider->getSiteConfig($this->getSite($request));

        $startDate = date('Y-m-d', (time() - (86400 * 31)));
        $endDate = date('Y-m-d');

        if ($request->get('dateFrom') && $request->get('dateTo')) {
            $startDate = date('Y-m-d', strtotime($request->get('dateFrom')));
            $endDate = date('Y-m-d', strtotime($request->get('dateTo')));
        }

        if ($filterPath = $this->getFilterPath($request)) {
            $filters[] = 'ga:pagePath=='.$filterPath;
        }

        $opts = [
            'dimensions' => 'ga:date',
        ];

        if (!empty($filters)) {
            $opts['filters'] = implode(';', $filters);
        }

        $result = $this->service->data_ga->get(
            'ga:' . $config['profile'],
            $startDate,
            $endDate,
            'ga:uniquePageviews,ga:pageviews,ga:exits,ga:bounces,ga:entrances',
            $opts
        );

        $data = [];
        $dailyDataGrouped = [];

        foreach ($result['rows'] as $row) {
            foreach ($result['columnHeaders'] as $index => $metric) {
                if ($index) {
                    $dailyDataGrouped[$metric['name']][] = $row[$index];
                    if (!isset($data[$metric['name']])) {
                        $data[$metric['name']] = 0;
                    }
                    $data[$metric['name']] += $row[$index];
                }
            }
        }

        $order = [
            'ga:pageviews' => 0,
            'ga:uniquePageviews' => 1,
            'ga:exits' => 2,
            'ga:entrances' => 3,
            'ga:bounces' => 4,
        ];

        $outputData = [];
        foreach ($data as $key => $value) {
            $outputData[$order[$key]] = [
                'label' => str_replace('ga:', '', $key),
                'value' => round($value, 2),
                'chart' => ImageChart::lineSmall($dailyDataGrouped[$key]),
                'metric' => str_replace('ga:', '', $key),
            ];
        }

        ksort($outputData);

        return $this->jsonResponse(['data' => $outputData]);
    }

    /**
     * @Route("/source", name="opendxp_bundle_googlemarketing_reports_analytics_source", methods={"GET"})
     *
     *
     */
    public function sourceAction(Request $request, SiteConfigProvider $siteConfigProvider): JsonResponse
    {
        $config = $siteConfigProvider->getSiteConfig($this->getSite($request));

        $startDate = date('Y-m-d', (time() - (86400 * 31)));
        $endDate = date('Y-m-d');

        if ($request->get('dateFrom') && $request->get('dateTo')) {
            $startDate = date('Y-m-d', strtotime($request->get('dateFrom')));
            $endDate = date('Y-m-d', strtotime($request->get('dateTo')));
        }

        if ($filterPath = $this->getFilterPath($request)) {
            $filters[] = 'ga:pagePath=='.$filterPath;
        }

        $opts = [
            'dimensions' => 'ga:source',
            'max-results' => '10',
            'sort' => '-ga:pageviews',
        ];

        if (!empty($filters)) {
            $opts['filters'] = implode(';', $filters);
        }

        $result = $this->service->data_ga->get(
            'ga:' . $config['profile'],
            $startDate,
            $endDate,
            'ga:pageviews',
            $opts
        );

        $data = [];

        foreach ((array) $result['rows'] as $row) {
            $data[] = [
                'pageviews' => $row[1],
                'source' => $row[0],
            ];
        }

        return $this->jsonResponse(['data' => $data]);
    }

    /**
     * @Route("/data-explorer", name="opendxp_bundle_googlemarketing_reports_analytics_dataexplorer", methods={"GET", "POST"})
     *
     *
     */
    public function dataExplorerAction(Request $request, SiteConfigProvider $siteConfigProvider): JsonResponse
    {
        $config = $siteConfigProvider->getSiteConfig($this->getSite($request));

        $startDate = date('Y-m-d', (time() - (86400 * 31)));
        $endDate = date('Y-m-d');
        $metric = 'ga:pageviews';
        $dimension = 'ga:date';
        $descending = true;
        $limit = 10;

        if ($request->get('dateFrom') && $request->get('dateTo')) {
            $startDate = date('Y-m-d', strtotime($request->get('dateFrom')));
            $endDate = date('Y-m-d', strtotime($request->get('dateTo')));
        }
        if ($request->get('dimension')) {
            $dimension = $request->get('dimension');
        }
        if ($request->get('metric')) {
            $metric = $request->get('metric');
        }
        if ($request->get('sort')) {
            if ($request->get('sort') == 'asc') {
                $descending = false;
            }
        }
        if ($request->get('limit')) {
            $limit = $request->get('limit');
        }

        if ($filterPath = $this->getFilterPath($request)) {
            $filters[] = 'ga:pagePath=='.$filterPath;
        }

        $opts = [
            'dimensions' => $dimension,
            'max-results' => $limit,
            'sort' => ($descending ? '-' : '') . $metric,
        ];

        if (!empty($filters)) {
            $opts['filters'] = implode(';', $filters);
        }

        $result = $this->service->data_ga->get(
            'ga:' . $config['profile'],
            $startDate,
            $endDate,
            $metric,
            $opts
        );

        $data = [];
        foreach ($result['rows'] as $row) {
            $data[] = [
                'dimension' => $this->formatDimension($dimension, $row[0]),
                'metric' => (float) $row[1],
            ];
        }

        return $this->jsonResponse(['data' => $data]);
    }

    /**
     * @Route("/get-dimensions", name="opendxp_bundle_googlemarketing_reports_analytics_getdimensions", methods={"GET"})
     *
     *
     */
    public function getDimensionsAction(Request $request): JsonResponse
    {
        return $this->jsonResponse(['data' => Api::getAnalyticsDimensions()]);
    }

    /**
     * @Route("/get-metrics", name="opendxp_bundle_googlemarketing_reports_analytics_getmetrics", methods={"GET"})
     *
     *
     */
    public function getMetricsAction(Request $request): JsonResponse
    {
        return $this->jsonResponse(['data' => Api::getAnalyticsMetrics()]);
    }

    /**
     * @Route("/get-segments", name="opendxp_bundle_googlemarketing_reports_analytics_getsegments", methods={"GET"})
     *
     *
     */
    public function getSegmentsAction(Request $request): JsonResponse
    {
        $result = $this->service->management_segments->listManagementSegments();

        $data = [];

        foreach ($result['items'] as $row) {
            $data[] = [
                'id' => $row['segmentId'],
                'name' => $row['name'],
            ];
        }

        return $this->jsonResponse(['data' => $data]);
    }

    protected function formatDimension(string $type, string $value): string
    {
        if (strpos($type, 'date') !== false) {
            $date = new \DateTime();
            $date->setTimestamp(strtotime($value));

            return $date->format('Y-m-d');
        }

        return $value;
    }

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $client = Api::getServiceClient();
        if (!$client) {
            die('Google Analytics is not configured');
        }

        $this->service = new Analytics($client);
    }
}
