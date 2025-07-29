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

use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;

class Installer extends SettingsStoreAwareInstaller
{
    protected const USER_PERMISSIONS_CATEGORY = 'OpenDXP Google Marketing Bundle';

    const USER_PERMISSIONS = [
        'google_marketing',
    ];

    protected function addPermissions(): void
    {
        $db = \OpenDxp\Db::get();

        foreach (self::USER_PERMISSIONS as $permission) {
            $db->insert('users_permission_definitions', [
                $db->quoteIdentifier('key') => $permission,
                $db->quoteIdentifier('category') => self::USER_PERMISSIONS_CATEGORY,
            ]);
        }
    }

    protected function removePermissions(): void
    {
        $db = \OpenDxp\Db::get();

        foreach (self::USER_PERMISSIONS as $permission) {
            $db->delete('users_permission_definitions', [
                $db->quoteIdentifier('key') => $permission,
            ]);
        }
    }

    public function install(): void
    {
        $this->addPermissions();
        $this->installDependentBundles();
        parent::install();
    }

    public function installDependentBundles(): void
    {
        //Install CustomReportsBundle
        $customReportsInstaller = \OpenDxp::getContainer()->get(\OpenDxp\Bundle\CustomReportsBundle\Installer::class);
        if (!$customReportsInstaller->isInstalled()) {
            $customReportsInstaller->install();
        }
    }

    public function uninstall(): void
    {
        $this->removePermissions();
        parent::uninstall();
    }
}
