# Accept-Language ![test workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves a preferred language from the HTTP Accept-Language request-header field. It can be used with
any web app to identify the visitors' language of preference. The retrieved language might be used to make various decisions
(e.g. set a locale, redirect a user to the specific page, etc.).


## Features

By default, the preferred language comes in a format pretty similar to the Unicode Locale Identifier. It consists of
a mandatory 2-/3-letter primary language subtag and a region subtag separated with an underscore (e.g., `en_GB`). The
format of the language and the set of included subtags are customizable and can be changed by using various options.

Main package features:
- it can use the default language value set by the `default_language` option
- it can return a default language value when a client accepts any language (e.g., `Accept-Language: *`)
- it can retrieve only the languages that are listed in the `accepted_languages` option and their derivatives
- it can retrieve only the languages that match exactly the `accepted_languages` by setting the `exact_match_only` option
- it can retrieve only the two-letter language codes by setting the `two_letter_only` option
- it can include extlang, script, and region subtags by setting the `use_<subtag-name>_subtag` options
- it can set the default separator value by providing the `separator` option
- it can log its activity for further examination by setting the `log_activity` option
- it can log its activity at the specific log level by providing the `log_level` option
- it can log only the events that are listed in the `log_only` option

The package goes with the built-in Laravel framework support. For more information see [Laravel usage](#laravel-usage) section.


## Installation

You can install the package via composer:
```bash
composer require kudashevs/accept-language
```


## Usage

To retrieve a preferred language you need to instantiate the `AcceptLanguage` class and call a `process` method on the
instance. It is best to do it somewhere before the place where you want the user's preferred language (for example,
in a front controller or in a middleware). If you don't call the `process` method, the values will remain empty.
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();
$service->process();
```

**Note!** The `AcceptLanguage` class at the moment of creation can throw a few exceptions: `InvalidOptionType`,
`InvalidOptionValue`, `InvalidLogEventName`, `InvalidLogLevelName`. All of these exceptions extend a common built-in
`InvalidArgumentException` class, so they are easy to deal with.

Once obtained, the preferred language value can be accessed in any part of your application by using one of these methods:
```php
$service->getPreferredLanguage();   # Returns the user's preferred language
$service->getLanguage();            # An alias of the getPreferredLanguage()
```

If you need the original HTTP Accept-Language header, it is available via the `getHeader` method.
```php
$service->getHeader();
```


## Options

The class accepts some configuration options:
```
'http_accept_language'      # A string with a custom HTTP Accept-Language header.
'default_language'          # A string with a default preferred language value (default is 'en')¹.
'accepted_languages'        # An array with a list of accepted languages (default is [])².
'exact_match_only'          # A boolean defines whether to retrieve only languages that match exactly a supported languages (default is false).
'two_letter_only'           # A boolean defines whether to retrieve only two-letter primary subtags (default is true).
'use_extlang_subtag'        # A boolean defines whether to include an extlang subtag in the result (default is false).
'use_script_subtag'         # A boolean defines whether to include a script subtag in the result (default is false).
'use_region_subtag'         # A boolean defines whether to include a region subtag in the result (default is true).
'separator'                 # A string with a character that will be used as a separator in the result (default is '_')³.
'log_activity'              # A boolean defines whether to log the activity of the package or not (default if false).
'log_level'                 # A string with a PSR-3 compatible log level (default is 'info').
'log_only'                  # An array with a list of log events to log (default is []).
```
<sub>1 - the `default_language` option should contain a valid Language Tag (it will be formatted according to the settings)</sub>  
<sub>2 - the `accepted_languages` option should include valid Language Tags only (the primary subtags are limited to 2-/3-letters for now)</sub>  
<sub>3 - the separator can accept any string value, however it is recommended to use the [URL Safe Alphabet](https://datatracker.ietf.org/doc/html/rfc4648#section-5).</sub>

### Notes

Some options require additional explanations:

- the `default_language` option should contain a valid Language Tag. This default value may be written in any case (as the standard says).
Different separators may be used too (for example, ['en-GB', 'en-CA'] may be written as ['en_GB', 'en_ca']). 

**Important note!** the package supports the `-` and `_` separators by default. If you want to use any other separator, use the `separator` option. 

- the `accepted_languages` option should include valid Language Tags only. These values may be written in any case (as the standard says).
Different separators may be used too (for example, ['en-GB', 'en-CA'] may be written as ['en_GB', 'en_ca']). If the `accepted_languages`
is empty, the package will retrieve a return the first valid language from an HTTP Accept-Language header as a preferred language. 

**Important note!** the values of the `accepted_languages` option will be formatted according to the settings. Therefore,
if you want to retrieve languages including script subtags you should enable the `use_script_subtag` option.

- the `exact_match_only` option instructs the matching algorithm to retrieve only the languages that exactly match the languages listed
in the `accepted_languages` option. By default, the matching algorithm is more flexible and retrieves a language and its derivatives.

- the `two_letter_only` option is set to `true` by default. When set to `true`, it orders the instance to retrieve only the languages
with the two-letter primary subtag. This option has a **higher priority** than the `accepted_languages` option. Thus, if you want to
accept languages with three-letter primary subtag (by listing them in the `accepted_languages`), don't forget to disable this option.


## Logging

There is the possibility to log information gathered throughout the execution process. To start logging set the configuration
option `log_activity` to `true` and provide an instance of `Psr\Log\LoggerInterface` implementation through the `useLogger` method.
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage([
    'log_activity' => true,
]);
$service->useLogger(new PsrCompatibleLogger());
$service->process();
```

