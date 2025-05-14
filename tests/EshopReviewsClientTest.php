<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use PHPUnit\Framework\TestCase;
use function array_map;
use function date;
use function range;

final class EshopReviewsClientTest extends TestCase
{

    public function testConstruct(): void
    {
        $client = new EshopReviewsClient('abcdeABCDE1234567890123456789012');
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client = new EshopReviewsClient('abcdeABCDE-234567890123456789012');
        self::assertSame('abcdeABCDE-234567890123456789012', $client->getSourceAddress());

        $client = new EshopReviewsClient('abcdeABCDE');
        self::assertSame('abcdeABCDE', $client->getSourceAddress());

        $client = new EshopReviewsClient('http://www.heureka.cz/direct/dotaznik/export-review.php?key=abcdeABCDE1234567890123456789012');
        self::assertSame('http://www.heureka.cz/direct/dotaznik/export-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client = new EshopReviewsClient();
        self::assertNull($client->getSourceAddress());
    }

    public function testGetSet(): void
    {
        $client = new EshopReviewsClient();

        $client->setSourceAddress('aaa');
        self::assertSame('aaa', $client->getSourceAddress());

        $client->setKey('abcdeABCDE1234567890123456789012');
        self::assertSame('https://www.heureka.cz/direct/dotaznik/export-review.php?key=abcdeABCDE1234567890123456789012', $client->getSourceAddress());

        $client->setKey('abcdeABCDE');
        self::assertSame('abcdeABCDE', $client->getSourceAddress());

        $client->setTempFile('abcde.xml');
        self::assertSame('abcde.xml', $client->getTempFile());
        self::assertTrue($client->getDeleteTempFileAfterParsing());

        $client->setTempFile('abcde.xml', false);
        self::assertSame('abcde.xml', $client->getTempFile());
        self::assertFalse($client->getDeleteTempFileAfterParsing());

        $client->setTempFile('abcde.xml');
        $client->setTempFile(null);
        self::assertNull($client->getTempFile());

        $client->useFile(__DIR__ . '/example-data/eshop-reviews.xml');
        self::assertSame(__DIR__ . '/example-data/eshop-reviews.xml', $client->getTempFile());
        self::assertFalse($client->getDeleteTempFileAfterParsing());

        self::assertSame(__DIR__ . '/example-data/eshop-reviews.xml', $client->getTempFile());
    }

    public function testParse(): void
    {
        $client = new EshopReviewsClient();

        $client->useFile(__DIR__ . '/example-data/eshop-reviews.xml');

        $reviews = [];

        $client->setCallback(static function (EshopReview $review) use (&$reviews): void {
            $reviews[] = $review;
        });

        $client->run();

        self::assertCount(5, $reviews);

        self::assertSame('14343', $reviews[0]->orderId);
        self::assertSame(140332011, $reviews[1]->ratingId);
        self::assertSame('', $reviews[2]->author);
        self::assertSame('bročka', $reviews[3]->author);
        self::assertSame(range(0, 4), array_map(static fn ($r) => $r->index, $reviews));

        self::assertSame(date('Y-m-d H:i:s', 1452012918), $reviews[0]->date->format('Y-m-d H:i:s'));

        self::assertSame(3.5, $reviews[2]->ratingWebUsability);
        self::assertSame("rychlé dodání\ndobré ceny", $reviews[1]->pros);
        self::assertSame('nevím o žádných', $reviews[1]->cons);
        self::assertSame('spokojena', $reviews[1]->summary);
        self::assertSame('', $reviews[3]->cons);
        self::assertSame('byl to německý výrobek', $reviews[2]->reaction);
        self::assertSame('žlutý kůň Úpěl Ďábelské ódy', $reviews[4]->cons);
    }

    public function testNullValues(): void
    {
        $client = new EshopReviewsClient();

        $client->useFile(__DIR__ . '/example-data/eshop-reviews.xml');

        $reviews = [];

        $client->setCallback(static function (EshopReview $review) use (&$reviews): void {
            $reviews[] = $review;
        });

        $client->run();

        self::assertSame(3.5, $reviews[2]->ratingWebUsability);
        self::assertSame(5.0, $reviews[3]->ratingWebUsability);
        self::assertNull($reviews[3]->ratingDelivery);

        self::assertNull($reviews[4]->ratingWebUsability);
        self::assertNull($reviews[4]->ratingTotal);
        self::assertNull($reviews[4]->ratingDelivery);
        self::assertNull($reviews[4]->ratingCommunication);
        self::assertNull($reviews[4]->ratingTransportQuality);
    }

}
