<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Lets staff add their own HTML, CSS or JavaScript to the page head or
 * body, across the public, portal and staff surfaces.
 *
 * This module must never be installed where ProjectSend is run on other
 * people's behalf. Letting an administrator inject arbitrary code into
 * every page is not a risk to switch off there — the code should be
 * absent from that installation entirely.
 *
 * What the host has to provide:
 *   - the `staff` and `capability` route middleware aliases must be
 *     registered, and the host must declare a `custom_assets.manage`
 *     capability that is Community-only — this module refuses to serve
 *     without it rather than trusting its own presence
 *   - Gates for `create_assets`, `edit_assets`, `delete_assets` must be
 *     defined (ProjectSend registers one generically for every
 *     permission, so a stock host already satisfies this)
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
        // registered and answered 200 even where this feature must not
        // exist at all. Not installing the package is still the primary
        // control (see the class docblock); this is the gate for when it
        // is present anyway, as it is in a development checkout.
        Route::middleware(['web', 'auth', 'staff', 'capability:custom_assets.manage'])
            ->group(__DIR__.'/routes.php');

        Gate::policy(CustomAsset::class, CustomAssetPolicy::class);
    }
}
