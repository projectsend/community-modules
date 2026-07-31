<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use ProjectSend\CommunityModules\Modules\CustomAssets\Http\Controllers\CustomAssetsController;

// Registered before the {customAsset} wildcard routes below so the
// literal "create" segment isn't swallowed by route-model binding.
// Authorization for every action here happens inside the controller via
// Gate::authorize/CustomAssetPolicy, not route middleware — this package
// can't assume the host has a `can:` middleware alias registered under
// that exact name.
Route::get('system/settings/custom-assets/create', [CustomAssetsController::class, 'create'])
    ->name('custom-assets.create');
Route::post('system/settings/custom-assets', [CustomAssetsController::class, 'store'])
    ->name('custom-assets.store');
Route::get('system/settings/custom-assets', [CustomAssetsController::class, 'index'])
    ->name('custom-assets.index');
Route::get('system/settings/custom-assets/{customAsset}', [CustomAssetsController::class, 'edit'])
    ->name('custom-assets.edit');
Route::patch('system/settings/custom-assets/{customAsset}', [CustomAssetsController::class, 'update'])
    ->name('custom-assets.update');
Route::patch('system/settings/custom-assets/{customAsset}/toggle', [CustomAssetsController::class, 'toggle'])
    ->name('custom-assets.toggle');
Route::delete('system/settings/custom-assets/{customAsset}', [CustomAssetsController::class, 'destroy'])
    ->name('custom-assets.destroy');
