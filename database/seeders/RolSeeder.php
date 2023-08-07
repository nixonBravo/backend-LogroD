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

        Permission::create(['name' => 'productos.allProductos'])->syncRoles([$rol1, $rol2]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.ec',
            'password' => bcrypt('12345678'),
        ])->assignRole('Admin');
    }
}
