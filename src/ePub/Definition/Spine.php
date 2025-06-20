<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ePub\Definition;

class Spine extends Collection
{
    private string $pageProgressionDirection = 'ltr';

    public function setPageProgressionDirection(string $direction): void
    {
        $this->pageProgressionDirection = $direction;
    }

    public function getPageProgressionDirection(): string
    {
        return $this->pageProgressionDirection;
    }
}
