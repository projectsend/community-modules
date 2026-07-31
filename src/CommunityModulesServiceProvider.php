<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules;

use Illuminate\Support\ServiceProvider;
use ProjectSend\CommunityModules\Modules\CustomAssets\CustomAssetsServiceProvider;
use ProjectSend\CommunityModules\Modules\Themes\ThemesServiceProvider;

/**
 * Entry point for every community-exclusive module in this package. Each
 * module is a self-contained ServiceProvider under src/Modules/<Name>/,
 * following the same one-concern-per-directory shape as the host app's
 * own app/Modules/<Name>/ convention. Add a new module by registering
 * it here — never by editing the host app.
 *
 * This package must never be required by the Cloud deployment
 * (projectsend.cloud/cloud's composer.json) — see each module's own
 * docblock for why. It's built and tested fully standalone against a
 * throwaway Testbench app; a real Community-edition host to install it
 * into doesn't exist yet as a running repo.
 */
class CommunityModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(CustomAssetsServiceProvider::class);
        $this->app->register(ThemesServiceProvider::class);
    }

    public function boot(): void
    {
        // The host app's own resources/js/app.tsx glob-merges every
        // package's pages into the frontend build already; Inertia's
        // server-side testing helper (AssertableInertia::component())
        // doesn't know about that merge and only looks under the host's
        // own resources/js/pages by default — extend its search paths
        // the same way so assertInertia(...->component(...)) works for a
        // package page in the host app's own test suite too.
        $testingPagePaths = config('inertia.testing.page_paths', []);
        $testingPagePaths[] = __DIR__.'/../resources/js/pages';
        config(['inertia.testing.page_paths' => $testingPagePaths]);
    }
}
