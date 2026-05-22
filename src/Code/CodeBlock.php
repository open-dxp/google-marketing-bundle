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
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\GoogleMarketingBundle\Code;

use Stringable;

/**
 * Represents a single template block. Parts are represented as array and concatenated
 * with newlines on render.
 */
final class CodeBlock implements Stringable
{
    public function __construct(private array $parts = [])
    {
    }

    public function setParts(array $parts): void
    {
        $this->parts = $parts;
    }

    public function getParts(): array
    {
        return $this->parts;
    }

    public function append(array|string $parts): void
    {
        $parts = (array)$parts;

        foreach ($parts as $part) {
            $this->parts[] = $part;
        }
    }

    public function prepend(array|string $parts): void
    {
        $parts = (array)$parts;
        $parts = array_reverse($parts); // prepend parts in the order they were passed

        foreach ($parts as $part) {
            array_unshift($this->parts, $part);
        }
    }

    public function asString(): string
    {
        $string = implode("\n", $this->parts);
        $string = trim($string);

        return $string;
    }

    public function __toString(): string
    {
        return $this->asString();
    }
}
