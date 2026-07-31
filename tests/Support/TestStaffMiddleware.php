<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Tests\Support;

use Closure;
use Illuminate\Http\Request;

/**
 * Stands in for the host's real `staff` middleware during isolated
 * package tests, which run against a throwaway Testbench app rather
 * than the real host.
 */
class TestStaffMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (config('community-modules-testing.is_staff', true)) {
            return $next($request);
        }

        abort(403);
    }
}
