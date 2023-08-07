<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:categorias.allCategorias')->only('allCategorias');
        $this->middleware('can:categorias.show')->only('show');
        $this->middleware('can:categorias.store')->only('store');
        $this->middleware('can:categorias.update')->only('update');
        $this->middleware('can:categorias.destroy')->only('destroy');
    }

    private $rules = array(
        'categoria' => 'required|unique:categorias,categoria'
    );
    private $messages = array(
        'categoria.required' => 'La categoria es requerida',
        'categoria.unique' => 'La categoria ingresada ya existe',
    );

    private $rulesU = array(
        'categoria' => 'required',
    );
    private $messagesU = array(
        'categoria' => 'La categoria es requerida',
    );

    public function allCategorias()
    {
        try {
            $categorias = Categoria::where('estado_categoria', true)->get();

            if ($categorias->isEmpty()) {
                return response()->json([
                    'massage' => 'No hay Categorias Disponibles',
                ]);
            }
            return response()->json([
                'Categorias' => $categorias,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Obtener las Categorias',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para ingresar categorias',
                ], 403);
            }
            $validator = Validator::make($request->all(), $this->rules, $this->messages);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $categoria = new Categoria([
                'categoria' => $request->categoria,
            ]);
            $categoria->save();

            return response()->json([
                'message' => 'Categoria Creada Con Exito',
                'Categoria' => $categoria
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al ingresar una Categoria',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $categoria = Categoria::where('id', $id)->where('estado_categoria', true)->first();
            if (!$categoria) {
                return response()->json([
                    'message' => 'Categoría no encontrada o inactiva',
                ], 404);
            }

            $productos = Producto::where('estado_producto', true)->where('categoria_id', $categoria->id)->get();

            if ($productos->isEmpty()) {
                return response()->json([
                    'message' => 'No hay productos disponibles en esta categoría',
                ], 404);
            }

            return response()->json([
                'Productos' => $productos,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener los productos'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para actualizar categorias',
                ], 403);
            }
            $validator = Validator::make($request->all(), $this->rulesU, $this->messagesU);
            if ($validator->fails()) {
                $messages = $validator->messages();
                return response()->json([
                    'messages' => $messages,
                ], 422);
            }

            $categoria = Categoria::find($id);
            if ($categoria->estado_categoria == false) {
                return response()->json([
                    'message' => 'La Categoria ya a sido eliminada',
                ], 404);
            }
            if (!$categoria) {
                return response()->json([
                    'message' => 'Categoria No Encontrada o No Existe'
                ], 404);
            }
            $categoria->update([
                'categoria' => $request->categoria,
            ]);
            return response()->json([
                'message' => 'Categoria Actualizada Con Exito',
                'Categoria' => $categoria
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Actualizar la Categoria',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::user()->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tienes permisos para eliminar categorias',
                ], 403);
            }

            $categoria = Categoria::find($id);
            if ($categoria->estado_categoria == false) {
                return response()->json([
                    'message' => 'La Categoria ya a sido eliminada',
                ], 404);
            }
            if (!$categoria) {
                return response()->json([
                    'message' => 'Categoria No Encontrada o No Existe',
                ], 404);
            }
            $categoria->estado_categoria = false;
            $categoria->save();
            return response()->json([
                'message' => 'Categoria Eliminada con Exito',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al Eliminar la Categoria',
            ], 500);
        }
    }
}
