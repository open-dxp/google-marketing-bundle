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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Event;

final class GoogleTagManagerEvents
{
    /**
     * Triggered before the tag manager head code block is rendered. Can be used to add additional code
     * snippets to the head code.
     *
     * @Event("OpenDxp\Bundle\GoogleMarketingBundle\Model\Event\CodeEvent")
     */
    const string CODE_HEAD = 'opendxp.analytics.google.tag_manager.code_head';

    /**
     * Triggered before the tag manager body code is rendered. Can be used to add additional code
     * snippets to the body code.
     *
     * @Event("OpenDxp\Bundle\GoogleMarketingBundle\Model\Event\TagManager\CodeEvent")
     */
    const string CODE_BODY = 'opendxp.analytics.google.tag_manager.code_body';
}
