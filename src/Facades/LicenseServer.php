<?php

namespace Xslain\LicenseServer\Facades;

use Illuminate\Support\Facades\Facade;

class LicenseServer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'license-server';
    }
}
