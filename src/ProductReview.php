<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;

/**
 * Recenze produktu
 */
final class ProductReview
{

    /**
     * @param int $index         Číslo recenze v rámci souboru, začíná se od 0
     * @param int $ratingId      Jedinečné ID recenze (dané Heurékou)
     * @param string $author        Jméno autora. Empty string = anonymní.
     * @param \DateTimeImmutable $date          Datum a čas napsání recenze
     * @param float|null $rating        Hodnocení produktu na stupnici od 0.5 do 5.
     * @param string $pros          Hlavní výhody produktu. Víceřádkový řetězec, zpravidla co řádek, to jeden bod
     * @param string $cons          Hlavní nevýhody produktu. Víceřádkový řetězec, zpravidla co řádek, to jeden bod
     * @param string $summary       Celkové shrnutí názoru zákazníka na produkt
     * @param int|string|null $productId     ID hodnoceného produktu dle e-shopu. Nepochází z Heuréky, jde o výstup z IdResolver funkce, pokud není definována, je vždy null.
     * @param string $productName   Název produktu
     * @param string $productUrl    URL produktu
     * @param float $productPrice  Cena produktu (bez DPH)
     * @param string $productEan    EAN produktu
     * @param string $productNumber Číslo produktu
     * @param string $orderId       Číslo objednávky, na níž zákazník psal recenzi
     */
    public function __construct(
        public readonly int $index,
        public readonly int $ratingId,
        public readonly string $author,
        public readonly DateTimeImmutable $date,
        public readonly ?float $rating,
        public readonly string $pros,
        public readonly string $cons,
        public readonly string $summary,
        private int|string|null $productId,
        public readonly string $productName,
        public readonly string $productUrl,
        public readonly float $productPrice,
        public readonly string $productEan,
        public readonly string $productNumber,
        public readonly string $orderId,
    ) {
    }

    public function getProductId(): int|string|null
    {
        return $this->productId;
    }

    public function setProductId(int|string|null $productId): void
    {
        $this->productId = $productId;
    }

}
