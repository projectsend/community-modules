<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Dispatched instead of calling a host activity logger directly — this
 * package has no dependency on the host's audit/logging classes (see
 * CustomAssetsServiceProvider's docblock for the full host-integration
 * contract). A host that wants these recorded subscribes its own
 * listener to this event.
 */
class CustomAssetCreated
{
    public function __construct(
        public readonly CustomAsset $asset,
        public readonly ?Authenticatable $actor,
    ) {}
}
