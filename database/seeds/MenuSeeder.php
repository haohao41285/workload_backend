<?php

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		Menu::truncate();
		$menu_arr = [
			[
				'name' => 'Dashboard',
				'url' => '/dashboard',
				'icon' => 'icon-speedometer',
				'id_parent' => 0,
			],
			[
				'name' => 'Tasks',
				'url' => '/tasks',
				'icon' => 'fa fa-tasks',
				'id_parent' => 0,
			],
			[
				'name' => 'Trello Board',
				'url' => '/trello-board',
				'icon' => 'icon-grid',
				'id_parent' => 0,
			],
			[
				'name' => 'Users',
				'url' => '/users',
				'icon' => 'icon-user',
				'id_parent' => 0,
			],
			[
				'name' => 'List',
				'id_parent' => 4,
				'url' => "/users/list",
				'icon' => 'icon-user',
			],
			[
				'name' => 'Role',
				'id_parent' => 4,
				'url' => '/users/roles',
				'icon' => 'icon-people',
			],
			[
				'name' => 'Permission',
				'id_parent' => 4,
				'url' => '/users/permissions',
				'icon' => 'icon-shield',
			],
			[
				'name' => 'Teams',
				'url' => '/teams',
				'icon' => 'icon-people',
				'id_parent' => 0,
			],
			[
				'name' => 'Project',
				'url' => '/projects',
				'icon' => 'fa fa-product-hunt',
				'id_parent' => 0,
			],
			[
				'name' => 'Reports',
				'url' => '/reports',
				'icon' => 'icon-notebook',
				'id_parent' => 0,
			],
		];

		Menu::insert($menu_arr);
	}
}
