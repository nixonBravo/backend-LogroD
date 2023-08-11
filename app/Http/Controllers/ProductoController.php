<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:productos.allProductos')->only('allProductos');
        $this->middleware('can:productos.show')->only('show');
        $this->middleware('can:productos.search')->only('search');
        $this->middleware('can:productos.store')->only('store');
        $this->middleware('can:productos.update')->only('update');
        $this->middleware('can:productos.destroy')->only('destroy');
    }

    private $rules = array(
        'categoria_id' => 'required',
        'producto' => 'required|unique:productos,producto',
        'descripcion' => 'required',
        'precio' => 'required|numeric',
        'stock' => 'required|integer|min:0',
        'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    );
    private $messages = array(
        'categoria_id.required' => 'La categoria es requerida',
        'producto.required' => 'El producto es requerido',
        'producto.unique' => 'El producto ingresado ya existe',
        'descripcion.required' => 'La descripcion es requerida',
        'precio.required' => 'El precio es requerido',
        'precio.numeric' => 'Solo valores enteros o con decimales',
        'stock.required' => 'El stock es requerido',
        'stock.integer' => 'Solo numeros enteros',
        'stock.min' => 'El stock no puede ser menor a 0',
        'imagen.required' => 'Se requiere una imagen',
        'imagen.image' => 'Solo se premiten imagenes',
        'imagen.mines' => 'Tipo de imagen no valido',
    );

    private $rulesU = array(
        'categoria_id' => 'required',
        'producto' => 'required',
        'descripcion' => 'required',
        'precio' => 'required|numeric',
        'stock' => 'required|integer|min:0',
    );
    private $messagesU = array(
        'categoria_id.required' => 'La categorias es requerida',
        'producto.required' => 'El producto es requerido',
        'descripcion.required' => 'La descripcion es requerida',
        'precio.required' => 'El precio es requerido',
        'precio.numeric' => 'Solo valores enteros o con decimales',
        'stock.required' => 'El stock es requerido',
        'stock.integer' => 'Solo numeros enteros',
        'stock.min' => 'El stock no puede ser menor a 0'
    );

    public function search(Request $request)
    {
        try {
            $request->validate([
                'buscar' => 'required|string',
            ]);

            $search = $request->buscar;

            $resultados = Producto::where('estado_producto', true)
                ->where(function ($query) use ($search) {
                    $query->where('producto', 'LIKE', "%$search%")
                        ->orWhereHas('categoria', function ($query) use ($search) {
                            $query->where('estado_categoria', true)
                                ->where('categoria', 'LIKE', "%$search%");
                        });
                })
                ->paginate(10);

            if ($resultados->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron Productos o Categorías con ese nombre'
                ], 404);
            }

            return response()->json([
                'Resultados de Busqueda' => $resultados
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'No se pudo realizar la busqueda'
            ], 500);
        }
    }

    public function allProductos()
    {
        try {
            $productos = Producto::where('estado_producto', true)->paginate(20);

            if ($productos->isEmpty()) {
                return response()->json([
                    'massage' => 'No hay Productos Disponibles',
                ]);
            }
            return response()->json([
                'Productos' => $productos,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Obtener los Productos',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para ingresar productos',
                ], 403);
            }
            $validator = Validator::make($request->all(), $this->rules, $this->messages);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $file = $request->imagen;
            $obj = Cloudinary::upload($file->getRealPath(), ['folder' => 'products']);
            $public_id = $obj->getPublicId();
            $url = $obj->getSecurePath();

            $producto = new Producto([
                'categoria_id' => $request->categoria_id,
                'producto' => $request->producto,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'stock' => $request->stock,
                'public_id' => $public_id,
                'url' => $url,
            ]);
            $producto->save();

            return response()->json([
                'message' => 'Producto Creado Con Exito',
                'Producto' => $producto
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al ingresar un Producto',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::where('id', $id)->where('estado_producto', true)->first();
            if (!$producto) {
                return response()->json([
                    'message' => 'Producto no encontrado o Inactivo',
                ], 404);
            }

            if ($producto->stock == 0) {
                return response()->json([
                    'message' => 'Stock Agotado',
                ], 404);
            }

            return response()->json([
                'Productos' => $producto
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener el producto',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para actualizar productos',
                ], 403);
            }
            $validator = Validator::make($request->all(), $this->rulesU, $this->messagesU);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $producto = Producto::find($id);
            if ($producto->estado_producto == false) {
                return response()->json([
                    'message' => 'El producto ya a sido eliminado',
                ], 404);
            }
            if (!$producto) {
                return response()->json([
                    'message' => 'Producto No Encontrado o No Existe'
                ], 404);
            }

            $url = $producto->url;
            $public_id = $producto->public_id;
            if ($request->hasFile('imagen')) {
                Cloudinary::destroy($public_id);
                $file = request()->file('imagen');
                $obj = Cloudinary::upload($file->getRealPath(), ['folder' => 'products']);
                $url = $obj->getSecurePath();
                $public_id = $obj->getPublicId();
            }

            $producto->update([
                'categoria_id' => $request->categoria_id,
                'producto' => $request->producto,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'stock' => $request->stock,
                'public_id' => $public_id,
                'url' => $url,
            ]);
            return response()->json([
                'message' => 'Producto Actualizado Con Exito',
                'Producto' => $producto
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Actualizar el Producto',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar Productos',
                ], 403);
            }

            $producto = Producto::find($id);
            if ($producto->estado_producto == false) {
                return response()->json([
                    'message' => 'El Producto ya a sido eliminado',
                ], 404);
            }
            if (!$producto) {
                return response()->json([
                    'message' => 'Producto No Encontrado o No Existe',
                ], 404);
            }
            $producto->estado_producto = false;
            $producto->save();
            return response()->json([
                'message' => 'Producto Eliminado con Exito',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Eliminar el Producto',
            ], 500);
        }
    }
}
