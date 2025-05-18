<?php

declare(strict_types = 1);

namespace Biano\Heureka\Eshop;

use Biano\Heureka\Source;
use Psr\Http\Message\StreamInterface;

final readonly class EshopSource implements Source
{

    public function __construct(
        public StreamInterface $stream,
    ) {
    }

}
