# Accept-Language ![test workflow](https://github.com/kudashevs/accept-language/actions/workflows/run-tests.yml/badge.svg)

This PHP package retrieves a language of preference (a preferred language) from an HTTP Accept-Language request-header field.
The package can be used in any web app to identify the visitors' language of choice. The retrieved information might be used
to make various decisions (for example, set a locale, redirect a user to the specific page, etc.).

## Features

The Accept-Language package retrieves a preferred language from an HTTP Accept-Language request-header field. The preferred
language information comes in the form of a language code that is pretty similar to the Unicode Locale Identifier. The code
consists of a mandatory 2-letter/3-letter primary language subtag and a region subtag separated by an underscore (e.g. en_GB).
Besides, the package provides the possibility to control the presence of different subtags in the resulting code (including
script and extlang subtags) and the possibility to set up the representation of a separator.

- Can return a default language when a client accepts any language (e.g. `Accept-Language: *`)
- Can configure a default returning language by providing the `default_language` option
- Can configure a default separator value by providing the `separator` option
- Can retrieve the two-letter languages only by setting the `two_letter_only` option
- Can restrict the language search to specific values by providing the `accepted_languages` option
- Can retrieve the exact match only languages by setting the `exact_match_only` option

The package goes with the built-in Laravel framework support. For more information see [Laravel usage](#laravel-usage) section.

## Installation

You can install the package via composer:
```bash
composer require kudashevs/accept-language
```

## Usage

The usage is quite simple. Just instantiate the `AcceptLanguage` class and call a `process` method on the instance.
Do it somewhere before a place where you want to get the user's preferred language (for example, in a front controller
or in a middleware). This method will retrieve an HTTP Accept-Language header, parse it, and find valid language tags.
Then it will apply a matching algorithm to the language tags and, finally, it will find and retain a language tag with
the highest priority value (e.g. the preferred language).
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage();
$service->process();
```

Once retrieved, the preferred language can be accessed from any part of your application. Just use one of these two methods:
```php
$service->getPreferredLanguage();   # Returns the user's preferred language
$service->getLanguage();            # Does the same (an alias of the getPreferredLanguage() method)
```

If for some reason you need the original HTTP Accept-Language header, it is available through the `getHeader` method.
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
'log_activity'              # A boolean defines whether to log the activity of the package or not (default if false).
```
<sub>1 - the `default_language` option should contain a valid Language Tag (it will be formatted according to the settings)</sub>  
<sub>2 - the `accepted_languages` option should include valid Language Tags only</sub>  
<sub>3 - the separator can accept any value, however it is recommended to use the [URL Safe Alphabet](https://datatracker.ietf.org/doc/html/rfc4648#section-5).</sub>

### Notes

Some options require additional explanations:

- the `accepted_languages` option should include valid Language Tags. These values may be written in any case (as the standard says)
and may use a separator different from the `separator` option (for example, ['en-GB', 'en-CA'] may be written as ['en_GB', 'en_ca']).
If no accepted languages provided, the resulting language will be equal to the `default_language` value.

**Important note!** the values of the `accepted_languages` option will be formatted according to the settings. Therefore,
if you want to retrieve languages including script subtags you should enable the `use_script_subtag` option.

- the `exact_match_only` option is set to `false` by default. When set to `true`, it restricts the matching algorithm to finding only
the languages that exactly match the languages listed in the `accepted_languages` option. When set to `false`, the matching algorithm
becomes more flexible and retrieves the language and its derivatives.

- the `two_letter_only` option is set to `true` by default. When set to `true`, it orders the instance to retrieve only the languages
with the two-letter primary subtag. This option has a **higher priority** than the `accepted_languages` option. Thus, if you want to
accept languages with three-letter primary subtag (by listing them in the `accepted_languages`), don't forget to disable this option.

## Logging

There is the possibility to log information gathered throughout the execution process. To activate it you should set the configuration
option `log_activity` to `true` and provide an instance of `Psr\Log\LoggerInterface` implementation to the `useLogger` method. 
```php
use \Kudashevs\AcceptLanguage\AcceptLanguage;

$service = new AcceptLanguage([
    'log_activity' => true,
]);
$service->useLogger(new PsrCompatibleLogger());
$service->process();
```

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
'default_language' => 'value'       # Sets the `default_language` option value (default is 'en')
'accepted_languages' => []          # Sets the `accepted_languages` option value (default is [])
'exact_match_only' => bool,         # Sets the `exact_match_only` option value (default is false)
'use_extlang_subtag' => bool,       # Sets the `use_extlang_subtag` option value (default is false)
'use_script_subtag' => bool,        # Sets the `use_script_subtag` option value (default is false)
'use_region_subtag' => bool,        # Sets the `use_region_subtag` option value (default is true)
'log_activity' => bool              # Sets the `log_activity` option value (default is false)
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
- [RFC 4646 Tags for Identifying Languages](https://tools.ietf.org/html/rfc4646#section-2)
- [RFC 4647 Matching of Language Tags](https://tools.ietf.org/html/rfc4647#section-2)

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
