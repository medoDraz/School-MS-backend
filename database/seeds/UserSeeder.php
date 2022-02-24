<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user=\App\User::create([
            'name'=>'Administrator Support',
            'first_name' =>'Administrator',
            'last_name' =>'Support',
            'email' =>'administrator@sms.com',
            'gender' =>'male',
            'password' =>bcrypt('123456'),
            'status' =>1,
        ]);
//        $user->attachRole('super_admin');
        $role = \Spatie\Permission\Models\Role::first();
        $user->assignRole($role->id);
    }
}
