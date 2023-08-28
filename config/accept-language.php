<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default language
    |--------------------------------------------------------------------------
    |
    | If an HTTP Accept-Language header value doesn't contain any valid language
    | or a client accepts any language, this default value is returned. Another
    | use case of the default language is when the header value doesn't contain
    | any of the languages listed in the `accepted_languages` option.
    |
    */

    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Accepted languages
    |--------------------------------------------------------------------------
    |
    | This option contains a list of accepted languages. The package will match
    | languages found in a header to the provided list. If no accepted languages
    | provided, the first valid language from the header will be retrieved.
    |
    */

    'accepted_languages' => [],

    /*
     |--------------------------------------------------------------------------
     | Matching strategy
     |--------------------------------------------------------------------------
     |
     | It defines the matching strategy. When set to `true`, it will restrict the
     | strategy to find only the languages that exactly match the languages listed
     | in the `accepted_languages` option's value.
     |
     | For more information, refer to the Notes section in README.md.
     |
     */

    'exact_match_only' => false,

    /*
     |--------------------------------------------------------------------------
     | Two letter only
     |--------------------------------------------------------------------------
     |
     | This option defines whether to retrieve only languages with the two-letter
     | primary subtags or not. When set to `true`, the package will retrieve only
     | the languages with the two-letter primary subtag.
     |
     | For more information, refer to the Notes section in README.md.
     |
     */

    'two_letter_only' => true,

    /*
     |--------------------------------------------------------------------------
     | Use different subtags
     |--------------------------------------------------------------------------
     |
     | These options define which of the language subtags should be included in
     | or excluded from a resulting preferred language. By default, it includes
     | only the region subtag to be similar to the Unicode Locale Identifier.
     |
     */

    'use_extlang_subtag' => false,
    'use_script_subtag' => false,
    'use_region_subtag' => true,

    /*
    |--------------------------------------------------------------------------
    | Separator
    |--------------------------------------------------------------------------
    |
    | This option defines a language tag separator. The separator is a character
    | that is used in a language tag to distinguish and separate its parts (also
    | known as subtags).
    |
    | For more information, refer to the Notes section in README.md.
    |
    */

    'separator' => '_',

    /*
    |--------------------------------------------------------------------------
    | Log activity
    |--------------------------------------------------------------------------
    |
    | This option defines whether to log the activity of the package or not. If
    | set to `true`, it will log the information gathered throughout different
    | stages of the execution process.
    |
    | For more information, refer to the Logging section in README.md.
    |
    */

    'log_activity' => false,

    /*
    |--------------------------------------------------------------------------
    | Log level
    |--------------------------------------------------------------------------
    |
    | This option defines the log level at which the gathered information will
    | be logged. The package uses the PSR-3 compatible log levels. These levels
    | are: emergency, alert, critical, error, warning, notice, info, debug.
    |
    */

    'log_level' => 'info',

];
