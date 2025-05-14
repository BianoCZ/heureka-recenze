<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use function date;

final class ProductReviewsClientTest extends TestCase
{

    public function testConstruct(): void
    {
        $client = new ProductReviewsClient('abcdeABCDE1234567890123456789012');
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client = new ProductReviewsClient('abcdeABCDE-234567890123456789012');
        self::assertSame('abcdeABCDE-234567890123456789012', $client->getSourceAddress());

        $client = new ProductReviewsClient('abcdeABCDE');
        self::assertSame('abcdeABCDE', $client->getSourceAddress());

        $client = new ProductReviewsClient('http://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012');
        self::assertSame('http://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client = new ProductReviewsClient();
        self::assertNull($client->getSourceAddress());

        // Now with time parametere
        $client = new ProductReviewsClient('abcdeABCDE1234567890123456789012', new DateTimeImmutable('2015-01-01'));
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012&from=2015-01-01 00:00:00', $client->getSourceAddress());

        $client = new ProductReviewsClient('abcdeABCDE1234567890123456789012', new DateTimeImmutable('now'));
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012&from=' . date('Y-m-d H:i:s'), $client->getSourceAddress());

        $client = new ProductReviewsClient('blbost', new DateTimeImmutable('now'));
        self::assertSame('blbost', $client->getSourceAddress());
    }

    public function testGetSet(): void
    {
        $client = new ProductReviewsClient();

        $client->setSourceAddress('aaa');
        self::assertSame('aaa', $client->getSourceAddress());

        $client->setKey('abcdeABCDE1234567890123456789012');
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-product-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client->setTempFile('abcde.xml');
        self::assertSame('abcde.xml', $client->getTempFile());
        self::assertTrue($client->getDeleteTempFileAfterParsing());

        $client->setTempFile('abcde.xml', false);
        self::assertSame('abcde.xml', $client->getTempFile());
        self::assertFalse($client->getDeleteTempFileAfterParsing());

        $client->setTempFile('abcde.xml');
        $client->setTempFile(null);
        self::assertNull($client->getTempFile());

        $client->useFile(__DIR__ . '/example-data/product-reviews.xml');
        self::assertSame(__DIR__ . '/example-data/product-reviews.xml', $client->getTempFile());
        self::assertFalse($client->getDeleteTempFileAfterParsing());

        self::assertSame(__DIR__ . '/example-data/product-reviews.xml', $client->getTempFile());
    }

    public function testParse(): void
    {
        $client = new ProductReviewsClient();
        $client->useFile(__DIR__ . '/example-data/product-reviews.xml');

        $reviews = [];

        $client->setCallback(static function (ProductReview $review) use (&$reviews): void {
            $reviews[] = $review;
        });

        $client->run();

        self::assertCount(5, $reviews);

        self::assertSame(290.00, $reviews[0]->productPrice);
        self::assertSame('14370', $reviews[1]->orderId);
        self::assertSame('Stojan notový Pecka MSP-008 RD', $reviews[2]->productName);
        self::assertSame(5948610, $reviews[3]->ratingId);
        self::assertSame('vhodná pro začátečníky', $reviews[4]->summary);
        self::assertSame('cena=kvalita', $reviews[3]->pros);
        self::assertSame('', $reviews[1]->cons);
        self::assertSame('', $reviews[1]->pros);
        self::assertSame('kalamajka', $reviews[2]->author);
        self::assertSame("zatím to vypadá dobře\nza ty peníze je to dobrý kšeft\nsnad se nerozpadne", $reviews[2]->pros);
        self::assertSame(date('Y-m-d H:i:s', 1452012942), $reviews[3]->date->format('Y-m-d H:i:s'));

        // ID resolver was not set, so no summaries can be constructed

        $summary = $client->getAllSummaries();
        self::assertCount(0, $summary);

        self::assertSame([], $client->getAllProductIds());

        self::assertNull($client->getSummaryOfProduct(12345));
    }

    public function testSummaries(): void
    {
        $client = new ProductReviewsClient();
        $client->useFile(__DIR__ . '/example-data/product-reviews.xml');
        self::assertFalse($client->getSaveSummary());
        $client->setSaveSummary(true);
        self::assertTrue($client->getSaveSummary());
        self::assertTrue($client->getSaveGroupedReviews());

        $calls = 0;
        $lastReview = null;

        $client->setIdResolver(static function (ProductReview $r) use (&$calls, &$lastReview): string {
            $calls++;
            $lastReview = $r;

            return $r->productUrl;
        });

        $client->run();

        self::assertSame(4, $calls);

        self::assertCount(4, $client->getAllProductIds());
        self::assertCount(4, $client->getAllSummaries());

        self::assertSame(
            [
                'http://www.someeshop.cz/stojan-notovy-pecka.html',
                'http://www.someeshop.cz/sopranova-zobcova-fletna-drevena-mollenhauer.html',
                'http://www.someeshop.cz/klasicka-kytara-44-pecka.html',
                'http://www.someeshop.cz/klasicka-kytara-44-pecka-nat.html',
            ],
            $client->getAllProductIds(),
        );

        self::assertNotNull($client->getSummaryOfProduct('http://www.someeshop.cz/stojan-notovy-pecka.html'));
        self::assertNull($client->getSummaryOfProduct('abcde'));

        $summary = $client->getSummaryOfProduct('http://www.someeshop.cz/stojan-notovy-pecka.html');
        self::assertSame(2, $summary->reviewCount);
        self::assertSame(4.0, $summary->averageRating);
        self::assertSame(5.0, $summary->bestRating);
        self::assertSame(3.0, $summary->worstRating);
        self::assertCount(2, $summary->reviews);
        self::assertSame(5957872, $summary->reviews[1]->ratingId);
        self::assertSame(date('Y-m-d H:i:s', 1452356372), $summary->newestReviewDate?->format('Y-m-d H:i:s'));
        self::assertSame(date('Y-m-d H:i:s', 1452316372), $summary->oldestReviewDate?->format('Y-m-d H:i:s'));
        self::assertSame(8.0, $summary->totalStars);
        self::assertSame('http://www.someeshop.cz/stojan-notovy-pecka.html', $summary->productId);

        $summaries = $client->getAllSummaries();
        $summary = $summaries['http://www.someeshop.cz/sopranova-zobcova-fletna-drevena-mollenhauer.html'];

        self::assertSame(5.0, $summary->averageRating);
        self::assertSame(1, $summary->reviewCount);
        self::assertSame('http://www.someeshop.cz/sopranova-zobcova-fletna-drevena-mollenhauer.html', $summary->productId);

        $summary = $client->getSummaryOfProduct('http://www.someeshop.cz/klasicka-kytara-44-pecka-nat.html');
        self::assertNotNull($summary);
        self::assertSame($lastReview, $summary->reviews[0]);
    }

    public function testWithNullRatings(): void
    {
        $client = new ProductReviewsClient();
        $client->useFile(__DIR__ . '/example-data/product-reviews-null.xml');
        $client->setSaveSummary(true);
        $client->setIdResolver(static fn (ProductReview $r): string => 'aaa');
        $client->run();

        $reviews = $client->getSummaryOfProduct('aaa');
        self::assertNotNull($reviews);

        self::assertSame(3, $reviews->reviewCount);
        self::assertSame(2, $reviews->ratingCount);
        self::assertSame(4.0, $reviews->averageRating);
        self::assertSame(3.0, $reviews->reviews[0]->rating);
        self::assertSame(5.0, $reviews->reviews[1]->rating);
        self::assertNull($reviews->reviews[2]->rating);
    }

}
