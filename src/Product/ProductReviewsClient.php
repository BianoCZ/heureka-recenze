<?php

declare(strict_types = 1);

namespace Biano\Heureka\Product;

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
 * @extends \Biano\Heureka\BaseClient<\Biano\Heureka\Product\ProductSource, \Biano\Heureka\Product\ProductReview>
 * @phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
 */
final readonly class ProductReviewsClient extends BaseClient
{

    protected function getNodeName(): string
    {
        return 'product';
    }

    protected function processElement(
        SimpleXMLElement $element,
        int $index,
    ): ProductReview {
        $reviewElement = $element->reviews->review[0];

        $author = trim((string) $reviewElement->name);
        $summary = trim((string) $reviewElement->summary);

        return new ProductReview(
            $index,
            (int) $reviewElement->rating_id,
            $author !== '' ? $author : null,
            (new DateTimeImmutable())->setTimestamp((int) $reviewElement->unix_timestamp),
            array_values(array_filter(array_map(trim(...), explode("\n", (string) $reviewElement->pros)), static fn (string $item): bool => $item !== '')),
            array_values(array_filter(array_map(trim(...), explode("\n", (string) $reviewElement->cons)), static fn (string $item): bool => $item !== '')),
            $summary !== '' ? $summary : null,
            (string) $element->order_id,
            count($reviewElement->rating) > 0 ? (float) $reviewElement->rating : null,
            (string) $element->product_name,
            (string) $element->url,
            (float) $element->price,
            (string) $element->ean,
            (string) $element->productno,
        );
    }

}
