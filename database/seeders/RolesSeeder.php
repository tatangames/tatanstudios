<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // este es para mi
        $roleAdmin = Role::create(['name' => 'admin', 'guard_name' => 'admin']);

        // este es para dueno de pagina web
        $roleEditor = Role::create(['name' => 'editor', 'guard_name' => 'admin']);


        // solo para administrador
        Permission::create(['name' => 'admin.sidebar.roles.y.permisos', 'description' => 'Sidebar Admin seccion roles y permisos'])->syncRoles($roleAdmin);

        // Editor
        Permission::create(['name' => 'editor.sidebar.dashboard', 'description' => 'Sidebar dashboard para editor'])->syncRoles($roleEditor);


    }
}
