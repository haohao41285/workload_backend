<?php

use Illuminate\Database\Seeder;
use App\User;
use Hash;
use App\Models\Role;
use App\Models\RolePermission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       User::create([
       	'name' => 'admin',
       	'full_name' => 'Nguyen Van Admin',
       	'email' => 'admin@vietguys.biz',
       	'id_trello' => 'empty',
       	'password' => Hash::make('Admin123456@')
       	'id_role' => 1
       ]);

       Role::create([
       	'name' => 'admin',
       	'active' => 1,
       	'created_by' => 1,
       ]);
       RolePermission::create([
       	'id_role' => 1,
       	'permissions' => '1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16',
       	'active' => 1
       ]);
    }
}
