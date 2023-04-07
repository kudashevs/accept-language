<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default language
    |--------------------------------------------------------------------------
    |
    | If an HTTP Accept-Language header value doesn't contain any of languages
    | that match the languages listed in the `accepted_languages` option, this
    | default value will be returned.
    |
    */

    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Accepted languages
    |--------------------------------------------------------------------------
    |
    | This option contains a list of supported languages. It should include only
    | valid Language Tags. If no accepted languages are provided, the resulting
    | language will be equal to the `default_language` value.
    |
    */

    'accepted_languages' => [],

    /*
     |--------------------------------------------------------------------------
     | Matching strategy
     |--------------------------------------------------------------------------
     |
     | It defines the matching strategy. When set to `true`, it is restricted to
     | finding only the languages that exactly match the languages listed in the
     | `accepted_languages`. For more information, refer to the Notes section.
     |
     */

    'exact_match_only' => false,

    /*
     |--------------------------------------------------------------------------
     | Two letter only
     |--------------------------------------------------------------------------
     |
     | This option defines whether to retrieve two-letter primary subtags only or
     | not. When set to `true`, it will retrieve only the languages with the two-
     | letter primary subtag. For more information, refer to the Notes section.
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
    | Log activity
    |--------------------------------------------------------------------------
    |
    | This option defines whether to log the activity of the package or not. If
    | set to `true`, it will log information gathered throughout the execution
    | process. For more information, refer to the Logging section in README.md.
    |
    */

    'log_activity' => false,

];
