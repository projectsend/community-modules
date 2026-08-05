<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Tests;

use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\TestCase as Orchestra;
use ProjectSend\CommunityModules\CommunityModulesServiceProvider;
use ProjectSend\CommunityModules\Tests\Support\FakeUser;
use ProjectSend\CommunityModules\Tests\Support\TestCapabilityMiddleware;
use ProjectSend\CommunityModules\Tests\Support\TestStaffMiddleware;

/**
 * Isolated package tests run against a throwaway Testbench app, not the
 * real host — so host-provided concerns (the `staff` and `capability`
 * route middleware aliases, and the generic
 * create_assets/edit_assets/delete_assets Gate registration the real
 * host's IdentityServiceProvider does for every Permission case) are
 * faked here rather than required.
 */
abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [CommunityModulesServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cache.default', 'array');

        $app->make('router')->aliasMiddleware('staff', TestStaffMiddleware::class);
        $app->make('router')->aliasMiddleware('capability', TestCapabilityMiddleware::class);

        foreach (['create_assets', 'edit_assets', 'delete_assets'] as $ability) {
            Gate::define($ability, fn (FakeUser $user): bool => in_array($ability, $user->abilities, true));
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
