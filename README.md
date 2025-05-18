# Heureka reviews

## Installation

```bash
composer require biano/heureka-recenze
```

## Usage

```php
use Biano\Heureka\Eshop\EshopReview;
use Biano\Heureka\Eshop\EshopReviewsClient;
use Biano\Heureka\HeurekaEnum;
use Biano\Heureka\Product\ProductReview;
use Biano\Heureka\Product\ProductReviewsClient;
use Biano\Heureka\SourceFactory;
use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$sourceFactory = new SourceFactory(new Client(), new HttpFactory());

// Get last 500 eshop reviews
(new EshopReviewsClient())->run(
    $sourceFactory->createEshopReviewsStream(HeurekaEnum::CZ, 'key'),
    static function (EshopReview $review): void {
        // do something with eshop review
    },
);

// Get product reviews from the last 10 days (max 6 months)
(new ProductReviewsClient())->run(
    $sourceFactory->createProductReviewsStream(HeurekaEnum::CZ, 'key', new DateTimeImmutable('-10 days')),
    static function (ProductReview $review): void {
        // do something with product review
    },
);
```
