<?php

declare(strict_types=1);

use ProjectSend\CommunityModules\Modules\CustomAssets\AssetPosition;
use ProjectSend\CommunityModules\Modules\CustomAssets\AssetSurface;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;
use ProjectSend\CommunityModules\Modules\CustomAssets\Rendering\CustomAssetRenderer;
use ProjectSend\CommunityModules\Tests\Support\FakeUser;

test('render includes an enabled asset matching surface and position, wrapped for its language', function () {
    CustomAsset::query()->create([
        'title' => 'Public head CSS',
        'language' => 'css',
        'content' => 'body { color: red; }',
        'surfaces' => ['public'],
        'position' => 'head',
        'enabled' => true,
        'created_by' => 1,
    ]);

    $html = app(CustomAssetRenderer::class)->render(AssetPosition::Head, AssetSurface::Public);

    expect($html)->toBe('<style>body { color: red; }</style>');
});

test('render excludes assets for a different surface or position', function () {
    CustomAsset::query()->create([
        'title' => 'Staff-only script',
        'language' => 'js',
        'content' => 'console.log(1);',
        'surfaces' => ['staff'],
        'position' => 'body_bottom',
        'enabled' => true,
        'created_by' => 1,
    ]);

    $renderer = app(CustomAssetRenderer::class);

    expect($renderer->render(AssetPosition::Head, AssetSurface::Public))->toBe('');
    expect($renderer->render(AssetPosition::BodyBottom, AssetSurface::Public))->toBe('');
    expect($renderer->render(AssetPosition::BodyBottom, AssetSurface::Staff))->toContain('console.log(1);');
});

test('render excludes disabled assets', function () {
    CustomAsset::query()->create([
        'title' => 'Disabled',
        'language' => 'html',
        'content' => '<div>hi</div>',
        'surfaces' => ['public'],
        'position' => 'body_top',
        'enabled' => false,
        'created_by' => 1,
    ]);

    expect(app(CustomAssetRenderer::class)->render(AssetPosition::BodyTop, AssetSurface::Public))->toBe('');
});

test('forget invalidates the cache so a mutation via the controller is reflected immediately', function () {
    $asset = CustomAsset::query()->create([
        'title' => 'Original',
        'language' => 'html',
        'content' => '<p>old</p>',
        'surfaces' => ['public'],
        'position' => 'body_top',
        'enabled' => true,
        'created_by' => 2,
    ]);

    $renderer = app(CustomAssetRenderer::class);
    expect($renderer->render(AssetPosition::BodyTop, AssetSurface::Public))->toContain('old');

    $this->actingAs(new FakeUser(1, ['edit_assets']));
    $this->patch(route('custom-assets.update', $asset), [
        'title' => 'Original',
        'language' => 'html',
        'content' => '<p>new</p>',
        'surfaces' => ['public'],
        'position' => 'body_top',
        'enabled' => true,
    ])->assertRedirect();

    expect($renderer->render(AssetPosition::BodyTop, AssetSurface::Public))->toContain('new');
});

test('AssetSurface::current derives the surface from guest, staff, and portal (client) users', function () {
    expect(AssetSurface::current(null, isStaff: false))->toBe(AssetSurface::Public);
    expect(AssetSurface::current(new FakeUser(1), isStaff: true))->toBe(AssetSurface::Staff);
    expect(AssetSurface::current(new FakeUser(1), isStaff: false))->toBe(AssetSurface::Portal);
});
