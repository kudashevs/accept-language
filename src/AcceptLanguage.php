<?php

namespace Kudashevs\AcceptLanguage;

class AcceptLanguage
{
    public const DEFAULT_LANGUAGE = 'en';

    /**
     * Contains the found language or the default one.
     *
     * @var string
     */
    private $language = self::DEFAULT_LANGUAGE;

    /**
     * Contains various options if any.
     *
     * @var array
     */
    private $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;

        $this->process();
    }

    /**
     * Processes HTTP Accept-Language information.
     */
    public function process(): void
    {
        $languageInformation = $this->retrieveAcceptLanguage();

        $this->language = $this->parse($languageInformation);
    }

    /**
     * @return string
     */
    private function retrieveAcceptLanguage(): string
    {
        return $this->options['accept_language'] ?? (string)@$_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    /**
     * Parse HTTP Accept-Language string.
     *
     * @param string $languageInformation
     * @return string
     */
    private function parse(string $languageInformation): string
    {
        if (empty($languageInformation)) {
            return $this->resolveEmptyInformation();
        }
    }

    /**
     * @param string $information
     * @return string
     */
    private function resolveEmptyInformation(): string
    {
        return self::DEFAULT_LANGUAGE;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }
}
