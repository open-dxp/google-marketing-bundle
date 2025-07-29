<?php

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

namespace OpenDxp\Bundle\GoogleMarketingBundle\DependencyInjection;

use OpenDxp\Bundle\GoogleMarketingBundle\Config\SiteConfigProvider;
use OpenDxp\Bundle\GoogleMarketingBundle\Tracker\Tracker as AnalyticsGoogleTracker;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class OpenDxpGoogleMarketingExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    public function loadInternal(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
        $loader->load('analytics.yaml');
        $this->configureGoogleAnalyticsFallbackServiceLocator($container);

        $container->setParameter('opendxp_google_marketing', $config);
    }

    /**
     * Creates service locator which is used from static OpenDxp\Google\Analytics class
     */
    private function configureGoogleAnalyticsFallbackServiceLocator(ContainerBuilder $container): void
    {
        $services = [
            AnalyticsGoogleTracker::class,
            SiteConfigProvider::class,
        ];

        $mapping = [];
        foreach ($services as $service) {
            $mapping[$service] = new Reference($service);
        }

        $serviceLocator = $container->getDefinition('opendxp.analytics.google.fallback_service_locator');
        $serviceLocator->setArguments([$mapping]);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('opendxp_admin')) {
            $loader = new YamlFileLoader(
                $container,
                new FileLocator(__DIR__ . '/../../config')
            );

            $loader->load('admin-classic.yaml');
        }

    }
}
