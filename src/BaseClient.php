<?php

declare(strict_types = 1);

namespace Biano\Heureka;

use DateTimeImmutable;
use RuntimeException;
use SimpleXMLElement;
use XMLReader;
use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function fclose;
use function file_exists;
use function fopen;
use function is_readable;
use function is_string;
use function simplexml_load_string;
use function unlink;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_FILE;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;

/**
 * Společný základ pro obě třídy *Client
 *
 * @template T
 */
abstract class BaseClient
{

    protected ?string $sourceAddress = null;

    protected ?string $tempFile = null;

    protected bool $downloadFinished = false;

    protected bool $deleteTempFileAfterParsing = true;

    /**
     * @var (callable(T): void)|null
     */
    protected $callback = null;

    protected ?string $xml = null;

    abstract public function setKey(string $key, ?DateTimeImmutable $from = null): void;

    /**
     * Konstruktor umožňuje rovnou nastavit klíč nebo adresu.
     */
    public function __construct(?string $key = null)
    {
        if ($key !== null) {
            $this->setKey($key);
        }
    }

    /**
     * Adresa, z níž se má stahovat XML feed s recenzemi.
     */
    public function getSourceAddress(): ?string
    {
        return $this->sourceAddress;
    }

    /**
     * Adresa, z níž se má stahovat XML feed s recenzemi.
     *
     * @return $this
     */
    public function setSourceAddress(?string $sourceAddress): static
    {
        $this->sourceAddress = $sourceAddress;

        return $this;
    }

    /**
     * Dočasný soubor
     */
    public function getTempFile(): ?string
    {
        return $this->tempFile;
    }

    /**
     * Má se dočasný soubor po parsování automaticky vymazat?
     */
    public function getDeleteTempFileAfterParsing(): bool
    {
        return $this->deleteTempFileAfterParsing;
    }

    /**
     * Nastaví dočasný soubor, kam se celý XML feed stáhne.
     * Použití dočasného souboru redukuje nároky na paměť.
     *
     * @param bool $deleteAfterParsing Smazat dočasný soubor automaticky?
     *
     * @return $this
     */
    public function setTempFile(?string $tempFile, bool $deleteAfterParsing = true): static
    {
        $this->tempFile = $tempFile;
        $this->deleteTempFileAfterParsing = $deleteAfterParsing;
        $this->downloadFinished = false;

        return $this;
    }

    /**
     * @return (callable(T): void)|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * Nastavení callbacku, který se spustí pro každou recenzi.
     *
     * @param callable(T): void $callback
     *
     * @return $this
     */
    public function setCallback(callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    abstract public function getNodeName(): string;

    /**
     * @return T
     */
    abstract protected function processElement(SimpleXMLElement $element, int $index): mixed;

    protected function downloadFile(): void
    {
        if ($this->sourceAddress === null) {
            throw new RuntimeException('Source address has not been set, can not download file.');
        }

        $c = curl_init($this->sourceAddress);

        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_TIMEOUT, 30);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);

        if ($this->tempFile !== null) {
            $fh = fopen($this->tempFile, 'w');
            if ($fh === false) {
                throw new RuntimeException('Temporary file "' . $this->tempFile . '" could not be open for writing.');
            }

            curl_setopt($c, CURLOPT_FILE, $fh);

            $downloadSuccess = curl_exec($c);
            if ($downloadSuccess === false) {
                throw new RuntimeException('File could not be downloaded from "' . $this->sourceAddress . '"');
            }

            $this->downloadFinished = true;
            curl_close($c);
            fclose($fh);
        } else {
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            $xml = curl_exec($c);

            if (!is_string($xml)) {
                throw new RuntimeException('File could not be downloaded from "' . $this->sourceAddress . '"');
            }

            $this->xml = $xml;

            $this->downloadFinished = true;
            curl_close($c);
        }
    }

    protected function processFile(): void
    {
        if (!$this->downloadFinished) {
            throw new RuntimeException('File has not been downloaded yet.');
        }

        $mainNodeName = $this->getNodeName();

        if ($this->tempFile !== null) {
            $xmlReader = XMLReader::open($this->tempFile);
        } else {
            $xmlReader = XMLReader::XML((string) $this->xml);
        }

        if (!$xmlReader instanceof XMLReader) {
            throw new RuntimeException('Error opening file.');
        }

        $elementIndex = 0;

        while (true) {
            $remainsAnything = $xmlReader->read();
            if (!$remainsAnything) {
                break;
            }

            $nodeName = $xmlReader->name;
            $nodeType = $xmlReader->nodeType;

            if ($nodeType !== XMLReader::ELEMENT || $nodeName !== $mainNodeName) {
                continue;
            }

            $nodeAsString = $xmlReader->readOuterXml();
            if ($nodeAsString === '') {
                continue;
            }

            $simpleXmlNode = simplexml_load_string($nodeAsString);
            if ($simpleXmlNode === false) {
                continue;
            }

            $review = $this->processElement($simpleXmlNode, $elementIndex);

            if ($this->callback !== null) {
                ($this->callback)($review);
            }

            $elementIndex++;
        }
    }

    private function deleteTempFileIfNeeded(): void
    {
        if ($this->tempFile !== null && file_exists($this->tempFile) && $this->deleteTempFileAfterParsing) {
            $deleted = unlink($this->tempFile);
            if (!$deleted) {
                throw new RuntimeException('Could not clean up the temp file "' . $this->tempFile . '" - unlink() failed');
            }
        }
    }

    /**
     * Umožní použít vlastní soubor (již stažený dříve) a ne ten z Heuréky.
     */
    public function useFile(string $filename): void
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new RuntimeException('File "' . $filename . '" is not readable.');
        }

        $this->tempFile = $filename;
        $this->downloadFinished = true;
        $this->deleteTempFileAfterParsing = false;
    }

    /**
     * Spustit import!
     */
    public function run(): void
    {
        if (!$this->downloadFinished) {
            $this->downloadFile();
        }

        $this->processFile();
        $this->deleteTempFileIfNeeded();
    }

    /**
     * Stáhnout soubor, ale dál ho nezpracovávat.
     *
     * @param string|null $file Kam se má stáhnout? Null = použít nastavený dočasný soubor z setTempFile().
     */
    public function download(?string $file = null): void
    {
        if ($file !== null) {
            $this->setTempFile($file);
        }

        $this->downloadFile();
    }

}
