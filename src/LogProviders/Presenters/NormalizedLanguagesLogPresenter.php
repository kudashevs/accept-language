<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

final class NormalizedLanguagesLogPresenter implements LogPresenterInterface
{
    private string $event;

    public function __construct(string $event)
    {
        $this->event = $event;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<LanguageInterface> $languages
     */
    public function present($languages): string
    {
        if ($this->isEmptyData($languages)) {
            return sprintf('Retrieved no normalized languages - the list is empty [%s event].', $this->event);
        }

        return sprintf(
            'Retrieved "%s" normalized languages [%s event].',
            $this->processLanguages($languages),
            $this->event,
        );
    }

    /**
     * @param array<LanguageInterface> $data
     */
    private function isEmptyData(array $data): bool
    {
        return count($data) === 0;
    }

    /**
     * @param array<LanguageInterface> $languages
     */
    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (LanguageInterface $lang) {
            return $lang->getTag() . ';q=' . $lang->getQuality();
        }, $languages));
    }
}
