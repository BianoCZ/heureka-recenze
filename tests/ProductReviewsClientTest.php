<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use Biano\Heureka\Product\ProductReview;
use Biano\Heureka\Product\ProductReviewsClient;
use Biano\Heureka\Product\ProductSource;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

final class ProductReviewsClientTest extends TestCase
{

    /**
     * @param list<\Biano\Heureka\Product\ProductReview> $expectedResult
     */
    #[DataProvider('runProvider')]
    public function testRun(string $fileName, array $expectedResult): void
    {
        $reviews = [];

        (new ProductReviewsClient())->run(
            new ProductSource(Utils::streamFor(file_get_contents($fileName))),
            static function (ProductReview $review) use (&$reviews): void {
                $reviews[] = $review;
            },
        );

        self::assertEquals($expectedResult, $reviews);
    }

    /**
     * @return iterable<string, array{fileName: string, expectedResult: list<\Biano\Heureka\Product\ProductReview>}>
     */
    public static function runProvider(): iterable
    {
        yield 'product-reviews' => [
            'fileName' => __DIR__ . '/example-data/product-reviews.xml',
            'expectedResult' => [
                new ProductReview(0, 5957872, 'kalamajka', new DateTimeImmutable('2016-01-09 05:12:52'), ['celkem dobrý'], ['zatím nic'], 'vše ok', '13960', 3.0, 'Stojan notový Pecka MSP-008 RD', 'http://www.someeshop.cz/stojan-notovy-pecka.html', 290.0, '', ''),
                new ProductReview(1, 5952149, null, new DateTimeImmutable('2016-01-07 08:36:53'), [], [], 'Dcera i její učitelé jsou s flétnou velmi spokojení. Jedná se o kvalitní výrobek.', '14370', 5.0, 'Sopranová zobcová flétna dřevěná Mollenhauer 1042 New Student NTP', 'http://www.someeshop.cz/sopranova-zobcova-fletna-drevena-mollenhauer.html', 1790.0, '', ''),
                new ProductReview(2, 5957872, 'kalamajka', new DateTimeImmutable('2016-01-09 16:19:32'), ['zatím to vypadá dobře', 'za ty peníze je to dobrý kšeft', 'snad se nerozpadne'], ['zatím nic'], 'vše ok', '13960', 5.0, 'Stojan notový Pecka MSP-008 RD', 'http://www.someeshop.cz/stojan-notovy-pecka.html', 290.0, '', ''),
                new ProductReview(3, 5948610, null, new DateTimeImmutable('2016-01-05 16:55:42'), ['cena=kvalita'], [], 'vhodná pro začátečníky', '14343', 4.0, 'Klasická kytara 4/4 Pecka CGP-44 SB (sunburst)', 'http://www.someeshop.cz/klasicka-kytara-44-pecka.html', 1490.0, '', ''),
                new ProductReview(4, 5948610, null, new DateTimeImmutable('2016-01-05 16:55:42'), ['cena=kvalita'], [], 'vhodná pro začátečníky', '14343', 4.0, 'Klasická kytara 4/4 Pecka CGP-44 NAT (natural)', 'http://www.someeshop.cz/klasicka-kytara-44-pecka-nat.html', 1490.0, '', ''),
            ],
        ];

        yield 'product-reviews-null' => [
            'fileName' => __DIR__ . '/example-data/product-reviews-null.xml',
            'expectedResult' => [
                new ProductReview(0, 5957872, 'kalamajka', new DateTimeImmutable('2016-01-09 05:12:52'), ['celkem dobrý'], ['zatím nic'], 'vše ok', '13960', 3.0, 'Stojan notový Pecka MSP-008 RD', 'http://www.someeshop.cz/stojan-notovy-pecka.html', 290.0, '', ''),
                new ProductReview(1, 5957872, 'kalamajka', new DateTimeImmutable('2016-01-09 16:19:32'), ['zatím to vypadá dobře', 'za ty peníze je to dobrý kšeft', 'snad se nerozpadne'], ['zatím nic'], 'vše ok', '13960', 5.0, 'Stojan notový Pecka MSP-008 RD', 'http://www.someeshop.cz/stojan-notovy-pecka.html', 290.0, '', ''),
                new ProductReview(2, 5957872, 'kalamajka', new DateTimeImmutable('2016-01-09 16:19:32'), ['zatím nechci hodnotit, moc jsem s tím nepracoval.'], ['zatím nic'], 'vše ok', '13960', null, 'Stojan notový Pecka MSP-008 RD', 'http://www.someeshop.cz/stojan-notovy-pecka.html', 290.0, '', ''),
            ],
        ];
    }

}
