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

namespace OpenDxp\Bundle\GoogleMarketingBundle;

use OpenDxp\Bundle\CustomReportsBundle\OpenDxpCustomReportsBundle;
use OpenDxp\Bundle\GoogleMarketingBundle\DependencyInjection\OpenDxpGoogleMarketingExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\Installer;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use OpenDxp\HttpKernel\Bundle\DependentBundleInterface;
use OpenDxp\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpGoogleMarketingBundle extends AbstractOpenDxpBundle implements DependentBundleInterface, OpenDxpBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new OpenDxpGoogleMarketingExtension();
        }

        return $this->extension;
    }

    public function getComposerPackageName(): string
    {
        return 'open-dxp/google-marketing-bundle';
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpgooglemarketing/css/googlemarketing.css',
        ];
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxpgooglemarketing/js/startup.js',
            '/bundles/opendxpgooglemarketing/js/settings.js',
            '/bundles/opendxpgooglemarketing/js/report/analytics/elementexplorer.js',
            '/bundles/opendxpgooglemarketing/js/report/analytics/elementoverview.js',
            '/bundles/opendxpgooglemarketing/js/report/analytics/settings.js',
            '/bundles/opendxpgooglemarketing/js/report/custom/definitions/analytics.js',
            '/bundles/opendxpgooglemarketing/js/report/tagmanager/settings.js',
            '/bundles/opendxpgooglemarketing/js/report/googleSearchConsole/settings.js',
            '/bundles/opendxpgooglemarketing/js/layout/portlets/analytics.js',
        ];
    }

    public function getInstaller(): ?Installer\InstallerInterface
    {
        /** @var \OpenDxp\Bundle\GoogleMarketingBundle\Installer $installer */
        $installer = $this->container->get(\OpenDxp\Bundle\GoogleMarketingBundle\Installer::class);

        return $installer;
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(OpenDxpCustomReportsBundle::class, 20);
    }
}
