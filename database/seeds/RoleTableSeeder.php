<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->delete();

        $perms = \Spatie\Permission\Models\Permission::all();
        $perms_name = [];
        foreach ($perms as $p) {
            array_push($perms_name, $p->name);
        }

        $role = \Spatie\Permission\Models\Role::create([
            'name' => 'Administrator',
            'description' => 'Administrator',
            'status' => 1,

        ]);

        foreach ($perms_name as $perm_name) {
            $role->givePermissionTo($perm_name);

        }
    }
}
