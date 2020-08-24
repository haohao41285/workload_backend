<?php
function getTeamTree($teams, $id_parent = 0, $str = "") {
	$team_arr = [];
	foreach ($teams as $key => $team) {
		if ($team['id_parent'] == $id_parent) {
			$team_arr[] = [
				'text' => $str,
				'id' => $team['id'],
				'name' => $team['name'],
			];
			unset($teams[$key]);
			$team_child = getTeamTree($teams, $team['id'], $str . "|______");
			if (count($team_child) > 0) {
				$team_arr = array_merge($team_arr, $team_child);
			}
		}
	}
	return $team_arr;
}
function getIdChildTeam($teams, $id_parent) {
	$team_arr = [];

	foreach ($teams as $key => $team) {
		if ($team['id_parent'] == $id_parent) {
			$team_arr[] = $team['id'];
			$team_child = getIdChildTeam($teams, $team['id']);

			if (count($team_child) > 0) {
				$team_arr = array_merge($team_arr, $team_child);
			}
		}
		// unset($teams[$key]);

	}
	return $team_arr;
}
function statusTask() {
	return [
		1 => 'NEW',
		2 => 'PROGRESSING',
		3 => 'DONE',
		4 => 'REOPEN',
	];
}

function menuTree($menus, $id_parent = 0) {
	$menu_arr = [];
	foreach ($menus as $key => $menu) {
		if ($menu['id_parent'] == $id_parent) {
			$menu_arr[$key] = [
				'url' => $menu['url'],
				'icon' => $menu['icon'],
				'name' => $menu['name'],
			];
			unset($menus[$key]);
			$team_child = menuTree($menus, $menu['id']);
			if (count($team_child) > 0) {
				$menu_arr[$key]['children'] = $team_child;
			}
		}
	}
	return $menu_arr;
}

//Get Child Team Tree
function getChildTeamTree($teams, $id_parent, $str = "") {
	$team_arr = [];

	foreach ($teams as $key => $team) {
		if ($team['id_parent'] == $id_parent) {
			$team_arr[] = [
				'text' => $str,
				'id' => $team['id'],
				'name' => $team['name'],
			];
			$team_child = getIdChildTeam($teams, $team['id'], $str . "|______");

			if (count($team_child) > 0) {
				$team_arr = array_merge($team_arr, $team_child);
			}
		}
		// unset($teams[$key]);

	}
	return $team_arr;
}

//check permission
function checkPermission($permission, $token) {
	try {
		$permission_id = \App\Models\Permission::where('slug', $permission)->first()->id;
		$user = \App\User::where('_token_api', $token)->first();
		$permission_list = \App\Models\RolePermission::where('id_role', $user->id_role)->first()->permissions;
		$permission_list = explode(';', $permission_list);

		if (in_array(intval($permission_id), $permission_list)) {
			return true;
		} else {
			return false;
		}
	} catch (\Exception $e) {
		\Log::info($e);
		return false;
	}

}
?>