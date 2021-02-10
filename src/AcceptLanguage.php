<?php

namespace Kudashevs\AcceptLanguage;

class AcceptLanguage
{
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * @var string Contains raw Accept-Language string.
     */
    private $raw;

    /**
     * @var string Contains the found language.
     */
    private $language = '';

    public function __construct(string $rawAcceptLanguage = '', array $options = [])
    {
        $this->raw = $rawAcceptLanguage;

        $this->setLanguage($rawAcceptLanguage);
    }

    /**
     * @param string $rawAcceptLanguage
     */
    private function setLanguage(string $rawAcceptLanguage): void
    {
        $this->language = $this->parse($rawAcceptLanguage);
    }

    /**
     * Parse HTTP Accept-Language string.
     *
     * @param string $rawAcceptLanguage
     */
    private function parse(string $rawAcceptLanguage): string
    {
        if ($rawAcceptLanguage === '*') {
            return self::DEFAULT_LANGUAGE;
        }

        return $rawAcceptLanguage;
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
}
