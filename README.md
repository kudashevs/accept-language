# HTTP Accept-Language 

[![Build Status](https://travis-ci.org/kudashevs/accept-language.svg?branch=master)](https://travis-ci.org/kudashevs/accept-language)
[![License](https://img.shields.io/github/license/kudashevs/accept-language)](https://packagist.org/packages/kudashevs/accept-language)

This PHP package recognizes the preferred language from an HTTP Accept-Language request-header field. It can be used
in many cases, for example, to identify the user's language of choice on their first site visit. Then this information
might be used to make some decisions (set locale, redirect user on the specific page or section, etc.).

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
$language = $service->getLanguage();
```

## References

- [RFC 7231 Hypertext Transfer Protocol (HTTP/1.1)] (https://tools.ietf.org/html/rfc7231#section-5.3.5)  
- [RFC 4647 Matching of Language Tags] (https://tools.ietf.org/html/rfc4647#section-2.1)

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.




 






