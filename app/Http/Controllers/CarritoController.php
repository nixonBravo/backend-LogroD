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
        $user = auth()->user();
        $carrito = $user->carritos()->where('estado', 'Activo')->first();

        if (!$carrito) {
            return response()->json(['message' => 'El carrito está vacío'], 404);
        }

        $productos = $carrito->productos;

        if ($productos->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 404);
        }

        return response()->json([
            'Productos en el Carrito' => $productos
        ]);
    }

    public function addItem(Request $request)
    {
        // Valida la solicitud y obtiene el ID del producto y la cantidad
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        // Obtiene el usuario autenticado
        $user = auth()->user();

        // Obtiene el producto seleccionado
        $producto = Producto::findOrFail($request->producto_id);

        // Verifica si hay suficiente stock disponible
        if ($producto->stock >= $request->cantidad) {
            // Crea o actualiza el carrito del usuario con el producto y cantidad
            $carrito = $user->carritos()->updateOrCreate(
                ['estado' => 'Activo'],
                ['estado' => 'Activo']
            );

            // Agrega el producto al carrito con la cantidad deseada
            $carrito->productos()->attach($producto->id, ['cantidad' => $request->cantidad]);

            // Disminuye el stock del producto
            $producto->stock -= $request->cantidad;
            $producto->save();

            return response()->json(['message' => 'Producto añadido al carrito con éxito']);
        } else {
            return response()->json(['message' => 'No hay suficiente stock o stock agotado'], 400);
        }
    }

    public function incrementarItem(CarritoProducto $carritoProducto)
    {
        $producto = $carritoProducto->producto;

        // Verifica si hay suficiente stock disponible
        if ($producto->stock >= $carritoProducto->cantidad) {
            $carritoProducto->increment('cantidad');

            // Disminuye el stock del producto incrementado
            $producto->stock -= 1;
            $producto->save();

            return response()->json(['message' => 'Cantidad incrementada con éxito']);
        } else {
            return response()->json(['message' => 'El stock del producto está agotado'], 400);
        }
    }

    public function decrementarItem(CarritoProducto $carritoProducto)
    {
        $producto = $carritoProducto->producto;

        if ($carritoProducto->cantidad > 1) {
            $carritoProducto->decrement('cantidad');

            // Aumenta el stock del producto decrementado
            $producto->stock += 1;
            $producto->save();

            return response()->json(['message' => 'Cantidad decrementada con éxito']);
        } elseif ($carritoProducto->cantidad === 1) { //0 talvez mñn se prueba
            // Elimina el producto del carrito y aumenta el stock
            $producto->stock += 1;
            $producto->save();

            $carritoProducto->delete();

            return response()->json(['message' => 'Producto eliminado del carrito debido a la cantidad mínima']);
        } else {
            return response()->json(['message' => 'La cantidad mínima es 1'], 400);
        }
    }

    public function eliminarItem(CarritoProducto $carritoProducto)
    {
        $producto = $carritoProducto->producto;

        // Aumenta el stock del producto eliminado del carrito
        $producto->stock += $carritoProducto->cantidad;
        $producto->save();

        $carritoProducto->delete();

        return response()->json(['message' => 'Producto eliminado del carrito con éxito']);
    }

    /* public function vaciarCarrito()
    {
        $user = auth()->user();
        $carrito = $user->carritos()->where('estado', 'Activo')->firstOrFail();

        if (!$carrito) {
            return response()->json([
                'message' => 'No se encontró el carrito del usuario'
            ], 404);
        }

        foreach ($carrito->productos as $carritoProducto) {
            $producto = $carritoProducto->producto;

            if ($producto) {
                // Aumenta el stock del producto eliminado del carrito
                //$producto->stock += $carritoProducto->cantidad;
                $producto->stock += $carritoProducto->pivot->cantidad;
                $producto->save();
            }
        }

        $carrito->productos()->detach();

        return response()->json(['message' => 'Carrito vaciado con éxito']);
    } */

    public function vaciarCarrito($id)
    {
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
    }
}