### Log events

To distinguish the stages of the execution process the package introduces the **log events**. If you want to log only specific
events, please add these events to the `log_only` option. If the `log_only` set to empty, the package logs all known events.

- `retrieve_header` occurs after retrieving an HTTP Accept-Language header. It logs a raw Accept-Language header value.
- `retrieve_default_language` occurs when it returns the default language without further processing (the default language case).
- `retrieve_raw_languages` occurs after retrieving raw languages from the header value. It logs the raw languages and their correctness.
- `retrieve_normalized_languages` occurs after applying the normalization process to the raw languages. It logs the normalized languages.
- `retrieve_preferred_languages` occurs after applying the matching algorithm to the normalized languages. It logs the found preferred languages.
- `retrieve_preferred_language` occurs after the preferred language was or was not found. It logs the preferred language.


## Usage example

Let's imaging that we have a web application that uses three different languages: American, British, and Canadian English.
We want to redirect users according to their HTTP Accept-Language header settings to specific sections: en_US, en_GB, en_CA.
All routes are set correctly, and we just want to retrieve the preferred language, if user has any, to redirect them.

To work properly, the package requires us to provide two initial options:
`default_language` let's give it the value `en_US`
`accepted_languages` let's give it the value `['en_US', 'en_GB', 'en_CA']`

```php
$service = new AcceptLanguage([
    'default_language' => 'en_US',
    'accepted_languages' => ['en_US', 'en_GB', 'en_CA'],
]);
$service->process();
```

These options instruct the package to retrieve only the values that are listed in the `accepted_languages` option.
If one of the language tags in an HTTP Accept-Language header matches any of these values, it will be retained for
the further processing. If none of them matches the listed values, the default language will be returned.

## Laravel integration

If you don't use auto-discovery just add a ServiceProvider to the `config/app.php` file.
```php
'providers' => [
    Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider::class,
];
```

Once added, the `AcceptLanguageServiceProvider` will instantiate the `AcceptLanguage` class, apply some initial configuration
settings, and call the `process` method. After finishing the setup process, it binds the instance into the Laravel service container.
Thus, the instance becomes accessible through a dependency injection mechanism or an alias (e.g `app('acceptlanguage')`).

If you want to add a Laravel Facade just add it to the aliases array in the `config/app.php` file.
```php
'aliases' => [
    'AcceptLanguage' => Kudashevs\AcceptLanguage\Facades\AcceptLanguage::class,
];
```

All of the available configuration settings are located in the `config/accept-language.php` file.
```
'default_language' => 'string'      # Set the `default_language` option value (default is `en`)
'accepted_languages' => []          # Set the `accepted_languages` option value (default is [])
'exact_match_only' => bool,         # Set the `exact_match_only` option value (default is `false`)
'use_extlang_subtag' => bool,       # Set the `use_extlang_subtag` option value (default is `false`)
'use_script_subtag' => bool,        # Set the `use_script_subtag` option value (default is `false`)
'use_region_subtag' => bool,        # Set the `use_region_subtag` option value (default is `true`)
'log_activity' => bool              # Set the `log_activity` option value (default is `false`)
```
<sub>for more information about different options, please refer to the [Options](#options) section</sub>

If you want to change the defaults, don't forget to publish the configuration file.
```bash
php artisan vendor:publish --provider="Kudashevs\AcceptLanguage\Providers\AcceptLanguageServiceProvider"
```


## Testing

If you want to make sure that everything works as expected, you can run unit tests provided with the package.
```bash
composer test
```


## References

- [RFC 7231 Hypertext Transfer Protocol (HTTP/1.1)](https://tools.ietf.org/html/rfc7231#section-5.3.5)
- [RFC 5646 Tags for Identifying Languages](https://tools.ietf.org/html/rfc5646#section-2)
- [RFC 4647 Matching of Language Tags](https://tools.ietf.org/html/rfc4647#section-2)


## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

The MIT License (MIT). Please see the [License file](LICENSE.md) for more information.