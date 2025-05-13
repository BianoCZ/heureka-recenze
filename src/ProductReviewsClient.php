<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;
use SimpleXMLElement;
use function array_key_exists;
use function array_keys;
use function count;
use function md5;
use function preg_match;
use function round;

/**
 * Klient umožňující stahovat recenze jednotlivých produktů
 *
 * @extends \Biano\Heureka\BaseClient<\Biano\Heureka\ProductReview>
 */
final class ProductReviewsClient extends BaseClient
{

    /** @var (callable(\Biano\Heureka\ProductReview): (int|string|null))|null  */
    protected $idResolver = null;

    protected bool $saveSummary = false;

    protected bool $saveGroupedReviews = false;

    /** @var array<string, int|string|null> */
    protected array $idResolverCache = [];

    /** @var array<int|string, \Biano\Heureka\ProductReviewSummary>  */
    protected array $summary = [];

    public function getNodeName(): string
    {
        return 'product';
    }

    /**
     * @param string|null $key  Heuréka klíč (32 znaků) anebo celá adresa pro stahování importu
     * @param \DateTimeImmutable|null $from Volitelně lze omezit, odkdy chceš recenze stáhnout. Max 6 měsíců zpátky. Funguje jen zadáš-li jako $key 32znakový klíč.
     */
    public function __construct(?string $key = null, ?DateTimeImmutable $from = null)
    {
        parent::__construct($key);

        if ($key !== null) {
            $this->setKey($key, $from);
        }
    }

    /**
     * @param string $key  Heuréka klíč (32 znaků) anebo celá adresa pro stahování importu
     * @param \DateTimeImmutable|null $from Volitelně lze omezit, odkdy chceš recenze stáhnout. Max 6 měsíců zpátky. Funguje jen zadáš-li jako $key 32znakový klíč.
     */
    public function setKey(string $key, ?DateTimeImmutable $from = null): void
    {
        $fromPart = '';
        if ($from !== null) {
            $fromPart = '&from=' . $from->format('Y-m-d H:i:s');
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $key) === 1) {
            $this->setSourceAddress('https://www.heureka.cz/direct/dotaznik/export-product-review.php?key=' . $key . $fromPart);
        } else {
            $this->setSourceAddress($key);
        }
    }

    protected function processFile(): void
    {
        $this->idResolverCache = [];
        $this->summary = [];

        parent::processFile();
    }

    public function processElement(SimpleXMLElement $element, int $index): ProductReview
    {
        $reviewElement = $element->reviews->review[0];

        $review = new ProductReview(
            $index,
            (int) $reviewElement->rating_id,
            (string) $reviewElement->name,
            (new DateTimeImmutable())->setTimestamp((int) $reviewElement->unix_timestamp),
            count($reviewElement->rating) > 0 ? (float) $reviewElement->rating : null,
            (string) $reviewElement->pros,
            (string) $reviewElement->cons,
            (string) $reviewElement->summary,
            null,
            (string) $element->product_name,
            (string) $element->url,
            (float) $element->price,
            (string) $element->ean,
            (string) $element->productno,
            (string) $element->order_id,
        );

        $prodId = $this->resolveId($review);
        $review->setProductId($prodId);

        if ($prodId !== null) {
            $this->addReviewToSummary($prodId, $review);
        }

        return $review;
    }

    /**
     * @return (callable(\Biano\Heureka\ProductReview): (int|string|null))|null
     */
    public function getIdResolver(): ?callable
    {
        return $this->idResolver;
    }

    /**
     * Nastaví funkci odpovědnou za převedení informací o produktu na jeho jednoznačné ID.
     * Tuto funkci je třeba implementovat, aby dobře fungovaly summary.
     *
     * @param (callable(\Biano\Heureka\ProductReview): (int|string|null))|null $idConverter
     *
     * @return $this
     */
    public function setIdResolver(?callable $idConverter): self
    {
        $this->idResolver = $idConverter;

        return $this;
    }

    /**
     * Mají se ukládat summary?
     */
    public function getSaveSummary(): bool
    {
        return $this->saveSummary;
    }

    /**
     * Mají se ukládat do summary i všechny recenze?
     */
    public function getSaveGroupedReviews(): bool
    {
        return $this->saveGroupedReviews;
    }

    /**
     * Mají se průběžně ukládat summary? Umožní po proběhnutí importu
     * pracovat s shrnujícími daty.
     *
     * @param bool $groupedReviews Mají se ukládat i všechny recenze?
     *
     * @return $this
     */
    public function setSaveSummary(bool $saveSummary, bool $groupedReviews = true): self
    {
        $this->saveSummary = $saveSummary;
        $this->saveGroupedReviews = $groupedReviews;

        return $this;
    }

    /**
     * Vyhodnotí ID produktu
     */
    public function resolveId(ProductReview $review): mixed
    {
        if ($this->idResolver === null) {
            return null;
        }

        $str = $review->productName . '|' . $review->productNumber . '|' . $review->productPrice . '|' . $review->productUrl;
        $hash = md5($str);

        if (array_key_exists($hash, $this->idResolverCache)) {
            return $this->idResolverCache[$hash];
        }

        return $this->idResolverCache[$hash] = ($this->idResolver)($review);
    }

    protected function addReviewToSummary(int|string $productId, ProductReview $review): void
    {
        if (!$this->saveSummary) {
            return;
        }

        if (!isset($this->summary[$productId])) {
            $summary = new ProductReviewSummary($productId);
            $this->summary[$productId] = $summary;
        }

        $summary = $this->summary[$productId];

        $summary->reviewCount++;

        if ($review->rating !== null) {
            $summary->ratingCount++;
            $summary->totalStars += $review->rating;
            $summary->averageRating = round($summary->totalStars / $summary->ratingCount, 1);

            if ($summary->bestRating === 0.0 || $summary->bestRating < $review->rating) {
                $summary->bestRating = $review->rating;
            }

            if ($summary->worstRating === 0.0 || $summary->worstRating > $review->rating) {
                $summary->worstRating = $review->rating;
            }
        }

        if ($summary->newestReviewDate === null || $summary->newestReviewDate < $review->date) {
            $summary->newestReviewDate = $review->date;
        }

        if ($summary->oldestReviewDate === null || $summary->oldestReviewDate > $review->date) {
            $summary->oldestReviewDate = $review->date;
        }

        if ($this->saveGroupedReviews) {
            $summary->reviews[] = $review;
        }
    }

    /**
     * Vrátí pole ID všech produktů, které v datech byly.
     *
     * @return list<int|string>
     */
    public function getAllProductIds(): array
    {
        return array_keys($this->summary);
    }

    /**
     * Vrát všechny summary jako pole.
     *
     * @return array<int|string, \Biano\Heureka\ProductReviewSummary>
     */
    public function getAllSummaries(): array
    {
        return $this->summary;
    }

    /**
     * Vrací summary pro konkrétní produkt, null když takový není nalezen.
     */
    public function getSummaryOfProduct(int|string $productId): ?ProductReviewSummary
    {
        return $this->summary[$productId] ?? null;
    }

    /**
     * Vrátí všechny recenze daného produktu jako pole. Prázdné pole, nemá-li žádné recenze.
     *
     * @return list<\Biano\Heureka\ProductReview>
     */
    public function getReviewsOfProduct(int|string $productId): array
    {
        return isset($this->summary[$productId]) ? $this->summary[$productId]->reviews : [];
    }

}
