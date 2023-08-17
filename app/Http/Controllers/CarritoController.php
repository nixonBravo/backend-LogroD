<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

class CarritoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:carritos.ver')->only('verCarrito');
        $this->middleware('can:carritos.add')->only('addItem');
        $this->middleware('can:carritos.incrementar')->only('incrementarItem');
        $this->middleware('can:carritos.decrementar')->only('decrementarItem');
        $this->middleware('can:carritos.eliminar')->only('eliminarItem');
        $this->middleware('can:carritos.vaciar')->only('vaciarCarrito');
    }

    public function verCarrito()
    {
        try {
            $user = auth()->user();
            $carrito = $user->carritos()->where('estado', 'Activo')->first();

            if ($carrito) {
                $carritoId = $carrito->id;
                $productos = $carrito->productos()->withPivot('id as producto_carrito')->get();

                if ($productos->isEmpty()) {
                    return response()->json([
                        'message' => 'El carrito está vacío'
                    ], 200);
                }

                $total = 0;
                foreach ($productos as $producto) {
                    $total += $producto->precio * $producto->pivot->cantidad;
                }

                return response()->json([
                    'Carrito' => $carritoId,
                    'Productos' => $productos,
                    'Total' => $total
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No tienes un carrito activo'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo ver el Carrito'
            ], 500);
        }
    }

    public function addItem(Request $request)
    {
        try {
            $request->validate([
                'producto_id' => 'required|exists:productos,id',
                'cantidad' => 'required|integer|min:1',
            ]);

            $user = auth()->user();

            $producto = Producto::findOrFail($request->producto_id);
            if ($producto->estado_producto == false) {
                return response()->json([
                    'message' => 'Producto no está disponible'
                ], 203);
            }

            if ($producto->stock >= $request->cantidad) {
                $carrito = $user->carritos()->updateOrCreate(
                    ['estado' => 'Activo'],
                    ['estado' => 'Activo']
                );
                $existingProduct = $carrito->productos()->where('producto_id', $producto->id)->first();
                if ($existingProduct) {
                    $newQuantity = $existingProduct->pivot->cantidad + $request->cantidad;
                    $carrito->productos()->updateExistingPivot($producto->id, ['cantidad' => $newQuantity]);
                } else {
                    $carrito->productos()->attach($producto->id, ['cantidad' => $request->cantidad]);
                }

                $producto->stock -= $request->cantidad;
                $producto->save();

                return response()->json([
                    'message' => 'Producto añadido al carrito con éxito',
                    'Producto Añadido' => $producto->producto,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No hay suficiente stock o stock agotado'
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo Agregar el Producto al Carrito'
            ], 500);
        }
    }

    public function incrementarItem(CarritoProducto $carritoProducto)
    {
        try {
            $user = auth()->user();

            if ($user->carritos->contains($carritoProducto->carrito)) {
                $producto = $carritoProducto->producto;

                if ($producto->stock > $carritoProducto->cantidad) {
                    $carrito = $carritoProducto->carrito;

                    $carrito->productos()->where('producto_id', $producto->id)->increment('cantidad', 1);

                    $producto->stock -= 1;
                    $producto->save();

                    return response()->json([
                        'message' => 'Cantidad incrementada con éxito',
                        'Producto Incrementado' => $producto->producto,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'El stock del producto está agotado'
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'El producto no existe en el carrito'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo incrementar la cantidad del producto en el carrito',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function decrementarItem(CarritoProducto $carritoProducto)
    {
        try {
            $user = auth()->user();

            if ($user->carritos->contains($carritoProducto->carrito)) {
                if ($carritoProducto->cantidad > 1) {
                    $carritoProducto->decrement('cantidad', 1);
                    $producto = $carritoProducto->producto;
                    $producto->stock += 1;
                    $producto->save();

                    return response()->json([
                        'message' => 'Cantidad decrementada con éxito'
                    ]);
                } else {
                    $producto = $carritoProducto->producto;
                    $producto->stock += 1;
                    $producto->save();
                    $carritoProducto->delete();

                    return response()->json([
                        'message' => 'Producto eliminado del carrito'
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'El producto no existe en el carrito'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo decrementar el producto del carrito'
            ], 500);
        }
    }

    public function eliminarItem(CarritoProducto $carritoProducto)
    {
        try {
            $user = auth()->user();

            if ($user->carritos->contains($carritoProducto->carrito)) {
                $producto = $carritoProducto->producto;

                $producto->stock += $carritoProducto->cantidad;
                $producto->save();

                $carritoProducto->delete();

                return response()->json([
                    'message' => 'Producto eliminado del carrito con éxito'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'El producto no existe en el carrito'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo vaciar el carrito'
            ], 500);
        }
    }

    public function vaciarCarrito()
    {
        try {
            $user = auth()->user();
            $carrito = $user->carritos()->where('estado', 'Activo')->first();

            if ($carrito) {

                $productos = $carrito->productos;
                foreach ($productos as $producto) {
                    $producto->stock += $producto->pivot->cantidad;
                    $producto->save();
                }

                $carrito->productos()->detach();
                $carrito->delete();

                return response()->json([
                    'message' => 'Carrito vaciado con éxito'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No tienes un carrito activo'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo vaciar el carrito'
            ], 500);
        }
    }
}
