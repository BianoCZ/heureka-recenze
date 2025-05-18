<?php

declare(strict_types = 1);

namespace Biano\Heureka\Eshop;

use Biano\Heureka\BaseClient;
use DateTimeImmutable;
use SimpleXMLElement;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function explode;
use function trim;

/**
 * @extends \Biano\Heureka\BaseClient<\Biano\Heureka\Eshop\EshopSource, \Biano\Heureka\Eshop\EshopReview>
 * @phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
 */
final readonly class EshopReviewsClient extends BaseClient
{

    protected function getNodeName(): string
    {
        return 'review';
    }

    protected function processElement(
        SimpleXMLElement $element,
        int $index,
    ): EshopReview {
        $author = trim((string) $element->name);
        $summary = trim((string) $element->summary);
        $reaction = trim((string) $element->reaction);

        return new EshopReview(
            $index,
            (int) $element->rating_id,
            $author !== '' ? $author : null,
            (new DateTimeImmutable())->setTimestamp((int) $element->unix_timestamp),
            array_values(array_filter(array_map(trim(...), explode("\n", (string) $element->pros)), static fn (string $item): bool => $item !== '')),
            array_values(array_filter(array_map(trim(...), explode("\n", (string) $element->cons)), static fn (string $item): bool => $item !== '')),
            $summary !== '' ? $summary : null,
            (string) $element->order_id,
            count($element->total_rating) > 0 ? (float) $element->total_rating : null,
            count($element->delivery_time) > 0 ? (float) $element->delivery_time : null,
            count($element->transport_quality) > 0 ? (float) $element->transport_quality : null,
            count($element->web_usability) > 0 ? (float) $element->web_usability : null,
            count($element->communication) > 0 ? (float) $element->communication : null,
            $reaction !== '' ? $reaction : null,
        );
    }

}
