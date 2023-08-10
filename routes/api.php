<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use Illuminate\Http\Request;

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

//User
Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware(['auth:sanctum'])->group(function () {
    //Categoria
    Route::get('categorias', [CategoriaController::class, 'allCategorias'])->name('categorias.allCategorias');
    Route::get('categoria/{id}', [CategoriaController::class, 'show'])->name('categorias.show');
    Route::post('categoria-store', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('categoria-update/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('categoria-delete/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    //Producto
    Route::get('productos', [ProductoController::class, 'allProductos'])->name('productos.allProductos');
    Route::get('producto/{id}', [ProductoController::class, 'show'])->name('productos.show');
    Route::get('search', [ProductoController::class, 'search'])->name('productos.search');
    Route::post('producto-store', [ProductoController::class, 'store'])->name('productos.store');
    Route::put('producto-update/{id}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('producto-delete/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    //Carrito
    Route::get('carrito', [CarritoController::class, 'verCarrito'])->name('carritos.ver');
    Route::post('addProducto', [CarritoController::class, 'addItem'])->name('carritos.add');
    Route::put('incrementarProducto/{carritoProducto}', [CarritoController::class, 'incrementarItem'])->name('carritos.incrementar');
    Route::put('decrementarProducto/{carritoProducto}', [CarritoController::class, 'decrementarItem'])->name('carritos.decrementar');
    Route::delete('eliminarProducto/{carritoProducto}', [CarritoController::class, 'eliminarItem'])->name('carritos.eliminar');
    Route::put('vaciar/{id}', [CarritoController::class, 'vaciarCarrito'])->name('carritos.vaciar');

    //Pedido
    Route::post('comprar', [PedidoController::class, 'realizarPedido'])->name('pedidos.comprar');
    Route::get('pedidos', [PedidoController::class, 'verPedidos'])->name('pedidos.ver');

    //User
    Route::get('auth/profile', [AuthController::class, 'userProfile'])->name('auth.profile');
    Route::get('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
