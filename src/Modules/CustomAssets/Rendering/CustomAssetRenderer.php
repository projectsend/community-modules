<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Rendering;

use Illuminate\Support\Facades\Cache;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetLanguage;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetSurface;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;

/**
 * Renders every enabled asset matching a surface+position, wrapped in
 * <style>/<script> for css/js or emitted raw for html — same shape as
 * v1's render_custom_assets(). Content is echoed unescaped by design:
 * this is a staff-only, permission-gated trust boundary, not an
 * oversight (see CustomAssetPolicy).
 *
 * The full enabled-asset list is cached forever (same pattern as the
 * host's Settings class) since it's a tiny dataset queried on every
 * request across every surface — call forget() after any mutation.
 */
class CustomAssetRenderer
{
    private const CACHE_KEY = 'custom_assets.enabled';

    public function render(AssetPosition $position, AssetSurface $surface): string
    {
        $matching = $this->enabled()
            ->filter(fn (CustomAsset $asset): bool => $asset->position === $position
                && in_array($surface->value, $asset->surfaces, true));

        return $matching
            ->map(fn (CustomAsset $asset): string => $this->wrap($asset))
            ->implode('');
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return \Illuminate\Support\Collection<int, CustomAsset>
     */
    private function enabled(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => CustomAsset::query()->where('enabled', true)->get(),
        );
    }

    private function wrap(CustomAsset $asset): string
    {
        return match ($asset->language) {
            AssetLanguage::Css => '<style>'.$asset->content.'</style>',
            AssetLanguage::Js => '<script>'.$asset->content.'</script>',
            AssetLanguage::Html => $asset->content,
        };
    }
}
