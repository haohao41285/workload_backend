<?php

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		Permission::truncate();
		$arr = [
			[
				'name' => 'Tasks',
				'slug' => 'tasks',
			],
			[
				'name' => 'Trello Board',
				'slug' => 'trello-board',
			],
			[
				'name' => 'Users',
				'slug' => 'users',
			],
			[
				'name' => 'User Leader',
				'slug' => 'user-leader',
			],
			[
				'name' => 'User Admin',
				'slug' => 'user-admin',
			],
			[
				'name' => 'Roles',
				'slug' => 'roles',
			],
			[
				'name' => 'Perminssions',
				'slug' => 'permissions',
			],
			[
				'name' => 'Teams',
				'slug' => 'teams',
			],
			[
				'name' => 'Team Leader',
				'slug' => 'team-leader',
			],
			[
				'name' => 'Team Admin',
				'slug' => 'team-admin',
			],
			[
				'name' => 'Profile',
				'slug' => 'profiles',
			],
			[
				'name' => 'Project',
				'slug' => 'projects',
			],
			[
				'name' => 'Reports',
				'slug' => 'reports',
			],
			[
				'name' => 'Reports Leader',
				'slug' => 'reports-leader',
			],
			[
				'name' => 'Reports Admin',
				'slug' => 'reports-admin',
			],
		];
		Permission::insert($arr);
	}
}
