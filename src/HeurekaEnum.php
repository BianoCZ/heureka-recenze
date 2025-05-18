<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;
use function sprintf;

enum HeurekaEnum: string
{

    case CZ = 'https://www.heureka.cz/direct/dotaznik';
    case SK = 'https://www.heureka.sk/direct/dotaznik';

    public function getEshopReviewsUri(string $key): string
    {
        return sprintf('%s/export-review.php?key=%s', $this->value, $key);
    }

    public function getProductReviewsUri(string $key, ?DateTimeImmutable $from = null): string
    {
        return sprintf('%s/export-product-review.php?key=%s%s', $this->value, $key, $from !== null ? sprintf('&from=%s', $from->format('Y-m-d H:i:s')) : '');
    }

}
