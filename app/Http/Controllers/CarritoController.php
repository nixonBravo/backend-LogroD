<?php

namespace App\Http\Controllers;

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
        $this->middleware('can:carritos.add')->only('addProducto');
        $this->middleware('can:carritos.update')->only('actualizarCarrito');
        $this->middleware('can:carritos.decrementar')->only('decrementarProducto');
        $this->middleware('can:carritos.eliminar')->only('eliminarProducto');
        $this->middleware('can:carritos.vaciar')->only('vaciarCarrito');
    }

    public function verCarrito()
    {
        /* $user = Auth::user();
        $carrito = $user->carrito;
        return response()->json([
            'Carrito' => $carrito,
        ]); */

        try {
            $user = Auth::user();
            $carrito = $user->carrito;

            $productosEnCarrito = $carrito->productosEnCarrito;

            if ($productosEnCarrito->isEmpty()) {
                return response()->json([
                    'message' => 'No hay productos en el carrito'
                ]);
            }

            return response()->json([
                'productos' => $productosEnCarrito
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No hay productos en el carrito'
            ], 500);
        }
    }

    public function addProducto(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $usuario = Auth::user();
            $carrito = $usuario->carrito;

            // Verificar si el producto ya está en el carrito
            $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

            // Obtener el producto desde la base de datos
            $producto = Producto::findOrFail($id);

            if ($producto->stock <= 0) {
                return response()->json(['error' => 'No hay suficiente stock disponible'], 400);
            }

            if ($productoEnCarrito) {
                // Incrementar cantidad en el carrito
                $productoEnCarrito->cantidad++;
                $productoEnCarrito->save();
            } else {
                // Agregar producto al carrito
                $productoEnCarrito = new CarritoProducto([
                    'producto_id' => $id,
                    'cantidad' => 1,
                ]);
                $carrito->carritoProducto()->save($productoEnCarrito);
            }

            // Decrementar el stock
            $producto->stock--;
            $producto->save();

            // Commit de la transacción
            DB::commit();

            return response()->json(['message' => 'Producto agregado al carrito']);

        } catch (\Exception $e) {
            // Rollback en caso de error
            DB::rollBack();

            return response()->json(['error' => 'Error al agregar el producto al carrito'], 500);
        }

        return response()->json([
            'message' => 'Producto Agregado al Carrito'
        ]);
        /* DB::beginTransaction();

        try {
            $user = Auth::user();
            $carrito = $user->carrito;

            $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

            $producto = Producto::findOrFail($id);

            if ($productoEnCarrito) {
                $productoEnCarrito->cantidad++;
                $productoEnCarrito->save();
            } else {
                if ($producto->stock > 0) {
                    $producto->stock--;
                    $producto->save();

                    $productoEnCarrito = new CarritoProducto([
                        'producto_id' => $id,
                        'cantidad' => 1,
                    ]);
                    $carrito->carritoProducto()->save($productoEnCarrito);
                } else {
                    return response()->json([
                        'error' => 'No hay suficiente stock'
                    ], 400);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Producto agregado al carrito']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al agregar producto al carrito',
                'errors' => $e->getMessage()
            ], 500);
        } */
    }

    public function actualizarCarrito(Request $request, $id)
    {
        $user = Auth::user();
        $carrito = $user->carrito;

        $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

        if (!$productoEnCarrito) {
            return response()->json([
                'message' => 'Producto no encontrado en el Carrito',
            ], 404);
        }

        $nuevaCantidad = $request->cantidad;
        $productoEnCarrito->cantidad = $nuevaCantidad;
        $productoEnCarrito->save();
        return response()->json([
            'message' => 'Producto del Carrito Actualizado',
        ], 200);
    }

    public function decrementarProducto($id)
    {
        /* $user = Auth::user();
        $carrito = $user->carrito;

        $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

        if (!$productoEnCarrito) {
            return response()->json([
                'message' => 'Producto no encontrado en el carrito'
            ], 404);
        }

        if ($productoEnCarrito->cantidad > 1) {
            $productoEnCarrito->cantidad--;
            $productoEnCarrito->save();
        } else {
            $productoEnCarrito->delete();
        }

        return response()->json([
            'message' => 'Cantidad de Producto Decrementada'
        ]); */
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $carrito = $user->carrito;

            $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

            if (!$productoEnCarrito) {
                return response()->json([
                    'error' => 'Producto no encontrado en el carrito'
                ], 404);
            }

            $producto = Producto::findOrFail($id);
            $producto->stock++;
            $producto->save();

            if ($productoEnCarrito->cantidad > 1) {
                $productoEnCarrito->cantidad--;
                $productoEnCarrito->save();
            } else {
                $productoEnCarrito->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Cantidad del producto en el carrito decrementada'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al decrementar la cantidad del producto en el carrito'
            ], 500);
        }
    }

    public function eliminarProducto($id)
    {
        /* $user = Auth::user();
        $carrito = $user->carrito;

        $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

        if (!$productoEnCarrito) {
            return response()->json([
                'message' => 'Producto no encontrado en el Carrito',
            ], 404);
        }

        $productoEnCarrito->delete();

        return response()->json([
            'message' => 'Producto eliminado del Carrito',
        ]); */
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $carrito = $user->carrito;

            $productoEnCarrito = $carrito->carritoProducto()->where('producto_id', $id)->first();

            if (!$productoEnCarrito) {
                return response()->json([
                    'message' => 'Producto no encontrado en el carrito'
                ], 404);
            }

            $producto = Producto::findOrFail($id);
            $producto->stock++;
            $producto->save();

            $productoEnCarrito->delete();

            DB::commit();

            return response()->json([
                'message' => 'Producto eliminado del carrito'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al eliminar producto del carrito'
            ], 500);
        }
    }

    public function vaciarCarrito()
    {
        /* $user = Auth::user();
        $carrito = $user->carrito;

        $carrito->carritoProducto()->delete();
        return response()->json([
            'message' => 'Carrito Vacio',
        ], 200); */
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $carrito = $user->carrito;

            foreach ($carrito->productosEnCarrito as $productoEnCarrito) {
                $producto = Producto::findOrFail($productoEnCarrito->producto_id);
                $producto->stock += $productoEnCarrito->cantidad;
                $producto->save();
            }

            $carrito->productosEnCarrito()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Carrito vaciado'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Error al vaciar el carrito'
            ], 500);
        }
    }
}
