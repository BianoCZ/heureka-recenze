<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use GuzzleHttp\Psr7\StreamWrapper;
use SimpleXMLElement;
use XMLReader;
use function simplexml_load_string;

/**
 * @template TSource of \Biano\Heureka\Source
 * @template TReview of \Biano\Heureka\Review
 */
abstract readonly class BaseClient
{

    abstract protected function getNodeName(): string;

    /** @return TReview */
    abstract protected function processElement(
        SimpleXMLElement $element,
        int $index,
    ): mixed;

    /**
     * @param TSource $source
     * @param callable(TReview): void $callback
     */
    final public function run(
        Source $source,
        callable $callback,
    ): void {
        $reader = XMLReader::fromStream(StreamWrapper::getResource($source->stream));

        $mainNodeName = $this->getNodeName();
        $elementIndex = 0;

        while (true) {
            if (!$reader->read()) {
                break;
            }

            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->name !== $mainNodeName) {
                continue;
            }

            $nodeAsString = $reader->readOuterXml();
            if ($nodeAsString === '') {
                continue;
            }

            $simpleXmlNode = simplexml_load_string($nodeAsString);
            if ($simpleXmlNode === false) {
                continue;
            }

            $callback($this->processElement($simpleXmlNode, $elementIndex));

            $elementIndex++;
        }
    }

}
