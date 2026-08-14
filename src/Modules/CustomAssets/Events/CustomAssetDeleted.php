<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Events;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Carries the title rather than the model — the asset is already gone
 * from the database by the time this dispatches, mirroring the host's
 * own convention for delete-shaped log entries (e.g. FilesController's
 * FileDeleted, which also logs a plain name rather than the deleted
 * model).
 */
class CustomAssetDeleted
{
    public function __construct(
        public readonly string $assetTitle,
        public readonly ?Authenticatable $actor,
    ) {}
}
