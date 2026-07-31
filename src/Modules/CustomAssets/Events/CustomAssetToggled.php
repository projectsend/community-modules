<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Only the dedicated enable/disable action dispatches this — editing an
 * asset's `enabled` checkbox via the regular edit form still dispatches
 * CustomAssetUpdated, not this, mirroring v1's split between its
 * enable()/disable() actions and its generic edit() logging.
 */
class CustomAssetToggled
{
    public function __construct(
        public readonly CustomAsset $asset,
        public readonly ?Authenticatable $actor,
        public readonly bool $enabled,
    ) {}
}
