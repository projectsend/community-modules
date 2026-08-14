<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Ownership rules as policy methods, mirroring the host's own FilePolicy
 * shape (ProjectSend's own file policy): "own"
 * versus "any" maps onto the create_assets/edit_assets/delete_assets
 * permission trio. Unlike Files (which has explicit edit_files vs
 * edit_others_files keys), Assets only has three keys total — v1
 * confirmed edit_assets/delete_assets already mean "any asset,"
 * with create_assets alone covering the "own only" case.
 *
 * @template TUser of Authenticatable&Authorizable
 */
class CustomAssetPolicy
{
    /**
     * @param TUser $user
     */
    public function create(Authenticatable&Authorizable $user): bool
    {
        return $user->can('create_assets');
    }

    /**
     * @param TUser $user
     */
    public function update(Authenticatable&Authorizable $user, CustomAsset $asset): bool
    {
        return $asset->isOwnedBy((int) $user->getAuthIdentifier())
            ? $user->can('create_assets')
            : $user->can('edit_assets');
    }

    /**
     * @param TUser $user
     */
    public function delete(Authenticatable&Authorizable $user, CustomAsset $asset): bool
    {
        return $asset->isOwnedBy((int) $user->getAuthIdentifier())
            ? $user->can('create_assets')
            : $user->can('delete_assets');
    }
}
