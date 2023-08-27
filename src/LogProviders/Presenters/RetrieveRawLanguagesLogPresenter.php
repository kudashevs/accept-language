<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\Language;
use Psr\Log\LoggerInterface;

class RetrieveRawLanguagesLogPresenter implements LogPresenterInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function present(string $event, $languages): void
    {
        $this->logger->info(
            sprintf(
                'Retrieved "%s" raw languages [%s event].',
                $this->processLanguages($languages),
                $event,
            )
        );
    }

    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (Language $lang) {
            return $lang->getTag() . ';' . ($lang->isValid() ? 'valid' : 'invalid');
        }, $languages));
    }
}
