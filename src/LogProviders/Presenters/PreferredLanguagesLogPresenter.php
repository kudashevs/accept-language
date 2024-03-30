<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Languages\LanguageInterface;

final class PreferredLanguagesLogPresenter implements LogPresenterInterface
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
            return sprintf('Retrieved no preferred languages - the list is empty [%s event].', $this->event);
        }

        return sprintf(
            'Retrieved "%s" preferred languages [%s event].',
            $this->processLanguages($languages),
            $this->event,
        );
    }

    private function isEmptyData(array $data): bool
    {
        return count($data) === 0;
    }

    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (LanguageInterface $lang) {
            return $lang->getTag() . ';q=' . $lang->getQuality();
        }, $languages));
    }
}
