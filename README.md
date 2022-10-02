# Accept-Language ![test workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves a language of preference (preferred language) from an HTTP Accept-Language request-header field.
It can be used in any web application to identify the visitor's language of choice. Later this information might be used
to make various decisions (set locale, redirect the user to the specific page, etc.).

## Features

The Accept-Language package retrieves a language code with the highest priority (the highest language associated
quality value) from an HTTP Accept-Language request-header field. The language code may consist of a 2-letter/3-letter
primary language subtag, an optional script subtag, and an optional region subtag. Usually, the language code consists
of a 2-letter primary subtag and an optional region subtag separated by the underscore (e.g. en_GB). This format is very 
similar to the CLDR format (overlaps with the ISO 15897), and it is used by the majority of frameworks in localization.  

- Can return the default language value if a client accepts any language
- Can override the predefined default value by providing the `default_language` option
- Can retrieve the two-letter language code only by setting the `two_letter_only` option
- Can override the default separator value by providing the `separator` option
- Can restrict the language search by providing the `accepted_languages` option

The package goes with the built-in Laravel framework support.

## Installation

You can install the package via composer:

```bash
composer require kudashevs/accept-language
```

## Usage

The usage is quite simple. Just instantiate the `AcceptLanguage` class and call a `process` method on the instance.
Do it somewhere before a place where you need the user's preferred language (for example, it can be done in a middleware).
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();
$service->process();
```

The instance will analyze an HTTP Accept-Language request-header field, retrieve the language of preference, and retain it.
Then you can get the preferred language in any place of your application. In order to do so, use one of two methods:
```php
// returns the user's preferred language
$service->getPreferredLanguage();
// a shorter method which does the same
$service->getLanguage();
```

If for some reason you need the original HTTP Accept-Language header, it is available through `getHeader` method.
```php
$service->getHeader();
```

In case, if the HTTP Accept-Language request-header field doesn't contain any of the accepted languages (see options),
or if something went wrong, a default language will be returned. The class can throw an `InvalidOptionArgumentException`
in case when any of the given options were of the incorrect type.

## Options

The class accepts some options which help you to control the result:

```
'http_accept_language'      # A string with custom a HTTP Accept-Language header.
'default_language'          # A string with a default preferred language value (default is 'en').
'accepted_languages'        # An array with a list of supported languages (default is []).
'two_letter_only'           # A boolean defines whether to use the two-letter codes only (default is true).
'separator'                 # A string with a character that will be used as the separator in the result (default is '_').
```

## Laravel usage

If you don't use auto-discovery just add a ServiceProvider to the config/app.php

```php
'providers' => [
    Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider::class,
];
```

Once added, the `AcceptLanguageServiceProvider` will instantiate the `AcceptLanguage` class and keep its instance in
the container. To get the preferred language just access the object in the container through a dependency injection
or directly by alias (e.g. `app('acceptlanguage')->getLanguage();`).

If you want to add a Laravel Facade just add it to the aliases array in the config/app.php

```php
'aliases' => [
    'AcceptLanguage' => Kudashevs\AcceptLanguage\Facades\AcceptLanguage::class,
];
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
