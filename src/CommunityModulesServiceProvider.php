<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules;

use Illuminate\Support\ServiceProvider;
use ProjectSend\CommunityModules\Modules\CustomAssets\CustomAssetsServiceProvider;

/**
 * Entry point for every community-exclusive module in this package. Each
 * module is a self-contained ServiceProvider under src/Modules/<Name>/,
 * following the same one-concern-per-directory shape as the host app's
 * own app/Modules/<Name>/ convention. Add a new module by registering
 * it here — never by editing the host app.
 *
 * This package must never be installed where ProjectSend is run on
 * other people's behalf — see each module's own docblock for why. It is
 * built and tested standalone against a throwaway test app, so it needs
 * no host application present to develop against.
 */
class CommunityModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(CustomAssetsServiceProvider::class);
    }

    public function boot(): void
    {
        // This package owns whole screens, so it owns their translations
        // too — lang/{locale}.json here, not in the host application.
        // The host's translation scan reads its own directories and never
        // looks inside packages/, so a string added here would otherwise
        // never be reported as missing and would sit in English in every
        // language, silently.
        //
        // The host's own lang/ is merged *after* this one by the
        // framework's loader, so an installation can still override any
        // of these without touching the package.
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');

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
