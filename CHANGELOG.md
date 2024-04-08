# Changelog

All Notable changes to `accept-language` will be documented in this file

## [v3.4.0 - 2024-04-08](https://github.com/kudashevs/accept-language/compare/v3.3.0...v3.4.0)

- Increase the minimum supported `orchestra/testbench` version
- Add a `phpstan/phpstan` dev dependency
- Add a Github Action with PHPStan analysis tool
- Add a new `retrieve_accepted_languages` log event
- Add a validation for the `default_language` option
- Add a new `InvalidOptionValue` exception for this validation
- Change the project folders structure
- Change Normalizers simplify the usage
- Fix `DefaultLanguage` wrong fallback option name
- Update PHPDoc types
- Update README.md
- Some improvements

Note: this release changes the default behavior for the situation when the `process` method has not been called.
Now the public methods will return empty values rather than throwing an error.

## [v3.3.0 - 2024-03-17](https://github.com/kudashevs/accept-language/compare/v3.2.0...v3.3.0)

- Add support for PHP 8.3
- Add support for PHPUnit 11
- Update README.md
- Some improvements

## [v3.2.0 - 2023-08-28](https://github.com/kudashevs/accept-language/compare/v3.1.1...v3.2.0)

- Fix an issue with boolean configuration values in the Laravel service provider
- Add a fallback language for the `default_language` option
- Add new log events to make logging more convenient
- Add a new LogPresenterInterface abstraction
- Add a `log_level` option and related functionality
- Add a `log_only` option and related functionality
- Update the Laravel configuration file
- Update some test suites to cover more test cases
- Update composer.json bump the PHPUnit version
- Update README.md
- Some improvements

Note: this release adds some new options, please update your Laravel config file (optional).

## [v3.1.1 - 2023-04-29](https://github.com/kudashevs/accept-language/compare/v3.1.0...v3.1.1)

- Fix the default log option type in the Laravel service provider
- Some insignificant improvements

## [v3.1.0 - 2023-04-07](https://github.com/kudashevs/accept-language/compare/v3.0.1...v3.1.0)

- Add logging functionality
- Add a new LogProvider abstraction
- Add a new `useLogger` public method
- Add a Laravel configuration file
- Add support for Laravel 10
- Add a `psr/log` dependency
- Update the Laravel service provider
- Massive update of README.md
- Some improvements

## [v3.0.1 - 2022-12-18](https://github.com/kudashevs/accept-language/compare/v3.0.0...v3.0.1)

- Fix the `AcceptLanguage` Laravel auto-discovery alias
- Add acceptance tests
- Refactor some abstractions
- Refactor the structure of tests
- Some improvements 

## [v3.0.0 - 2022-11-11](https://github.com/kudashevs/accept-language/compare/v2.0.0...v3.0.0)

- Increase the minimum supported PHP version to 7.4
- Add support for PHP 8.2 version
- Add new `process` and `getHeader` public methods
- Add new `use_extlang_subtag`, `use_script_subtag`, `use_region_subtag` options
- Add a new `exact_match_only` option for the restrictive matching
- Add support for variant, extension, and private-use subtags
- Update the `accepted_languages` option to accept different separators
- Update the matching algorithm to retrieve language tag derivatives
- Update the Laravel service provider
- Update README.md
- Massive refactoring

## [v2.0.0 - 2022-03-11](https://github.com/kudashevs/accept-language/compare/v1.9.0...v2.0.0)

- Increase the minimum PHP version to 7.3
- Refactor AcceptLanguageServiceProvider
- Update README.md 
- Some improvements

## [v1.9.0 - 2022-01-15](https://github.com/kudashevs/accept-language/compare/v1.8.2...v1.9.0)

- Add a TagNormalizer abstraction
- Add a separator option to the LanguageTagNormalizer
- Update README.md usage and options sections
- Massive refactoring

## [v1.8.2 - 2022-01-14](https://github.com/kudashevs/accept-language/compare/v1.8.1...v1.8.2)

- Fix a bug in the parseHeaderValue method
- Add CHANGELOG.md file
- Update .gitattributes with new exclusions
- Some improvements

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