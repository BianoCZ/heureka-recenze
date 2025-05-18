<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;

interface Review
{

    public int $index { get; }

    public int $ratingId { get; }

    public ?string $author { get; }

    public DateTimeImmutable $date { get; }

    /** @var list<string> */
    public array $pros { get; }

    /** @var list<string> */
    public array $cons { get; }

    public ?string $summary { get; }

    public string $orderId { get; }

}
