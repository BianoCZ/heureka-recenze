<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use Biano\Heureka\Eshop\EshopSource;
use Biano\Heureka\Product\ProductSource;
use DateTimeImmutable;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final readonly class SourceFactory
{

    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
    ) {
    }

    public function createEshopReviewsStream(
        HeurekaEnum $heureka,
        string $key,
    ): EshopSource {
        return new EshopSource($this->client->sendRequest($this->requestFactory->createRequest('GET', $heureka->getEshopReviewsUri($key)))->getBody());
    }

    public function createProductReviewsStream(
        HeurekaEnum $heureka,
        string $key,
        ?DateTimeImmutable $from = null,
    ): ProductSource {
        return new ProductSource($this->client->sendRequest($this->requestFactory->createRequest('GET', $heureka->getProductReviewsUri($key, $from)))->getBody());
    }

}
