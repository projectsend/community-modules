<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Tests\Support;

use Closure;
use Illuminate\Http\Request;

/**
 * Stands in for the host's real `capability` middleware during isolated
 * package tests — toggled via config so tests can exercise both the
 * "capability available" (community) and "capability unavailable"
 * (cloud) paths without depending on the host's Capability enum, which
 * does not exist in this throwaway Testbench app. Mirrors the same stub
 * in the cloud-modules package.
 */
class TestCapabilityMiddleware
{
    public function handle(Request $request, Closure $next, string $key): mixed
    {
        if (config('community-modules-testing.capability_enabled', true)) {
            return $next($request);
        }

        abort(404);
    }
}
