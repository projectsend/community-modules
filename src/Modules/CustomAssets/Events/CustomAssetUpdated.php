<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

class CustomAssetUpdated
{
    public function __construct(
        public readonly CustomAsset $asset,
        public readonly ?Authenticatable $actor,
    ) {}
}
