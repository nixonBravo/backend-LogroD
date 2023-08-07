<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Role;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('auth/register', 'register')->name('auth.register');
    Route::post('auth/login', 'login')->name('auth.login');
});

Route::middleware(['auth:sanctum'])->group(function () {
    //Categorias
    Route::controller(CategoriaController::class)->group(function(){
        Route::get('categorias', 'allCategorias')->name('categorias.allCategorias');
        Route::get('categoria/{id}', 'show')->name('categorias.show');
        Route::post('categoria-store', 'store')->name('categorias.store');
        Route::put('categoria-update/{id}', 'update')->name('categorias.update');
        Route::delete('categoria-delete/{id}', 'destroy')->name('categorias.destroy');
    });
    //Productos
    Route::controller(ProductoController::class)->group(function () {
        Route::get('productos', 'allProductos')->name('productos.allProductos');
        Route::get('producto/{id}', 'show')->name('productos.show');
        Route::post('producto-store', 'store')->name('productos.store');
        Route::put('producto-update/{id}', 'update')->name('productos.update');
        Route::delete('producto-delete/{id}', 'destroy')->name('productos.destroy');
    });

    //Auth
    Route::controller(AuthController::class)->group(function () {
        Route::get('auth/profile', 'userProfile')->name('auth.profile');
        Route::get('auth/logout', 'logout')->name('auth.logout');
    });
});
