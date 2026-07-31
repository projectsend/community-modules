<?php

declare(strict_types=1);

namespace ProjectSend\CommunityModules\Modules\CustomAssets\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetLanguage;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetSurface;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetCreated;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetDeleted;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetToggled;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetUpdated;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;
use ProjectSend\CommunityModules\Modules\CustomAssets\Rendering\CustomAssetRenderer;

class CustomAssetsController extends Controller
{
    public function __construct(
        private readonly CustomAssetRenderer $renderer,
    ) {}

    public function index(Request $request): Response
    {
        // Reachable by any of the three abilities — mirrors v1's
        // check_access_enhanced([...], 'any'). Not a policy method:
        // there's no single model instance to check "viewAny" against
        // here, just the raw permission trio.
        if (! Gate::any(['create_assets', 'edit_assets', 'delete_assets'])) {
            abort(403);
        }

        return Inertia::render('system/settings/custom-assets/index', [
            'assets' => CustomAsset::query()->latest()->get()->map(fn (CustomAsset $asset): array => [
                'id' => $asset->id,
                'title' => $asset->title,
                'language' => $asset->language->value,
                'surfaces' => $asset->surfaces,
                'position' => $asset->position->value,
                'enabled' => $asset->enabled,
                'can_edit' => Gate::forUser($request->user())->allows('update', $asset),
                'can_delete' => Gate::forUser($request->user())->allows('delete', $asset),
            ]),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', CustomAsset::class);

        return Inertia::render('system/settings/custom-assets/create', [
            'languages' => $this->languageOptions(),
            'surfaces' => $this->surfaceOptions(),
            'positions' => $this->positionOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', CustomAsset::class);

        $data = $this->validated($request);

        $asset = CustomAsset::query()->create([
            ...$data,
            'created_by' => $request->user()?->getAuthIdentifier(),
        ]);

        $this->renderer->forget();
        event(new CustomAssetCreated($asset, $request->user()));

        return redirect()->route('custom-assets.edit', $asset)->with('success', 'Asset created.');
    }

    public function edit(CustomAsset $customAsset): Response
    {
        Gate::authorize('update', $customAsset);

        return Inertia::render('system/settings/custom-assets/edit', [
            'asset' => [
                'id' => $customAsset->id,
                'title' => $customAsset->title,
                'language' => $customAsset->language->value,
                'content' => $customAsset->content,
                'surfaces' => $customAsset->surfaces,
                'position' => $customAsset->position->value,
                'enabled' => $customAsset->enabled,
            ],
            'languages' => $this->languageOptions(),
            'surfaces' => $this->surfaceOptions(),
            'positions' => $this->positionOptions(),
        ]);
    }

    public function update(Request $request, CustomAsset $customAsset): RedirectResponse
    {
        Gate::authorize('update', $customAsset);

        $customAsset->update($this->validated($request));

        $this->renderer->forget();
        event(new CustomAssetUpdated($customAsset, $request->user()));

        return back()->with('success', 'Asset updated.');
    }

    public function toggle(Request $request, CustomAsset $customAsset): RedirectResponse
    {
        Gate::authorize('update', $customAsset);

        $customAsset->update(['enabled' => ! $customAsset->enabled]);

        $this->renderer->forget();
        event(new CustomAssetToggled($customAsset, $request->user(), $customAsset->enabled));

        return back()->with('success', $customAsset->enabled ? 'Asset enabled.' : 'Asset disabled.');
    }

    public function destroy(Request $request, CustomAsset $customAsset): RedirectResponse
    {
        Gate::authorize('delete', $customAsset);

        $title = $customAsset->title;
        $customAsset->delete();

        $this->renderer->forget();
        event(new CustomAssetDeleted($title, $request->user()));

        return redirect()->route('custom-assets.index')->with('success', 'Asset deleted.');
    }

    /**
     * @return array{title: string, language: AssetLanguage, content: string, surfaces: list<string>, position: AssetPosition, enabled: bool}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'language' => ['required', Rule::enum(AssetLanguage::class)],
            'content' => ['required', 'string'],
            'surfaces' => ['required', 'array', 'min:1'],
            'surfaces.*' => [Rule::enum(AssetSurface::class)],
            'position' => ['required', Rule::enum(AssetPosition::class)],
            'enabled' => ['sometimes', 'boolean'],
        ]);

        $data['language'] = AssetLanguage::from($data['language']);
        $data['position'] = AssetPosition::from($data['position']);
        $data['enabled'] = $data['enabled'] ?? false;

        return $data;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function languageOptions(): array
    {
        return array_map(
            fn (AssetLanguage $language): array => ['value' => $language->value, 'label' => $language->label()],
            AssetLanguage::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function surfaceOptions(): array
    {
        return array_map(
            fn (AssetSurface $surface): array => ['value' => $surface->value, 'label' => $surface->label()],
            AssetSurface::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function positionOptions(): array
    {
        return array_map(
            fn (AssetPosition $position): array => ['value' => $position->value, 'label' => $position->label()],
            AssetPosition::cases(),
        );
    }
}
