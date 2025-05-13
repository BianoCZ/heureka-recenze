<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;

/**
 * Recenze e-shopu
 */
final readonly class EshopReview
{

    /**
     * @param int $index                  Číslo recenze v rámci souboru, začíná se od 0
     * @param int $ratingId               Jedinečné ID recenze (dané Heurékou)
     * @param string $author                 Jméno autora. Empty string = anonymní.
     * @param \DateTimeImmutable $date                   Datum a čas napsání recenze
     * @param float|null $ratingTotal            Hodnocení celkové - na stupnici 0.5 až 5 hvězdiček
     * @param float|null $ratingDelivery         Hodnocení délky dodací lhůty - na stupnici 0.5 až 5 hvězdiček
     * @param float|null $ratingTransportQuality Hodnocení kvality dopravy zboží - na stupnici 0.5 až 5 hvězdiček
     * @param float|null $ratingWebUsability     Hodnocení použitelnosti a přehlednosti e-shopu na stupnici 0.5 až 5 hvězdiček
     * @param float|null $ratingCommunication    Hodnocení komunikace ze strany e-shopu na stupnici 0.5 až 5 hvězdiček
     * @param string $pros                   Hlavní výhody e-shopu. Víceřádkový řetězec, zpravidla co řádek, to jeden bod
     * @param string $cons                   Hlavní nevýhody e-shopu. Víceřádkový řetězec, zpravidla co řádek, to jeden bod
     * @param string $summary                Celkové shrnutí názoru zákazníka na obchod
     * @param string $reaction               Reakce provozovatele e-shopu na recenzi zákazníka
     * @param string $orderId                Číslo objednávky, na níž zákazník psal recenzi
     */
    public function __construct(
        public int $index,
        public int $ratingId,
        public string $author,
        public DateTimeImmutable $date,
        public ?float $ratingTotal,
        public ?float $ratingDelivery,
        public ?float $ratingTransportQuality,
        public ?float $ratingWebUsability,
        public ?float $ratingCommunication,
        public string $pros,
        public string $cons,
        public string $summary,
        public string $reaction,
        public string $orderId,
    ) {
    }

}
