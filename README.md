# Accept-Language ![test workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves a language of preference (a preferred language) from an HTTP Accept-Language request-header field.
It can be used in any web application to identify the visitors' language of choice. Later this information may be used to
make various decisions (for example, set a locale, redirect a user to the specific page, etc.).

## Features

The Accept-Language package retrieves a preferred language from an HTTP Accept-Language request-header field. It retrieves
the language in the form of a language code. This code is pretty similar to the Unicode Locale Identifier which is widely
used in localization. The code consists of a mandatory 2-letter/3-letter language subtag and a region subtag separated by
an underscore (e.g. en_GB). It is worth noting that the package allows to control the presence of different subtags in
the resulting code (including script and extlang subtags) and the representation of the separator.

- Can return a default language value if a client accepts any language
- Can configure a default returning language by providing the `default_language` option
- Can configure a default separator value by providing the `separator` option
- Can retrieve the two-letter languages only by setting the `two_letter_only` option
- Can restrict the language search to specific values by providing the `accepted_languages` option
- Can retrieve the exact match languages only by setting the `exact_match_only` option

The package goes with the built-in Laravel framework support. For more information see [Laravel usage](#laravel-usage) section.

## Installation

You can install the package via composer:

```bash
composer require kudashevs/accept-language
```

## Usage

The usage is quite simple. Just instantiate the `AcceptLanguage` class and call a `process` method on the instance.
Do it somewhere before a place where you want to use the user's preferred language (for example, in a front controller
or in a middleware). The method will retrieve an HTTP Accept-Language header, parse and analyze all language tags in it.
Then it will retrieve and retain the language tag with the highest priority value (the preferred language).
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();
$service->process();
```

This preferred language value can then be accessed from any part of your application. Just use one of two methods:
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
'http_accept_language'      # A string with a custom HTTP Accept-Language header.
'default_language'          # A string with a default preferred language value (default is 'en')¹.
'accepted_languages'        # An array with a list of supported languages (default is [])².
'exact_match_only'          # A boolean defines whether to retrieve only languages that match exactly a supported language (default is true).
'two_letter_only'           # A boolean defines whether to retrieve only two-letter primary subtags (default is true).
'use_extlang_subtag'        # A boolean defines whether to include an extlang subtag in the result (default is false).
'use_script_subtag'         # A boolean defines whether to include a script subtag in the result (default is false).
'use_region_subtag'         # A boolean defines whether to include a region subtag in the result (default is true).
'separator'                 # A string with a character that will be used as a separator in the result (default is '_')³.
```
<sub><sup>1 - the default language should be a valid Language Tag (it will be formatted according to the settings)</sup></sub>  
<sub><sup>2 - the value of the `accepted_languages` option should include only valid Language Tags</sup></sub>  
<sub><sup>3 - the separator can accept any value, however it is recommended to use the [URL Safe Alphabet](https://datatracker.ietf.org/doc/html/rfc4648#section-5).</sup></sub>

### Notes

Some options require additional explanations:

- the `accepted_languages` option should contain only string values that represent valid Language Tags. However,
these values may be written in any case (as standard says) and may include a separator value from the `separator` option
(for example, the values ['en-GB', 'en-CA'] may be written as ['en_GB', 'en_ca'] if the `separator` value is an underscore).
If no values are given the package returns the found languages in their natural order (returns the first language found).

**Important note!** the values of the `accepted_languages` option will be formatted according to the settings. Therefore,
if you want to find languages including script subtags you should enable the `use_script_subtag` option.

- the `exact_match_only` option is set to `false` by default. When set to `true`, the option restricts the searching
algorithm to find only languages that exactly match the languages listed in the `accepted_languages` option. When
set to `false`, the finding algorithm becomes more flexible and retrieves the language and its derivatives (default behavior).

## Usage example

Let's consider that we have a web application that uses three different languages: American, British, and Canadian English.
We want to redirect users according to their HTTP Accept-Language header settings to specific sections: en_US, en_GB, en_CA.
All routes are set correctly, and we just want to retrieve the preferred language, if user has any, to redirect them.

To work properly in this case, the package requires us to provide two initial options:
`default_language` let's give it the value `en_US`
`accepted_languages` let's give it the value `['en_US', 'en_GB', 'en_CA']`

```php
$service = new AcceptLanguage([
    'default_language' => 'en_US',
    'accepted_languages' => ['en_US', 'en_GB', 'en_CA'],
]);
$service->process();
```

By doing that, we instruct the package to retrieve only the values that are listed in the `accepted_languages` option.
If one of the language tags in a user HTTP Accept-Language header matches any of these values, it will be retained for
the further processing. If none of them matches the listed values, the default language will be returned.

## Laravel usage

If you don't use auto-discovery just add a ServiceProvider to the `config/app.php` file.
```php
'providers' => [
    Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider::class,
];
```

Once added, the `AcceptLanguageServiceProvider` will instantiate the `AcceptLanguage` class, call the `process` method,
and put the instance into the Laravel container. To get a preferred language just refer to the object in the container
(through a dependency injection mechanism or by using an alias `app('acceptlanguage')`).

If you want to add a Laravel Facade just add it to the aliases array in the `config/app.php` file.
```php
'aliases' => [
    'AcceptLanguage' => Kudashevs\AcceptLanguage\Facades\AcceptLanguage::class,
];
```

The service provider by default uses two Laravel options (`app.locale` and `app.accepted_locales`). These options
correspond respectively to the `default_language` and `accepted_languages` package options.

## Testing

If you want to make sure that everything works as expected, just run the unit tests provided with the package.
```bash
composer test
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
