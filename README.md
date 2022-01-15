# HTTP Accept-Language ![test workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves the preferred language from an HTTP Accept-Language request-header field. The package can
be used to identify the user's language of choice on their first site visit. Later this information might be used
to make various decisions (set locale, redirect the user to the specific page or section, etc.).

## Features

The HTTP Accept-Language package retrieves a language code with the highest priority (the highest language associated
quality value) from an HTTP Accept-Language request-header field. The language code may consist of a 2-letter/3-letter
primary language subtag, an optional script subtag, and an optional region subtag. Usually, the language code consists
of a 2-letter primary subtag and an optional region subtag separated by the underscore (e.g. en_GB). This format is very 
similar to the CLDR format (overlaps with the ISO 15897), and it is used by the majority of frameworks in localization.  

- Can return the default language value if a client accepts any language 
- Can override the default value with the `default_language` option
- Can retrieve the three-letter language code with the `two_letter_only` option
- Can override the default separator with the `separator` option
- Can restrict the search by values in the `accepted_languages` option

The package goes with the built-in Laravel framework support.

## Installation

You can install the package via composer:

```bash
composer require kudashevs/accept-language
```

## Usage

The usage is quite simple. Just instantiate the class somewhere before you need the preferred user language (personally,
I do it in a middleware). The object will analyze the HTTP Accept-Language request-header field and retain a result.
Therefore, you can get the preferred user language in any place of your application. 

```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();

[...]

// returns the user's preferred language
$service->getPreferredLanguage();
// or the shorter method which does the same
$service->getLanguage();
```

In case, if the HTTP Accept-Language request-header field doesn't contain any of the accepted languages (see options),
or if something went wrong, the default language will be returned. Another remark is that the class throws
`InvalidOptionArgumentException` in case some of the given options were of the incorrect type.

## Options

The class accepts some options which help you to control the result:

```
'http_accept_language'      # A string with custom a HTTP Accept-Language header.
'default_language'          # A string with a default preferred language value (default is 'en').
'accepted_languages'        # An array with a list of supported languages (default is []).
'two_letter_only'           # A boolean defines whether to use only the two-letter codes or not (default is true).
'separator'                 # A string with a character that will be used as the separator in the result (default is '_').
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
