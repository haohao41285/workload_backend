<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusExtendTasks extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('extend_tasks', function (Blueprint $table) {
			$table->string('status', 4)->default(0)->comment('0: New, 1: active, 2: cancel')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('extend_tasks', function (Blueprint $table) {
			//
		});
	}
}
