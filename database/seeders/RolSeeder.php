<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rol1 = Role::create(['name' => 'Admin']);
        $rol2 = Role::create(['name' => 'Cliente']);
        //Categoria
        Permission::create(['name' => 'categorias.allCategorias'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'categorias.show'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'categorias.store'])->syncRoles([$rol1]);
        Permission::create(['name' => 'categorias.update'])->syncRoles([$rol1]);
        Permission::create(['name' => 'categorias.destroy'])->syncRoles([$rol1]);
        //Producto
        Permission::create(['name' => 'productos.allProductos'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.show'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.search'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.store'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.update'])->syncRoles([$rol1]);
        Permission::create(['name' => 'productos.destroy'])->syncRoles([$rol1]);
        //Carrito
        Permission::create(['name' => 'carritos.ver'])->syncRoles([$rol2]);
        Permission::create(['name' => 'carritos.add'])->syncRoles([$rol2]);
        Permission::create(['name' => 'carritos.incrementar'])->syncRoles([$rol2]);
        Permission::create(['name' => 'carritos.decrementar'])->syncRoles([$rol2]);
        Permission::create(['name' => 'carritos.eliminar'])->syncRoles([$rol2]);
        Permission::create(['name' => 'carritos.vaciar'])->syncRoles([$rol2]);
        //Pedido
        Permission::create(['name' => 'pedidos.comprar'])->syncRoles([$rol2]);
        Permission::create(['name' => 'pedidos.ver'])->syncRoles([$rol2]);


        User::create([
            'cedula' => '1302580739',
            'nombre' => 'Miguel',
            'apellido' => 'Alcivar',
            'email' => 'admin@admin.com',
            'password' => bcrypt('12345678'),
        ])->assignRole('Admin');

        User::create([
            'cedula' => '0985230178',
            'nombre' => 'Belen',
            'apellido' => 'Cedeño',
            'email' => 'prueba@gmail.com',
            'password' => bcrypt('12345678'),
        ])->assignRole('Cliente');
    }
}
