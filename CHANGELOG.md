# Changelog

All Notable changes to `accept-language` will be documented in this file

## [v1.8.1 - 2021-11-11](https://github.com/kudashevs/accept-language/compare/v1.8.0...v1.8.1)

- Add .gitattributes
- Add Github action with tests
- Remove composer.lock
- Remove Travis-CI
- Some improvements

## [v1.8.0 - 2021-03-08](https://github.com/kudashevs/accept-language/compare/v1.7.0...v1.8.0)

- Add a new two_letter_only option to AcceptLanguage
- Update README.md with the two_letter_only option information
- Update README.md with a more detailed features description

## [v1.7.0 - 2021-03-08](https://github.com/kudashevs/accept-language/compare/v1.6.0...v1.7.0)

- Change LanguageTagNormalizer the normalize method signature back
- Add optional options to the LanguageTagNormalizer class
- Various LanguageTagNormalizer class improvements

## [v1.6.0 - 2021-03-08](https://github.com/kudashevs/accept-language/compare/v1.5.0...v1.6.0)

- Add a new separator option to AcceptLanguage
- Change LanguageTagNormalizer a normalize method signature
- Update README.md with the separator option information

## [v1.5.0 - 2021-03-08](https://github.com/kudashevs/accept-language/compare/v1.4.0...v1.5.0)

- Change return language code format to the CLDR like.
- Update README.md with a more detailed features description.

## [v1.4.0 - 2021-03-02](https://github.com/kudashevs/accept-language/compare/v1.3.0...v1.4.0)

- Change process flow (allows processing wrongly formed values)
- Update validation according to the RFC 4647
- Various readability improvements

## [v1.3.0 - 2021-03-02](https://github.com/kudashevs/accept-language/compare/v1.2.0...v1.3.0)

- Add LanguageTagNormalizer with tests (process complex tags)

## [v1.2.0 - 2021-03-02](https://github.com/kudashevs/accept-language/compare/v1.1.0...v1.2.0)

- Add new getPreferredLanguage method (replaces getLanguage)
- Deprecate getLanguage (will be removed in 2.x)
- Various readability improvements

## [v1.1.0 - 2021-03-02](https://github.com/kudashevs/accept-language/compare/v1.0.1...v1.1.0)

- Remove constant with default language value
- Change the logic of setting options
- Update setOption method visibility for reuse

## [v1.0.1 - 2021-02-21](https://github.com/kudashevs/accept-language/compare/v1.0.0...v1.0.1)

- Update README.md with features and references
- Some internal refactoring

## v1.0.0 - 2021-02-21

- The initial release includes the full support of two-letters language tags