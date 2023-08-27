<?php

declare(strict_types=1);

namespace Kudashevs\AcceptLanguage\LogProviders\Presenters;

use Kudashevs\AcceptLanguage\Language\AbstractLanguage;

final class RetrieveRawLanguagesLogPresenter implements LogPresenterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string $event
     * @param array<AbstractLanguage> $languages
     */
    public function present(string $event, $languages): string
    {
        if ($this->isEmptyData($languages)) {
            return sprintf('Retrieved no raw languages - the list is empty [%s event].', $event);
        }

        return sprintf(
            'Retrieved "%s" raw languages [%s event].',
            $this->processLanguages($languages),
            $event,
        );
    }

    private function isEmptyData(array $data): bool
    {
        return count($data) === 0;
    }

    private function processLanguages(array $languages): string
    {
        return implode(',', array_map(static function (AbstractLanguage $lang) {
            return $lang->getTag() . ';' . ($lang->isValid() ? 'valid' : 'invalid');
        }, $languages));
    }
}
