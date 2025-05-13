<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;
use SimpleXMLElement;
use function count;
use function preg_match;

/**
 * Klient umožňující stahovat recenze e-shopu jako takového
 *
 * @extends \Biano\Heureka\BaseClient<\Biano\Heureka\EshopReview>
 */
final class EshopReviewsClient extends BaseClient
{

    public function getNodeName(): string
    {
        return 'review';
    }

    public function setKey(string $key, ?DateTimeImmutable $from = null): void
    {
        if (preg_match('/^[a-f0-9]{32}$/i', $key) === 1) {
            $this->setSourceAddress('https://www.heureka.cz/direct/dotaznik/export-review.php?key=' . $key);
        } else {
            $this->setSourceAddress($key);
        }
    }

    public function processElement(SimpleXMLElement $element, int $index): EshopReview
    {
        return new EshopReview(
            $index,
            (int) $element->rating_id,
            (string) $element->name,
            (new DateTimeImmutable())->setTimestamp((int) $element->unix_timestamp),
            count($element->total_rating) > 0 ? (float) $element->total_rating : null,
            count($element->delivery_time) > 0 ? (float) $element->delivery_time : null,
            count($element->transport_quality) > 0 ? (float) $element->transport_quality : null,
            count($element->web_usability) > 0 ? (float) $element->web_usability : null,
            count($element->communication) > 0 ? (float) $element->communication : null,
            (string) $element->pros,
            (string) $element->cons,
            (string) $element->summary,
            (string) $element->reaction,
            (string) $element->order_id,
        );
    }

}
