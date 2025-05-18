<?php

declare(strict_types = 1);

namespace Biano\Heureka\Product;

use Biano\Heureka\Review;
use DateTimeImmutable;

final readonly class ProductReview implements Review
{

    /**
     * @param list<string> $pros
     * @param list<string> $cons
     */
    public function __construct(
        public int $index,
        public int $ratingId,
        public ?string $author,
        public DateTimeImmutable $date,
        public array $pros,
        public array $cons,
        public ?string $summary,
        public string $orderId,
        public ?float $rating,
        public string $productName,
        public string $productUrl,
        public float $productPrice,
        public string $productEan,
        public string $productNumber,
    ) {
    }

}
