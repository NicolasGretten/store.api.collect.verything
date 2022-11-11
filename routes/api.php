<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreClosingController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreImageController;
use App\Http\Controllers\StoreMediaController;
use App\Http\Controllers\StoreSlotController;
use App\Http\Controllers\StoreTranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::controller(StoreController::class)->group(function () {
    Route::get("stores/", 'list');
    Route::post("stores/", 'create');
    Route::patch("stores/{id}", 'update');
    Route::delete("stores/{id}", 'delete');
    Route::get("stores/{id}", 'retrieve');
});

Route::controller(StoreClosingController::class)->group(function () {
    Route::post("stores/{id}/closings", 'addClosing');
    Route::delete("stores/{id}/closings/{closing_id}", 'removeClosing');
});

Route::controller(StoreImageController::class)->group(function () {
    Route::post("stores/{id}/images", 'addImage');
    Route::delete("stores/{id}/images/{store_image_id}", 'removeImage');
});

Route::controller(StoreMediaController::class)->group(function () {
    Route::post("stores/{id}/medias", 'addMedia');
    Route::delete("stores/{id}/medias/{media_id}", 'removeMedia');
});

Route::controller(StoreSlotController::class)->group(function () {
    Route::post("stores/{id}/slots", 'addSlot');
    Route::delete("stores/{id}/slots/{id}", 'removeSlot');
});

Route::controller(StoreTranslationController::class)->group(function () {
    Route::post("stores/{id}/translations", 'addTranslation');
    Route::delete("stores/{id}/translations", 'removeTranslation');
});
