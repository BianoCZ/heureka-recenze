<?php

declare(strict_types = 1);

namespace Biano\Heureka\Product;

use Biano\Heureka\Source;
use Psr\Http\Message\StreamInterface;

final readonly class ProductSource implements Source
{

    public function __construct(
        public StreamInterface $stream,
    ) {
    }

}
