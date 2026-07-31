<?php

declare(strict_types=1);

use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;
use ProjectSend\CommunityModules\Tests\Support\FakeUser;

function makeCustomAsset(int $ownerId, array $overrides = []): CustomAsset
{
    return CustomAsset::query()->create(array_merge([
        'title' => 'Test asset',
        'language' => 'css',
        'content' => 'body { color: red; }',
        'surfaces' => ['public'],
        'position' => 'head',
        'enabled' => true,
        'created_by' => $ownerId,
    ], $overrides));
}

function customAssetPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'A snippet',
        'language' => 'css',
        'content' => 'body { color: blue; }',
        'surfaces' => ['public'],
        'position' => 'head',
        'enabled' => true,
    ], $overrides);
}

test('index is reachable by any of the three abilities', function () {
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->withHeaders(['X-Inertia' => 'true'])
        ->get(route('custom-assets.index'))
        ->assertOk();
});

test('index is forbidden without any custom-assets ability', function () {
    $this->actingAs(new FakeUser(1, []));

    $this->withHeaders(['X-Inertia' => 'true'])
        ->get(route('custom-assets.index'))
        ->assertForbidden();
});

test('a user with only create_assets can create their own asset, and edit/delete it afterwards', function () {
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->post(route('custom-assets.store'), customAssetPayload())->assertRedirect();

    $asset = CustomAsset::query()->sole();
    expect($asset->created_by)->toBe(1);

    $this->patch(route('custom-assets.update', $asset), customAssetPayload(['title' => 'Renamed']))
        ->assertRedirect();
    expect($asset->refresh()->title)->toBe('Renamed');

    $this->delete(route('custom-assets.destroy', $asset))->assertRedirect();
    expect(CustomAsset::query()->find($asset->id))->toBeNull();
});

test('a user with only create_assets cannot edit or delete another users asset', function () {
    $theirs = makeCustomAsset(ownerId: 2);
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->patch(route('custom-assets.update', $theirs), customAssetPayload())->assertForbidden();
    $this->delete(route('custom-assets.destroy', $theirs))->assertForbidden();
    expect(CustomAsset::query()->find($theirs->id))->not->toBeNull();
});

test('a user with edit_assets and delete_assets can edit and delete any asset', function () {
    $theirs = makeCustomAsset(ownerId: 2);
    $this->actingAs(new FakeUser(1, ['edit_assets', 'delete_assets']));

    $this->patch(route('custom-assets.update', $theirs), customAssetPayload(['title' => 'Retitled']))
        ->assertRedirect();
    expect($theirs->refresh()->title)->toBe('Retitled');

    $this->delete(route('custom-assets.destroy', $theirs))->assertRedirect();
    expect(CustomAsset::query()->find($theirs->id))->toBeNull();
});

test('creating an asset requires create_assets', function () {
    $this->actingAs(new FakeUser(1, ['edit_assets', 'delete_assets']));

    $this->withHeaders(['X-Inertia' => 'true'])
        ->get(route('custom-assets.create'))
        ->assertForbidden();

    $this->post(route('custom-assets.store'), customAssetPayload())->assertForbidden();
    expect(CustomAsset::query()->count())->toBe(0);
});

test('toggle flips enabled and is subject to the same own-vs-any authorization as update', function () {
    $theirs = makeCustomAsset(ownerId: 2, overrides: ['enabled' => false]);

    $this->actingAs(new FakeUser(1, ['create_assets']));
    $this->patch(route('custom-assets.toggle', $theirs))->assertForbidden();

    $this->actingAs(new FakeUser(1, ['edit_assets']));
    $this->patch(route('custom-assets.toggle', $theirs))->assertRedirect();
    expect($theirs->refresh()->enabled)->toBeTrue();
});
