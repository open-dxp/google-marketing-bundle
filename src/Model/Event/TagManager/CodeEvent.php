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

namespace OpenDxp\Bundle\GoogleMarketingBundle\Model\Event\TagManager;

use OpenDxp\Bundle\GoogleMarketingBundle\Code\CodeBlock;
use Symfony\Contracts\EventDispatcher\Event;

class CodeEvent extends Event
{
    private array $data;

    /**
     * @var CodeBlock[]
     */
    private array $blocks;

    private string $template;

    public function __construct(
        array $data,
        array $blocks,
        string $template
    ) {
        $this->data = $data;
        $this->blocks = $blocks;
        $this->template = $template;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return CodeBlock[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getBlock(string $block): CodeBlock
    {
        if (!isset($this->blocks[$block])) {
            throw new \InvalidArgumentException(sprintf('Invalid block "%s"', $block));
        }

        return $this->blocks[$block];
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
}
