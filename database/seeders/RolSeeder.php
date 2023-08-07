<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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

        Permission::create(['name' => 'categorias.allCategorias'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'categorias.show'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'categorias.store'])->syncRoles([$rol1]);
        Permission::create(['name' => 'categorias.update'])->syncRoles([$rol1]);
        Permission::create(['name' => 'categorias.destroy'])->syncRoles([$rol1]);

        Permission::create(['name' => 'productos.allProductos'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.show'])->syncRoles([$rol1, $rol2]);
        Permission::create(['name' => 'productos.store'])->syncRoles([$rol1]);
        Permission::create(['name' => 'productos.update'])->syncRoles([$rol1]);
        Permission::create(['name' => 'productos.destroy'])->syncRoles([$rol1]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.ec',
            'password' => bcrypt('12345678'),
        ])->assignRole('Admin');
        User::create([
            'name' => 'Prueba',
            'email' => 'prueba@gmail.com',
            'password' => bcrypt('12345678'),
        ])->assignRole('Cliente');
    }
}
