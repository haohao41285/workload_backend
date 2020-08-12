<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtendTasksTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('extend_tasks', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->dateTime('expired');
			$table->text('note')->nullable();
			$table->integer('id_detail_task')->comment('id from task_detail table');
			$table->integer('id_task')->comment('id from tasks');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('extend_tasks');
	}
}
