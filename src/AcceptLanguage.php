<?php

namespace Kudashevs\AcceptLanguage;

class AcceptLanguage
{
    public const DEFAULT_LANGUAGE = 'en';

    private $language = '';

    public function __construct(string $rawAcceptLanguage = '', array $options = [])
    {
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
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
}
