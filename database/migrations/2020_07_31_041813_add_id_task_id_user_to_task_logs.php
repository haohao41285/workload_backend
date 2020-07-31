<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdTaskIdUserToTaskLogs extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('task_logs', function (Blueprint $table) {
			$table->integer('id_task')->after('id_task_detail');
			$table->integer('id_user')->after('id_task_detail');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('task_logs', function (Blueprint $table) {
			//
		});
	}
}
