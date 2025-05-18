<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use Biano\Heureka\Eshop\EshopReview;
use Biano\Heureka\Eshop\EshopReviewsClient;
use Biano\Heureka\Eshop\EshopSource;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

final class EshopReviewsClientTest extends TestCase
{

    /**
     * @param list<\Biano\Heureka\Eshop\EshopReview> $expectedResult
     */
    #[DataProvider('runProvider')]
    public function testRun(string $fileName, array $expectedResult): void
    {
        $reviews = [];

        (new EshopReviewsClient())->run(
            new EshopSource(Utils::streamFor(file_get_contents($fileName))),
            static function (EshopReview $review) use (&$reviews): void {
                $reviews[] = $review;
            },
        );

        self::assertEquals($expectedResult, $reviews);
    }

    /**
     * @return iterable<string, array{fileName: string, expectedResult: list<\Biano\Heureka\Eshop\EshopReview>}>
     */
    public static function runProvider(): iterable
    {
        yield 'eshop-reviews' => [
            'fileName' => __DIR__ . '/example-data/eshop-reviews.xml',
            'expectedResult' => [
                new EshopReview(0, 141604079, null, new DateTimeImmutable('2016-01-05 16:55:18'), ['-velmi rychlé dodání'], [], 'doporučuji', '14343', 5, 5, 5, 5, 5, null),
                new EshopReview(1, 140332011, 'Věra - J.', new DateTimeImmutable('2016-01-05 10:46:25'), ['rychlé dodání', 'dobré ceny'], ['nevím o žádných'], 'spokojena', '14088', 5, 5, 5, 5, 5, null),
                new EshopReview(2, 138008506, null, new DateTimeImmutable('2016-01-04 18:42:39'), [], ['u výrobků chyběl návod v češtině (způsob použití byl pouze v německém jazyce nebo vůbec)'], 'dobrý obchod s rychlým vyřízením a doručením objednávky', '13594', 4, 5, 5, 3.5, 5, 'byl to německý výrobek'),
                new EshopReview(3, 61843903, 'bročka', new DateTimeImmutable('2014-01-14 19:32:49'), ['výběr zboží v klidu z domova'], [], 'velká spokojenost', '3049', 5, null, 5, 5, 5, null),
                new EshopReview(4, 59065101, 'BreHa', new DateTimeImmutable('2013-12-25 08:50:08'), ['super ceny za kvalitní zboží'], ['žlutý kůň Úpěl Ďábelské ódy'], null, '2382', null, null, null, null, null, null),
            ],
        ];
    }

}
