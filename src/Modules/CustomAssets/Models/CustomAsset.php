<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Models;

use Illuminate\Database\Eloquent\Model;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetLanguage;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition;

/**
 * @property int $id
 * @property string $title
 * @property AssetLanguage $language
 * @property string $content
 * @property list<string> $surfaces
 * @property AssetPosition $position
 * @property bool $enabled
 * @property int $created_by
 */
class CustomAsset extends Model
{
    protected $table = 'custom_assets';

    protected $guarded = [];

    protected $casts = [
        'language' => AssetLanguage::class,
        'surfaces' => 'array',
        'position' => AssetPosition::class,
        'enabled' => 'boolean',
    ];

    /**
     * Ownership check backing the "create_assets alone manages only your
     * own assets" rule — mirrors the host's File::isOwnedBy, but takes a
     * plain user id rather than a host-specific User type, since this
     * package must not assume the host's Authenticatable shape.
     */
    public function isOwnedBy(int $userId): bool
    {
        return $this->created_by === $userId;
    }
}
