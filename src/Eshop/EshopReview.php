<?php

declare(strict_types = 1);

namespace Biano\Heureka\Eshop;

use Biano\Heureka\Review;
use DateTimeImmutable;

final readonly class EshopReview implements Review
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
        public ?float $ratingTotal,
        public ?float $ratingDelivery,
        public ?float $ratingTransportQuality,
        public ?float $ratingWebUsability,
        public ?float $ratingCommunication,
        public ?string $reaction,
    ) {
    }

}
