<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{

    public function allProductos()
    {
        $productos = Producto::where('estado_producto', true)->paginate(20);
        return response()->json([
            'status' => 1,
            'Productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Producto $producto)
    {
        //
    }

    public function update(Request $request, Producto $producto)
    {
        //
    }

    public function destroy(Producto $producto)
    {
        //
    }
}
