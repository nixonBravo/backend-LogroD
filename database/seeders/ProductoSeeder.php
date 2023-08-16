<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoria1 = Categoria::create([
            'categoria' => 'Bebidas'
        ]);

        Producto::create([
            'categoria_id' => $categoria1->id,
            'producto' => 'Coca Cola',
            'descripcion' => 'Coca Cola 3 Litros',
            'precio' => 3,
            'stock' => 10,
            'public_id' => '',
            'url' => ''
        ]);
        $categoria2 = Categoria::create([
            'categoria' => 'Computadoras'
        ]);

        Producto::create([
            'categoria_id' => $categoria2->id,
            'producto' => 'Dell',
            'descripcion' => 'Dell sdnivdsiv',
            'precio' => 3,
            'stock' => 10,
            'public_id' => '',
            'url' => ''
        ]);

        $categoria3 = Categoria::create([
            'categoria' => 'Electrodomestricos'
        ]);
        $categoria4 = Categoria::create([
            'categoria' => 'Farmacia'
        ]);
        $categoria5 = Categoria::create([
            'categoria' => 'Camaras'
        ]);
        $categoria6 = Categoria::create([
            'categoria' => 'Aseo'
        ]);
        $categoria7 = Categoria::create([
            'categoria' => 'Accesoriarios'
        ]);
        $categoria8 = Categoria::create([
            'categoria' => 'Ropa'
        ]);
        $categoria9 = Categoria::create([
            'categoria' => 'Categoria Cualquiera'
        ]);

    }
}
