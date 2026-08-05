<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Community-exclusive: lets staff inject arbitrary HTML/CSS/JS into
 * page head/body across the public, portal, and staff surfaces — v1
 * parity for create_assets/edit_assets/delete_assets. This module (and
 * the whole package it lives in) must never be required by the Cloud
 * deployment: arbitrary code injection into every page of a hosted,
 * multi-tenant product shouldn't merely be capability-gated off, it
 * should be physically absent from that codebase.
 *
 * Host integration contract (see this package's README):
 *   - the `staff` and `capability` route middleware aliases must be
 *     registered, and the host must declare a `custom_assets.manage`
 *     capability that is Community-only — this module refuses to serve
 *     without it rather than trusting its own presence
 *   - Gates for `create_assets`, `edit_assets`, `delete_assets` must be
 *     defined (the Cloud repo's IdentityServiceProvider already does
 *     this generically for every Permission case — a shared Community
 *     host is expected to carry the same registration)
 *   - the host's root Blade view must call
 *     CustomAssetRenderer::render() at <head>, top-of-<body>, and
 *     before </body>, passing AssetSurface::current(...)
 */
class CustomAssetsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');

        // `capability:custom_assets.manage` is load-bearing, not belt-and-
        // braces. This package being *present* is not supposed to be what
        // decides whether the feature is live — the host's Capability enum
        // marks custom_assets.manage as Community-only, and without this
        // middleware that declaration was never consulted: the routes
        // registered and answered 200 in a Cloud install, where arbitrary
        // HTML/JS injection into every page of a hosted, multi-tenant
        // product is precisely what must not exist. Physical absence from
        // the Cloud deployment is still the intended primary control (see
        // the class docblock); this is the gate for when it is present
        // anyway, which is the case in the shared development checkout.
        Route::middleware(['web', 'auth', 'staff', 'capability:custom_assets.manage'])
            ->group(__DIR__.'/routes.php');

        Gate::policy(CustomAsset::class, CustomAssetPolicy::class);
    }
}
