<?php

declare(strict_types=1);

/*
 * This file is part of the Contao News Filter Event extension.
 *
 * (c) INSPIRED MINDS
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoNewsFilterEvent;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoNewsFilterEventBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
