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

test('an administrator with create_assets can create their own asset, and edit/delete it afterwards', function () {
    $this->actingAs(new FakeUser(1, ['create_assets', 'administer']));

    $this->post(route('custom-assets.store'), customAssetPayload())->assertRedirect();

    $asset = CustomAsset::query()->sole();
    expect($asset->created_by)->toBe(1);

    $this->patch(route('custom-assets.update', $asset), customAssetPayload(['title' => 'Renamed']))
        ->assertRedirect();
    expect($asset->refresh()->title)->toBe('Renamed');

    $this->delete(route('custom-assets.destroy', $asset))->assertRedirect();
    expect(CustomAsset::query()->find($asset->id))->toBeNull();
});

test('create_assets alone does not reach another users asset', function () {
    $theirs = makeCustomAsset(ownerId: 2);
    $this->actingAs(new FakeUser(1, ['create_assets', 'administer']));

    $this->patch(route('custom-assets.update', $theirs), customAssetPayload())->assertForbidden();
    $this->delete(route('custom-assets.destroy', $theirs))->assertForbidden();
    expect(CustomAsset::query()->find($theirs->id))->not->toBeNull();
});

test('an administrator with edit_assets and delete_assets can edit and delete any asset', function () {
    $theirs = makeCustomAsset(ownerId: 2);
    $this->actingAs(new FakeUser(1, ['edit_assets', 'delete_assets', 'administer']));

    $this->patch(route('custom-assets.update', $theirs), customAssetPayload(['title' => 'Retitled']))
        ->assertRedirect();
    expect($theirs->refresh()->title)->toBe('Retitled');

    $this->delete(route('custom-assets.destroy', $theirs))->assertRedirect();
    expect(CustomAsset::query()->find($theirs->id))->toBeNull();
});

test('creating an asset requires create_assets', function () {
    $this->actingAs(new FakeUser(1, ['edit_assets', 'delete_assets', 'administer']));

    $this->withHeaders(['X-Inertia' => 'true'])
        ->get(route('custom-assets.create'))
        ->assertForbidden();

    $this->post(route('custom-assets.store'), customAssetPayload())->assertForbidden();
    expect(CustomAsset::query()->count())->toBe(0);
});

test('toggle flips enabled and is subject to the same own-vs-any authorization as update', function () {
    $theirs = makeCustomAsset(ownerId: 2, overrides: ['enabled' => false]);

    $this->actingAs(new FakeUser(1, ['create_assets', 'administer']));
    $this->patch(route('custom-assets.toggle', $theirs))->assertForbidden();

    $this->actingAs(new FakeUser(1, ['edit_assets', 'administer']));
    $this->patch(route('custom-assets.toggle', $theirs))->assertRedirect();
    expect($theirs->refresh()->enabled)->toBeTrue();
});

// This module injects arbitrary HTML/JS into every page, so it is
// Community-only by design. Being *installed* must not be what decides
// whether it is live: the shared development checkout requires this
// package from the Cloud host, where the routes previously registered and
// answered 200 despite Capability::CustomAssets being false there.
test('every route is gone when the host does not grant the capability', function () {
    config(['community-modules-testing.capability_enabled' => false]);

    $this->actingAs(new FakeUser(1, ['create_assets', 'edit_assets', 'delete_assets']));
    $asset = makeCustomAsset(1, ['title' => 'Tracker', 'language' => 'js', 'content' => 'alert(1)']);

    $this->get('/system/settings/custom-assets')->assertNotFound();
    $this->get('/system/settings/custom-assets/create')->assertNotFound();
    $this->get("/system/settings/custom-assets/{$asset->id}")->assertNotFound();
    $this->post('/system/settings/custom-assets', customAssetPayload())->assertNotFound();
    $this->patch("/system/settings/custom-assets/{$asset->id}/toggle")->assertNotFound();
    $this->delete("/system/settings/custom-assets/{$asset->id}")->assertNotFound();

    expect(CustomAsset::query()->count())->toBe(1)
        ->and($asset->refresh()->enabled)->toBeTrue();
});

// An asset is script on the application's own origin. A public one can
// frame the sign-in form or wait in an open tab for somebody to sign in;
// a portal one runs as every client. No surface keeps it away from a
// privileged session, so every write is the administrator's.
test('writing an asset on any surface needs the administrator', function (array $surfaces) {
    foreach ([['create_assets'], ['create_assets', 'edit_settings']] as $abilities) {
        $this->actingAs(new FakeUser(1, $abilities));

        $this->withHeaders(['X-Inertia' => 'true'])->get(route('custom-assets.create'))->assertForbidden();
        $this->post(route('custom-assets.store'), customAssetPayload(['surfaces' => $surfaces]))->assertForbidden();
    }

    expect(CustomAsset::query()->count())->toBe(0);

    $this->actingAs(new FakeUser(1, ['create_assets', 'administer']));
    $this->post(route('custom-assets.store'), customAssetPayload(['surfaces' => $surfaces]))
        ->assertSessionHasNoErrors();
    expect(CustomAsset::query()->count())->toBe(1);
})->with([
    'public' => [['public']],
    'portal' => [['portal']],
    'staff' => [['staff']],
]);

test('an asset the holder wrote before cannot be edited, toggled or deleted without the administrator', function () {
    $mine = makeCustomAsset(1, ['surfaces' => ['public'], 'enabled' => false]);
    $this->actingAs(new FakeUser(1, ['create_assets', 'edit_assets', 'delete_assets']));

    $this->patch(route('custom-assets.update', $mine), customAssetPayload(['content' => 'alert(1)', 'language' => 'js']))
        ->assertForbidden();
    $this->patch(route('custom-assets.toggle', $mine))->assertForbidden();
    $this->delete(route('custom-assets.destroy', $mine))->assertForbidden();

    expect($mine->refresh()->enabled)->toBeFalse()
        ->and($mine->content)->toBe('body { color: red; }');
});

test('the list is still readable without the administrator, with no way to write', function () {
    makeCustomAsset(2);
    $this->actingAs(new FakeUser(1, ['create_assets', 'edit_assets', 'delete_assets']));

    $this->withHeaders(['X-Inertia' => 'true'])->get(route('custom-assets.index'))
        ->assertOk()
        ->assertJsonPath('props.can_create', false)
        ->assertJsonPath('props.assets.0.can_edit', false)
        ->assertJsonPath('props.assets.0.can_delete', false);
});
