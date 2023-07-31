<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\LogHandlers;

use Kudashevs\AcceptLanguage\Language\Language;
use Psr\Log\LoggerInterface;

class RetrievePreferredLanguagesLogHandler implements LogHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $event, $languages): void
    {
        $this->logger->info(
            sprintf(
                'Retrieved "%s" preferred languages [%s event].',
                $this->processLanguages($languages),
                $event,
            )
        );
    }

    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (Language $lang) {
            return $lang->getTag() . ';q=' . $lang->getQuality();
        }, $languages));
    }
}
