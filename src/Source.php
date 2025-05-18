<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use Psr\Http\Message\StreamInterface;

interface Source
{

    public StreamInterface $stream { get; }

}
