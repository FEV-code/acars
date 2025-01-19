<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarBrandController;
use App\Http\Controllers\CarModelController;

Route::apiResource ( 'cars', CarController::class );
Route::get ( 'export', [ CarController::class, 'export' ] );

Route::get ( 'carbrands', [ CarBrandController::class, 'index' ] );
Route::post ( 'carbrands', [ CarBrandController::class, 'store' ] );
Route::get ( 'carbrands/{id}', [ CarBrandController::class, 'show' ] );

Route::get ( 'carmodels', [ CarModelController::class, 'index' ] );
Route::post ( 'carmodels', [ CarModelController::class, 'store' ] );
Route::get ( 'carmodels/{id}', [ CarModelController::class, 'show' ] );

Route::post ( 'brandsmodelsupdate', [ CarBrandController::class, 'update' ] );
