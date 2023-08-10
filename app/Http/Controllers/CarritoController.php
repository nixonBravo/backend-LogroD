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

            if (!$carrito) {
                return response()->json([
                    'message' => 'El carrito está vacío'
                ], 404);
            }

            $productos = $carrito->productos;

            if ($productos->isEmpty()) {
                return response()->json([
                    'message' => 'El carrito está vacío'
                ], 404);
            }

            return response()->json([
                'Productos en el Carrito' => $productos
            ], 200);
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

            if ($producto->stock >= $request->cantidad) {
                $carrito = $user->carritos()->updateOrCreate(
                    ['estado' => 'Activo'],
                    ['estado' => 'Activo']
                );

                $carrito->productos()->attach($producto->id, ['cantidad' => $request->cantidad]);

                $producto->stock -= $request->cantidad;
                $producto->save();

                return response()->json([
                    'message' => 'Producto añadido al carrito con éxito'
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
            $producto = $carritoProducto->producto;

            if ($producto->stock >= $carritoProducto->cantidad) {
                $carritoProducto->increment('cantidad');

                $producto->stock -= 1;
                $producto->save();

                return response()->json([
                    'message' => 'Cantidad incrementada con éxito'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'El stock del producto está agotado'
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo incrementar 1 cantidad del Producto al Carrito'
            ], 500);
        }
    }

    public function decrementarItem(CarritoProducto $carritoProducto)
    {
        try {
            $producto = $carritoProducto->producto;

            if ($carritoProducto->cantidad > 1) {
                $carritoProducto->decrement('cantidad');

                $producto->stock += 1;
                $producto->save();

                return response()->json([
                    'message' => 'Cantidad decrementada con éxito'
                ], 200);
            } elseif ($carritoProducto->cantidad === 1) { //probar con 0
                $producto->stock += 1;
                $producto->save();

                $carritoProducto->delete();

                return response()->json([
                    'message' => 'Producto eliminado del carrito debido a la cantidad mínima'
                ], 204);
            } else {
                return response()->json([
                    'message' => 'La cantidad mínima es 1'
                ], 400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo decrementar 1 cantidad del Producto al Carrito'
            ], 500);
        }
    }

    public function eliminarItem(CarritoProducto $carritoProducto)
    {
        try {
            $producto = $carritoProducto->producto;

            $producto->stock += $carritoProducto->cantidad;
            $producto->save();

            $carritoProducto->delete();

            return response()->json([
                'message' => 'Producto eliminado del carrito con éxito'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo eliminar el producto del Carrito'
            ], 500);
        }
    }

    public function vaciarCarrito($id)
    {
        try {
            $user = auth()->user();
            $carrito = Carrito::where('user_id', $user->id)->first();

            if (!$carrito) {
                return response()->json([
                    'message' => 'No se encontró el carrito del usuario'
                ], 404);
            }

            $productos = $carrito->productos;

            foreach ($productos as $producto) {
                $producto->stock += $producto->pivot->cantidad;
                $producto->save();
            }

            $carrito->delete();

            return response()->json([
                'message' => 'Se ha vaciado el carrito del usuario',
                //'cart' => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo vaciar el carrito',
            ], 500);
        }
    }
}
