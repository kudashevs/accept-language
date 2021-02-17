<?php

namespace Kudashevs\AcceptLanguage\Facades;

use Illuminate\Support\Facades\Facade;

class AcceptLanguage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'acceptlanguage';
    }
}
