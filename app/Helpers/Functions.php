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
?>