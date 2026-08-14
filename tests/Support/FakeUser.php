<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Tests\Support;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

/**
 * A bare authenticatable for `actingAs()` in isolated package tests —
 * `actingAs()` sets it directly on the guard without touching a
 * database, so no `users` table/migration is needed here. Carries a
 * fake ability list so TestCase's Gate::define stand-ins (for
 * create_assets/edit_assets/delete_assets) can decide access without
 * depending on the real host's PermissionChecker.
 */
class FakeUser extends GenericUser implements AuthorizableContract
{
    use Authorizable;

    /**
     * @param list<string> $abilities
     */
    public function __construct(int $id = 1, public readonly array $abilities = [])
    {
        parent::__construct(['id' => $id]);
    }
}
