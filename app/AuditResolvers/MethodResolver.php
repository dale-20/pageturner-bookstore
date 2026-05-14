<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class MethodResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?string
    {
        return request()?->method();
    }
}
