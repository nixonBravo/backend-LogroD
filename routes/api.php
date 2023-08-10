<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
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

//User
Route::controller(AuthController::class)->group(function () {
    Route::post('auth/register', 'register')->name('auth.register');
    Route::post('auth/login', 'login')->name('auth.login');
});

Route::middleware(['auth:sanctum'])->group(function () {
    //Categoria
    Route::controller(CategoriaController::class)->group(function () {
        Route::get('categorias', 'allCategorias')->name('categorias.allCategorias');
        Route::get('categoria/{id}', 'show')->name('categorias.show');
        Route::post('categoria-store', 'store')->name('categorias.store');
        Route::put('categoria-update/{id}', 'update')->name('categorias.update');
        Route::delete('categoria-delete/{id}', 'destroy')->name('categorias.destroy');
    });
    //Producto
    Route::controller(ProductoController::class)->group(function () {
        Route::get('productos', 'allProductos')->name('productos.allProductos');
        Route::get('producto/{id}', 'show')->name('productos.show');
        Route::get('search', 'search')->name('productos.search');
        Route::post('producto-store', 'store')->name('productos.store');
        Route::put('producto-update/{id}', 'update')->name('productos.update');
        Route::delete('producto-delete/{id}', 'destroy')->name('productos.destroy');
    });

    //Carrito
    /* Route::controller(CarritoController::class)->group(function(){
        Route::get('carrito', 'verCarrito')->name('carritos.ver');
        Route::post('add-producto/{id}', 'addProducto')->name('carritos.add');
        Route::put('update-producto/{id}', 'actualizarCarrito')->name('carritos.update');
        Route::put('decrementar/{id}', 'decrementarProducto')->name('carritos.decrementar');
        Route::delete('delete-producto/{id}', 'eliminarProducto')->name('carritos.eliminar');
        Route::delete('vaciar-carrito', 'vaciarCarrito')->name('carritos.vaciar');
    }); */
    Route::get('carrito', [CarritoController::class, 'verCarrito'])->name('carritos.ver');
    Route::post('add-producto', [CarritoController::class, 'addItem'])->name('carritos.add');
    Route::put('incrementar-producto/{carritoProducto}', [CarritoController::class, 'incrementarItem'])->name('carritos.incrementar');
    Route::put('decrementar-producto/{carritoProducto}', [CarritoController::class, 'decrementarItem'])->name('carritos.decrementar');
    Route::delete('eliminar-producto/{carritoProducto}', [CarritoController::class, 'eliminarItem'])->name('carritos.eliminar');
    Route::put('vaciar/{id}', [CarritoController::class, 'vaciarCarrito'])->name('carritos.vaciar');

    //Pedido
    /* Route::controller(PedidoController::class)->group(function () {
        //Route::post('stripe', [PedidoController::class, 'stripePost']);
        Route::get('pedidos', 'allPedidos')->name('pedidos.pedidos');
        Route::get('pedido/{id}', 'detallePedido')->name('pedidos.detalle');
        Route::post('realizar-pedido', 'realizarPedido')->name('pedidos.realizar');
    }); */
    Route::post('pedidos/realizar', [PedidoController::class, 'realizarPedido']);
    Route::get('pedidos/{pedido}', [PedidoController::class, 'verDetallePedido']);
    Route::get('pedidos', [PedidoController::class, 'verDetallesPedidos']);

    //User
    Route::controller(AuthController::class)->group(function () {
        Route::get('auth/profile', 'userProfile')->name('auth.profile');
        Route::get('auth/logout', 'logout')->name('auth.logout');
    });
});
