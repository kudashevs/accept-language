# HTTP Accept-Language ![example workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves the preferred language from an HTTP Accept-Language request-header field. The package can
be used to identify the user's language of choice on their first site visit. Later this information might be used
to make various decisions (set locale, redirect the user to the specific page or section, etc.).

## Features

The HTTP Accept-Language package retrieves a language code with the highest priority (the highest language associated
quality value) from an HTTP Accept-Language request-header field. The language code may consist of a 2-letter/3-letter
primary language subtag, an optional script subtag, and an optional region subtag. Usually the language code consists
of a 2-letter primary subtag and an optional region subtag separated by the underscore (e.g. en_GB). This format is very 
similar to the CLDR format (overlaps with the ISO 15897), and it is used by the majority of frameworks in localization.  

- Can return the default language value if a client accepts any language 
- Can override the default value with the `default_language` option
- Can retrieve the three-letter language code with the `two_letter_only` option
- Can override the default separator with the `separator` option
- Can restrict the search by values in the `accepted_languages` option

At the moment, the package goes with Laravel framework support (it includes a service provider and a facade).

## Installation

You can install the package via composer:

```bash
composer require kudashevs/accept-language
```

## Usage

General usage of the package: 

```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();
$language = $service->getPreferredLanguage();
```

## References

- [RFC 7231 Hypertext Transfer Protocol (HTTP/1.1)](https://tools.ietf.org/html/rfc7231#section-5.3.5)
- [RFC 4646 Tags for Identifying Languages](https://tools.ietf.org/html/rfc4646#section-2)  
- [RFC 4647 Matching of Language Tags](https://tools.ietf.org/html/rfc4647#section-2)

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
