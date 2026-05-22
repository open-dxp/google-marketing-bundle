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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('opendxp_google_marketing');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('client_id')
                    ->info('This is required for the Google API integrations. Only use a `Service Account´ from the Google Cloud Console.')
                    ->defaultNull()
                ->end()
                ->scalarNode('email')
                    ->info('Email address of the Google service account')
                    ->defaultNull()
                ->end()
                ->scalarNode('simple_api_key')
                    ->info('Server API key')
                    ->defaultNull()
                ->end()
                ->scalarNode('browser_api_key')
                    ->info('Browser API key')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
