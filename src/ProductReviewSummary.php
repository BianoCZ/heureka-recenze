<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;

/**
 * Shrnutí recenzí jednoho konkrétního produktu
 */
final class ProductReviewSummary
{

    /**
     * @param int|string $productId        ID produktu
     * @param int $reviewCount      Počet recenzí na tento produkt
     * @param int $ratingCount      Počet hodnocení na tento produkt. Počet hodnocení a počet recenzí nemusí nutně být totéž.
     * @param float $averageRating    Průměrné hodnocení na stupnici 0.5 až 5 hvězdiček
     * @param float $totalStars       Celkový počet hvězdiček
     * @param float $bestRating       Nejlepší hodnocení
     * @param float $worstRating      Nejhorší hodnocení
     * @param \DateTimeImmutable|null $oldestReviewDate Datum nejstarší recenze
     * @param \DateTimeImmutable|null $newestReviewDate Datum nejmladší recenze
     * @param list<\Biano\Heureka\ProductReview> $reviews          Jednotlivé recenze, které se tohoto produktu týkají
     */
    public function __construct(
        public readonly int|string $productId,
        public int $reviewCount = 0,
        public int $ratingCount = 0,
        public float $averageRating = 0.0,
        public float $totalStars = 0.0,
        public float $bestRating = 0.0,
        public float $worstRating = 0.0,
        public ?DateTimeImmutable $oldestReviewDate = null,
        public ?DateTimeImmutable $newestReviewDate = null,
        public array $reviews = [],
    ) {
    }

}
