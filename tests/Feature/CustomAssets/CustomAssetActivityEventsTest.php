<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetCreated;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetDeleted;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetToggled;
use ProjectSend\CommunityModules\Modules\CustomAssets\Events\CustomAssetUpdated;
use ProjectSend\CommunityModules\Modules\CustomAssets\Models\CustomAsset;
use ProjectSend\CommunityModules\Tests\Support\FakeUser;

test('creating an asset dispatches CustomAssetCreated', function () {
    Event::fake();
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->post(route('custom-assets.store'), customAssetPayload());

    Event::assertDispatched(CustomAssetCreated::class, fn (CustomAssetCreated $event): bool => $event->asset->title === 'A snippet' && $event->actor?->getAuthIdentifier() === 1);
});

test('updating an asset dispatches CustomAssetUpdated, not CustomAssetToggled, even when the enabled checkbox changes', function () {
    $asset = makeCustomAsset(ownerId: 1, overrides: ['enabled' => false]);
    Event::fake();
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->patch(route('custom-assets.update', $asset), customAssetPayload(['enabled' => true]));

    Event::assertDispatched(CustomAssetUpdated::class, fn (CustomAssetUpdated $event): bool => $event->asset->is($asset));
    Event::assertNotDispatched(CustomAssetToggled::class);
});

test('toggle dispatches CustomAssetToggled with the new enabled state', function () {
    $asset = makeCustomAsset(ownerId: 1, overrides: ['enabled' => false]);
    Event::fake();
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->patch(route('custom-assets.toggle', $asset));

    Event::assertDispatched(CustomAssetToggled::class, fn (CustomAssetToggled $event): bool => $event->enabled === true);
});

test('deleting an asset dispatches CustomAssetDeleted carrying the title, after the row is gone', function () {
    $asset = makeCustomAsset(ownerId: 1, overrides: ['title' => 'Doomed snippet']);
    Event::fake();
    $this->actingAs(new FakeUser(1, ['create_assets']));

    $this->delete(route('custom-assets.destroy', $asset));

    Event::assertDispatched(CustomAssetDeleted::class, fn (CustomAssetDeleted $event): bool => $event->assetTitle === 'Doomed snippet');
    expect(CustomAsset::query()->find($asset->id))->toBeNull();
});
